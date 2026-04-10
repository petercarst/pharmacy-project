<?php 
require 'config.php'; 
requireLogin(); 

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id     = (int)$_POST['item_id'];
    $quantity    = (int)$_POST['quantity'];
    $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;

    $pdo->beginTransaction();

    try {
        // Get item details including selling price
        $stmt = $pdo->prepare("SELECT item_name, stock, selling_price FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Item not found!");
        }

        if ($item['stock'] < $quantity) {
            throw new Exception("Not enough stock! Available: " . $item['stock']);
        }

        // Calculate total using real selling price
        $price_per_unit = $item['selling_price'] ?? 1000;
        $total = $quantity * $price_per_unit;

        // Record the sale
        $stmt = $pdo->prepare("INSERT INTO sales (item_id, quantity, total, customer_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$item_id, $quantity, $total, $customer_id]);

        // Decrease stock
        $stmt = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $item_id]);

        $pdo->commit();
        $message = "Sale completed successfully! Stock updated.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale - BETHEL PHARMACY</title>
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
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 4px 20px rgba(15, 76, 117, 0.08);
        }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .top-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 22px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left h4 {
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-left .icon-wrap {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .form-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 32px;
            margin-top: 24px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
        }

        .btn-sale {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .stock-info {
            font-size: 0.82rem;
            color: #10b981;
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <h4>
                <span class="icon-wrap"><i class="bi bi-cart-plus"></i></span>
                New Sale
            </h4>
        </div>
    </div>

    <div class="p-4 p-md-5">

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h5 class="mb-4">Record New Sale</h5>
            
            <form method="POST">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label required">Select Item</label>
                        <select name="item_id" class="form-select" required>
                            <option value="">-- Choose Item --</option>
                            <?php
                            $items = $pdo->query("SELECT id, item_name, stock, selling_price 
                                                FROM items 
                                                ORDER BY item_name")->fetchAll();
                            foreach($items as $item):
                                $price_display = number_format($item['selling_price'] ?? 1000);
                            ?>
                            <option value="<?= $item['id'] ?>">
                                <?= htmlspecialchars($item['item_name']) ?> 
                                — TZS <?= $price_display ?> 
                                (Stock: <?= $item['stock'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Customer (Optional)</label>
                        <select name="customer_id" class="form-select">
                            <option value="">Walk-in Customer</option>
                            <?php
                            $customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
                            foreach($customers as $c):
                            ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-sale w-100">
                        <i class="bi bi-cart-check"></i> Complete Sale & Update Stock
                    </button>
                    <div class="mt-3">
                         <a href="sales.php" class="btn btn-secondary w-100">
                            ← Back
                         </a>
                  </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>