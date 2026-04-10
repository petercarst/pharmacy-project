<?php 
require 'config.php'; 

// If user is already logged in, go directly to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Otherwise, redirect to login page
header("Location: login.php");
exit;
?>