<?php
require 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email) VALUES (?, ?, ?)");
    $stmt->execute([$name, $phone, $email]);

    header("Location: customers.php?success=1");
    exit;
}
?>