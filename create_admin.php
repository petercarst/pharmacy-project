<?php
require 'config.php';

$username = "admin";
$full_name = "System Admin";
$password = "admin123";
$role = "admin";

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $full_name, $hashed, $role]);

echo "Admin created successfully!";
?>