<?php
// includes/db.php

$db_file = __DIR__ . '/../database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch objects by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Ensure budgets table exists (Auto-migration for new feature)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS budgets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL DEFAULT 0,
        for_child INTEGER DEFAULT 0
    )
");

// Initialize budget rows if missing
$stmt = $pdo->query("SELECT COUNT(*) FROM budgets");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO budgets (amount, for_child) VALUES (0, 0), (0, 1)");
}
