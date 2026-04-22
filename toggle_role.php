<?php
// toggle_role.php
session_start();

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'parent';
} else {
    $_SESSION['role'] = ($_SESSION['role'] === 'parent') ? 'child' : 'parent';
}

// Redirect back to where the user came from, or dashboard
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// If switching to child, don't land on a parents-only page
if ($_SESSION['role'] === 'child' && strpos($redirect, 'parents.php') !== false) {
    $redirect = 'index.php';
}

header("Location: $redirect");
exit;
