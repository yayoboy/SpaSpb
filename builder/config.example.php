<?php
/**
 * SpaSpb - Configurazione (ESEMPIO)
 *
 * ISTRUZIONI:
 * 1. Copia questo file come config.php (se non esiste già)
 * 2. Modifica i valori secondo le tue necessità
 * 3. IMPORTANTE: Cambia username e password prima del deploy!
 */

// Autenticazione - CAMBIA QUESTI VALORI!
define('AUTH_ENABLED', true); // ABILITATA di default per sicurezza - disabilita solo per sviluppo locale
define('AUTH_USERNAME', 'admin'); // Cambia con il tuo username
define('AUTH_PASSWORD', '$2y$12$HHzsAbc4WSriK8tIlfCsoOVwEP72BmBZW38DBgev6JRy0Wdt435Hu'); // Hash di "admin123" - CAMBIA!

// Per generare un nuovo hash della password:
// php -r "echo password_hash('tua_password_qui', PASSWORD_DEFAULT);"

// Database
define('DB_PATH', __DIR__ . '/db/data.db');

// Upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

// Export
define('EXPORT_DIR', dirname(__DIR__));
define('ASSETS_DIR', EXPORT_DIR . '/assets/');

// Sessioni
define('SESSION_TIMEOUT', 7200); // 2 ore in secondi

// Sicurezza
define('CSRF_ENABLED', true);

// Debug (SEMPRE false in produzione!)
define('DEBUG_MODE', false);

// Timezone
date_default_timezone_set('Europe/Rome');
