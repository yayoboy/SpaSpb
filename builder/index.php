<?php
/**
 * SpaSpb Page Builder
 * Single-file PHP + SQLite Page Builder
 */

// Carica configurazione e autenticazione
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Inizializza database
function initDatabase() {
    $dbPath = DB_PATH;

    // Crea directory se non esiste
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }

    try {
        $db = new SQLite3($dbPath);

        // Crea tabella pages
        $db->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                blocks TEXT DEFAULT '[]',
                ui_library TEXT DEFAULT 'tailwind',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Crea tabella settings
        $db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT
            )
        ");

        // Crea tabella login_attempts per rate limiting
        $db->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                ip TEXT PRIMARY KEY,
                attempts INTEGER DEFAULT 0,
                last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Inserisci impostazioni di default
        $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('default_ui_library', 'tailwind')");

        return $db;
    } catch (Exception $e) {
        // Non esporre dettagli dell'errore in produzione
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Errore database: " . $e->getMessage());
        }
        die("Errore di sistema. Riprova più tardi.");
    }
}

// Connessione database
$db = initDatabase();

// Router
$action = $_GET['action'] ?? 'dashboard';

// Headers per API JSON
if (in_array($action, ['api_save', 'api_load', 'api_delete', 'api_upload'])) {
    header('Content-Type: application/json');
}

