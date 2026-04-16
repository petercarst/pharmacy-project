<?php 
require 'config.php'; 
requireLogin(); 

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name      = trim($_POST['item_name']);
    $category       = trim($_POST['category']);
    $product_unit   = trim($_POST['product_unit'] ?? '');
    $stock          = (int)$_POST['stock'];
    $selling_price  = (float)$_POST['selling_price'];
    $unit_per_pack  = (int)$_POST['unit_per_pack'] ?? 1;
    $supplier_name  = trim($_POST['supplier_name'] ?? '');
    $supplier_id    = null;

    if (empty($item_name) || empty($category) || $selling_price <= 0) {
        $error = "Item Name, Category and Selling Price are required!";
    } else {
        $pdo->beginTransaction();
        
        try {
            if (!empty($supplier_name)) {
                $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ?");
                $stmt->execute([$supplier_name]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $supplier_id = $existing['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO suppliers (name) VALUES (?)");
                    $stmt->execute([$supplier_name]);
                    $supplier_id = $pdo->lastInsertId();
                }
            }

            $stmt = $pdo->prepare("INSERT INTO items 
                (item_name, category, product_unit, stock, selling_price, unit_per_pack, 
                 date_delivered, expiration_date, supplier_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $item_name,
                $category,
                $product_unit,
                $stock,
                $selling_price,
                $unit_per_pack,
                $_POST['date_delivered'] ?? null,
                $_POST['expiration_date'] ?? null,
                $supplier_id
            ]);

            $pdo->commit();
            $msg = "✅ New item added successfully!";

        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Your original style - unchanged */
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

        .form-control {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
        }

        .btn-add {
            background: linear-gradient(135deg, var(--accent), #00b894);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <div class="top-header">
        <div class="header-left">
            <h4>
                <span class="icon-wrap"><i class="bi bi-plus-circle"></i></span>
                Add New Item
            </h4>
        </div>
    </div>

    <div class="p-4 p-md-5">

        <?php if($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h5 class="mb-4">Item Details</h5>
            
            <form method="POST">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label required">Item Name</label>
                        <input type="text" name="item_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Category</label>
                        <input type="text" name="category" class="form-control" required 
                               placeholder="e.g. GNR, Tablet, Syrup, Injection">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Unit</label>
                        <input type="text" name="product_unit" class="form-control" 
                               placeholder="e.g. 200G, Box, Bottle, Strip">
                    </div>

                    <!-- New field: Unit Per Pack -->
                    <div class="col-md-6">
                        <label class="form-label">Unit Per Pack</label>
                        <input type="number" name="unit_per_pack" class="form-control" min="1" value="1">
                        <small class="text-muted">How many units in one pack/box (e.g. 10 tablets in 1 box)</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Initial Stock (in packs/boxes)</label>
                        <input type="number" name="stock" class="form-control" min="0" value="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Selling Price (TZS)</label>
                        <input type="number" name="selling_price" class="form-control" step="0.01" min="0" 
                               value="1000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date Delivered</label>
                        <input type="date" name="date_delivered" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Expiration Date</label>
                        <input type="date" name="expiration_date" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" class="form-control" 
                               placeholder="Type supplier name (e.g. MEDI SUPPLY LTD)">
                        <small class="text-muted">New suppliers will be created automatically</small>
                    </div>
                </div>

                <div class="mt-5 d-flex gap-3">
                    <button type="submit" class="btn btn-add">
                        <i class="bi bi-plus-lg"></i> Add Item to Inventory
                    </button>
                    <a href="items_list.php" class="btn btn-secondary px-4 py-2">
                        ← Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>