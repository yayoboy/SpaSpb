# Sicurezza - SpaSpb Page Builder

## ⚠️ IMPORTANTE: Configurazione Pre-Produzione

**Prima di mettere il builder online, DEVI configurare la sicurezza!**

Il builder include un sistema di autenticazione ma **è disabilitato di default** per facilitare lo sviluppo locale.

## Passi Obbligatori Prima del Deploy

### 1. Abilita l'Autenticazione

Modifica `builder/config.php`:

```php
define('AUTH_ENABLED', true); // IMPOSTA A TRUE!
```

### 2. Cambia le Credenziali di Default

**Le credenziali di default sono:**
- Username: `admin`
- Password: `admin123`

**DEVI cambiarle immediatamente!**

#### Come generare una nuova password:

```bash
php -r "echo password_hash('tua_password_sicura', PASSWORD_DEFAULT);"
```

Copia l'output e incollalo in `builder/config.php`:

```php
define('AUTH_PASSWORD', '$2y$10$tu_hash_generato_qui');
```

Puoi anche cambiare lo username:

```php
define('AUTH_USERNAME', 'tuo_username');
```

### 3. Verifica i Permessi delle Directory

```bash
chmod 755 builder/db
chmod 755 builder/uploads
chmod 755 assets
chmod 644 builder/db/data.db  # Dopo che viene creato
```

### 4. Proteggi il Database

Il file `.htaccess` già include questa protezione:

```apache
# Nega accesso diretto al database
RewriteRule ^builder/db/.*$ - [F,L]
```

Verifica che funzioni tentando di accedere a:
```
http://tuosito.it/builder/db/data.db
```

Dovresti ricevere un **403 Forbidden**.

### 5. Limita Upload di File

In `builder/config.php` puoi configurare:

```php
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
```

### 6. Disabilita Debug Mode

In `builder/config.php`:

```php
define('DEBUG_MODE', false); // SEMPRE FALSE in produzione!
```

## Funzionalità di Sicurezza Implementate

### ✅ Autenticazione

- Sistema di login con username/password
- Password hashate con `password_hash()` (bcrypt)
- Timeout sessione configurabile (default: 2 ore)
- Logout automatico dopo inattività

### ✅ Protezione API

Tutte le API sono protette:
- `api_save` - Richiede autenticazione
- `api_load` - Richiede autenticazione
- `api_delete` - Richiede autenticazione
- `api_upload` - Richiede autenticazione

Le richieste non autenticate ricevono:
```json
{"success": false, "error": "Non autenticato", "redirect": "?action=login"}
```

### ✅ Validazione Upload

L'upload di file include:
1. **Verifica dimensione** - Max 5MB (configurabile)
2. **Verifica estensione** - Solo immagini permesse
3. **Verifica MIME type** - Controllo tipo file reale
4. **Nome file sicuro** - Generato con `uniqid()`
5. **Directory separata** - File in `uploads/`

### ✅ Protezione Database

- File `.db` non accessibile via web (`.htaccess`)
- Query parametrizzate (protezione SQL injection)
- Validazione input lato server

### ✅ Protezione CSRF

Sistema CSRF pronto (da abilitare):
```php
define('CSRF_ENABLED', true);
```

### ✅ Headers Sicurezza

Il file `.htaccess` include:
- Protezione directory listing
- Compressione
- Cache headers
- Blocco file sensibili

## Configurazione Hosting

### Hosting Condiviso (cPanel, Plesk, ecc.)

1. Carica tutti i file via FTP/SFTP
2. Modifica `builder/config.php` come indicato sopra
3. Verifica che PHP abbia l'estensione SQLite3:
   ```php
   <?php phpinfo(); ?>
   ```
   Cerca "SQLite3" - deve essere presente

4. Imposta permessi:
   ```
   755 per directory
   644 per file PHP
   ```

### VPS/Dedicato

1. Clona il repository
2. Configura virtual host Apache/Nginx
3. Abilita moduli necessari:
   ```bash
   sudo a2enmod rewrite  # Apache
   sudo systemctl restart apache2
   ```

4. Configura `builder/config.php`
5. Imposta permessi corretti

### Nginx

Se usi Nginx invece di Apache, aggiungi al tuo config:

```nginx
location ^~ /builder/db/ {
    deny all;
    return 403;
}

location ~ /\. {
    deny all;
    return 403;
}
```

## Raccomandazioni Aggiuntive

### 1. HTTPS Obbligatorio

**Usa SEMPRE HTTPS in produzione!**

Ottieni certificato gratuito:
- [Let's Encrypt](https://letsencrypt.org/)
- [Certbot](https://certbot.eff.org/)

### 2. Backup Regolari

Fai backup di:
- `builder/db/data.db` (database)
- `builder/uploads/` (immagini caricate)
- `index.html` e `assets/` (sito esportato)

### 3. Monitoring

Monitora:
- Tentativi di login falliti
- Upload di file sospetti
- Accessi al database

### 4. Aggiornamenti

Tieni aggiornato:
- PHP (usa almeno PHP 7.4+, meglio 8.0+)
- Moduli Apache/Nginx
- Sistema operativo

### 5. Protezione Brute Force

Considera di aggiungere:
- Rate limiting
- Fail2ban
- IP whitelisting

Esempio in `.htaccess`:
```apache
# Permetti solo da IP specifici
Order Deny,Allow
Deny from all
Allow from 192.168.1.100
Allow from tuo.ip.statico.qui
```

### 6. Multi-Utente

Se hai bisogno di più utenti:
- Crea tabella `users` nel database
- Implementa ruoli e permessi
- Aggiungi audit log

## Verifica Sicurezza

### Checklist Pre-Deploy

- [ ] `AUTH_ENABLED` impostato a `true`
- [ ] Password cambiata (non più `admin123`)
- [ ] `DEBUG_MODE` impostato a `false`
- [ ] HTTPS abilitato
- [ ] Permessi file verificati
- [ ] Database non accessibile via web
- [ ] Upload validato correttamente
- [ ] Backup configurato
- [ ] `.htaccess` funzionante

### Test di Sicurezza

1. **Test accesso non autenticato:**
   ```
   http://tuosito.it/builder/?action=dashboard
   ```
   Dovrebbe redirigere a login

2. **Test protezione database:**
   ```
   http://tuosito.it/builder/db/data.db
   ```
   Dovrebbe dare 403 Forbidden

3. **Test upload file non valido:**
   Prova a caricare un file `.php` - dovrebbe essere rifiutato

4. **Test timeout sessione:**
   Fai login, aspetta 2+ ore, prova ad accedere - dovrebbe richiedere re-login

## Segnalazione Vulnerabilità

Se trovi una vulnerabilità:

1. **NON** aprire issue pubbliche
2. Contatta privatamente il maintainer
3. Fornisci dettagli tecnici
4. Attendi risposta prima di rendere pubblica

## Risorse

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [SQLite Security](https://www.sqlite.org/security.html)

---

**Ricorda: La sicurezza è un processo continuo, non una configurazione una tantum!**
