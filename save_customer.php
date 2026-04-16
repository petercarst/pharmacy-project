<?php
require 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($name)) {
        header("Location: customers.php?error=Name is required");
        exit;
    }

    if (empty($email)) {
        header("Location: customers.php?error=Email is required");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $email]);

        header("Location: customers.php?success=1");
        exit;

    } catch (PDOException $e) {
        // Check if it's a duplicate email error (MySQL error code 23000)
        if ($e->getCode() == 23000) {
            header("Location: customers.php?error=This email address is already registered!");
        } else {
            header("Location: customers.php?error=Failed to add customer. Please try again.");
        }
        exit;
    }
}

// If someone accesses this file directly
header("Location: customers.php");
exit;
?>