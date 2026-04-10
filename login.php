<?php 
require 'config.php'; 

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php"); 
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];        

    header("Location: dashboard.php");
    exit;
} else {
        $error = "Incorrect username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --accent: #00c9a7;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #1e2937;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(15, 76, 117, 0.25);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header .icon-wrap {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.2rem;
        }

        .login-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .login-body {
            padding: 40px 35px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
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

        .btn-login {
            background: linear-gradient(135deg, var(--accent), #00b894);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 201, 167, 0.4);
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Header -->
    <div class="login-header">
        <div class="icon-wrap">
            <i class="bi bi-capsule-pill"></i>
        </div>
        <h2>BETHEL</h2>
        <p class="mb-0 opacity-75">PHARMACY MANAGEMENT SYSTEM</p>
    </div>

    <!-- Body -->
    <div class="login-body">
        <h5 class="text-center mb-4">Sign In to Continue</h5>

        <?php if($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted small">
                Don't have an account? 
                <a href="register.php" class="text-decoration-none fw-semibold" style="color: var(--primary);">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>