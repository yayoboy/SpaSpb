#!/bin/bash

# SpaSpb Page Builder - Docker Start Script
# Avvia il page builder in un container Docker

set -e

echo "🎨 SpaSpb Page Builder - Docker Setup"
echo "====================================="
echo ""

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verifica che Docker sia installato
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker non è installato!${NC}"
    echo "Installa Docker da: https://docs.docker.com/get-docker/"
    exit 1
fi

# Verifica che docker-compose sia installato
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ docker-compose non è installato!${NC}"
    echo "Installa docker-compose da: https://docs.docker.com/compose/install/"
    exit 1
fi

echo -e "${GREEN}✓${NC} Docker e docker-compose trovati"
echo ""

# Vai nella directory root del progetto
cd "$(dirname "$0")/.."

# Crea directory necessarie se non esistono
echo -e "${BLUE}📁 Creazione directory...${NC}"
mkdir -p builder/db
mkdir -p builder/uploads
mkdir -p assets/css
mkdir -p assets/img

# Imposta permessi (se su Linux/Mac)
if [[ "$OSTYPE" == "linux-gnu"* ]] || [[ "$OSTYPE" == "darwin"* ]]; then
    echo -e "${BLUE}🔧 Impostazione permessi...${NC}"
    chmod -R 755 builder/db builder/uploads assets
fi

echo -e "${GREEN}✓${NC} Directory create"
echo ""

# Verifica se il container è già in esecuzione
if docker ps | grep -q spaspb-pagebuilder; then
    echo -e "${YELLOW}⚠️  Container già in esecuzione${NC}"
    echo ""
    read -p "Vuoi riavviarlo? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        echo -e "${BLUE}🔄 Riavvio container...${NC}"
        docker-compose down
    else
        echo -e "${GREEN}✓${NC} Container già attivo su http://localhost:8080"
        exit 0
    fi
fi

# Build dell'immagine
echo -e "${BLUE}🏗️  Build dell'immagine Docker...${NC}"
echo "Questo potrebbe richiedere alcuni minuti alla prima esecuzione..."
echo ""

if docker-compose build; then
    echo -e "${GREEN}✓${NC} Build completata"
else
    echo -e "${RED}❌ Errore durante il build${NC}"
    exit 1
fi

echo ""

# Avvia i container
echo -e "${BLUE}🚀 Avvio container...${NC}"
echo ""

if docker-compose up -d; then
    echo -e "${GREEN}✓${NC} Container avviato"
else
    echo -e "${RED}❌ Errore durante l'avvio${NC}"
    exit 1
fi

echo ""

# Attendi che il servizio sia pronto
echo -e "${BLUE}⏳ Attendo che il servizio sia pronto...${NC}"
sleep 3

# Verifica che il container sia in esecuzione
if docker ps | grep -q spaspb-pagebuilder; then
    echo -e "${GREEN}✓${NC} Container in esecuzione"
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}✅ SpaSpb Page Builder è pronto!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "📍 Accedi al builder su: ${BLUE}http://localhost:8080/builder/${NC}"
    echo ""
    echo -e "🔑 Credenziali di default:"
    echo -e "   Username: ${YELLOW}admin${NC}"
    echo -e "   Password: ${YELLOW}admin123${NC}"
    echo ""
    echo -e "${YELLOW}⚠️  IMPORTANTE: Cambia le credenziali in builder/config.php prima di usare in produzione!${NC}"
    echo ""
    echo -e "📚 Comandi utili:"
    echo -e "   ${BLUE}docker-compose logs -f${NC}        - Mostra i logs"
    echo -e "   ${BLUE}docker-compose stop${NC}           - Ferma il container"
    echo -e "   ${BLUE}docker-compose down${NC}           - Ferma e rimuove il container"
    echo -e "   ${BLUE}docker-compose restart${NC}        - Riavvia il container"
    echo ""
else
    echo -e "${RED}❌ Errore: il container non è in esecuzione${NC}"
    echo ""
    echo "Controlla i logs con: docker-compose logs"
    exit 1
fi
