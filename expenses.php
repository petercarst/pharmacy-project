<?php 
require 'config.php'; 
requireLogin();
requireAdmin(); // ← ADMIN ONLY: pharmacists are redirected to dashboard

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);
    $amount      = (float)$_POST['amount'];
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');

    if (empty($description) || $amount <= 0) {
        $error = "Description and valid amount are required!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO expenses (description, amount, expense_date) VALUES (?, ?, ?)");
        $stmt->execute([$description, $amount, $expense_date]);
        $message = "Expense recorded successfully!";
    }
}

// Fetch all expenses
$stmt = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");
$expenses = $stmt->fetchAll();

// Today's total expense
$today = date('Y-m-d');
$today_expense = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date = ?");
$today_expense->execute([$today]);
$today_total = $today_expense->fetchColumn();

// Total expenses all time
$total_expenses = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM expenses")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

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

        .table-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 24px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px 24px;
            box-shadow: var(--shadow);
        }

        .expense-amount {
            font-size: 1.35rem;
            font-weight: 700;
            color: #e63946;
        }

        .table thead th {
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
        }

        .table td {
            padding: 16px 20px;
            vertical-align: middle;
        }

        .btn-save {
            background: linear-gradient(135deg, #e63946, #d62839);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
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
                <span class="icon-wrap"><i class="bi bi-cash-stack"></i></span>
                Expenses
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

        <!-- Summary Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="stat-card">
                    <h5 class="text-muted small mb-2">TODAY'S EXPENSES</h5>
                    <div class="expense-amount">TZS <?= number_format($today_total, 2) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h5 class="text-muted small mb-2">TOTAL EXPENSES</h5>
                    <div class="expense-amount">TZS <?= number_format($total_expenses, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add New Expense Form -->
            <div class="col-lg-5">
                <div class="table-card p-4">
                    <h5 class="mb-4"><i class="bi bi-plus-circle"></i> Record New Expense</h5>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" 
                                   placeholder="e.g. Electricity bill, Transport, Rent, Salary" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <button type="submit" class="btn btn-save w-100">
                            <i class="bi bi-save"></i> Save Expense
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Expenses Table -->
            <div class="col-lg-7">
                <div class="table-card">
                    <div class="p-4 border-bottom">
                        <h5 class="mb-0">Recent Expenses</h5>
                    </div>
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-end">Amount (TZS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($expenses) > 0): ?>
                                <?php foreach ($expenses as $exp): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
                                    <td><?= htmlspecialchars($exp['description']) ?></td>
                                    <td class="text-end fw-semibold text-danger">
                                        TZS <?= number_format($exp['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        No expenses recorded yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>