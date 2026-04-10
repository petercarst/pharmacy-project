<?php 
require 'config.php'; 
requireLogin();

// Restrict access: only admin can register users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $full_name  = trim($_POST['full_name']);
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($full_name) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $full_name]);
            
            $success = "User account created successfully!";
            
            // Optional: clear form values after success
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username already exists! Please choose another.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --accent: #00c9a7;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(15, 76, 117, 0.25);
            max-width: 460px;
            width: 100%;
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .register-header .icon-wrap {
            width: 75px;
            height: 75px;
            background: rgba(255,255,255,0.2);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 2rem;
        }

        .register-body {
            padding: 40px 35px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(27,108,168,0.15);
        }

        .btn-register {
            background: linear-gradient(135deg, var(--accent), #00b894);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 201, 167, 0.4);
        }
    </style>
</head>
<body>

<div class="register-card">
    <!-- Header -->
    <div class="register-header">
        <div class="icon-wrap">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <h2>Create Account</h2>
        <p class="mb-0 opacity-75">BETHEL PHARMACY</p>
    </div>

    <!-- Body -->
    <div class="register-body">
        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" 
                       value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Create password" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
            </div>

            <button type="submit" class="btn btn-register w-100">
                <i class="bi bi-person-plus"></i> Create Account
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted small">
                Already have an account? 
                <a href="login.php" class="text-decoration-none fw-semibold" style="color: var(--primary);">Login here</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
