<?php
// Script per inizializzare il database SQLite

$dbPath = __DIR__ . '/db/data.db';

// Crea la directory se non esiste
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // Inserisci impostazioni di default
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
    $stmt->execute(['default_ui_library', 'tailwind']);

    echo "Database inizializzato con successo!\n";
    echo "Percorso: {$dbPath}\n";

} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
