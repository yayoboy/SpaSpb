# Setup Docker con Volume Unificato da 1GB

Questa documentazione descrive il sistema di volume unificato da 1GB implementato per SpaSpb Page Builder.

## 📦 Panoramica

Tutti i dati persistenti dell'applicazione sono ora salvati in un **unico volume da 1GB** per semplificare la gestione e il monitoraggio dello spazio.

## 🗂️ Struttura Volume

```
./data/                  # Volume unificato (limite: 1GB)
├── db/                  # Database SQLite
│   └── data.db         # File database principale
├── uploads/             # Immagini caricate dagli utenti
│   └── *.jpg,*.png     # File immagini
├── assets/              # Assets esportati
│   ├── css/            # Fogli di stile generati
│   └── img/            # Immagini del sito esportato
└── export/              # File temporanei export (può essere pulito)
```

## 🚀 Quick Start

### Prima Installazione

```bash
# Crea il volume e avvia l'applicazione
make install

# Oppure manualmente:
make setup-volume
make build
make start
```

### Monitoraggio Spazio

```bash
# Verifica spazio utilizzato
make check-space

# Monitor in tempo reale (aggiornamento continuo)
make check-space-watch

# Output JSON per automazione
make check-space-json
```

## 🔧 Comandi Disponibili

### Gestione Volume

- **`make setup-volume`** - Crea e configura il volume unificato
- **`make check-space`** - Mostra uso spazio con dettagli
- **`make check-space-watch`** - Monitor spazio in tempo reale
- **`make check-space-json`** - Output JSON dello spazio

### Gestione Docker

- **`make install`** - Setup iniziale completo (include setup-volume)
- **`make start`** - Avvia il container
- **`make stop`** - Ferma il container
- **`make restart`** - Riavvia il container
- **`make build`** - Build immagine Docker
- **`make clean`** - Pulisci container/volumi (preserva ./data)
- **`make clean-all`** - Pulisci tutto incluso volume data/

### Backup e Ripristino

- **`make db-backup`** - Backup database in backups/
- **`make db-restore FILE=...`** - Ripristina database da backup

## 📊 Monitoraggio e Limiti

### Limiti Consigliati

Il volume è configurato con un limite di **1GB (1024MB)**:

| Utilizzo | Stato | Azione |
|----------|-------|--------|
| 0-60% | ✅ OK | Nessuna azione necessaria |
| 60-80% | ⚠️ Attenzione | Monitora regolarmente |
| 80-90% | ⚠️ Critico | Libera spazio |
| >90% | 🚨 Urgente | Rimuovi file immediatamente |

### Output Monitor

Lo script `monitor-space.sh` fornisce:

- **Spazio totale utilizzato** con barra di progresso visiva
- **Dettaglio per directory** (db, uploads, assets, export)
- **Numero di file** per ogni directory
- **Top 10 file più grandi** nel volume
- **Info volume** (data creazione, limite, crescita)

Esempio output:

```
═══════════════════════════════════════════════════
  SpaSpb Page Builder - Monitor Spazio Volume
═══════════════════════════════════════════════════

📊 Analisi spazio...

╔═══════════════════════════════════════════════════╗
║  Spazio Totale Utilizzato                          ║
╠═══════════════════════════════════════════════════╣
║  [██████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░] 12% ║
║  123MB / 1024MB                                    ║
╚═══════════════════════════════════════════════════╝
```

## 🧹 Liberare Spazio

### Pulire File Temporanei

```bash
# Rimuovi file temporanei export
rm -rf ./data/export/*
```

### Rimuovere Upload Non Utilizzati

```bash
# ATTENZIONE: Verifica prima nel builder quali immagini sono in uso!

# Lista immagini
ls -lh ./data/uploads/

# Rimuovi immagine specifica
rm ./data/uploads/old_image.jpg
```

### Compattare Database

```bash
# Backup e ripristino per compattare
make db-backup

# Se necessario, ripristina da backup
make db-restore FILE=backups/data_YYYYMMDD_HHMMSS.db
```

## 💾 Backup

### Backup Completo Volume

```bash
# Backup dell'intero volume (raccomandato)
tar -czf backup_volume_$(date +%Y%m%d).tar.gz ./data/

# Backup programmato (aggiungi a crontab)
0 2 * * * cd /path/to/SpaSpb && tar -czf backups/volume_$(date +\%Y\%m\%d).tar.gz ./data/
```

### Ripristino Volume