// Routing
switch ($action) {
    case 'login':
        showLoginPage();
        break;

    case 'do_login':
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Verifica rate limiting
        if (isLoginBlocked($db, $ip)) {
            $minutes = getBlockedMinutesRemaining($db, $ip);
            showLoginPage("Troppi tentativi falliti. Riprova tra {$minutes} minuti.");
            break;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (login($username, $password)) {
            clearLoginAttempts($db, $ip);
            header('Location: ?action=dashboard');
            exit;
        } else {
            recordFailedLogin($db, $ip);
            showLoginPage('Username o password non corretti');
        }
        break;

    case 'logout':
        logout();
        header('Location: ?action=login');
        exit;

    // Tutte le altre route richiedono autenticazione
    case 'dashboard':
        requireAuth();
        showDashboard($db);
        break;

    case 'new':
        requireAuth();
        createNewPage($db);
        break;

    case 'edit':
        requireAuth();
        showEditor($db);
        break;

    case 'api_save':
        requireAuth();
        apiSavePage($db);
        break;

    case 'api_load':
        requireAuth();
        apiLoadPage($db);
        break;

    case 'api_delete':
        requireAuth();
        apiDeletePage($db);
        break;

    case 'api_upload':
        requireAuth();
        apiUploadImage();
        break;

    case 'export':
        requireAuth();
        exportPage($db);
        break;

    default:
        requireAuth();
        showDashboard($db);
}

// ==================== FUNZIONI ====================

/**
 * Sanitizza contenuto HTML per prevenire XSS
 * Mantiene i tag HTML base ma rimuove script e event handlers
 */
function sanitizeHtml($html) {
    if (empty($html)) {
        return '';
    }

    // Tag permessi (whitelist)
    $allowedTags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><s><strike>'
                 . '<a><ul><ol><li><blockquote><pre><code><hr><span><div>'
                 . '<table><thead><tbody><tr><th><td><img><figure><figcaption><small>';

    // Prima rimuovi i tag non permessi
    $html = strip_tags($html, $allowedTags);

    // Rimuovi event handlers e attributi pericolosi
    $dangerousPatterns = [
        // Event handlers
        '/\s+on\w+\s*=\s*["\'][^"\']*["\']/i',
        '/\s+on\w+\s*=\s*[^\s>]+/i',
        // javascript: URLs
        '/href\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i',
        '/src\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i',
        // data: URLs (possono contenere script)
        '/href\s*=\s*["\']?\s*data:[^"\'>\s]*/i',
        // Expression e behavior (IE)
        '/expression\s*\([^)]*\)/i',
        '/behavior\s*:/i',
        // vbscript
        '/vbscript\s*:/i',
    ];

    foreach ($dangerousPatterns as $pattern) {
        $html = preg_replace($pattern, '', $html);
    }

    return $html;
}

/**
 * Verifica se l'IP è bloccato per troppi tentativi di login
 */
function isLoginBlocked($db, $ip) {
    $maxAttempts = 5;
    $lockoutTime = 15 * 60; // 15 minuti in secondi

    $stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip = ?");
    $stmt->bindValue(1, $ip, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if (!$row) {
        return false;
    }

    $lastAttempt = strtotime($row['last_attempt']);
    $timePassed = time() - $lastAttempt;

    // Se è passato il tempo di lockout, resetta
    if ($timePassed > $lockoutTime) {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
        $stmt->bindValue(1, $ip, SQLITE3_TEXT);
        $stmt->execute();
        return false;
    }

    return $row['attempts'] >= $maxAttempts;
}

/**
 * Registra un tentativo di login fallito
 */
function recordFailedLogin($db, $ip) {
    $stmt = $db->prepare("
        INSERT INTO login_attempts (ip, attempts, last_attempt)
        VALUES (?, 1, CURRENT_TIMESTAMP)
        ON CONFLICT(ip) DO UPDATE SET
            attempts = attempts + 1,
            last_attempt = CURRENT_TIMESTAMP
    ");
    $stmt->bindValue(1, $ip, SQLITE3_TEXT);
    $stmt->execute();
}

/**
 * Pulisce i tentativi di login dopo successo
 */
function clearLoginAttempts($db, $ip) {
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
    $stmt->bindValue(1, $ip, SQLITE3_TEXT);
    $stmt->execute();
}

/**
 * Ottiene minuti rimanenti per il blocco
 */
function getBlockedMinutesRemaining($db, $ip) {
    $lockoutTime = 15 * 60;

    $stmt = $db->prepare("SELECT last_attempt FROM login_attempts WHERE ip = ?");
    $stmt->bindValue(1, $ip, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if (!$row) {
        return 0;
    }

    $lastAttempt = strtotime($row['last_attempt']);
    $timePassed = time() - $lastAttempt;
    $remaining = $lockoutTime - $timePassed;

    return max(0, ceil($remaining / 60));
}

/**
 * Dashboard - Lista pagine
 */
function showDashboard($db) {
    $result = $db->query("SELECT * FROM pages ORDER BY updated_at DESC");
    $pages = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pages[] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SpaSpb Page Builder - Dashboard</title>
        <link rel="stylesheet" href="css/builder.css">
    </head>
    <body class="dashboard">
        <div class="container">
            <header class="dashboard-header">
                <h1>🎨 SpaSpb Page Builder</h1>
                <div class="dashboard-actions">
                    <?php if (AUTH_ENABLED): ?>
                        <span class="user-info">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Utente') ?></span>
                    <?php endif; ?>
                    <a href="?action=new" class="btn btn-primary">+ Nuova Pagina</a>
                    <?php if (AUTH_ENABLED): ?>
                        <a href="?action=logout" class="btn btn-secondary">Logout</a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="pages-grid">
                <?php if (empty($pages)): ?>
                    <div class="empty-state">
                        <p>Nessuna pagina creata.</p>
                        <a href="?action=new" class="btn btn-primary">Crea la tua prima pagina</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($pages as $page): ?>
                        <div class="page-card">
                            <div class="page-card-header">
                                <h3><?= htmlspecialchars($page['title']) ?></h3>
                                <span class="badge"><?= htmlspecialchars($page['ui_library']) ?></span>
                            </div>
                            <div class="page-card-meta">
                                <small>Aggiornato: <?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?></small>
                            </div>
                            <div class="page-card-actions">
                                <a href="?action=edit&id=<?= $page['id'] ?>" class="btn btn-sm">Modifica</a>
                                <a href="?action=export&id=<?= $page['id'] ?>" class="btn btn-sm btn-success">Esporta</a>
                                <button onclick="deletePage(<?= $page['id'] ?>)" class="btn btn-sm btn-danger">Elimina</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <script>
        const CSRF_TOKEN = '<?= getCsrfToken() ?>';

        function deletePage(id) {
            if (!confirm('Sei sicuro di voler eliminare questa pagina?')) return;

            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);

            fetch('?action=api_delete&id=' + id, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            });
        }
        </script>
    </body>
    </html>
    <?php
}

/**
 * Crea nuova pagina
 */
function createNewPage($db) {
    $title = "Nuova Pagina " . date('d/m/Y H:i');
    $slug = 'page-' . time();
    $uiLibrary = $_GET['ui_library'] ?? 'tailwind';

    $stmt = $db->prepare("INSERT INTO pages (title, slug, ui_library, blocks) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $slug, SQLITE3_TEXT);
    $stmt->bindValue(3, $uiLibrary, SQLITE3_TEXT);
    $stmt->bindValue(4, '[]', SQLITE3_TEXT);
    $stmt->execute();

    $id = $db->lastInsertRowID();
    header("Location: ?action=edit&id={$id}");
    exit;
}

/**
 * Editor Page Builder
 */
function showEditor($db) {
    $id = $_GET['id'] ?? 0;

    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $page = $result->fetchArray(SQLITE3_ASSOC);

    if (!$page) {
        die("Pagina non trovata");
    }
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Editor - <?= htmlspecialchars($page['title']) ?></title>
        <link rel="stylesheet" href="css/builder.css">
    </head>
    <body class="editor">
        <div class="editor-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <a href="?action=dashboard" class="btn-back">← Dashboard</a>
                    <h2>Blocchi</h2>
                </div>

                <div class="blocks-palette">
                    <div class="block-item" draggable="true" data-type="hero">
                        <span class="block-icon">🎯</span>
                        <span>Hero Section</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="text">
                        <span class="block-icon">📝</span>
                        <span>Testo</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="image">
                        <span class="block-icon">🖼️</span>
                        <span>Immagine</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="gallery">
                        <span class="block-icon">🎨</span>
                        <span>Galleria</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="cta">
                        <span class="block-icon">🚀</span>
                        <span>Call to Action</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="features">
                        <span class="block-icon">⭐</span>
                        <span>Features</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="contact">
                        <span class="block-icon">📧</span>
                        <span>Contatti</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="video">
                        <span class="block-icon">🎬</span>
                        <span>Video</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="tabs">
                        <span class="block-icon">📑</span>
                        <span>Tabs</span>
                    </div>
                    <div class="block-item" draggable="true" data-type="countdown">
                        <span class="block-icon">⏱️</span>
                        <span>Countdown</span>
                    </div>
                </div>
            </aside>

            <!-- Main Editor -->
            <main class="editor-main">
                <div class="editor-toolbar">
                    <div class="toolbar-left">
                        <input type="text" id="page-title" value="<?= htmlspecialchars($page['title']) ?>"
                               class="page-title-input" placeholder="Titolo pagina">

                        <select id="ui-library" class="ui-library-select">
                            <option value="tailwind" <?= $page['ui_library'] === 'tailwind' ? 'selected' : '' ?>>Tailwind CSS</option>
                            <option value="bootstrap" <?= $page['ui_library'] === 'bootstrap' ? 'selected' : '' ?>>Bootstrap 5</option>
                            <option value="shadcn" <?= $page['ui_library'] === 'shadcn' ? 'selected' : '' ?>>shadcn/ui</option>
                            <option value="bulma" <?= $page['ui_library'] === 'bulma' ? 'selected' : '' ?>>Bulma</option>
                        </select>

                        <div class="theme-selector">
                            <label>Tema:</label>
                            <select id="theme-selector">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="professional">Professional</option>
                                <option value="creative">Creative</option>
                                <option value="minimal">Minimal</option>
                            </select>
                        </div>
                    </div>

                    <div class="toolbar-right">
                        <div class="responsive-buttons">
                            <button id="btn-desktop" class="btn btn-sm active" onclick="setViewport('desktop')" title="Desktop">🖥️</button>
                            <button id="btn-tablet" class="btn btn-sm" onclick="setViewport('tablet')" title="Tablet">📱</button>
                            <button id="btn-mobile" class="btn btn-sm" onclick="setViewport('mobile')" title="Mobile">📲</button>
                        </div>
                        <button id="btn-undo" class="btn btn-secondary" onclick="undo()" title="Annulla (Ctrl+Z)" style="opacity: 0.5;">↩️</button>
                        <button id="btn-redo" class="btn btn-secondary" onclick="redo()" title="Ripristina (Ctrl+Y)" style="opacity: 0.5;">↪️</button>
                        <button id="btn-preview" class="btn btn-secondary">👁️</button>
                        <button id="btn-save" class="btn btn-primary">💾</button>
                        <a href="?action=export&id=<?= $id ?>" class="btn btn-success">📤</a>
                    </div>
                </div>

                <div id="canvas" class="canvas">
                    <div class="canvas-placeholder">
                        Trascina i blocchi qui per iniziare a costruire la tua pagina
                    </div>
                </div>
            </main>

            <!-- Properties Panel -->
            <aside class="properties-panel" id="properties-panel">
                <div class="properties-header">
                    <h3>Proprietà</h3>
                    <button id="close-properties">×</button>
                </div>
                <div id="properties-content" class="properties-content">
                    <p class="text-muted">Seleziona un blocco per modificarne le proprietà</p>
                </div>
            </aside>
        </div>

        <script>
        const PAGE_ID = <?= $id ?>;
        const CSRF_TOKEN = '<?= getCsrfToken() ?>';
        </script>
        <script src="js/builder.js"></script>
    </body>
    </html>
    <?php
}

/**
 * API: Salva pagina
 */
function apiSavePage($db) {
    // Verifica CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token di sicurezza non valido']);
        return;
    }

    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $blocks = $_POST['blocks'] ?? '[]';
    $uiLibrary = $_POST['ui_library'] ?? 'tailwind';
    $theme = $_POST['theme'] ?? 'light';

    // Verifica se la colonna theme esiste, altrimenti la aggiunge
    $result = $db->query("PRAGMA table_info(pages)");
    $hasTheme = false;
    while ($col = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($col['name'] === 'theme') {
            $hasTheme = true;
            break;
        }
    }
    if (!$hasTheme) {
        $db->exec("ALTER TABLE pages ADD COLUMN theme TEXT DEFAULT 'light'");
    }

    $stmt = $db->prepare("UPDATE pages SET title = ?, blocks = ?, ui_library = ?, theme = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $blocks, SQLITE3_TEXT);
    $stmt->bindValue(3, $uiLibrary, SQLITE3_TEXT);
    $stmt->bindValue(4, $theme, SQLITE3_TEXT);
    $stmt->bindValue(5, $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio']);
    }
}

/**
 * API: Carica pagina
 */
function apiLoadPage($db) {
    $id = $_GET['id'] ?? 0;

    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $page = $result->fetchArray(SQLITE3_ASSOC);

    if ($page) {
        echo json_encode([
            'success' => true,
            'page' => $page
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Pagina non trovata']);
    }
}

/**
 * API: Elimina pagina
 */
function apiDeletePage($db) {
    // Verifica CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token di sicurezza non valido']);
        return;
    }

    $id = $_GET['id'] ?? 0;

    $stmt = $db->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Errore nell\'eliminazione']);
    }
}

/**
 * API: Upload immagine
 */
function apiUploadImage() {
    // Verifica CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token di sicurezza non valido']);
        return;
    }

    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'Nessun file caricato']);
        return;
    }

    $file = $_FILES['image'];

    // Verifica errori upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Errore durante l\'upload']);
        return;
    }

    // Verifica dimensione
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $maxMB = MAX_UPLOAD_SIZE / 1024 / 1024;
        echo json_encode(['success' => false, 'error' => "File troppo grande (max {$maxMB}MB)"]);
        return;
    }

    // Verifica estensione
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        echo json_encode(['success' => false, 'error' => 'Formato file non permesso']);
        return;
    }

    // Verifica MIME type
    $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'error' => 'Tipo di file non valido']);
        return;
    }

    // Crea directory se non esiste
    $uploadDir = UPLOAD_DIR;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Genera nome file sicuro
    $filename = uniqid('img_', true) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    // Sposta file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode([
            'success' => true,
            'url' => 'uploads/' . $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio del file']);
    }
}

