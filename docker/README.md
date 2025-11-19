# SpaSpb Page Builder - Docker Setup

Guida completa per eseguire SpaSpb Page Builder con Docker.

## 💾 Volume Unificato da 1GB

**Novità:** Tutti i dati persistenti (database, uploads, assets) sono salvati in un **unico volume da 1GB** in `./data/`:

```bash
# Prima installazione: crea automaticamente il volume
make install

# Monitora lo spazio utilizzato
make check-space
```

Il volume è limitato a **1GB** per un utilizzo ottimale. Usa `make check-space` per monitorare l'uso dello spazio.

## 🚀 Quick Start

### Metodo 1: Script Automatico (Raccomandato)

```bash
./docker/start.sh
```

Poi apri: **http://localhost:8080/builder/**

### Metodo 2: Docker Compose Manuale

```bash
docker-compose up -d
```

### Metodo 3: Makefile (Più Comandi)

```bash
make install    # Prima installazione
make start      # Avvia
make stop       # Ferma
make logs       # Mostra logs
```

## 📋 Prerequisiti

- **Docker** 20.10 o superiore
- **Docker Compose** 2.0 o superiore (o plugin `docker compose`)
- Almeno **1GB** di spazio libero (per il volume dati)

### Installazione Docker

#### Linux (Ubuntu/Debian)
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

#### macOS
```bash
brew install --cask docker
```

