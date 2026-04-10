<?php
// ======================
// CONFIG.PHP - Database & Session Setup (UPDATED)
// ======================

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================
// DATABASE CONNECTION
// ======================
$host     = 'localhost';
$dbname   = 'pharmacy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// ======================
// AUTH FUNCTIONS
// ======================

// Check login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login (FIXED)
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// ======================
// ROLE CHECKS
// ======================

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPharmacist() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pharmacist';
}

// Require Admin (FIXED - NO FORCE DASHBOARD REDIRECT LOOP)
function requireAdmin() {
    if (!isAdmin()) {
        // Instead of redirect loop, show message or fallback
        header("Location: dashboard.php?error=access_denied");
        exit();
    }
}

// ======================
// SECURITY HELPERS
// ======================

// Sanitize output (avoid repeating htmlspecialchars everywhere)
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect helper (clean redirects)
function redirect($url) {
    header("Location: $url");
    exit();
}
?>