/**
 * Export pagina come HTML statico
 */
function exportPage($db) {
    $id = $_GET['id'] ?? 0;

    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $page = $result->fetchArray(SQLITE3_ASSOC);

    if (!$page) {
        die("Pagina non trovata");
    }

    $blocks = json_decode($page['blocks'], true) ?: [];
    $uiLibrary = $page['ui_library'];

    // Genera HTML
    $html = generateHTML($page['title'], $blocks, $uiLibrary);

    // Salva index.html nella root
    $indexPath = EXPORT_DIR . '/index.html';
    file_put_contents($indexPath, $html);

    // Crea directory assets
    if (!is_dir(ASSETS_DIR . 'css')) {
        mkdir(ASSETS_DIR . 'css', 0755, true);
    }
    if (!is_dir(ASSETS_DIR . 'img')) {
        mkdir(ASSETS_DIR . 'img', 0755, true);
    }

    // Copia CSS
    $cssSource = __DIR__ . '/css/theme.css';
    $cssDest = ASSETS_DIR . 'css/style.css';
    if (file_exists($cssSource)) {
        copy($cssSource, $cssDest);
    }

    // Copia immagini
    copyImages($blocks);

    // Redirect al sito esportato
    header("Location: ../index.html");
    exit;
}

/**
 * Genera HTML completo
 */
