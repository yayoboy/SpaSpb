#!/bin/bash

# SpaSpb Page Builder - Monitor Space Script
# Questo script monitora l'uso dello spazio nel volume data/

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

# Verifica che la directory esista
if [ ! -d "$DATA_DIR" ]; then
    echo -e "${RED}❌ Errore: Directory $DATA_DIR non trovata${NC}"
    echo -e "${YELLOW}→${NC} Esegui prima: ${BLUE}make setup-volume${NC}"
    exit 1
fi

# Funzione per convertire byte in formato leggibile
human_readable() {
    local bytes=$1
    if [ $bytes -lt 1024 ]; then
        echo "${bytes}B"
    elif [ $bytes -lt $((1024 * 1024)) ]; then
        echo "$(( bytes / 1024 ))KB"
    elif [ $bytes -lt $((1024 * 1024 * 1024)) ]; then
        echo "$(( bytes / 1024 / 1024 ))MB"
    else
        echo "$(( bytes / 1024 / 1024 / 1024 ))GB"
    fi
}

# Funzione per creare barra di progresso
progress_bar() {
    local percent=$1
    local width=50
    local filled=$((percent * width / 100))
    local empty=$((width - filled))

    # Colore in base alla percentuale
    local color=$GREEN
    if [ $percent -gt 80 ]; then
        color=$RED
    elif [ $percent -gt 60 ]; then
        color=$YELLOW
    fi

    echo -ne "${color}["
    printf '%*s' "$filled" | tr ' ' '█'
    printf '%*s' "$empty" | tr ' ' '░'
    echo -ne "]${NC} ${percent}%"
}

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  SpaSpb Page Builder - Monitor Spazio Volume${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

# Calcola spazio totale
echo -e "${BLUE}📊 Analisi spazio...${NC}"
TOTAL_USED=$(du -sm "$DATA_DIR" 2>/dev/null | cut -f1)
TOTAL_PERCENT=$((TOTAL_USED * 100 / MAX_SIZE_MB))

# Spazio per sottodirectory
DB_SIZE=$(du -sm "$DATA_DIR/db" 2>/dev/null | cut -f1 || echo "0")
UPLOADS_SIZE=$(du -sm "$DATA_DIR/uploads" 2>/dev/null | cut -f1 || echo "0")
ASSETS_SIZE=$(du -sm "$DATA_DIR/assets" 2>/dev/null | cut -f1 || echo "0")
EXPORT_SIZE=$(du -sm "$DATA_DIR/export" 2>/dev/null | cut -f1 || echo "0")

# Conta file
DB_FILES=$(find "$DATA_DIR/db" -type f 2>/dev/null | wc -l | tr -d ' ')
UPLOAD_FILES=$(find "$DATA_DIR/uploads" -type f 2>/dev/null | wc -l | tr -d ' ')
ASSET_FILES=$(find "$DATA_DIR/assets" -type f 2>/dev/null | wc -l | tr -d ' ')

echo ""
echo -e "${BLUE}╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║${NC}  ${YELLOW}Spazio Totale Utilizzato${NC}                          ${BLUE}║${NC}"
echo -e "${BLUE}╠═══════════════════════════════════════════════════╣${NC}"
echo -e "${BLUE}║${NC}  $(progress_bar $TOTAL_PERCENT) ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}  ${TOTAL_USED}MB / ${MAX_SIZE_MB}MB                                   ${BLUE}║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════╝${NC}"

echo ""
echo -e "${BLUE}📁 Dettaglio per directory:${NC}"
echo ""

# Database
DB_PERCENT=$((DB_SIZE * 100 / MAX_SIZE_MB))
echo -e "  ${YELLOW}▸${NC} Database (db/)"
echo -e "    Spazio: ${BLUE}${DB_SIZE}MB${NC} ($DB_PERCENT%)"
echo -e "    File:   ${DB_FILES}"
echo ""

# Uploads
UPLOADS_PERCENT=$((UPLOADS_SIZE * 100 / MAX_SIZE_MB))
echo -e "  ${YELLOW}▸${NC} Uploads (uploads/)"
echo -e "    Spazio: ${BLUE}${UPLOADS_SIZE}MB${NC} ($UPLOADS_PERCENT%)"
echo -e "    File:   ${UPLOAD_FILES}"
echo ""

# Assets
ASSETS_PERCENT=$((ASSETS_SIZE * 100 / MAX_SIZE_MB))
echo -e "  ${YELLOW}▸${NC} Assets (assets/)"
echo -e "    Spazio: ${BLUE}${ASSETS_SIZE}MB${NC} ($ASSETS_PERCENT%)"
echo -e "    File:   ${ASSET_FILES}"
echo ""

# Export
EXPORT_PERCENT=$((EXPORT_SIZE * 100 / MAX_SIZE_MB))
echo -e "  ${YELLOW}▸${NC} Export (export/)"
echo -e "    Spazio: ${BLUE}${EXPORT_SIZE}MB${NC} ($EXPORT_PERCENT%)"
echo ""

# File più grandi
echo -e "${BLUE}📦 Top 10 file più grandi:${NC}"
echo ""
find "$DATA_DIR" -type f -exec du -h {} + 2>/dev/null | sort -rh | head -10 | while read size file; do
    # Rimuovi il prefixo del path
    short_file=${file#$DATA_DIR/}
    echo -e "  ${YELLOW}${size}${NC}  ${short_file}"
done

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"

# Avvisi
if [ $TOTAL_PERCENT -gt 90 ]; then
    echo -e "${RED}⚠️  ATTENZIONE CRITICA: Spazio quasi esaurito!${NC}"
    echo -e "${YELLOW}→${NC} Azioni consigliate:"
    echo -e "  1. Rimuovi file non necessari da ${DATA_DIR}/"
    echo -e "  2. Esegui backup: ${BLUE}make db-backup${NC}"
    echo -e "  3. Pulisci export temporanei: ${BLUE}rm -rf ${DATA_DIR}/export/*${NC}"
elif [ $TOTAL_PERCENT -gt 80 ]; then
    echo -e "${YELLOW}⚠️  ATTENZIONE: Spazio oltre l'80%${NC}"
    echo -e "${YELLOW}→${NC} Considera di liberare spazio o aumentare il limite"
elif [ $TOTAL_PERCENT -gt 60 ]; then
    echo -e "${YELLOW}ℹ️  Spazio oltre il 60%, monitora regolarmente${NC}"
else
    echo -e "${GREEN}✓ Spazio disponibile OK${NC}"
fi

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"

# Leggi info volume se esiste
if [ -f "$DATA_DIR/.volume-info" ]; then
    echo ""
    echo -e "${BLUE}ℹ️  Informazioni volume:${NC}"
    . "$DATA_DIR/.volume-info"
    echo -e "  Creato:        ${CREATED}"
    echo -e "  Limite:        ${MAX_SIZE_GB}GB (${MAX_SIZE_MB}MB)"
    echo -e "  Spazio iniziale: ${INITIAL_SIZE_MB}MB"
    GROWTH=$((TOTAL_USED - INITIAL_SIZE_MB))
    if [ $GROWTH -gt 0 ]; then
        echo -e "  Crescita:      ${YELLOW}+${GROWTH}MB${NC}"
    else
        echo -e "  Crescita:      ${GREEN}${GROWTH}MB${NC}"
    fi
fi

echo ""

# Modalità watch (opzionale)
if [ "$1" == "--watch" ] || [ "$1" == "-w" ]; then
    echo -e "${BLUE}🔄 Modalità watch attiva (aggiornamento ogni 5 secondi)${NC}"
    echo -e "${YELLOW}Premi Ctrl+C per uscire${NC}"
    echo ""
    while true; do
        sleep 5
        clear
        $0  # Richiama se stesso senza --watch
    done
fi

# Modalità JSON (per script esterni)
if [ "$1" == "--json" ]; then
    cat <<EOF
{
  "total_mb": $TOTAL_USED,
  "total_percent": $TOTAL_PERCENT,
  "max_mb": $MAX_SIZE_MB,
  "db_mb": $DB_SIZE,
  "uploads_mb": $UPLOADS_SIZE,
  "assets_mb": $ASSETS_SIZE,
  "export_mb": $EXPORT_SIZE,
  "db_files": $DB_FILES,
  "upload_files": $UPLOAD_FILES,
  "asset_files": $ASSET_FILES
}
EOF
fi
