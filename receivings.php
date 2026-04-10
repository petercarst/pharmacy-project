<?php 
require 'config.php'; 
requireLogin();
requireAdmin(); // ← ADMIN ONLY: pharmacists are redirected to dashboard

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id       = (int)$_POST['item_id'];
    $quantity      = (int)$_POST['quantity'];
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $supplier_id   = null;

    if ($quantity <= 0) {
        $error = "Quantity must be greater than 0.";
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
            $stmt = $pdo->prepare("INSERT INTO receivings (item_id, quantity, supplier_id) VALUES (?, ?, ?)");
            $stmt->execute([$item_id, $quantity, $supplier_id]);
            $stmt = $pdo->prepare("UPDATE items SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$quantity, $item_id]);
            $pdo->commit();
            $message = "Stock received successfully! Inventory has been updated.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

$items     = $pdo->query("SELECT id, item_name, stock FROM items ORDER BY item_name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
$recent    = $pdo->query("SELECT r.*, i.item_name, s.name as supplier_name 
                           FROM receivings r 
                           JOIN items i ON r.item_id = i.id 
                           LEFT JOIN suppliers s ON r.supplier_id = s.id 
                           ORDER BY r.date_received DESC LIMIT 15")->fetchAll();

$total_received_today = $pdo->query("SELECT COALESCE(SUM(quantity),0) FROM receivings WHERE DATE(date_received) = CURDATE()")->fetchColumn();
$total_suppliers      = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$total_receivings     = $pdo->query("SELECT COUNT(*) FROM receivings")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receivings - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:       #0f4c75;
            --primary-light: #1b6ca8;
            --accent:        #00c9a7;
            --accent-soft:   #e6faf7;
            --danger:        #e63946;
            --danger-soft:   #fde8ea;
            --warning:       #f4a261;
            --warning-soft:  #fff4e0;
            --success:       #22c55e;
            --success-soft:  #dcfce7;
            --bg:            #f0f4f8;
            --surface:       #ffffff;
            --surface-2:     #f8fafc;
            --border:        #e2e8f0;
            --text:          #1a2332;
            --text-muted:    #64748b;
            --shadow-sm:     0 1px 3px rgba(0,0,0,0.06);
            --shadow-md:     0 4px 16px rgba(15,76,117,0.10);
            --shadow-lg:     0 8px 32px rgba(15,76,117,0.14);
            --radius:        14px;
            --radius-sm:     8px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 20px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .header-left h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-left h4 .icon-wrap {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1rem;
            box-shadow: 0 3px 8px rgba(15,76,117,0.25);
        }

        .header-left p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 2px;
            padding-left: 48px;
        }

        .page-body { padding: 28px 32px; flex: 1; }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: var(--shadow-sm);
            animation: fadeUp 0.4s ease both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }

        .stat-icon.green  { background: var(--success-soft); color: var(--success); }
        .stat-icon.blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.teal   { background: var(--accent-soft); color: var(--accent); }

        .stat-info .label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info .value { font-size: 1.4rem; font-weight: 700; color: var(--text); line-height: 1.2; font-family: 'DM Mono', monospace; }

        .main-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 24px;
            align-items: start;
        }

        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            position: sticky;
            top: 100px;
            animation: fadeUp 0.4s 0.20s ease both;
        }

        .form-card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 20px 24px;
        }

        .form-card-header h5 {
            font-size: 0.98rem;
            font-weight: 700;
            color: white;
            display: flex; align-items: center; gap: 9px;
            margin: 0;
        }

        .form-card-header h5 .hicon {
            width: 30px; height: 30px;
            background: rgba(255,255,255,0.18);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
        }

        .form-card-header p {
            font-size: 0.76rem;
            color: rgba(255,255,255,0.60);
            margin-top: 4px;
            padding-left: 39px;
        }

        .form-card-body { padding: 24px; }

        .form-group { margin-bottom: 20px; }

        .form-label-custom {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 7px;
            display: block;
        }

        .form-label-custom .req { color: var(--danger); margin-left: 2px; }

        .input-icon-wrap { position: relative; }

        .input-icon-wrap i {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: 0.88rem;
            pointer-events: none; z-index: 2;
        }

        .input-icon-wrap .form-control,
        .input-icon-wrap .form-select { padding-left: 38px; }

        .form-control,
        .form-select {
            font-family: 'Sora', sans-serif;
            font-size: 0.87rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            color: var(--text);
            background: var(--surface-2);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            width: 100%;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
            background: white;
        }

        .form-hint { font-size: 0.72rem; color: var(--text-muted); margin-top: 5px; }

        .stock-hint {
            display: none;
            margin-top: 8px;
            padding: 9px 13px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            color: var(--text-muted);
            align-items: center;
            gap: 8px;
        }

        .stock-hint.visible { display: flex; }
        .stock-hint .stock-val { font-family: 'DM Mono', monospace; font-weight: 700; }
        .stock-hint .stock-val.ok       { color: var(--success); }
        .stock-hint .stock-val.low      { color: var(--warning); }
        .stock-hint .stock-val.critical { color: var(--danger); }

        .form-divider { height: 1px; background: var(--border); margin: 20px 0; }

        .btn-receive {
            width: 100%;
            background: linear-gradient(135deg, var(--accent), #00a98e);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 3px 12px rgba(0,201,167,0.30);
            letter-spacing: 0.2px;
        }

        .btn-receive:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 18px rgba(0,201,167,0.40);
        }

        .alert-banner {
            display: flex; align-items: center; gap: 12px;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            animation: fadeUp 0.3s ease both;
            font-size: 0.85rem;
        }

        .alert-banner.success {
            background: var(--success-soft);
            border: 1px solid rgba(34,197,94,0.25);
            color: #15803d;
        }

        .alert-banner.error {
            background: var(--danger-soft);
            border: 1px solid rgba(230,57,70,0.25);
            color: #b91c1c;
        }

        .alert-banner i { font-size: 1.1rem; flex-shrink: 0; }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.25s ease both;
        }

        .table-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }

        .table-card-header h6 {
            font-size: 0.9rem; font-weight: 700;
            color: var(--text);
            display: flex; align-items: center; gap: 8px;
            margin: 0;
        }

        .table-card-header h6 .hbadge {
            width: 28px; height: 28px;
            border-radius: 8px;
            background: var(--accent-soft);
            color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.82rem;
        }

        .record-count {
            font-size: 0.75rem; font-weight: 600;
            color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            padding: 3px 11px; border-radius: 20px;
        }

        .recv-table { width: 100%; border-collapse: collapse; }

        .recv-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .recv-table thead th {
            padding: 12px 18px;
            font-size: 0.70rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .recv-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .recv-table tbody tr:last-child { border-bottom: none; }
        .recv-table tbody tr:hover { background: #f8fbff; }

        .recv-table td {
            padding: 13px 18px;
            font-size: 0.845rem;
            vertical-align: middle;
        }

        .date-tag {
            font-family: 'DM Mono', monospace;
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        .item-cell { display: flex; align-items: center; gap: 9px; }
        .item-cell .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary-light); flex-shrink: 0; }
        .item-cell .name { font-weight: 600; color: var(--text); }

        .qty-in {
            display: inline-flex; align-items: center; gap: 5px;
            font-family: 'DM Mono', monospace;
            font-size: 0.82rem; font-weight: 700;
            color: #16a34a;
            background: var(--success-soft);
            border: 1px solid rgba(34,197,94,0.2);
            padding: 4px 11px; border-radius: 7px;
        }

        .supplier-cell { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: var(--text-muted); }
        .supplier-cell i { font-size: 0.75rem; }

        .empty-state { padding: 50px 20px; text-align: center; }
        .empty-state .empty-icon {
            width: 60px; height: 60px;
            background: var(--surface-2);
            border: 2px dashed var(--border);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #cbd5e1;
            margin: 0 auto 12px;
        }
        .empty-state h6 { font-weight: 600; color: var(--text-muted); font-size: 0.9rem; }
        .empty-state p  { font-size: 0.78rem; color: #94a3b8; margin-top: 4px; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .main-grid { grid-template-columns: 1fr; }
            .form-card { position: static; }
            .stats-bar { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .page-body { padding: 16px; }
            .stats-bar { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <!-- Header -->
    <div class="top-header">
        <div class="header-left">
            <h4>
                <span class="icon-wrap"><i class="bi bi-truck-front-fill"></i></span>
                Stock Receivings
            </h4>
            <p>Record incoming stock and update inventory levels</p>
        </div>
    </div>

    <div class="page-body">

        <?php if ($message): ?>
        <div class="alert-banner success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert-banner error">
            <i class="bi bi-x-circle-fill"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon teal"><i class="bi bi-arrow-down-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Total Receivings</div>
                    <div class="value"><?= number_format($total_receivings) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-boxes"></i></div>
                <div class="stat-info">
                    <div class="label">Units In Today</div>
                    <div class="value"><?= number_format($total_received_today) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-building"></i></div>
                <div class="stat-info">
                    <div class="label">Total Suppliers</div>
                    <div class="value"><?= number_format($total_suppliers) ?></div>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">

            <!-- Form Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <h5>
                        <span class="hicon"><i class="bi bi-box-arrow-in-down"></i></span>
                        Receive New Stock
                    </h5>
                    <p>Fill in the details to update inventory</p>
                </div>
                <div class="form-card-body">
                    <form method="POST" id="receiveForm">

                        <div class="form-group">
                            <label class="form-label-custom">Select Item <span class="req">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-box-seam"></i>
                                <select name="item_id" class="form-select" required id="itemSelect" onchange="showStock(this)">
                                    <option value="">— Choose a product —</option>
                                    <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['id'] ?>" data-stock="<?= $item['stock'] ?>">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="stock-hint" id="stockHint">
                                <i class="bi bi-info-circle"></i>
                                Current stock: <span class="stock-val" id="stockVal">—</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label-custom">Quantity Received <span class="req">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-123"></i>
                                <input type="number" name="quantity" class="form-control"
                                       min="1" required placeholder="e.g. 50">
                            </div>
                        </div>

                        <div class="form-divider"></div>

                        <div class="form-group">
                            <label class="form-label-custom">Supplier Name</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-building"></i>
                                <input type="text" name="supplier_name" class="form-control"
                                       placeholder="e.g. MEDI SUPPLY LTD"
                                       list="suppliersList">
                                <datalist id="suppliersList">
                                    <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= htmlspecialchars($s['name']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <p class="form-hint"><i class="bi bi-lightbulb"></i> Type a new name to auto-create, or leave blank.</p>
                        </div>

                        <button type="submit" class="btn-receive">
                            <i class="bi bi-arrow-down-circle-fill"></i>
                            Confirm Stock Receipt
                        </button>

                    </form>
                </div>
            </div>

            <!-- Recent Receivings Table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h6>
                        <span class="hbadge"><i class="bi bi-clock-history"></i></span>
                        Recent Receivings
                    </h6>
                    <span class="record-count"><?= count($recent) ?> records</span>
                </div>

                <table class="recv-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Qty In</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-truck"></i></div>
                                    <h6>No receivings yet</h6>
                                    <p>Use the form to record your first stock receipt.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent as $row): ?>
                        <tr>
                            <td><span class="date-tag"><?= date('d M Y', strtotime($row['date_received'])) ?></span></td>
                            <td>
                                <div class="item-cell">
                                    <span class="dot"></span>
                                    <span class="name"><?= htmlspecialchars($row['item_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="qty-in">
                                    <i class="bi bi-plus-lg"></i><?= $row['quantity'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="supplier-cell">
                                    <i class="bi bi-building"></i>
                                    <?= htmlspecialchars($row['supplier_name'] ?? 'No Supplier') ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showStock(select) {
        const opt   = select.options[select.selectedIndex];
        const stock = parseInt(opt.dataset.stock ?? '-1');
        const hint  = document.getElementById('stockHint');
        const val   = document.getElementById('stockVal');

        if (!opt.value) {
            hint.classList.remove('visible');
            return;
        }

        hint.classList.add('visible');
        val.textContent = stock + ' units';
        val.className   = 'stock-val';

        if (stock <= 0)      val.classList.add('critical');
        else if (stock < 10) val.classList.add('low');
        else                 val.classList.add('ok');
    }
</script>
</body>
</html>