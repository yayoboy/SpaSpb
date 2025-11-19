#!/bin/bash

# SpaSpb Page Builder - Setup Volume Script
# Questo script crea e configura la directory data/ con limite di 1GB

set -e

# Colori per output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configurazione
DATA_DIR="./data"
MAX_SIZE_GB=1
MAX_SIZE_MB=$((MAX_SIZE_GB * 1024))

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  SpaSpb Page Builder - Setup Volume da 1GB${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

# Verifica se esiste già
if [ -d "$DATA_DIR" ]; then
    echo -e "${YELLOW}⚠️  La directory $DATA_DIR esiste già!${NC}"
    read -p "Vuoi ricrearla? (s/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo -e "${BLUE}ℹ️  Operazione annullata${NC}"
        exit 0
    fi
    echo -e "${YELLOW}🗑️  Rimozione directory esistente...${NC}"
    rm -rf "$DATA_DIR"
fi

# Crea struttura directory
echo -e "${BLUE}📁 Creazione struttura directory...${NC}"
mkdir -p "$DATA_DIR"/{db,uploads,assets/css,assets/img,export}
echo -e "${GREEN}✓ Directory create${NC}"

# Copia file esistenti se presenti
echo ""
echo -e "${BLUE}📋 Migrazione file esistenti...${NC}"

# Copia database
if [ -f "./builder/db/data.db" ]; then
    echo -e "  ${YELLOW}→${NC} Copia database SQLite..."
    cp -r ./builder/db/* "$DATA_DIR/db/" 2>/dev/null || true
    echo -e "  ${GREEN}✓${NC} Database copiato"
else
    echo -e "  ${BLUE}ℹ️${NC}  Nessun database trovato (sarà creato al primo avvio)"
fi

# Copia uploads
if [ -d "./builder/uploads" ] && [ "$(ls -A ./builder/uploads 2>/dev/null | grep -v '.gitkeep')" ]; then
    echo -e "  ${YELLOW}→${NC} Copia immagini caricate..."
    cp -r ./builder/uploads/* "$DATA_DIR/uploads/" 2>/dev/null || true
    echo -e "  ${GREEN}✓${NC} Uploads copiati"
else
    echo -e "  ${BLUE}ℹ️${NC}  Nessun upload trovato"
fi

# Copia assets
if [ -d "./assets" ] && [ "$(ls -A ./assets 2>/dev/null | grep -v '.gitkeep')" ]; then
    echo -e "  ${YELLOW}→${NC} Copia assets esportati..."
    cp -r ./assets/* "$DATA_DIR/assets/" 2>/dev/null || true
    echo -e "  ${GREEN}✓${NC} Assets copiati"
else
    echo -e "  ${BLUE}ℹ️${NC}  Nessun asset trovato"
fi

# Mantieni i .gitkeep
touch "$DATA_DIR/db/.gitkeep"
touch "$DATA_DIR/uploads/.gitkeep"
touch "$DATA_DIR/assets/img/.gitkeep"
touch "$DATA_DIR/export/.gitkeep"

# Imposta permessi
echo ""
echo -e "${BLUE}🔒 Configurazione permessi...${NC}"
chmod -R 755 "$DATA_DIR"

# Se siamo su Linux/Mac, proviamo a impostare ownership
if command -v chown &> /dev/null; then
    # Prova a determinare l'utente www-data (33 su Linux)
    if id www-data &> /dev/null; then
        echo -e "  ${YELLOW}→${NC} Impostazione owner www-data..."
        sudo chown -R www-data:www-data "$DATA_DIR" 2>/dev/null || {
            echo -e "  ${YELLOW}⚠️${NC}  Impossibile impostare www-data (richiede sudo)"
            echo -e "  ${BLUE}ℹ️${NC}  I permessi saranno gestiti dal container Docker"
        }
    else
        echo -e "  ${BLUE}ℹ️${NC}  www-data non trovato (normale su macOS)"
    fi
fi
echo -e "${GREEN}✓ Permessi configurati${NC}"

# Verifica spazio disponibile
echo ""
echo -e "${BLUE}💾 Verifica spazio disco...${NC}"

# Calcola spazio usato
USED_SPACE=$(du -sm "$DATA_DIR" 2>/dev/null | cut -f1)
USED_PERCENT=$((USED_SPACE * 100 / MAX_SIZE_MB))

echo -e "  ${BLUE}ℹ️${NC}  Spazio utilizzato: ${YELLOW}${USED_SPACE}MB${NC} / ${MAX_SIZE_MB}MB (${USED_PERCENT}%)"

if [ $USED_SPACE -gt $MAX_SIZE_MB ]; then
    echo -e "  ${RED}⚠️  ATTENZIONE: Spazio già superiore al limite di 1GB!${NC}"
    echo -e "  ${YELLOW}→${NC} Considera di rimuovere alcuni file o aumentare il limite"
elif [ $USED_PERCENT -gt 80 ]; then
    echo -e "  ${YELLOW}⚠️  ATTENZIONE: Spazio utilizzato oltre l'80%${NC}"
else
    echo -e "  ${GREEN}✓${NC} Spazio disponibile OK"
fi

# Crea file di configurazione volume
cat > "$DATA_DIR/.volume-info" <<EOF
# SpaSpb Page Builder - Volume Info
CREATED="$(date '+%Y-%m-%d %H:%M:%S')"
MAX_SIZE_GB=$MAX_SIZE_GB
MAX_SIZE_MB=$MAX_SIZE_MB
INITIAL_SIZE_MB=$USED_SPACE
EOF

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Setup volume completato con successo!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}📊 Struttura creata:${NC}"
echo -e "  $DATA_DIR/"
echo -e "  ├── db/           ${YELLOW}(Database SQLite)${NC}"
echo -e "  ├── uploads/      ${YELLOW}(Immagini caricate)${NC}"
echo -e "  ├── assets/       ${YELLOW}(CSS/immagini esportate)${NC}"
echo -e "  │   ├── css/"
echo -e "  │   └── img/"
echo -e "  └── export/       ${YELLOW}(File temporanei export)${NC}"
echo ""
echo -e "${BLUE}📝 Prossimi passi:${NC}"
echo -e "  1. Esegui: ${YELLOW}make start${NC} per avviare il container"
echo -e "  2. Usa: ${YELLOW}make check-space${NC} per monitorare lo spazio"
echo -e "  3. Accedi: ${YELLOW}http://localhost:8080/builder/${NC}"
echo ""
