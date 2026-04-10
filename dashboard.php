<?php 
require 'config.php'; 
requireLogin(); 

// ==================== FETCH REAL DATA ====================
$total_items     = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$today = date('Y-m-d');
$today_sales = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM sales WHERE sale_date = ?");
$today_sales->execute([$today]);
$todays_total = $today_sales->fetchColumn();

// Total revenue — admin only
$total_sales_all = isAdmin()
    ? $pdo->query("SELECT COALESCE(SUM(total), 0) FROM sales")->fetchColumn()
    : 0;

$low_stock   = $pdo->query("SELECT COUNT(*) FROM items WHERE stock < 10")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM items WHERE stock <= 0")->fetchColumn();

// Yesterday's sales for trend
$yesterday = date('Y-m-d', strtotime('-1 day'));
$yest_sales = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM sales WHERE sale_date = ?");
$yest_sales->execute([$yesterday]);
$yesterdays_total = $yest_sales->fetchColumn();
$trend = $yesterdays_total > 0 ? round((($todays_total - $yesterdays_total) / $yesterdays_total) * 100, 1) : null;

// Recent Sales (last 6)
$recent_sales = $pdo->query("SELECT s.*, i.item_name 
                              FROM sales s 
                              JOIN items i ON s.item_id = i.id 
                              ORDER BY s.sale_date DESC, s.id DESC 
                              LIMIT 6")->fetchAll();

// Low stock items list
$low_stock_items = $pdo->query("SELECT item_name, stock FROM items WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();

// KPI grid columns: 4 for admin, 3 for pharmacist
$kpiCols = isAdmin() ? 4 : 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BETHEL PHARMACY</title>
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

        /* Welcome chip */
        .welcome-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 6px 14px 6px 6px;
        }

        .welcome-chip .avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; color: white;
        }

        .welcome-chip span {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .welcome-chip strong {
            color: var(--text);
        }

        /* ── Page Body ── */
        .page-body { padding: 28px 32px; flex: 1; }

        /* ── Welcome Banner ── */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, #1e87c8 100%);
            border-radius: var(--radius);
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            position: relative;
            animation: fadeUp 0.4s ease both;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            right: 60px; bottom: -60px;
            width: 150px; height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .banner-text h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .banner-text p {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.65);
            margin-top: 4px;
        }

        /* Role badge inside banner */
        .banner-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 0.72rem;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0.5px;
        }

        .banner-date {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            padding: 10px 18px;
            text-align: center;
            backdrop-filter: blur(4px);
            z-index: 1;
        }

        .banner-date .day {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            line-height: 1;
            font-family: 'DM Mono', monospace;
        }

        .banner-date .month {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(<?= $kpiCols ?>, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
            animation: fadeUp 0.4s ease both;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .kpi-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            color: inherit;
        }

        .kpi-card:nth-child(1) { animation-delay: 0.08s; }
        .kpi-card:nth-child(2) { animation-delay: 0.13s; }
        .kpi-card:nth-child(3) { animation-delay: 0.18s; }
        .kpi-card:nth-child(4) { animation-delay: 0.23s; }

        .kpi-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .kpi-card.blue::after   { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
        .kpi-card.green::after  { background: linear-gradient(90deg, #16a34a, var(--success)); }
        .kpi-card.amber::after  { background: linear-gradient(90deg, #d97706, var(--warning)); }
        .kpi-card.teal::after   { background: linear-gradient(90deg, #0097a7, var(--accent)); }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .kpi-icon {
            width: 42px; height: 42px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; flex-shrink: 0;
        }

        .kpi-card.blue  .kpi-icon { background: #dbeafe; color: #1d4ed8; }
        .kpi-card.green .kpi-icon { background: var(--success-soft); color: #16a34a; }
        .kpi-card.amber .kpi-icon { background: var(--warning-soft); color: #d97706; }
        .kpi-card.teal  .kpi-icon { background: var(--accent-soft); color: #0097a7; }

        .kpi-trend {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
        }

        .kpi-trend.up   { background: var(--success-soft); color: #16a34a; }
        .kpi-trend.down { background: var(--danger-soft); color: var(--danger); }
        .kpi-trend.flat { background: var(--surface-2); color: var(--text-muted); }

        .kpi-value {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            letter-spacing: -0.5px;
            font-family: 'DM Mono', monospace;
        }

        .kpi-value .currency {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            font-family: 'Sora', sans-serif;
            margin-right: 3px;
        }

        .kpi-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 5px;
        }

        /* ── Bottom Grid ── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }

        /* ── Section Card ── */
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.30s ease both;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header h6 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .section-header h6 i {
            width: 28px; height: 28px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
        }

        .section-header h6 i.teal-icon  { background: var(--accent-soft); color: var(--accent); }
        .section-header h6 i.amber-icon { background: var(--warning-soft); color: var(--warning); }

        .section-link {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--primary-light);
            text-decoration: none;
            display: flex; align-items: center; gap: 4px;
        }

        .section-link:hover { color: var(--accent); }

        /* ── Sales Table ── */
        .sales-table { width: 100%; border-collapse: collapse; }

        .sales-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .sales-table thead th {
            padding: 11px 16px;
            font-size: 0.70rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px;
        }

        .sales-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .sales-table tbody tr:last-child { border-bottom: none; }
        .sales-table tbody tr:hover { background: #f8fbff; }

        .sales-table td {
            padding: 12px 16px;
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .item-pill {
            display: flex; align-items: center; gap: 8px;
        }

        .item-pill .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
        }

        .date-tag {
            font-family: 'DM Mono', monospace;
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        .qty-badge {
            background: #ede9fe; color: #6d28d9;
            font-size: 0.76rem; font-weight: 600;
            padding: 3px 9px; border-radius: 6px;
            font-family: 'DM Mono', monospace;
        }

        .amount-cell {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
            font-size: 0.84rem;
            color: var(--text);
            text-align: right;
        }

        .amount-cell .curr {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 400;
            margin-right: 2px;
        }

        /* ── Low Stock Panel ── */
        .stock-list { padding: 8px 0; }

        .stock-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .stock-item:last-child { border-bottom: none; }
        .stock-item:hover { background: var(--surface-2); }

        .stock-item-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: var(--warning-soft);
            color: var(--warning);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0;
        }

        .stock-item-name {
            flex: 1;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stock-level {
            font-family: 'DM Mono', monospace;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .stock-level.critical { background: var(--danger-soft); color: var(--danger); }
        .stock-level.low      { background: var(--warning-soft); color: #d97706; }

        /* Alert banner */
        .alert-banner {
            display: flex; align-items: center; gap: 12px;
            background: var(--warning-soft);
            border: 1px solid rgba(244,162,97,0.35);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            animation: fadeUp 0.4s 0.28s ease both;
        }

        .alert-banner .alert-icon {
            width: 34px; height: 34px;
            background: var(--warning);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 0.9rem; flex-shrink: 0;
        }

        .alert-banner .alert-text { flex: 1; }
        .alert-banner .alert-text strong { font-size: 0.85rem; color: #92400e; display: block; }
        .alert-banner .alert-text span   { font-size: 0.78rem; color: #a16207; }

        .alert-banner .alert-link {
            font-size: 0.78rem; font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            background: white;
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid var(--border);
            white-space: nowrap;
            transition: all 0.15s;
        }

        .alert-banner .alert-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Empty state */
        .empty-row td {
            padding: 40px 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.84rem;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; }
            .bottom-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .page-body { padding: 16px; }
            .kpi-grid { grid-template-columns: 1fr 1fr; }
            .welcome-banner { flex-direction: column; gap: 16px; align-items: flex-start; }
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
                <span class="icon-wrap"><i class="bi bi-grid-fill"></i></span>
                Dashboard
            </h4>
            <p>Overview of your pharmacy operations</p>
        </div>
        <div class="welcome-chip">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'U', 0, 1)) ?>
            </div>
            <span>Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></strong></span>
        </div>
    </div>

    <div class="page-body">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-text">
                <h2>Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'] ?? 'User')[0]) ?> 👋</h2>
                <p>Here's what's happening at Bethel Pharmacy today.</p>
                <!-- Role badge -->
                <span class="banner-role-badge">
                    <i class="bi bi-<?= isAdmin() ? 'shield-fill' : 'capsule-pill' ?>"></i>
                    <?= isAdmin() ? 'Administrator' : 'Pharmacist' ?>
                </span>
            </div>
            <div class="banner-date">
                <div class="day"><?= date('d') ?></div>
                <div class="month"><?= date('M Y') ?></div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <?php if ($low_stock > 0): ?>
        <div class="alert-banner">
            <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="alert-text">
                <strong>Low Stock Alert — <?= $low_stock ?> item<?= $low_stock > 1 ? 's' : '' ?> need restocking</strong>
                <span><?= $out_of_stock ?> item<?= $out_of_stock != 1 ? 's' : '' ?> completely out of stock</span>
            </div>
            <a href="items_list.php" class="alert-link"><i class="bi bi-arrow-right-circle"></i> View Items</a>
        </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="kpi-grid">

            <!-- Items: visible to all -->
            <a href="items_list.php" class="kpi-card blue">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="bi bi-box-seam-fill"></i></div>
                    <span class="kpi-trend flat"><i class="bi bi-dash"></i> Items</span>
                </div>
                <div class="kpi-value"><?= number_format($total_items) ?></div>
                <div class="kpi-label">Total Products</div>
            </a>

            <!-- Customers: visible to all -->
            <a href="customers.php" class="kpi-card green">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
                    <span class="kpi-trend flat"><i class="bi bi-dash"></i> Customers</span>
                </div>
                <div class="kpi-value"><?= number_format($total_customers) ?></div>
                <div class="kpi-label">Registered Customers</div>
            </a>

            <!-- Today's Revenue: visible to all -->
            <a href="sales.php" class="kpi-card amber">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="bi bi-cart-check-fill"></i></div>
                    <?php if ($trend !== null): ?>
                        <span class="kpi-trend <?= $trend >= 0 ? 'up' : 'down' ?>">
                            <i class="bi bi-arrow-<?= $trend >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($trend) ?>%
                        </span>
                    <?php else: ?>
                        <span class="kpi-trend flat">Today</span>
                    <?php endif; ?>
                </div>
                <div class="kpi-value">
                    <span class="currency">TZS</span><?= number_format($todays_total) ?>
                </div>
                <div class="kpi-label">Today's Revenue</div>
            </a>

            <!-- Total Revenue (All Time): ADMIN ONLY -->
            <?php if (isAdmin()): ?>
            <a href="sales.php" class="kpi-card teal">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <span class="kpi-trend flat">All Time</span>
                </div>
                <div class="kpi-value">
                    <span class="currency">TZS</span><?= number_format($total_sales_all) ?>
                </div>
                <div class="kpi-label">Total Revenue</div>
            </a>
            <?php endif; ?>

        </div>

        <!-- Bottom Grid -->
        <div class="bottom-grid">

            <!-- Recent Sales Table: visible to all -->
            <div class="section-card">
                <div class="section-header">
                    <h6>
                        <i class="bi bi-clock-history teal-icon"></i>
                        Recent Sales
                    </h6>
                    <a href="sales.php" class="section-link">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th style="text-align:right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_sales) > 0): ?>
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td><span class="date-tag"><?= date('d M', strtotime($sale['sale_date'])) ?></span></td>
                                <td>
                                    <div class="item-pill">
                                        <span class="dot"></span>
                                        <?= htmlspecialchars($sale['item_name']) ?>
                                    </div>
                                </td>
                                <td><span class="qty-badge">×<?= $sale['quantity'] ?></span></td>
                                <td class="amount-cell">
                                    <span class="curr">TZS</span><?= number_format($sale['total']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="4">
                                    <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px;color:#cbd5e1"></i>
                                    No sales recorded yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock Panel: visible to all -->
            <div class="section-card" style="animation-delay:0.35s">
                <div class="section-header">
                    <h6>
                        <i class="bi bi-exclamation-triangle-fill amber-icon"></i>
                        Low Stock Items
                    </h6>
                    <a href="items_list.php?filter=low" class="section-link">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stock-list">
                    <?php if (count($low_stock_items) > 0): ?>
                        <?php foreach ($low_stock_items as $item): ?>
                        <div class="stock-item">
                            <div class="stock-item-icon">
                                <i class="bi bi-capsule"></i>
                            </div>
                            <div class="stock-item-name" title="<?= htmlspecialchars($item['item_name']) ?>">
                                <?= htmlspecialchars($item['item_name']) ?>
                            </div>
                            <span class="stock-level <?= $item['stock'] <= 0 ? 'critical' : 'low' ?>">
                                <?= $item['stock'] <= 0 ? 'Out' : $item['stock'] . ' left' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($low_stock > 5): ?>
                        <div style="padding:10px 20px; font-size:0.78rem; color:var(--text-muted); text-align:center;">
                            +<?= $low_stock - 5 ?> more items need attention
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="padding:40px 20px; text-align:center; color:var(--text-muted); font-size:0.84rem;">
                            <i class="bi bi-check-circle-fill" style="font-size:1.5rem;display:block;margin-bottom:6px;color:var(--success)"></i>
                            All items are well stocked
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>