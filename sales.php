<?php 
require 'config.php'; 
requireLogin(); 

$stmt = $pdo->query("SELECT s.*, i.item_name, c.name as customer_name 
                     FROM sales s 
                     JOIN items i ON s.item_id = i.id 
                     LEFT JOIN customers c ON s.customer_id = c.id 
                     ORDER BY s.sale_date DESC");
$sales = $stmt->fetchAll();

$today         = date('Y-m-d');
$total_revenue = array_sum(array_column($sales, 'total'));
$today_revenue = array_sum(array_map(fn($s) => $s['sale_date'] === $today ? $s['total'] : 0, $sales));
$today_count   = count(array_filter($sales, fn($s) => $s['sale_date'] === $today));
$walkin_count  = count(array_filter($sales, fn($s) => empty($s['customer_name'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - BETHEL PHARMACY</title>
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

        /* ── Layout ── */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Header ── */
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

        .btn-new-sale {
            background: linear-gradient(135deg, var(--accent), #00a98e);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex; align-items: center; gap: 7px;
            transition: all 0.2s;
            box-shadow: 0 3px 10px rgba(0,201,167,0.30);
            text-decoration: none;
        }

        .btn-new-sale:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(0,201,167,0.40);
            color: white;
        }

        /* ── Page Body ── */
        .page-body { padding: 28px 32px; flex: 1; }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .stat-card.teal::after  { background: linear-gradient(90deg, #0097a7, var(--accent)); }
        .stat-card.green::after { background: linear-gradient(90deg, #16a34a, var(--success)); }
        .stat-card.blue::after  { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
        .stat-card.amber::after { background: linear-gradient(90deg, #d97706, var(--warning)); }

        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.20s; }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }

        .stat-icon.teal  { background: var(--accent-soft); color: var(--accent); }
        .stat-icon.green { background: var(--success-soft); color: var(--success); }
        .stat-icon.blue  { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.amber { background: var(--warning-soft); color: var(--warning); }

        .stat-info .label {
            font-size: 0.72rem; color: var(--text-muted);
            font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .stat-info .value {
            font-size: 1.3rem; font-weight: 700;
            color: var(--text); line-height: 1.2;
            font-family: 'DM Mono', monospace;
        }

        .stat-info .value .currency {
            font-size: 0.78rem; font-weight: 500;
            color: var(--text-muted);
            font-family: 'Sora', sans-serif;
            margin-right: 3px;
        }

        /* ── Table Card ── */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.25s ease both;
        }

        /* ── Toolbar ── */
        .table-toolbar {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1; min-width: 200px; max-width: 280px;
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
            font-size: 0.83rem; color: var(--text);
            background: var(--surface-2); outline: none;
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
            font-size: 0.83rem; color: var(--text);
            background: var(--surface-2); outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .filter-select:focus { border-color: var(--primary-light); }

        .toolbar-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }

        .toolbar-badge {
            background: var(--accent-soft); color: var(--accent);
            font-size: 0.75rem; font-weight: 600;
            padding: 4px 12px; border-radius: 20px;
            border: 1px solid rgba(0,201,167,0.2);
            white-space: nowrap;
        }

        /* ── Sales Table ── */
        .sales-table { width: 100%; border-collapse: collapse; }

        .sales-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .sales-table thead th {
            padding: 12px 18px;
            font-size: 0.70rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .sales-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .sales-table tbody tr:last-child { border-bottom: none; }
        .sales-table tbody tr:hover { background: #f8fbff; }

        .sales-table td {
            padding: 13px 18px;
            font-size: 0.845rem;
            vertical-align: middle;
        }

        /* Date cell */
        .date-wrap { display: flex; flex-direction: column; gap: 1px; }
        .date-main { font-family: 'DM Mono', monospace; font-size: 0.82rem; font-weight: 600; color: var(--text); }
        .date-sub  { font-size: 0.70rem; color: var(--text-muted); }

        /* Today badge */
        .today-dot {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 6px var(--accent);
            margin-right: 5px;
            vertical-align: middle;
        }

        /* Item cell */
        .item-cell { display: flex; align-items: center; gap: 9px; }

        .item-avatar {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; color: #1d4ed8; flex-shrink: 0;
        }

        .item-name { font-weight: 600; font-size: 0.87rem; color: var(--text); }

        /* Qty badge */
        .qty-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: #ede9fe; color: #6d28d9;
            font-size: 0.76rem; font-weight: 700;
            padding: 4px 10px; border-radius: 6px;
            font-family: 'DM Mono', monospace;
        }

        /* Amount */
        .amount-cell {
            font-family: 'DM Mono', monospace;
            font-weight: 700; font-size: 0.88rem;
            color: var(--text); text-align: right;
        }

        .amount-cell .curr {
            font-size: 0.68rem; font-weight: 500;
            color: var(--text-muted);
            font-family: 'Sora', sans-serif;
            margin-right: 2px;
        }

        /* Customer cell */
        .customer-cell { display: flex; align-items: center; gap: 7px; }

        .customer-avatar {
            width: 26px; height: 26px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            font-size: 0.65rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .customer-avatar.walkin {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        .customer-name { font-size: 0.83rem; color: var(--text); }
        .walkin-label  { font-size: 0.8rem; color: var(--text-muted); font-style: italic; }

        /* Empty state */
        .empty-state {
            padding: 60px 20px; text-align: center;
        }

        .empty-state .empty-icon {
            width: 64px; height: 64px;
            background: var(--surface-2);
            border: 2px dashed var(--border);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: #cbd5e1;
            margin: 0 auto 14px;
        }

        .empty-state h6 { font-weight: 600; color: var(--text-muted); }
        .empty-state p  { font-size: 0.82rem; color: #94a3b8; margin-top: 4px; }

        /* Totals footer row */
        .totals-row td {
            background: var(--surface-2);
            border-top: 2px solid var(--border) !important;
            font-weight: 700;
            font-size: 0.84rem;
            padding: 12px 18px;
        }

        .totals-row .amount-cell {
            color: var(--primary);
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .page-body { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .top-header { padding: 14px 16px; }
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
                <span class="icon-wrap"><i class="bi bi-cart-check-fill"></i></span>
                Sales Ledger
            </h4>
            <p>Track all pharmacy sales and revenue</p>
        </div>
        <a href="new_sale.php" class="btn-new-sale">
            <i class="bi bi-plus-circle-fill"></i> New Sale
        </a>
    </div>

    <div class="page-body">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card teal">
                <div class="stat-icon teal"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-info">
                    <div class="label">Total Revenue</div>
                    <div class="value"><span class="currency">TZS</span><?= number_format($total_revenue) ?></div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Today's Revenue</div>
                    <div class="value"><span class="currency">TZS</span><?= number_format($today_revenue) ?></div>
                </div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue"><i class="bi bi-receipt"></i></div>
                <div class="stat-info">
                    <div class="label">Today's Sales</div>
                    <div class="value"><?= number_format($today_count) ?></div>
                </div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon amber"><i class="bi bi-person-walking"></i></div>
                <div class="stat-info">
                    <div class="label">Walk-in Sales</div>
                    <div class="value"><?= number_format($walkin_count) ?></div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search item or customer…" oninput="filterTable()">
                </div>
                <select class="filter-select" id="customerFilter" onchange="filterTable()">
                    <option value="">All Customers</option>
                    <option value="walkin">Walk-in Only</option>
                    <option value="registered">Registered Only</option>
                </select>
                <div class="toolbar-right">
                    <span class="toolbar-badge" id="countBadge"><?= count($sales) ?> records</span>
                </div>
            </div>

            <table class="sales-table" id="salesTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th style="text-align:right">Amount (TZS)</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-cart-x"></i></div>
                                <h6>No sales recorded yet</h6>
                                <p>Click "New Sale" to record your first transaction.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sales as $row):
                        $isToday    = $row['sale_date'] === $today;
                        $isWalkin   = empty($row['customer_name']);
                        $initials   = $isWalkin ? '?' : strtoupper(substr($row['customer_name'], 0, 1));
                        $words      = explode(' ', trim($row['customer_name'] ?? ''));
                        if (!$isWalkin && count($words) >= 2)
                            $initials = strtoupper($words[0][0] . $words[1][0]);
                    ?>
                    <tr data-customer="<?= $isWalkin ? 'walkin' : 'registered' ?>">
                        <td>
                            <div class="date-wrap">
                                <div class="date-main">
                                    <?php if ($isToday): ?><span class="today-dot"></span><?php endif; ?>
                                    <?= date('d M Y', strtotime($row['sale_date'])) ?>
                                </div>
                                <?php if ($isToday): ?>
                                <div class="date-sub" style="color:var(--accent);font-weight:600;">Today</div>
                                <?php else: ?>
                                <div class="date-sub"><?= date('l', strtotime($row['sale_date'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="item-cell">
                                <div class="item-avatar"><i class="bi bi-capsule"></i></div>
                                <span class="item-name"><?= htmlspecialchars($row['item_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="qty-badge">
                                <i class="bi bi-x"></i><?= $row['quantity'] ?>
                            </span>
                        </td>
                        <td class="amount-cell">
                            <span class="curr">TZS</span><?= number_format($row['total'], 2) ?>
                        </td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar <?= $isWalkin ? 'walkin' : '' ?>">
                                    <?= $isWalkin ? '<i class="bi bi-person" style="font-size:0.7rem"></i>' : htmlspecialchars($initials) ?>
                                </div>
                                <?php if ($isWalkin): ?>
                                    <span class="walkin-label">Walk-in Customer</span>
                                <?php else: ?>
                                    <span class="customer-name"><?= htmlspecialchars($row['customer_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Totals footer -->
                    <tr class="totals-row" id="totalsRow">
                        <td colspan="3" style="color:var(--text-muted); font-size:0.8rem; letter-spacing:0.5px; text-transform:uppercase;">
                            Grand Total — <?= count($sales) ?> transactions
                        </td>
                        <td class="amount-cell">
                            <span class="curr">TZS</span><?= number_format($total_revenue, 2) ?>
                        </td>
                        <td></td>
                    </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterTable() {
        const q          = document.getElementById('searchInput').value.toLowerCase();
        const custFilter = document.getElementById('customerFilter').value;
        const rows       = document.querySelectorAll('#salesTable tbody tr:not(.totals-row)');
        let visible = 0, visibleTotal = 0;

        rows.forEach(row => {
            const text     = row.textContent.toLowerCase();
            const custType = row.dataset.customer || '';

            const matchText = text.includes(q);
            const matchCust = !custFilter || custType === custFilter;

            if (matchText && matchCust) {
                row.style.display = '';
                visible++;
                // Sum visible amounts
                const amtCell = row.querySelector('.amount-cell');
                if (amtCell) {
                    const raw = amtCell.textContent.replace(/[^0-9.]/g, '');
                    visibleTotal += parseFloat(raw) || 0;
                }
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('countBadge').textContent = visible + ' records';

        // Update totals row
        const totalsRow = document.getElementById('totalsRow');
        if (totalsRow) {
            totalsRow.cells[0].textContent = `Grand Total — ${visible} transactions`;
            totalsRow.querySelector('.amount-cell').innerHTML =
                `<span class="curr">TZS</span>${visibleTotal.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
        }
    }
</script>
</body>
</html>