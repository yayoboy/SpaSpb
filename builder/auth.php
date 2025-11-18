<?php
/**
 * SpaSpb - Sistema di Autenticazione
 */

// Avvia sessione se non già avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se l'utente è autenticato
 */
function isAuthenticated() {
    if (!AUTH_ENABLED) {
        return true; // Se auth è disabilitata, passa sempre
    }

    // Verifica sessione
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        return false;
    }

    // Verifica timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logout();
        return false;
    }

    // Aggiorna last activity
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Richiede autenticazione (redirect a login se non autenticato)
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Se è una richiesta AJAX, restituisci JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Non autenticato', 'redirect' => '?action=login']);
            exit;
        }

        // Altrimenti redirect a login
        header('Location: ?action=login');
        exit;
    }
}

/**
 * Effettua login
 */
function login($username, $password) {
    if (!AUTH_ENABLED) {
        return true;
    }

    if ($username === AUTH_USERNAME && password_verify($password, AUTH_PASSWORD)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }

    return false;
}

/**
 * Effettua logout
 */
function logout() {
    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    session_destroy();
}

/**
 * Genera token CSRF
 */
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica token CSRF
 */
function verifyCsrfToken($token) {
    if (!CSRF_ENABLED) {
        return true;
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Mostra pagina di login
 */
function showLoginPage($error = '') {
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - SpaSpb Page Builder</title>
        <link rel="stylesheet" href="css/builder.css">
        <style>
            .login-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 400px;
            }
            .login-header {
                text-align: center;
                margin-bottom: 32px;
            }
            .login-header h1 {
                font-size: 1.75rem;
                margin-bottom: 8px;
            }
            .login-header p {
                color: #6b7280;
                font-size: 0.95rem;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: #374151;
            }
            .form-group input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.2s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #3b82f6;
            }
            .error-message {
                background: #fee2e2;
                color: #dc2626;
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 0.9rem;
            }
            .btn-login {
                width: 100%;
                padding: 14px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
            }
            .btn-login:hover {
                background: #2563eb;
            }
            .login-footer {
                margin-top: 24px;
                text-align: center;
                color: #6b7280;
                font-size: 0.85rem;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <h1>🎨 SpaSpb Builder</h1>
                    <p>Accedi per continuare</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?action=do_login">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required autofocus autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-login">Accedi</button>
                </form>

                <div class="login-footer">
                    <p><strong>Default:</strong> admin / admin123</p>
                    <p style="margin-top: 8px; color: #ef4444;">⚠️ Cambia le credenziali in config.php!</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
