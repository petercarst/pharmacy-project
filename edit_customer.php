<?php 
require 'config.php'; 
requireLogin();  // only check login, no role restriction

$error = '';
$msg = '';

// Get customer ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: customers.php");
    exit;
}

// Fetch customer
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name)) {
        $error = "Customer name is required!";
    } else {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $email, $id]);

        $msg = "Customer updated successfully!";

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $customer = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - BETHEL PHARMACY</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Sora', sans-serif;
            background: #f8fafc;
        }
        .form-card {
            max-width: 520px;
            margin: 40px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15,76,117,0.1);
            padding: 40px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0f4c75, #1b6ca8);
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="p-5">
    <div class="form-card">
        <h4 class="mb-4">
            <i class="bi bi-pencil-square"></i> Edit Customer
        </h4>

        <?php if($msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    class="form-control" 
                    value="<?= htmlspecialchars($customer['name']) ?>" 
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input 
                    type="text" 
                    name="phone" 
                    class="form-control" 
                    value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                >
            </div>

            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control" 
                    value="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                >
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary flex-fill py-2">
                    <i class="bi bi-check-circle"></i> Update Customer
                </button>

                <a href="customers.php" class="btn btn-secondary flex-fill py-2">
                    Back
                </a>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>