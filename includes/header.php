<?php
/**
 * Header template with navigation
 */

// Determine current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Budget Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">💰 Budget</h1>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="transactions.php" class="nav-link <?php echo $current_page === 'transactions.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">💳</span>
                    <span class="nav-text">Transactions</span>
                </a>
                <a href="savings.php" class="nav-link <?php echo $current_page === 'savings.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏦</span>
                    <span class="nav-text">Savings</span>
                </a>
                <a href="goals.php" class="nav-link <?php echo $current_page === 'goals.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🎯</span>
                    <span class="nav-text">Goals</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <p class="footer-text">Family Budget Manager v1.0</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
