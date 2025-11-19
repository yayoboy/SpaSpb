<?php
/**
 * SpaSpb - Configurazione
 * IMPORTANTE: Cambia questi valori prima di mettere online!
 */

// Autenticazione - CAMBIA QUESTI VALORI!
define('AUTH_ENABLED', false); // Imposta a true per produzione!
define('AUTH_USERNAME', 'admin'); // Cambia con il tuo username
define('AUTH_PASSWORD', '$2y$12$HHzsAbc4WSriK8tIlfCsoOVwEP72BmBZW38DBgev6JRy0Wdt435Hu'); // Hash di "admin123" - CAMBIA!

// Per generare un nuovo hash:
// php -r "echo password_hash('tuapassword', PASSWORD_DEFAULT);"

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
define('SESSION_TIMEOUT', 7200); // 2 ore

// Sicurezza
define('CSRF_ENABLED', true);

// Debug (DISABILITA in produzione!)
define('DEBUG_MODE', false);

// Timezone
date_default_timezone_set('Europe/Rome');
