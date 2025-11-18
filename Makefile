.PHONY: help start stop restart logs build clean shell db-backup

# Colori per output
GREEN=\033[0;32m
YELLOW=\033[1;33m
BLUE=\033[0;34m
NC=\033[0m # No Color

help: ## Mostra questo messaggio di aiuto
	@echo "$(GREEN)SpaSpb Page Builder - Comandi Docker$(NC)"
	@echo "======================================"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""

start: ## Avvia il page builder
	@echo "$(GREEN)🚀 Avvio SpaSpb Page Builder...$(NC)"
	@./docker/start.sh

stop: ## Ferma il page builder
	@echo "$(YELLOW)⏸️  Arresto container...$(NC)"
	@docker-compose stop
	@echo "$(GREEN)✓ Container fermato$(NC)"

restart: ## Riavvia il page builder
	@echo "$(BLUE)🔄 Riavvio container...$(NC)"
	@docker-compose restart
	@echo "$(GREEN)✓ Container riavviato$(NC)"
	@echo ""
	@echo "Accedi su: http://localhost:8080/builder/"

logs: ## Mostra i logs in tempo reale
	@docker-compose logs -f

build: ## Build dell'immagine Docker
	@echo "$(BLUE)🏗️  Build immagine...$(NC)"
	@docker-compose build
	@echo "$(GREEN)✓ Build completata$(NC)"

clean: ## Ferma e rimuove container, network e volumi
	@echo "$(YELLOW)🧹 Pulizia completa...$(NC)"
	@docker-compose down -v
	@echo "$(GREEN)✓ Pulizia completata$(NC)"

shell: ## Accedi alla shell del container
	@echo "$(BLUE)💻 Accesso shell container...$(NC)"
	@docker-compose exec spaspb /bin/bash

db-backup: ## Backup del database
	@echo "$(BLUE)💾 Backup database...$(NC)"
	@mkdir -p backups
	@docker-compose exec -T spaspb cat /var/www/html/builder/db/data.db > backups/data_$(shell date +%Y%m%d_%H%M%S).db
	@echo "$(GREEN)✓ Backup salvato in backups/$(NC)"

db-restore: ## Ripristina database (usa: make db-restore FILE=backups/data.db)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)❌ Specifica il file: make db-restore FILE=backups/data.db$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)⚠️  Ripristino database da $(FILE)...$(NC)"
	@cat $(FILE) | docker-compose exec -T spaspb tee /var/www/html/builder/db/data.db > /dev/null
	@echo "$(GREEN)✓ Database ripristinato$(NC)"

status: ## Mostra lo stato del container
	@echo "$(BLUE)📊 Stato container:$(NC)"
	@docker-compose ps

info: ## Mostra informazioni sul setup
	@echo "$(GREEN)SpaSpb Page Builder - Info$(NC)"
	@echo "=========================="
	@echo ""
	@echo "URL:          http://localhost:8080/builder/"
	@echo "Username:     admin"
	@echo "Password:     admin123"
	@echo ""
	@echo "Directory:"
	@echo "  Database:   builder/db/"
	@echo "  Uploads:    builder/uploads/"
	@echo "  Assets:     assets/"
	@echo ""

install: ## Installa dipendenze e avvia (primo setup)
	@echo "$(GREEN)🎨 Setup iniziale SpaSpb Page Builder$(NC)"
	@echo "======================================"
	@echo ""
	@mkdir -p builder/db builder/uploads assets/css assets/img backups
	@chmod -R 755 builder/db builder/uploads assets || true
	@echo "$(GREEN)✓ Directory create$(NC)"
	@echo ""
	@$(MAKE) build
	@echo ""
	@$(MAKE) start

update: ## Aggiorna container con ultime modifiche
	@echo "$(BLUE)🔄 Aggiornamento container...$(NC)"
	@docker-compose down
	@docker-compose build --no-cache
	@docker-compose up -d
	@echo "$(GREEN)✓ Aggiornamento completato$(NC)"

dev: ## Avvia in modalità development con logs
	@echo "$(GREEN)🔧 Avvio in modalità development...$(NC)"
	@docker-compose up --build