#### Windows
Scarica [Docker Desktop](https://www.docker.com/products/docker-desktop/)

## 🏗️ Struttura Docker

```
SpaSpb/
├── Dockerfile                 # Immagine PHP 8.1 + Apache
├── docker-compose.yml         # Orchestrazione servizi
├── .dockerignore             # File esclusi dall'immagine
├── Makefile                  # Comandi semplificati
└── docker/
    ├── start.sh              # Script avvio automatico
    ├── apache-config.conf    # Configurazione Apache
    └── README.md             # Questa guida
```

## 🎯 Utilizzo

### Avvio Iniziale

```bash
# Con script (consigliato)
./docker/start.sh

# Con Make
make install

# Con Docker Compose
docker-compose up -d
```

### Accesso all'Applicazione

Dopo l'avvio, accedi a:

- **Builder**: http://localhost:8080/builder/
- **Sito Esportato**: http://localhost:8080/ (dopo export)

**Credenziali di default:**
- Username: `admin`
- Password: `admin123`

⚠️ **Cambia le credenziali** in `builder/config.php` prima di usare in produzione!

### Comandi Make Disponibili

```bash
make help               # Lista tutti i comandi
make start              # Avvia il builder
make stop               # Ferma il builder
make restart            # Riavvia il builder
make logs               # Mostra logs in tempo reale
make status             # Mostra stato container
make shell              # Accedi alla shell del container
make build              # Rebuild immagine Docker
make clean              # Rimuovi container/volumi (preserva ./data)
make clean-all          # Rimuovi tutto incluso volume data/
make db-backup          # Backup del database
make db-restore         # Ripristina database
make update             # Aggiorna con ultime modifiche
make dev                # Avvia in modalità development
make info               # Mostra informazioni setup
make setup-volume       # Crea/configura volume unificato da 1GB
make check-space        # Monitora uso spazio nel volume
make check-space-watch  # Monitor spazio in tempo reale
make check-space-json   # Output JSON dello spazio utilizzato
```

### Comandi Docker Compose

```bash
# Avvia in foreground (vedi i logs)
docker-compose up

# Avvia in background
docker-compose up -d

# Ferma i container
docker-compose stop

# Ferma e rimuovi
docker-compose down

# Rebuild immagine
docker-compose build

# Rebuild senza cache
docker-compose build --no-cache

# Mostra logs
docker-compose logs -f

# Lista container
docker-compose ps

# Riavvia
docker-compose restart
```

## 🔧 Configurazione

### Porta Personalizzata

Modifica `docker-compose.yml`:

```yaml
ports:
  - "3000:80"  # Usa porta 3000 invece di 8080
```

Poi riavvia:
```bash
docker-compose down
docker-compose up -d
```

### Variabili d'Ambiente

Puoi personalizzare il comportamento tramite variabili d'ambiente:

```yaml
environment:
  - PHP_MEMORY_LIMIT=512M
  - PHP_UPLOAD_MAX_FILESIZE=20M
  - PHP_POST_MAX_SIZE=20M
```

### Volume Unificato da 1GB

**Tutti i dati persistenti sono salvati in un unico volume da 1GB:**

```
./data/                  # Volume unificato (limite consigliato: 1GB)
├── db/                  # Database SQLite
├── uploads/             # Immagini caricate
├── assets/              # CSS e immagini esportate
│   ├── css/
│   └── img/
└── export/              # File temporanei export
```

Il volume è montato in `./data` e mappato internamente al container tramite symlink.
Anche se elimini il container, tutti i dati rimangono sul tuo sistema.

#### Gestione Volume

```bash
# Crea e configura il volume
make setup-volume

# Monitora lo spazio utilizzato
make check-space

# Monitor in tempo reale (aggiornamento continuo)
make check-space-watch

# Output JSON per script esterni
make check-space-json
```

#### Limiti e Monitoraggio

Il volume è configurato con un **limite consigliato di 1GB (1024MB)**:

- ⚠️ **80-90%**: Considera di liberare spazio
- 🚨 **>90%**: Critico, rimuovi file non necessari

**Liberare spazio:**

```bash
# Pulisci file temporanei export
rm -rf ./data/export/*

# Rimuovi vecchi upload non utilizzati
# (verifica prima nel builder quali immagini sono in uso!)

# Backup e ripristino per compattare il database
make db-backup
# Poi se necessario ripristina
```

## 🛠️ Sviluppo

### Modalità Development

```bash
# Con Make
make dev

# Con Docker Compose
docker-compose up
```

Questo avvia il container in foreground e mostra tutti i logs in tempo reale.

### Hot Reload

Le modifiche ai file PHP sono automaticamente visibili senza rebuild:

```bash
# Modifica un file
vim builder/index.php

# Ricarica la pagina nel browser - modifiche applicate!
```

### Accesso Shell Container

```bash
# Con Make
make shell

# Con Docker Compose
docker-compose exec spaspb /bin/bash
```

Una volta dentro:
```bash
# Verifica estensioni PHP
php -m

# Controlla permessi
ls -la builder/db/

# Testa configurazione Apache
apache2ctl -t

# Vedi logs PHP
tail -f /var/log/apache2/php_errors.log
```

## 💾 Backup e Ripristino

### Backup Database

```bash
# Con Make (raccomandato)
make db-backup

# Manualmente
docker-compose exec spaspb cat /var/www/html/data/db/data.db > backup.db
```

I backup vengono salvati in `backups/data_YYYYMMDD_HHMMSS.db`

### Ripristino Database

```bash
# Con Make (raccomandato)
make db-restore FILE=backups/data_20231118_120000.db

# Manualmente
cat backup.db | docker-compose exec -T spaspb tee /var/www/html/data/db/data.db > /dev/null
```

### Backup Completo del Volume

```bash
# Backup dell'intero volume data/ (raccomandato)
tar -czf backup_$(date +%Y%m%d).tar.gz ./data/

# Oppure solo i file importanti
tar -czf backup_$(date +%Y%m%d).tar.gz \
  ./data/db/ \
  ./data/uploads/ \
  ./data/assets/
```

### Ripristino Completo

```bash
# Ferma il container
docker-compose stop

# Ripristina il volume
tar -xzf backup_20231118.tar.gz

# Riavvia
docker-compose start
```

## 🐛 Troubleshooting

### Container non si avvia

```bash
# Controlla i logs
docker-compose logs

# Controlla lo stato
docker-compose ps

# Verifica errori specifici
docker-compose logs spaspb
```

### Errore permessi

```bash
# Linux/Mac: imposta permessi corretti sul volume
chmod -R 755 ./data

# Se necessario, imposta ownership (richiede sudo)
sudo chown -R www-data:www-data ./data

# Poi riavvia
docker-compose restart
```

### Porta già in uso

Se la porta 8080 è occupata:

```bash
# Cambia porta in docker-compose.yml
ports:
  - "9090:80"  # Usa 9090
```

### Database corrotto

```bash
# Ferma container
docker-compose stop

# Backup del database corrotto (per sicurezza)
cp ./data/db/data.db ./data/db/data.db.broken

# Elimina database
rm ./data/db/data.db

# Riavvia (verrà ricreato)
docker-compose start
```

### Spazio volume esaurito

```bash
# Verifica l'uso dello spazio
make check-space

# Libera spazio da file temporanei
rm -rf ./data/export/*

# Considera backup e rimozione vecchi upload
make db-backup
# Poi rimuovi manualmente immagini non utilizzate da ./data/uploads/
```

### Reset completo

```bash
# Ferma tutto
docker-compose down -v

# Backup preventivo (raccomandato!)
tar -czf backup_before_reset.tar.gz ./data/

# Rimuovi tutto il volume
rm -rf ./data

# Ricrea il volume
make setup-volume

# Riavvia
docker-compose up -d
```

### Immagine non si builda

```bash
# Pulisci cache Docker
docker system prune -a

# Rebuild senza cache
docker-compose build --no-cache
```

## 📊 Monitoring

### Logs in Tempo Reale

```bash
# Tutti i servizi
docker-compose logs -f

# Solo builder
docker-compose logs -f spaspb

# Ultimi 100 righe
docker-compose logs --tail=100 spaspb
```

### Risorse Utilizzate

```bash
# Statistiche container
docker stats spaspb-pagebuilder
```

### Health Check

Il container include un health check automatico:

```bash
# Verifica stato
docker inspect --format='{{.State.Health.Status}}' spaspb-pagebuilder
```

## 🔒 Sicurezza in Produzione

### ⚠️ PRIMA DI ANDARE IN PRODUZIONE:

1. **Abilita autenticazione** in `builder/config.php`:
   ```php
   define('AUTH_ENABLED', true);
   ```

2. **Cambia password**:
   ```bash
   php -r "echo password_hash('tua_password_sicura', PASSWORD_DEFAULT);"
   ```
   Poi aggiorna `AUTH_PASSWORD` in `builder/config.php`

3. **Usa HTTPS**: Configura reverse proxy (nginx/traefik) con SSL

4. **Limita accesso**: Usa firewall o IP whitelisting

5. **Backup automatici**: Configura cron per backup giornalieri

### Reverse Proxy con HTTPS (Nginx)

Esempio configurazione nginx:

```nginx
server {
    listen 443 ssl http2;
    server_name tuodominio.it;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 🚀 Deploy Produzione

### Opzione 1: VPS con Docker

```bash
# Su server
git clone https://github.com/tuouser/SpaSpb.git
cd SpaSpb

# Configura
vim builder/config.php  # Abilita auth e cambia password

# Avvia
docker-compose up -d
```

### Opzione 2: Docker Swarm

```bash
docker stack deploy -c docker-compose.yml spaspb
```

### Opzione 3: Kubernetes

Converti con kompose:
```bash
kompose convert
kubectl apply -f .
```

## 📚 Risorse

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [SpaSpb Security Guide](../SECURITY.md)
- [PHP Docker Official Image](https://hub.docker.com/_/php)

## 🆘 Supporto

Se riscontri problemi:

1. Controlla i logs: `docker-compose logs`
2. Verifica lo stato: `docker-compose ps`
3. Consulta [SECURITY.md](../SECURITY.md)
4. Apri un issue su GitHub

---

**Buon building con Docker! 🐳**
