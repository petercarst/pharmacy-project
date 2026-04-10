<?php 
require 'config.php'; 
requireLogin();  // ✅ only check login, no role restriction

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: customers.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - BETHEL PHARMACY</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f4c75;
        }
        body {
            font-family: 'Sora', sans-serif;
            background: #f8fafc;
        }
        .view-card {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(15,76,117,0.12);
            overflow: hidden;
        }
        .view-header {
            background: linear-gradient(135deg, var(--primary), #1b6ca8);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .avatar-big {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            background: rgba(255,255,255,0.25);
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="p-5">
    <div class="view-card">

        <!-- Header -->
        <div class="view-header">
            <div class="avatar-big">
                <?= strtoupper(substr($customer['name'], 0, 2)) ?>
            </div>
            <h3><?= htmlspecialchars($customer['name']) ?></h3>
            <p class="mb-0 opacity-75">Customer Profile</p>
        </div>

        <!-- Details -->
        <div class="p-5">
            <div class="row g-4">

                <div class="col-12">
                    <label class="text-muted small text-uppercase">Customer ID</label>
                    <p class="fw-bold fs-5">
                        #<?= str_pad($customer['id'], 4, '0', STR_PAD_LEFT) ?>
                    </p>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Phone Number</label>
                    <p class="fs-5">
                        <?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'Not provided' ?>
                    </p>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Email Address</label>
                    <p class="fs-5">
                        <?= !empty($customer['email']) ? htmlspecialchars($customer['email']) : 'Not provided' ?>
                    </p>
                </div>

                <div class="col-12">
                    <label class="text-muted small text-uppercase">Date Added</label>
                    <p class="fs-5">
                        <?= isset($customer['created_at']) ? date('d M Y', strtotime($customer['created_at'])) : 'N/A' ?>
                    </p>
                </div>

            </div>

            <div class="mt-5">
                <a href="customers.php" class="btn btn-secondary btn-lg">
                    ← Back to Customers
                </a>

                <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn btn-primary btn-lg ms-3">
                    <i class="bi bi-pencil"></i> Edit Customer
                </a>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>