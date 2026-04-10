<?php 
require 'config.php'; 
requireLogin();
requireAdmin();

// ==================== UPDATE LOGIC ====================
if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_GET['id'];

    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;

    $stmt = $pdo->prepare("UPDATE items SET 
        upc_ean_isbn = ?, 
        product_id = ?, 
        item_name = ?, 
        category = ?, 
        product_unit = ?, 
        date_delivered = ?, 
        expiration_date = ?, 
        supplier_id = ?, 
        stock = ?, 
        selling_price = ? 
        WHERE id = ?");

    $stmt->execute([
        $_POST['upc_ean_isbn'] ?? '',
        $_POST['product_id'] ?? '',
        $_POST['item_name'] ?? '',
        $_POST['category'] ?? '',
        $_POST['product_unit'] ?? '',
        $_POST['date_delivered'] ?? null,
        $_POST['expiration_date'] ?? null,
        $supplier_id,
        (int)($_POST['stock'] ?? 0),
        (float)($_POST['selling_price'] ?? 1000),
        $id
    ]);

    $msg = "Item updated successfully!";
}

// ==================== FETCH ITEM ====================
$item = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $item = $stmt->fetch();
}

if (!$item) {
    header("Location: items_list.php");
    exit;
}

// ==================== FETCH SUPPLIERS ====================
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Item</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary:#0f4c75;
    --primary-light:#1b6ca8;
    --bg:#f8fafc;
    --surface:#fff;
    --border:#e2e8f0;
}

body{
    font-family:'Sora',sans-serif;
    background:var(--bg);
}

.main-content{ margin-left:260px; }

.top-header{
    background:#fff;
    padding:20px 30px;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.icon-wrap{
    width:42px;height:42px;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    margin-right:10px;
}

.form-card{
    background:#fff;
    padding:30px;
    border-radius:15px;
    margin-top:20px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.form-label{
    font-size:12px;
    font-weight:600;
    text-transform:uppercase;
    color:#64748b;
}

.btn-update{
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:#fff;
    padding:12px 30px;
    border-radius:50px;
    border:none;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<div class="top-header">
    <h4 class="d-flex align-items-center">
        <span class="icon-wrap"><i class="bi bi-pencil-square"></i></span>
        Update Item
    </h4>
</div>

<div class="p-4">

<?php if(isset($msg)): ?>
<div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<div class="form-card">

<h5 class="mb-4">Item Information</h5>

<form method="POST">
<div class="row g-3">

<div class="col-md-6">
<label class="form-label">UPC/EAN/ISBN</label>
<input type="text" name="upc_ean_isbn" class="form-control"
value="<?= htmlspecialchars($item['upc_ean_isbn'] ?? '') ?>">
</div>

<div class="col-md-6">
<label class="form-label">Product ID</label>
<input type="text" name="product_id" class="form-control"
value="<?= htmlspecialchars($item['product_id'] ?? '') ?>">
</div>

<div class="col-12">
<label class="form-label">Item Name</label>
<input type="text" name="item_name" class="form-control" required
value="<?= htmlspecialchars($item['item_name']) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Category</label>
<input type="text" name="category" class="form-control" required
value="<?= htmlspecialchars($item['category']) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Product Unit</label>
<input type="text" name="product_unit" class="form-control"
value="<?= htmlspecialchars($item['product_unit']) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Stock</label>
<input type="number" name="stock" class="form-control"
value="<?= $item['stock'] ?>">
</div>

<div class="col-md-6">
<label class="form-label">Selling Price</label>
<input type="number" name="selling_price" class="form-control"
value="<?= $item['selling_price'] ?>">
</div>

<div class="col-md-6">
<label class="form-label">Date Delivered</label>
<input type="date" name="date_delivered" class="form-control"
value="<?= $item['date_delivered'] ?>">
</div>

<div class="col-md-6">
<label class="form-label">Expiration Date</label>
<input type="date" name="expiration_date" class="form-control"
value="<?= $item['expiration_date'] ?>">
</div>

<!-- SUPPLIER -->
<div class="col-12">
<label class="form-label">Supplier</label>
<select name="supplier_id" class="form-control">
    <option value="">-- Select Supplier --</option>
    <?php foreach($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>"
            <?= ($item['supplier_id'] == $s['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
</div>

</div>

<div class="mt-4">
    <button class="btn-update">Update Item</button>
    <a href="items_list.php" class="btn btn-secondary">Back</a>
</div>

</form>

</div>

</div>

</div>

</body>
</html>