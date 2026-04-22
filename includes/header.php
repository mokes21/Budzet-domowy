<?php
// includes/header.php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'parent'; // default to parent view
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = ucfirst(str_replace('.php', '', $current_page));
if ($page_title == 'Index') $page_title = 'Dashboard';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Budget Manager - <?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= $_SESSION['role'] === 'child' ? 'mode-child' : 'mode-parent' ?>">

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <span class="icon">💰</span>
            <h2>FamilyBudget</h2>
        </div>
        <nav class="side-nav">
            <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="start_of_month.php" class="<?= $current_page == 'start_of_month.php' ? 'active' : '' ?>">Start of Month</a>
            <a href="transactions.php" class="<?= $current_page == 'transactions.php' ? 'active' : '' ?>">Transactions</a>
            <a href="savings.php" class="<?= $current_page == 'savings.php' ? 'active' : '' ?>">Savings & Goals</a>
            <?php if ($_SESSION['role'] === 'parent'): ?>
                <a href="parents.php" class="<?= $current_page == 'parents.php' ? 'active' : '' ?>">Parents Only</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <div class="role-indicator">
                Current View: <strong><?= ucfirst($_SESSION['role']) ?></strong>
            </div>
            <a class="toggle-btn" href="toggle_role.php">Switch to <?= $_SESSION['role'] === 'parent' ? 'Child' : 'Parent' ?></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <div class="top-right-actions">
                <a class="toggle-btn alt" href="toggle_role.php">
                    🔄 Switch to <?= $_SESSION['role'] === 'parent' ? 'Child' : 'Parent' ?>
                </a>
            </div>
        </header>

        <div class="content-body">
            <!-- Page content begins -->
