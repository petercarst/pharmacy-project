<?php
require 'config.php';
requireLogin();

$id = (int)$_GET['id'];

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: manage_user.php");
exit;
?>