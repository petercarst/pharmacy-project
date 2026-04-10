<?php 
require 'config.php'; 
requireLogin(); 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: customers.php?deleted=1");
        exit;
    } catch (Exception $e) {
        header("Location: customers.php?error=1");
        exit;
    }
} else {
    header("Location: customers.php");
    exit;
}
?>