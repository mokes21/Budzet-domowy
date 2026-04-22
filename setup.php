<?php
// setup.php
require_once 'includes/db.php';

echo "Setting up SQLite Database for Family Budget Manager...<br>";

try {
    // 1. Transactions Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,         -- 'income' or 'expense'
            category TEXT NOT NULL,     -- 'food', 'books', 'bus_passes', etc.
            amount REAL NOT NULL,
            description TEXT,
            date TEXT NOT NULL,         -- YYYY-MM-DD
            by_child INTEGER DEFAULT 0  -- 0 (Parent), 1 (Child)
        )
    ");
    echo "Table 'transactions' created.<br>";

    // 2. Goals Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS goals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            target_amount REAL NOT NULL,
            current_amount REAL DEFAULT 0,
            for_child INTEGER DEFAULT 0
        )
    ");
    echo "Table 'goals' created.<br>";

    // 3. Savings Table (General savings)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS savings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            amount REAL DEFAULT 0,
            for_child INTEGER DEFAULT 0
        )
    ");
    echo "Table 'savings' created.<br>";
    
    // Check if savings defaults exist, if not insert
    $stmt = $pdo->query("SELECT COUNT(*) FROM savings WHERE for_child = 0");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO savings (amount, for_child) VALUES (0, 0)"); // Parent savings
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM savings WHERE for_child = 1");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO savings (amount, for_child) VALUES (0, 1)"); // Child savings
    }

    echo "Database setup completed successfully!";

} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