function generateHTML($title, $blocks, $uiLibrary) {
    $cdnLinks = getUILibraryCDN($uiLibrary);
    $blocksHTML = '';

    foreach ($blocks as $block) {
        $blocksHTML .= renderBlock($block, $uiLibrary);
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    {$cdnLinks}
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    {$blocksHTML}
</body>
</html>
HTML;
}

/**
 * Restituisce i link CDN per la libreria UI
 */
function getUILibraryCDN($library) {
    $cdn = [
        'tailwind' => '<script src="https://cdn.tailwindcss.com"></script>',
        'bootstrap' => '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>',
        'shadcn' => '<script src="https://cdn.tailwindcss.com"></script>',
        'bulma' => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">'
    ];

    return $cdn[$library] ?? $cdn['tailwind'];
}

/**
 * Renderizza un blocco in HTML
 */
function renderBlock($block, $uiLibrary) {
    $type = $block['type'] ?? 'text';
    $content = sanitizeHtml($block['content'] ?? '');
    $settings = $block['settings'] ?? [];

    $templateFile = __DIR__ . "/templates/{$type}.php";

    if (file_exists($templateFile)) {
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    // Fallback
    return "<div class='block block-{$type}'>" . $content . "</div>";
}

/**
 * Copia immagini nella cartella assets
 */
/**
 * Valida un path immagine per prevenire path traversal
 */
function isValidImagePath($path) {
    // Non permettere path vuoti
    if (empty($path)) {
        return false;
    }

    // Non permettere path traversal
    if (strpos($path, '..') !== false) {
        return false;
    }

    // Non permettere path assoluti
    if ($path[0] === '/' || preg_match('/^[a-zA-Z]:/', $path)) {
        return false;
    }

    // Deve iniziare con 'uploads/'
    if (strpos($path, 'uploads/') !== 0) {
        return false;
    }

    return true;
}

function copyImages($blocks) {
    foreach ($blocks as $block) {
        if (isset($block['settings']['image'])) {
            $image = $block['settings']['image'];

            // Valida il path per sicurezza
            if (!isValidImagePath($image)) {
                continue;
            }

            $source = __DIR__ . '/' . $image;
            $dest = ASSETS_DIR . 'img/' . basename($image);

            if (file_exists($source)) {
                copy($source, $dest);
            }
        }

        if (isset($block['settings']['images']) && is_array($block['settings']['images'])) {
            foreach ($block['settings']['images'] as $image) {
                // Valida il path per sicurezza
                if (!isValidImagePath($image)) {
                    continue;
                }

                $source = __DIR__ . '/' . $image;
                $dest = ASSETS_DIR . 'img/' . basename($image);

                if (file_exists($source)) {
                    copy($source, $dest);
                }
            }
        }
    }
}
