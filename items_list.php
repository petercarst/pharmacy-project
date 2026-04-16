<?php 
require 'config.php'; 
requireLogin(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items List - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* Your original full CSS - unchanged */
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

        .view-only-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ede9fe;
            color: #6d28d9;
            border: 1px solid rgba(109,40,217,0.2);
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--accent), #00a98e);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex; align-items: center; gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 3px 10px rgba(0,201,167,0.30);
            text-decoration: none;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(0,201,167,0.40);
            color: white;
        }

        .page-body { padding: 28px 32px; flex: 1; }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: var(--shadow-sm);
            animation: fadeUp 0.4s ease both;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }

        .stat-icon.blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.green  { background: var(--success-soft); color: var(--success); }
        .stat-icon.red    { background: var(--danger-soft); color: var(--danger); }
        .stat-icon.amber  { background: var(--warning-soft); color: var(--warning); }

        .stat-info .label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info .value { font-size: 1.4rem; font-weight: 700; color: var(--text); line-height: 1.2; }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.25s ease both;
        }

        .table-toolbar {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1; min-width: 200px; max-width: 300px;
        }

        .search-box i {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: 0.88rem;
            pointer-events: none;
        }

        .search-box input {
            width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-size: 0.83rem;
            color: var(--text);
            background: var(--surface-2);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
        }

        .filter-select {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-size: 0.83rem;
            color: var(--text);
            background: var(--surface-2);
            outline: none;
            cursor: pointer;
        }

        .items-table { width: 100%; border-collapse: collapse; }

        .items-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .items-table thead th {
            padding: 13px 18px;
            font-size: 0.72rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .items-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody tr:hover { background: #f8fbff; }

        .items-table td {
            padding: 13px 18px;
            font-size: 0.855rem;
            color: var(--text);
            vertical-align: middle;
        }

        .item-cell { display: flex; align-items: center; gap: 11px; }

        .item-icon-wrap {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem; color: #1d4ed8;
            flex-shrink: 0;
        }

        .item-name { font-weight: 600; font-size: 0.88rem; }
        .item-sub  { font-size: 0.72rem; color: var(--text-muted); margin-top: 1px; }

        .category-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
            background: #ede9fe; color: #6d28d9;
            border: 1px solid rgba(109,40,217,0.15);
        }

        .stock-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem; font-weight: 700;
            font-family: 'DM Mono', monospace;
        }

        .stock-badge.ok       { background: var(--success-soft); color: #16a34a; }
        .stock-badge.low      { background: var(--warning-soft); color: #d97706; }
        .stock-badge.critical { background: var(--danger-soft);  color: var(--danger); }

        .stock-badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .unit-tag {
            font-family: 'DM Mono', monospace;
            font-size: 0.76rem; color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            padding: 3px 9px; border-radius: 6px;
        }

        .btn-edit {
            width: 32px; height: 32px;
            background: #dbeafe; color: #1d4ed8;
            border: none; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            cursor: pointer; transition: all 0.15s;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #1d4ed8; color: white;
            transform: scale(1.08);
        }

        .empty-state { text-align: center; padding: 60px 20px; }

        .empty-state .empty-icon {
            width: 70px; height: 70px;
            background: var(--surface-2);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: var(--text-muted);
            margin: 0 auto 16px;
            border: 2px dashed var(--border);
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <div class="top-header">
        <div class="header-left">
            <h4>
                <span class="icon-wrap"><i class="bi bi-box-seam-fill"></i></span>
                Items Inventory
            </h4>
            <p>Manage your pharmacy stock and product catalogue</p>
        </div>

        <?php if (isAdmin()): ?>
            <a href="add_item.php" class="btn-add">
                <i class="bi bi-plus-circle-fill"></i> Add New Item
            </a>
        <?php else: ?>
            <span class="view-only-badge">
                <i class="bi bi-eye-fill"></i> View Only
            </span>
        <?php endif; ?>
    </div>

    <div class="page-body">

        <?php
        $stmt = $pdo->query("SELECT i.*, s.name as supplier_name 
                             FROM items i 
                             LEFT JOIN suppliers s ON i.supplier_id = s.id 
                             ORDER BY i.item_name");
        $items = $stmt->fetchAll();

        $total      = count($items);
        $inStock    = count(array_filter($items, fn($i) => $i['stock'] >= 10));
        $lowStock   = count(array_filter($items, fn($i) => $i['stock'] > 0 && $i['stock'] < 10));
        $outOfStock = count(array_filter($items, fn($i) => $i['stock'] <= 0));
        ?>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Total Items</div>
                    <div class="value"><?= $total ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="label">In Stock</div>
                    <div class="value"><?= $inStock ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Low Stock</div>
                    <div class="value"><?= $lowStock ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Out of Stock</div>
                    <div class="value"><?= $outOfStock ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search items…" oninput="filterTable()">
                </div>
                <select class="filter-select" id="stockFilter" onchange="filterTable()">
                    <option value="">All Stock Levels</option>
                    <option value="ok">In Stock (10+)</option>
                    <option value="low">Low Stock (1–9)</option>
                    <option value="critical">Out of Stock</option>
                </select>
                <div class="toolbar-right">
                    <span class="toolbar-badge" id="countBadge"><?= $total ?> items</span>
                </div>
            </div>

            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Unit</th>
                        <th>Unit Per Pack</th>
                        <th>Supplier</th>
                        <?php if (isAdmin()): ?>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="<?= isAdmin() ? 7 : 6 ?>">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-box-seam"></i></div>
                                <h6>No items found</h6>
                                <p>Add your first item to get started.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($items as $row):
                        $stock = (int)$row['stock'];
                        if ($stock <= 0)     { $stockClass = 'critical'; $stockLabel = 'Out of Stock'; }
                        elseif ($stock < 10) { $stockClass = 'low';      $stockLabel = 'Low'; }
                        else                 { $stockClass = 'ok';       $stockLabel = 'Good'; }
                    ?>
                    <tr data-stock="<?= $stockClass ?>">
                        <td>
                            <div class="item-cell">
                                <div class="item-icon-wrap">
                                    <i class="bi bi-capsule"></i>
                                </div>
                                <div>
                                    <div class="item-name"><?= htmlspecialchars($row['item_name']) ?></div>
                                    <div class="item-sub">ID #<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($row['category'])): ?>
                                <span class="category-pill">
                                    <i class="bi bi-tag-fill"></i>
                                    <?= htmlspecialchars($row['category']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#cbd5e1">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="stock-badge <?= $stockClass ?>">
                                <span class="dot"></span>
                                <?= $stock ?> &nbsp;<small style="font-weight:500;opacity:.7"><?= $stockLabel ?></small>
                            </span>
                        </td>
                        <td>
                            <span class="unit-tag"><?= htmlspecialchars($row['product_unit'] ?? '—') ?></span>
                        </td>
                        <td>
                            <span class="unit-tag"><?= (int)$row['unit_per_pack'] ?> per pack</span>
                        </td>
                        <td>
                            <div class="supplier-cell">
                                <i class="bi bi-building"></i>
                                <?= htmlspecialchars($row['supplier_name'] ?? 'No Supplier') ?>
                            </div>
                        </td>
                        <?php if (isAdmin()): ?>
                        <td>
                            <a href="items.php?id=<?= $row['id'] ?>" class="btn-edit" title="Edit Item">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterTable() {
        const q      = document.getElementById('searchInput').value.toLowerCase();
        const filter = document.getElementById('stockFilter').value;
        const rows   = document.querySelectorAll('#itemsTable tbody tr');
        let visible  = 0;

        rows.forEach(row => {
            const text      = row.textContent.toLowerCase();
            const stockType = row.dataset.stock || '';
            const matchText  = text.includes(q);
            const matchStock = !filter || stockType === filter;

            if (matchText && matchStock) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('countBadge').textContent = visible + ' items';
    }
</script>
</body>
</html>