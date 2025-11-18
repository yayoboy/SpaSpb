# SpaSpb - PHP Page Builder

Un potente page builder single-file in PHP + SQLite per creare pagine one-page (landing pages, gallerie, vetrine) con supporto per diverse librerie UI.

## ⚠️ IMPORTANTE: Sicurezza

**Prima di usare questo builder su un hosting web pubblico:**

1. **Abilita l'autenticazione** in `builder/config.php`:
   ```php
   define('AUTH_ENABLED', true);
   ```

2. **Cambia le credenziali di default**:
   - Default: `admin` / `admin123`
   - Genera nuovo hash: `php -r "echo password_hash('tuapassword', PASSWORD_DEFAULT);"`
   - Aggiorna `AUTH_PASSWORD` in `builder/config.php`

3. **Verifica protezione database**: `builder/db/` NON deve essere accessibile via web

4. **Usa HTTPS** in produzione

📖 **Leggi la guida completa:** [SECURITY.md](SECURITY.md)

## Caratteristiche

- **Single-file architecture**: Backend completo in un unico file PHP
- **Database SQLite**: Nessun setup MySQL richiesto
- **Drag & Drop**: Interfaccia intuitiva per costruire pagine
- **Librerie UI multiple**: Supporto per Tailwind CSS, Bootstrap 5, shadcn/ui, Bulma
- **Export HTML statico**: Genera siti HTML pronti per il deploy
- **7 Blocchi predefiniti**: Hero, Testo, Immagine, Galleria, CTA, Features, Contatti
- **Responsive**: Interfaccia e siti generati completamente responsive

## Struttura del Progetto

```
/
├── index.html              # Sito esportato
├── assets/                 # Asset del sito esportato
│   ├── css/
│   │   └── style.css
│   └── img/
├── builder/                # Page Builder
│   ├── index.php          # Backend + Router + Editor
│   ├── db/
│   │   └── data.db       # Database SQLite
│   ├── js/
│   │   └── builder.js    # Logica del page builder
│   ├── css/
│   │   ├── builder.css   # Stile dell'editor
│   │   └── theme.css     # Stile del sito esportato
│   ├── uploads/          # Immagini caricate
│   ├── templates/        # Template dei blocchi
│   └── export/           # Staging temporaneo
└── README.md
```

## Requisiti

- PHP 7.4 o superiore
- Estensione SQLite3 abilitata
- Web server (Apache, Nginx, PHP built-in server)

## Installazione

### Metodo 1: Server Web (Apache/Nginx)

1. Clona il repository nella root del tuo web server:
```bash
git clone https://github.com/tuouser/SpaSpb.git
cd SpaSpb
```

2. Assicurati che la directory `builder/db/` e `builder/uploads/` siano scrivibili:
```bash
chmod 755 builder/db
chmod 755 builder/uploads
chmod 755 assets
```

3. Apri il browser e vai su:
```
http://tuodominio.it/builder/
```

### Metodo 2: PHP Built-in Server (Sviluppo)

1. Clona il repository:
```bash
git clone https://github.com/tuouser/SpaSpb.git
cd SpaSpb
```

2. Avvia il server PHP:
```bash
php -S localhost:8000
```

3. Apri il browser e vai su:
```
http://localhost:8000/builder/
```

## Utilizzo

### 1. Accedi alla Dashboard

Vai su `http://tuodominio.it/builder/` per vedere la dashboard con tutte le tue pagine.

### 2. Crea una Nuova Pagina

- Clicca su "Nuova Pagina"
- Scegli la libreria UI (Tailwind, Bootstrap, shadcn/ui, Bulma)
- Si aprirà l'editor

### 3. Costruisci la Pagina

- **Trascina i blocchi** dalla sidebar di sinistra al canvas centrale
- **Clicca su un blocco** per modificarne le proprietà nel pannello di destra
- **Riordina i blocchi** trascinandoli verticalmente
- **Modifica il contenuto HTML** direttamente nel pannello proprietà

### 4. Blocchi Disponibili

#### 🎯 Hero Section
Sezione principale con titolo, sottotitolo e background personalizzabile.

#### 📝 Testo
Blocco di contenuto testuale con HTML personalizzabile.

#### 🖼️ Immagine
Carica e mostra immagini con allineamento personalizzabile.

#### 🎨 Galleria
Griglia di immagini con colonne configurabili.

#### 🚀 Call to Action
Sezione con bottone d'azione e sfondo colorato.

#### ⭐ Features
Mostra le caratteristiche del prodotto/servizio con icone.

#### 📧 Contatti
Informazioni di contatto (email, telefono, indirizzo).

### 5. Salva e Esporta

- **Salva**: Il builder salva automaticamente ogni 2 secondi
- **Anteprima**: Clicca su "Anteprima" per vedere la pagina in una nuova finestra
- **Esporta**: Clicca su "Esporta" per generare il sito statico nella root

## Librerie UI Supportate

### Tailwind CSS
Framework utility-first moderno e flessibile.

### Bootstrap 5
Framework CSS più popolare al mondo con componenti pronti.

### shadcn/ui
Libreria di componenti moderna basata su Tailwind e Radix UI.

### Bulma
Framework CSS leggero e modulare.

## Export

Quando clicchi su "Esporta", il builder:

1. Genera il file `index.html` nella root del progetto
2. Copia il CSS nella cartella `assets/css/style.css`
3. Copia le immagini in `assets/img/`
4. Include i CDN della libreria UI scelta

Il sito esportato è pronto per essere caricato su qualsiasi hosting statico.

## API Interne

Il builder usa queste API AJAX:

- `?action=api_save` - Salva la pagina
- `?action=api_load&id=X` - Carica i dati della pagina
- `?action=api_delete&id=X` - Elimina una pagina
- `?action=api_upload` - Carica un'immagine

## Personalizzazione

### Aggiungere un Nuovo Blocco

1. Crea il template in `builder/templates/nome_blocco.php`
2. Aggiungi il blocco alla palette in `index.php` (funzione `showEditor`)
3. Aggiungi il rendering in `builder.js` (funzione `renderBlockPreview`)
4. Aggiungi le impostazioni in `builder.js` (funzione `getDefaultSettings`)

### Modificare gli Stili

- **Editor**: Modifica `builder/css/builder.css`
- **Sito esportato**: Modifica `builder/css/theme.css`

## Database

Il database SQLite contiene 2 tabelle:

### Tabella `pages`
- `id` - ID univoco
- `title` - Titolo della pagina
- `slug` - Slug URL-friendly
- `blocks` - JSON con i blocchi
- `ui_library` - Libreria UI scelta
- `created_at` - Data creazione
- `updated_at` - Data ultimo aggiornamento

### Tabella `settings`
- `key` - Chiave impostazione
- `value` - Valore

## Troubleshooting

### Il database non si crea

Verifica che l'estensione SQLite3 sia abilitata:
```bash
php -m | grep sqlite3
```

### Errore "Permission denied"

Dai i permessi corretti:
```bash
chmod 755 builder/db
chmod 755 builder/uploads
chmod 755 assets
```

### Le immagini non vengono caricate

Verifica che `upload_max_filesize` e `post_max_size` in `php.ini` siano adeguati.

## Licenza

MIT License - Sentiti libero di usare questo progetto per qualsiasi scopo.

## Autore

Progetto SpaSpb - Page Builder in PHP + SQLite

## Contribuire

Pull request benvenute! Per modifiche importanti, apri prima un issue per discutere cosa vorresti cambiare.

---

**Buon building! 🚀**