```bash
# Ferma container
docker-compose stop

# Ripristina volume
tar -xzf backup_volume_20231118.tar.gz

# Riavvia
docker-compose start
```

## 🔒 Implementazione Tecnica

### Docker Compose

Il volume è montato come bind mount in `docker-compose.yml`:

```yaml
volumes:
  # Volume unificato da 1GB per tutti i dati persistenti
  - ./data:/var/www/html/data:rw
```

### Dockerfile

Il Dockerfile crea symlink dalle directory originali al volume unificato:

```dockerfile
# Crea directory volume unificato
RUN mkdir -p /var/www/html/data/{db,uploads,assets/css,assets/img,export}

# Crea symlink per compatibilità
RUN ln -sf /var/www/html/data/db /var/www/html/builder/db \
    && ln -sf /var/www/html/data/uploads /var/www/html/builder/uploads \
    && ln -sf /var/www/html/data/assets /var/www/html/assets
```

### Script

Due script bash gestiscono il volume:

1. **`scripts/setup-volume.sh`**
   - Crea struttura directory
   - Migra file esistenti
   - Imposta permessi
   - Verifica spazio disponibile

2. **`scripts/monitor-space.sh`**
   - Analizza uso spazio
   - Mostra dettagli per directory
   - Lista file più grandi
   - Supporta modalità watch e JSON

## 🔄 Migrazione da Sistema Precedente

Se hai già un'installazione esistente con i volumi separati:

```bash
# 1. Ferma il container
docker-compose stop

# 2. Esegui setup volume (migra automaticamente i file)
make setup-volume

# 3. Rebuild immagine con nuova configurazione
make build

# 4. Riavvia
make start
```

Lo script `setup-volume.sh` migra automaticamente:
- Database da `builder/db/` → `data/db/`
- Uploads da `builder/uploads/` → `data/uploads/`
- Assets da `assets/` → `data/assets/`

## ⚙️ Configurazione Avanzata

### Modificare Limite Volume

Per cambiare il limite da 1GB:

```bash
# Modifica scripts/setup-volume.sh e scripts/monitor-space.sh
MAX_SIZE_GB=2  # Cambia in 2GB

# Ricrea configurazione
rm ./data/.volume-info
./scripts/setup-volume.sh
```

### Monitoraggio Automatico

Aggiungi al crontab per alert automatici:

```bash
# Verifica spazio ogni ora e logga
0 * * * * cd /path/to/SpaSpb && make check-space-json > /var/log/spaspb-volume.log

# Alert se >80%
0 * * * * cd /path/to/SpaSpb && USAGE=$(make check-space-json | jq '.total_percent') && [ $USAGE -gt 80 ] && echo "ALERT: Volume SpaSpb al $USAGE%" | mail -s "SpaSpb Volume Alert" admin@example.com
```

## 📚 File Modificati

Questa implementazione ha modificato i seguenti file:

- ✅ **docker-compose.yml** - Volume unificato invece di volumi separati
- ✅ **Dockerfile** - Symlink per compatibilità con codice esistente
- ✅ **Makefile** - Nuovi comandi setup-volume, check-space, clean-all
- ✅ **docker/README.md** - Documentazione aggiornata
- ✅ **.gitignore** - Escluso data/ dal versionamento
- ✅ **scripts/setup-volume.sh** - Script setup volume (nuovo)
- ✅ **scripts/monitor-space.sh** - Script monitoring spazio (nuovo)

## 🆘 Troubleshooting

### Volume non creato

```bash
# Verifica che la directory esista
ls -la ./data

# Se non esiste, esegui setup
make setup-volume
```

### Permessi errati

```bash
# Linux: imposta ownership corretta
sudo chown -R www-data:www-data ./data
chmod -R 755 ./data

# macOS: i permessi sono gestiti automaticamente da Docker
```

### Spazio esaurito

```bash
# Verifica uso
make check-space

# Libera export temporanei
rm -rf ./data/export/*

# Backup e reset se necessario
make db-backup
make clean-all
make install
```

### Symlink non funzionanti

```bash
# Rebuild immagine
make build

# Riavvia container
make restart
```

## 📖 Riferimenti

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Docker Volumes Guide](https://docs.docker.com/storage/volumes/)
- [Documentazione Docker Completa](docker/README.md)
- [Security Guide](SECURITY.md)

---

**Implementato:** 2025-11-19
**Versione:** 1.0
**Autore:** SpaSpb Development Team
