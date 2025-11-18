# SpaSpb Page Builder - Docker Setup

Guida completa per eseguire SpaSpb Page Builder con Docker.

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
- Almeno **500MB** di spazio libero

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
make help           # Lista tutti i comandi
make start          # Avvia il builder
make stop           # Ferma il builder
make restart        # Riavvia il builder
make logs           # Mostra logs in tempo reale
make status         # Mostra stato container
make shell          # Accedi alla shell del container
make build          # Rebuild immagine Docker
make clean          # Rimuovi tutto (container, volumi, network)
make db-backup      # Backup del database
make db-restore     # Ripristina database
make update         # Aggiorna con ultime modifiche
make dev            # Avvia in modalità development
make info           # Mostra informazioni setup
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

### Volumi Persistenti

I dati persistono automaticamente in:

```yaml
volumes:
  - ./builder/db:/var/www/html/builder/db          # Database
  - ./builder/uploads:/var/www/html/builder/uploads # Uploads
  - ./assets:/var/www/html/assets                   # Assets
```

Anche se elimini il container, i dati rimangono sul tuo sistema.

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
# Con Make
make db-backup

# Manualmente
docker-compose exec spaspb cat /var/www/html/builder/db/data.db > backup.db
```

I backup vengono salvati in `backups/data_YYYYMMDD_HHMMSS.db`

### Ripristino Database

```bash
# Con Make
make db-restore FILE=backups/data_20231118_120000.db

# Manualmente
cat backup.db | docker-compose exec -T spaspb tee /var/www/html/builder/db/data.db > /dev/null
```

### Backup Completo

```bash
# Backup tutto (database + uploads + assets)
tar -czf backup_$(date +%Y%m%d).tar.gz \
  builder/db/ \
  builder/uploads/ \
  assets/
```

### Ripristino Completo

```bash
tar -xzf backup_20231118.tar.gz
docker-compose restart
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
# Linux/Mac: imposta permessi corretti
chmod -R 755 builder/db builder/uploads assets

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

# Elimina database
rm builder/db/data.db

# Riavvia (verrà ricreato)
docker-compose start
```

### Reset completo

```bash
# Ferma tutto
docker-compose down -v

# Rimuovi database e uploads
rm -rf builder/db/*.db builder/uploads/*

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
