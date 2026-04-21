<?php
/**
 * Configuration file for Family Budget Manager
 */

// Database configuration
define('DB_PATH', __DIR__ . '/budget.db');

// Formatting constants
define('CURRENCY_SYMBOL', '$');
define('DATE_FORMAT', 'Y-m-d');
define('DISPLAY_DATE_FORMAT', 'M d, Y');

// Categories for transactions
define('EXPENSE_CATEGORIES', ['Groceries', 'Rent', 'Salary', 'Entertainment', 'Transport', 'Medical', 'Other']);
define('INCOME_CATEGORIES', ['Salary', 'Bonus', 'Investment', 'Other']);

// Session configuration
session_start();

// Initialize flash messages array if not exists
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}
?>
