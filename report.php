<?php
require 'config.php';
requireLogin();

$type = $_GET['type'] ?? 'weekly';

// ================= DATE RANGE =================
if ($type == 'weekly') {
    $start = date('Y-m-d', strtotime('-7 days'));
    $title = "Weekly Sales Report";
} else {
    $start = date('Y-m-d', strtotime('-30 days'));
    $title = "Monthly Sales Report";
}

// ================= FETCH SALES =================
$stmt = $pdo->prepare("
    SELECT s.*, i.item_name, c.name AS customer_name
    FROM sales s
    LEFT JOIN items i ON s.item_id = i.id
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE DATE(s.sale_date) >= ?
    ORDER BY s.sale_date DESC
");

$stmt->execute([$start]);
$sales = $stmt->fetchAll();

// ================= TOTALS =================
$total_sales = 0;
$total_qty = 0;

foreach ($sales as $s) {
    $total_sales += $s['total'];
    $total_qty += $s['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Sora',sans-serif;
    background:#f8fafc;
}

.card-box{
    background:#fff;
    border-radius:14px;
    padding:20px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="p-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><?= $title ?></h4>

    <form method="GET" class="d-flex gap-2">
        <select name="type" class="form-control">
            <option value="weekly" <?= $type=='weekly'?'selected':'' ?>>Weekly</option>
            <option value="monthly" <?= $type=='monthly'?'selected':'' ?>>Monthly</option>
        </select>
        <button class="btn btn-primary">Generate</button>
    </form>
</div>

<!-- STATS -->
<div class="row mb-3">

    <div class="col-md-4">
        <div class="card-box">
            <h6>Total Sales</h6>
            <h3>TZS <?= number_format($total_sales) ?></h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <h6>Total Quantity Sold</h6>
            <h3><?= $total_qty ?></h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <h6>Transactions</h6>
            <h3><?= count($sales) ?></h3>
        </div>
    </div>

</div>

<!-- TABLE -->
<div class="card-box">

<table class="table table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Customer</th>
            <th>Qty</th>
            <th>Total (TZS)</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($sales as $s): ?>
        <tr>
            <td><?= date('d M Y', strtotime($s['sale_date'])) ?></td>
            <td><?= htmlspecialchars($s['item_name'] ?? 'Unknown') ?></td>
            <td><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in') ?></td>
            <td><?= $s['quantity'] ?></td>
            <td><?= number_format($s['total']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>

</table>

</div>

</div>

</body>
</html>