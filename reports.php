<?php
require 'config.php';
requireLogin();

// ── Defaults ──────────────────────────────────────────
$selected_month = $_GET['month'] ?? date('Y-m');
[$year, $month] = explode('-', $selected_month);
$month_start    = "$year-$month-01";
$month_end      = date('Y-m-t', strtotime($month_start));
$month_label    = date('F Y', strtotime($month_start));

// ── Sales this month ───────────────────────────────────
$sales_stmt = $pdo->prepare("
    SELECT s.*, i.item_name, i.category, c.name AS customer_name
    FROM sales s
    JOIN items i ON s.item_id = i.id
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.sale_date BETWEEN ? AND ?
    ORDER BY s.sale_date ASC
");
$sales_stmt->execute([$month_start, $month_end]);
$sales = $sales_stmt->fetchAll();

$total_revenue   = array_sum(array_column($sales, 'total'));
$total_sales_qty = array_sum(array_column($sales, 'quantity'));
$total_txn       = count($sales);
$walkin_count    = count(array_filter($sales, fn($s) => empty($s['customer_name'])));
$avg_sale        = $total_txn > 0 ? $total_revenue / $total_txn : 0;

// ── Expenses this month ────────────────────────────────
$exp_stmt = $pdo->prepare("
    SELECT * FROM expenses
    WHERE expense_date BETWEEN ? AND ?
    ORDER BY expense_date ASC
");
$exp_stmt->execute([$month_start, $month_end]);
$expenses      = $exp_stmt->fetchAll();
$total_expenses = array_sum(array_column($expenses, 'amount'));
$net_profit     = $total_revenue - $total_expenses;

// ── Top selling items ──────────────────────────────────
$top_items_stmt = $pdo->prepare("
    SELECT i.item_name, i.category,
           SUM(s.quantity) AS total_qty,
           SUM(s.total)    AS total_rev
    FROM sales s
    JOIN items i ON s.item_id = i.id
    WHERE s.sale_date BETWEEN ? AND ?
    GROUP BY s.item_id
    ORDER BY total_rev DESC
    LIMIT 8
");
$top_items_stmt->execute([$month_start, $month_end]);
$top_items = $top_items_stmt->fetchAll();

// ── Daily sales breakdown ──────────────────────────────
$daily_stmt = $pdo->prepare("
    SELECT sale_date,
           SUM(total)    AS daily_rev,
           COUNT(*)      AS daily_txn
    FROM sales
    WHERE sale_date BETWEEN ? AND ?
    GROUP BY sale_date
    ORDER BY sale_date ASC
");
$daily_stmt->execute([$month_start, $month_end]);
$daily_sales = $daily_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build lookup for chart
$daily_map = [];
foreach ($daily_sales as $d) $daily_map[$d['sale_date']] = (float)$d['daily_rev'];

// Fill all days in month
$chart_labels = [];
$chart_values = [];
$days_in_month = (int)date('t', strtotime($month_start));
for ($d = 1; $d <= $days_in_month; $d++) {
    $key = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
    $chart_labels[] = $d;
    $chart_values[] = $daily_map[$key] ?? 0;
}

// ── Previous month comparison ──────────────────────────
$prev_start = date('Y-m-01', strtotime('-1 month', strtotime($month_start)));
$prev_end   = date('Y-m-t',  strtotime($prev_start));
$prev_rev_row = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ?");
$prev_rev_row->execute([$prev_start, $prev_end]);
$prev_revenue = (float)$prev_rev_row->fetchColumn();
$rev_change   = $prev_revenue > 0 ? round((($total_revenue - $prev_revenue) / $prev_revenue) * 100, 1) : null;

// ── Available months (for selector) ───────────────────
$months_stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(sale_date,'%Y-%m') AS ym FROM sales ORDER BY ym DESC LIMIT 24");
$available_months = $months_stmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array(date('Y-m'), $available_months)) array_unshift($available_months, date('Y-m'));

$max_top_rev = !empty($top_items) ? max(array_column($top_items, 'total_rev')) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report – BETHEL PHARMACY</title>
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
            display: flex; align-items: center; gap: 10px;
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
            font-size: 0.78rem; color: var(--text-muted);
            margin-top: 2px; padding-left: 48px;
        }

        .header-actions { display: flex; align-items: center; gap: 10px; }

        /* Month Selector */
        .month-selector {
            display: flex; align-items: center; gap: 8px;
            background: var(--surface-2);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px 14px;
        }

        .month-selector i { color: var(--text-muted); font-size: 0.9rem; }

        .month-selector select {
            font-family: 'Sora', sans-serif;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text);
            background: transparent;
            border: none;
            outline: none;
            cursor: pointer;
        }

        /* Print / Export buttons */
        .btn-action {
            display: flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-family: 'Sora', sans-serif;
            font-size: 0.83rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.18s;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background: var(--surface-2);
            border: 1.5px solid var(--border);
            color: var(--text-muted);
        }

        .btn-print:hover { border-color: var(--primary-light); color: var(--primary); }

        .btn-pdf {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 3px 10px rgba(15,76,117,0.25);
        }

        .btn-pdf:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(15,76,117,0.35);
            color: white;
        }

        /* ── Page Body ── */
        .page-body { padding: 28px 32px; flex: 1; }

        /* ── Report Header Banner ── */
        .report-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, #1e87c8 100%);
            border-radius: var(--radius);
            padding: 24px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            overflow: hidden;
            position: relative;
            animation: fadeUp 0.4s ease both;
        }

        .report-banner::before {
            content: '';
            position: absolute; right: -30px; top: -40px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }

        .report-banner::after {
            content: '';
            position: absolute; right: 80px; bottom: -50px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .banner-left h3 {
            font-size: 1.2rem; font-weight: 700; color: white;
            letter-spacing: -0.3px;
        }

        .banner-left p {
            font-size: 0.78rem; color: rgba(255,255,255,0.60);
            margin-top: 3px;
        }

        .banner-right {
            display: flex; gap: 12px; z-index: 1;
        }

        .banner-stat {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            padding: 10px 18px;
            text-align: center;
            backdrop-filter: blur(4px);
        }

        .banner-stat .bval {
            font-size: 1.1rem; font-weight: 700; color: white;
            font-family: 'DM Mono', monospace; line-height: 1;
        }

        .banner-stat .blabel {
            font-size: 0.66rem; color: rgba(255,255,255,0.55);
            text-transform: uppercase; letter-spacing: 0.8px; margin-top: 3px;
        }

        /* ── KPI Row ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 17px 18px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.4s ease both;
        }

        .kpi-card:nth-child(1) { animation-delay: 0.06s; }
        .kpi-card:nth-child(2) { animation-delay: 0.11s; }
        .kpi-card:nth-child(3) { animation-delay: 0.16s; }
        .kpi-card:nth-child(4) { animation-delay: 0.21s; }
        .kpi-card:nth-child(5) { animation-delay: 0.26s; }

        .kpi-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0; height: 3px;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .kpi-card.teal::after  { background: linear-gradient(90deg, #0097a7, var(--accent)); }
        .kpi-card.green::after { background: linear-gradient(90deg, #16a34a, var(--success)); }
        .kpi-card.red::after   { background: linear-gradient(90deg, #be123c, var(--danger)); }
        .kpi-card.blue::after  { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
        .kpi-card.amber::after { background: linear-gradient(90deg, #d97706, var(--warning)); }

        .kpi-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem; margin-bottom: 12px;
        }

        .kpi-card.teal  .kpi-icon { background: var(--accent-soft); color: var(--accent); }
        .kpi-card.green .kpi-icon { background: var(--success-soft); color: #16a34a; }
        .kpi-card.red   .kpi-icon { background: var(--danger-soft);  color: var(--danger); }
        .kpi-card.blue  .kpi-icon { background: #dbeafe; color: #1d4ed8; }
        .kpi-card.amber .kpi-icon { background: var(--warning-soft); color: #d97706; }

        .kpi-val {
            font-size: 1.25rem; font-weight: 700; color: var(--text);
            font-family: 'DM Mono', monospace; line-height: 1;
        }

        .kpi-val .curr {
            font-size: 0.7rem; font-weight: 500; color: var(--text-muted);
            font-family: 'Sora', sans-serif; margin-right: 2px;
        }

        .kpi-label { font-size: 0.73rem; color: var(--text-muted); font-weight: 500; margin-top: 5px; }

        .kpi-trend {
            font-size: 0.70rem; font-weight: 600;
            padding: 2px 7px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 3px;
            margin-top: 6px;
        }

        .kpi-trend.up   { background: var(--success-soft); color: #16a34a; }
        .kpi-trend.down { background: var(--danger-soft); color: var(--danger); }
        .kpi-trend.flat { background: var(--surface-2); color: var(--text-muted); }

        /* ── Two-col layout ── */
        .report-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            margin-bottom: 22px;
        }

        /* ── Section Cards ── */
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.30s ease both;
        }

        .section-head {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }

        .section-head h6 {
            font-size: 0.88rem; font-weight: 700; color: var(--text);
            display: flex; align-items: center; gap: 8px; margin: 0;
        }

        .section-head h6 .sh-icon {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.82rem;
        }

        .sh-icon.teal  { background: var(--accent-soft); color: var(--accent); }
        .sh-icon.amber { background: var(--warning-soft); color: var(--warning); }
        .sh-icon.blue  { background: #dbeafe; color: #1d4ed8; }
        .sh-icon.red   { background: var(--danger-soft); color: var(--danger); }

        .section-sub {
            font-size: 0.73rem; color: var(--text-muted); font-weight: 400;
        }

        /* ── Chart Container ── */
        .chart-wrap {
            padding: 20px;
            height: 220px;
            position: relative;
        }

        /* ── Top Items Bars ── */
        .top-items-list { padding: 12px 20px; }

        .top-item-row {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 13px;
        }

        .top-item-row:last-child { margin-bottom: 0; }

        .ti-rank {
            width: 20px; font-size: 0.72rem;
            font-family: 'DM Mono', monospace;
            color: var(--text-muted); font-weight: 600;
            flex-shrink: 0; text-align: right;
        }

        .ti-info { flex: 1; min-width: 0; }

        .ti-name {
            font-size: 0.82rem; font-weight: 600; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .ti-bar-wrap {
            height: 5px; background: var(--border);
            border-radius: 10px; margin-top: 5px; overflow: hidden;
        }

        .ti-bar {
            height: 100%; border-radius: 10px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 0.8s cubic-bezier(.4,0,.2,1);
        }

        .ti-rev {
            font-family: 'DM Mono', monospace;
            font-size: 0.76rem; font-weight: 700;
            color: var(--text); flex-shrink: 0; text-align: right;
            white-space: nowrap;
        }

        .ti-qty {
            font-size: 0.68rem; color: var(--text-muted);
            margin-top: 2px; text-align: right;
        }

        /* ── Full-width table card ── */
        .full-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 22px;
            animation: fadeUp 0.4s 0.35s ease both;
        }

        /* ── Tables ── */
        .data-table { width: 100%; border-collapse: collapse; }

        .data-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .data-table thead th {
            padding: 11px 16px;
            font-size: 0.70rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.14s;
        }

        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: #f8fbff; }

        .data-table td {
            padding: 11px 16px;
            font-size: 0.835rem;
            vertical-align: middle;
        }

        .date-mono { font-family: 'DM Mono', monospace; font-size: 0.78rem; color: var(--text-muted); }

        .item-pill { display: flex; align-items: center; gap: 7px; }

        .item-pill .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--primary-light); flex-shrink: 0;
        }

        .item-pill .dot.exp { background: var(--danger); }

        .qty-tag {
            font-family: 'DM Mono', monospace;
            font-size: 0.76rem; font-weight: 700;
            background: #ede9fe; color: #6d28d9;
            padding: 3px 9px; border-radius: 6px;
        }

        .amt-cell {
            font-family: 'DM Mono', monospace;
            font-weight: 700; font-size: 0.84rem;
            text-align: right;
        }

        .amt-cell .curr { font-size: 0.66rem; color: var(--text-muted); font-family: 'Sora', sans-serif; margin-right: 2px; }
        .amt-cell.red   { color: var(--danger); }
        .amt-cell.green { color: #16a34a; }

        .cust-tag {
            font-size: 0.78rem; color: var(--text-muted);
            display: flex; align-items: center; gap: 5px;
        }

        /* Footer summary row */
        .summary-row td {
            background: var(--surface-2);
            border-top: 2px solid var(--border) !important;
            font-weight: 700; font-size: 0.84rem;
            padding: 11px 16px;
        }

        /* Profit/Loss card */
        .pnl-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.4s 0.40s ease both;
            margin-bottom: 22px;
        }

        .pnl-body {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: var(--border);
        }

        .pnl-cell {
            background: var(--surface);
            padding: 18px 20px;
        }

        .pnl-cell .plabel {
            font-size: 0.72rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px;
            font-weight: 500; margin-bottom: 8px;
        }

        .pnl-cell .pval {
            font-family: 'DM Mono', monospace;
            font-size: 1.5rem; font-weight: 700;
            line-height: 1;
        }

        .pnl-cell .pval .pcurr {
            font-size: 0.8rem; font-weight: 500; color: var(--text-muted);
            font-family: 'Sora', sans-serif; margin-right: 3px;
        }

        .pnl-cell.revenue .pval { color: var(--primary); }
        .pnl-cell.expense .pval { color: var(--danger); }
        .pnl-cell.profit  .pval { color: <?= $net_profit >= 0 ? 'var(--success)' : 'var(--danger)' ?>; }

        .pnl-pill {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 0.72rem; font-weight: 600;
            padding: 3px 9px; border-radius: 20px; margin-top: 8px;
        }

        .pnl-pill.profit-pill { background: var(--success-soft); color: #16a34a; }
        .pnl-pill.loss-pill   { background: var(--danger-soft); color: var(--danger); }

        /* Empty state */
        .empty-state {
            padding: 50px 20px; text-align: center;
        }

        .empty-state .ei {
            width: 56px; height: 56px;
            background: var(--surface-2); border: 2px dashed var(--border);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #cbd5e1;
            margin: 0 auto 12px;
        }

        .empty-state h6 { font-weight: 600; color: var(--text-muted); font-size: 0.88rem; }
        .empty-state p  { font-size: 0.78rem; color: #94a3b8; margin-top: 3px; }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Print Styles ── */
        @media print {
            .main-content { margin-left: 0 !important; }
            .sidebar, .top-header, .no-print { display: none !important; }
            .page-body { padding: 0 !important; }
            .report-banner { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .kpi-card, .section-card, .full-card, .pnl-card { break-inside: avoid; box-shadow: none; }
            body { background: white; }
        }

        @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 992px)  {
            .report-grid { grid-template-columns: 1fr; }
            .pnl-body { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px)  {
            .main-content { margin-left: 0; }
            .page-body { padding: 16px; }
            .kpi-grid { grid-template-columns: 1fr 1fr; }
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
                <span class="icon-wrap"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
                Monthly Report
            </h4>
            <p>Financial summary and performance for <?= $month_label ?></p>
        </div>
        <div class="header-actions no-print">
            <!-- Month Selector -->
            <form method="GET" id="monthForm">
                <div class="month-selector">
                    <i class="bi bi-calendar3"></i>
                    <select name="month" onchange="document.getElementById('monthForm').submit()">
                        <?php foreach ($available_months as $m): ?>
                        <option value="<?= $m ?>" <?= $m === $selected_month ? 'selected' : '' ?>>
                            <?= date('F Y', strtotime("$m-01")) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <button class="btn-action btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button class="btn-action btn-pdf" onclick="generatePDF()">
                <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="page-body">

        <!-- Report Banner -->
        <div class="report-banner">
            <div class="banner-left">
                <h3><i class="bi bi-file-earmark-bar-graph-fill" style="margin-right:8px;opacity:.8"></i><?= $month_label ?> — Financial Report</h3>
                <p>Bether Pharmacy &nbsp;·&nbsp; Generated <?= date('d M Y, H:i') ?> &nbsp;·&nbsp; <?= $days_in_month ?> days covered</p>
            </div>
            <div class="banner-right">
                <div class="banner-stat">
                    <div class="bval"><?= $total_txn ?></div>
                    <div class="blabel">Transactions</div>
                </div>
                <div class="banner-stat">
                    <div class="bval"><?= count($daily_sales) ?></div>
                    <div class="blabel">Active Days</div>
                </div>
                <div class="banner-stat">
                    <div class="bval"><?= count($expenses) ?></div>
                    <div class="blabel">Expenses</div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card teal">
                <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="kpi-val"><span class="curr">TZS</span><?= number_format($total_revenue) ?></div>
                <div class="kpi-label">Total Revenue</div>
                <?php if ($rev_change !== null): ?>
                <div class="kpi-trend <?= $rev_change >= 0 ? 'up' : 'down' ?>">
                    <i class="bi bi-arrow-<?= $rev_change >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($rev_change) ?>% vs last month
                </div>
                <?php endif; ?>
            </div>
            <div class="kpi-card red">
                <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="kpi-val"><span class="curr">TZS</span><?= number_format($total_expenses) ?></div>
                <div class="kpi-label">Total Expenses</div>
            </div>
            <div class="kpi-card <?= $net_profit >= 0 ? 'green' : 'red' ?>">
                <div class="kpi-icon"><i class="bi bi-<?= $net_profit >= 0 ? 'trending-up' : 'trending-down' ?>"></i></div>
                <div class="kpi-val"><span class="curr">TZS</span><?= number_format(abs($net_profit)) ?></div>
                <div class="kpi-label"><?= $net_profit >= 0 ? 'Net Profit' : 'Net Loss' ?></div>
                <div class="kpi-trend <?= $net_profit >= 0 ? 'up' : 'down' ?>">
                    <i class="bi bi-<?= $net_profit >= 0 ? 'check-circle' : 'x-circle' ?>"></i>
                    <?= $net_profit >= 0 ? 'Profitable' : 'Loss' ?>
                </div>
            </div>
            <div class="kpi-card blue">
                <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
                <div class="kpi-val"><?= number_format($total_txn) ?></div>
                <div class="kpi-label">Transactions</div>
                <div class="kpi-trend flat"><?= number_format($total_sales_qty) ?> units sold</div>
            </div>
            <div class="kpi-card amber">
                <div class="kpi-icon"><i class="bi bi-calculator"></i></div>
                <div class="kpi-val"><span class="curr">TZS</span><?= number_format($avg_sale) ?></div>
                <div class="kpi-label">Avg Sale Value</div>
                <div class="kpi-trend flat"><?= $walkin_count ?> walk-ins</div>
            </div>
        </div>

        <!-- Profit & Loss Summary -->
        <div class="pnl-card">
            <div class="section-head" style="padding:15px 24px;">
                <h6>
                    <span class="sh-icon teal"><i class="bi bi-bar-chart-line-fill"></i></span>
                    Profit & Loss Summary — <?= $month_label ?>
                </h6>
            </div>
            <div class="pnl-body">
                <div class="pnl-cell revenue">
                    <div class="plabel">💰 Total Revenue</div>
                    <div class="pval"><span class="pcurr">TZS</span><?= number_format($total_revenue) ?></div>
                    <div class="pnl-pill profit-pill"><i class="bi bi-arrow-up"></i> Income</div>
                </div>
                <div class="pnl-cell expense">
                    <div class="plabel">💸 Total Expenses</div>
                    <div class="pval"><span class="pcurr">TZS</span><?= number_format($total_expenses) ?></div>
                    <div class="pnl-pill loss-pill"><i class="bi bi-arrow-down"></i> Outflow</div>
                </div>
                <div class="pnl-cell profit">
                    <div class="plabel"><?= $net_profit >= 0 ? '📈 Net Profit' : '📉 Net Loss' ?></div>
                    <div class="pval"><span class="pcurr">TZS</span><?= number_format(abs($net_profit)) ?></div>
                    <?php $margin = $total_revenue > 0 ? round(($net_profit/$total_revenue)*100,1) : 0; ?>
                    <div class="pnl-pill <?= $net_profit >= 0 ? 'profit-pill' : 'loss-pill' ?>">
                        <i class="bi bi-percent"></i> <?= $margin ?>% margin
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart + Top Items -->
        <div class="report-grid">

            <!-- Daily Revenue Chart -->
            <div class="section-card">
                <div class="section-head">
                    <h6>
                        <span class="sh-icon teal"><i class="bi bi-bar-chart-fill"></i></span>
                        Daily Revenue — <?= $month_label ?>
                    </h6>
                    <span class="section-sub"><?= count($daily_sales) ?> active days</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Top Selling Items -->
            <div class="section-card">
                <div class="section-head">
                    <h6>
                        <span class="sh-icon amber"><i class="bi bi-trophy-fill"></i></span>
                        Top Selling Items
                    </h6>
                    <span class="section-sub">by revenue</span>
                </div>
                <div class="top-items-list">
                    <?php if (empty($top_items)): ?>
                    <div class="empty-state">
                        <div class="ei"><i class="bi bi-box-seam"></i></div>
                        <h6>No sales data</h6>
                    </div>
                    <?php else: ?>
                    <?php foreach ($top_items as $idx => $item):
                        $pct = $max_top_rev > 0 ? ($item['total_rev'] / $max_top_rev) * 100 : 0;
                    ?>
                    <div class="top-item-row">
                        <div class="ti-rank"><?= $idx + 1 ?></div>
                        <div class="ti-info">
                            <div class="ti-name"><?= htmlspecialchars($item['item_name']) ?></div>
                            <div class="ti-bar-wrap">
                                <div class="ti-bar" style="width:<?= round($pct) ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="ti-rev"><?= number_format($item['total_rev']) ?></div>
                            <div class="ti-qty"><?= $item['total_qty'] ?> units</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sales Transactions Table -->
        <div class="full-card">
            <div class="section-head">
                <h6>
                    <span class="sh-icon blue"><i class="bi bi-cart-check-fill"></i></span>
                    All Sales — <?= $month_label ?>
                </h6>
                <span class="section-sub"><?= $total_txn ?> transactions &nbsp;·&nbsp; <?= $total_sales_qty ?> units</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Customer</th>
                        <th style="text-align:right">Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="ei"><i class="bi bi-cart-x"></i></div>
                            <h6>No sales for this month</h6>
                            <p>Select a different month or record new sales.</p>
                        </div>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($sales as $row): ?>
                    <tr>
                        <td><span class="date-mono"><?= date('d M', strtotime($row['sale_date'])) ?></span></td>
                        <td>
                            <div class="item-pill">
                                <span class="dot"></span>
                                <?= htmlspecialchars($row['item_name']) ?>
                            </div>
                        </td>
                        <td><span class="qty-tag">×<?= $row['quantity'] ?></span></td>
                        <td>
                            <div class="cust-tag">
                                <i class="bi bi-person<?= empty($row['customer_name']) ? '' : '-fill' ?>"></i>
                                <?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?>
                            </div>
                        </td>
                        <td class="amt-cell green">
                            <span class="curr">TZS</span><?= number_format($row['total'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="summary-row">
                        <td colspan="4" style="color:var(--text-muted);font-size:0.78rem;letter-spacing:.5px;text-transform:uppercase;">
                            Total — <?= $total_txn ?> transactions
                        </td>
                        <td class="amt-cell green">
                            <span class="curr">TZS</span><?= number_format($total_revenue, 2) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Expenses Table -->
        <div class="full-card">
            <div class="section-head">
                <h6>
                    <span class="sh-icon red"><i class="bi bi-cash-stack"></i></span>
                    All Expenses — <?= $month_label ?>
                </h6>
                <span class="section-sub"><?= count($expenses) ?> entries</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th style="text-align:right">Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                    <tr><td colspan="3">
                        <div class="empty-state">
                            <div class="ei"><i class="bi bi-receipt-cutoff"></i></div>
                            <h6>No expenses recorded</h6>
                        </div>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($expenses as $exp): ?>
                    <tr>
                        <td><span class="date-mono"><?= date('d M', strtotime($exp['expense_date'])) ?></span></td>
                        <td>
                            <div class="item-pill">
                                <span class="dot exp"></span>
                                <?= htmlspecialchars($exp['description'] ?? $exp['title'] ?? '—') ?>
                            </div>
                        </td>
                        <td class="amt-cell red">
                            <span class="curr">TZS</span><?= number_format($exp['amount'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="summary-row">
                        <td colspan="2" style="color:var(--text-muted);font-size:0.78rem;letter-spacing:.5px;text-transform:uppercase;">
                            Total — <?= count($expenses) ?> entries
                        </td>
                        <td class="amt-cell red">
                            <span class="curr">TZS</span><?= number_format($total_expenses, 2) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /page-body -->
</div><!-- /main-content -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Daily Revenue Chart ──────────────────────────────
    const ctx = document.getElementById('dailyChart').getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(0,201,167,0.28)');
    gradient.addColorStop(1, 'rgba(0,201,167,0.01)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Revenue (TZS)',
                data:  <?= json_encode($chart_values) ?>,
                backgroundColor: gradient,
                borderColor: '#00c9a7',
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'TZS ' + ctx.raw.toLocaleString()
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Mono', size: 10 }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: 'DM Mono', size: 10 }, color: '#94a3b8',
                        callback: v => v >= 1000 ? (v/1000).toFixed(0)+'k' : v
                    }
                }
            }
        }
    });

    // ── PDF Export via print ─────────────────────────────
    function generatePDF() {
        window.print();
    }
</script>
</body>
</html>