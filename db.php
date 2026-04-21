<?php
/**
 * Database initialization and PDO connection
 */

require_once __DIR__ . '/config.php';

/**
 * Get or create PDO connection to SQLite database
 */
function get_db() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            init_database($pdo);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Initialize database tables if they don't exist
 */
function init_database($pdo) {
    try {
        // Create transactions table
        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date DATE NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('income', 'expense')),
            category TEXT NOT NULL,
            amount REAL NOT NULL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Create savings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS savings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date DATE NOT NULL,
            amount REAL NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('deposit', 'withdrawal')),
            notes TEXT,
            goal_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (goal_id) REFERENCES goals(id)
        )");

        // Create goals table
        $pdo->exec("CREATE TABLE IF NOT EXISTS goals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            target_amount REAL NOT NULL,
            saved_amount REAL DEFAULT 0,
            deadline DATE,
            notes TEXT,
            is_complete INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

    } catch (PDOException $e) {
        die('Database initialization failed: ' . $e->getMessage());
    }
}

?>
