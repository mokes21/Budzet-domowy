<?php
/**
 * Configuration file for Family Budget Manager
 */

// Database configuration
if (!defined('DB_PATH')) {
    define('DB_PATH', __DIR__ . '/budget.db');
}

// Formatting constants
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '$');
}
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'Y-m-d');
}
if (!defined('DISPLAY_DATE_FORMAT')) {
    define('DISPLAY_DATE_FORMAT', 'M d, Y');
}

// Categories for transactions
if (!defined('EXPENSE_CATEGORIES')) {
    define('EXPENSE_CATEGORIES', ['Groceries', 'Rent', 'Salary', 'Entertainment', 'Transport', 'Medical', 'Other']);
}
if (!defined('INCOME_CATEGORIES')) {
    define('INCOME_CATEGORIES', ['Salary', 'Bonus', 'Investment', 'Other']);
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize flash messages array if not exists
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}
?>
