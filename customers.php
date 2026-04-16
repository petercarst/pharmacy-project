<?php 
require 'config.php'; 
requireLogin(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - BETHEL PHARMACY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --accent: #00c9a7;
            --accent-soft: #e6faf7;
            --danger: #e63946;
            --warning: #f4a261;
            --bg: #f0f4f8;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --border: #e2e8f0;
            --text: #1a2332;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(15,76,117,0.10);
            --shadow-lg: 0 8px 32px rgba(15,76,117,0.14);
            --radius: 14px;
            --radius-sm: 8px;
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
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            box-shadow: 0 3px 8px rgba(15,76,117,0.25);
        }

        .header-left p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 2px;
            padding-left: 48px;
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
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 3px 10px rgba(0,201,167,0.30);
            cursor: pointer;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(0,201,167,0.40);
            color: white;
        }

        .page-body {
            padding: 28px 32px;
            flex: 1;
        }

        .stats-bar {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
            flex: 1;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-icon.blue  { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.green { background: var(--accent-soft); color: var(--accent); }
        .stat-icon.amber { background: #fff4e0; color: var(--warning); }

        .stat-info .label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info .value { font-size: 1.4rem; font-weight: 700; color: var(--text); line-height: 1.2; }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .table-toolbar {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 320px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
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
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .search-box input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
        }

        .toolbar-badge {
            margin-left: auto;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(0,201,167,0.2);
        }

        .customers-table { width: 100%; border-collapse: collapse; }

        .customers-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .customers-table thead th {
            padding: 13px 18px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .customers-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .customers-table tbody tr:last-child { border-bottom: none; }
        .customers-table tbody tr:hover { background: #f8fbff; }

        .customers-table td {
            padding: 14px 18px;
            font-size: 0.85rem;
            color: var(--text);
            vertical-align: middle;
        }

        .id-badge {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            padding: 3px 9px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .customer-name { font-weight: 600; font-size: 0.88rem; color: var(--text); }
        .customer-sub  { font-size: 0.73rem; color: var(--text-muted); margin-top: 1px; }

        .contact-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .empty-dash {
            color: #cbd5e1;
            font-size: 1.1rem;
            font-weight: 300;
        }

        .action-group { display: flex; gap: 6px; }

        .btn-icon {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-icon.edit  { background: #fff4e0; color: var(--warning); }
        .btn-icon.edit:hover  { background: var(--warning); color: white; transform: scale(1.08); }

        .btn-icon.delete { background: #fde8ea; color: var(--danger); }
        .btn-icon.delete:hover { background: var(--danger); color: white; transform: scale(1.08); }

        .btn-icon.view  { background: #dbeafe; color: #1d4ed8; }
        .btn-icon.view:hover  { background: #1d4ed8; color: white; transform: scale(1.08); }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state .empty-icon {
            width: 70px;
            height: 70px;
            background: var(--surface-2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--text-muted);
            margin: 0 auto 16px;
            border: 2px dashed var(--border);
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
                <span class="icon-wrap"><i class="bi bi-people-fill"></i></span>
                Customer Management
            </h4>
            <p>View, add, and manage your pharmacy customers</p>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-person-plus-fill"></i> Add New Customer
        </button>
    </div>

    <div class="page-body">

        <?php 
        // Show success or error messages
        if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ Customer added successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php
        $stmt = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
        $customers = $stmt->fetchAll();
        $total = count($customers);

        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        ?>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card fade-up">
                <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Total Customers</div>
                    <div class="value"><?= $total ?></div>
                </div>
            </div>
            <div class="stat-card fade-up">
                <div class="stat-icon green"><i class="bi bi-telephone-fill"></i></div>
                <div class="stat-info">
                    <div class="label">With Phone</div>
                    <div class="value"><?= count(array_filter($customers, fn($c) => !empty($c['phone']))) ?></div>
                </div>
            </div>
            <div class="stat-card fade-up">
                <div class="stat-icon amber"><i class="bi bi-envelope-fill"></i></div>
                <div class="stat-info">
                    <div class="label">With Email</div>
                    <div class="value"><?= count(array_filter($customers, fn($c) => !empty($c['email']))) ?></div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card fade-up">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search customers…" oninput="filterTable()">
                </div>
                <span class="toolbar-badge" id="countBadge"><?= $total ?> records</span>
            </div>

            <table class="customers-table" id="customerTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                                <h6>No customers yet</h6>
                                <p>Click "Add New Customer" to get started.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $row): 
                        $initials = strtoupper(substr($row['name'], 0, 1));
                        $words = explode(' ', trim($row['name']));
                        if (count($words) >= 2) $initials = strtoupper($words[0][0] . $words[1][0]);
                    ?>
                    <tr>
                        <td><span class="id-badge">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                        <td>
                            <div class="customer-cell">
                                <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                                <div>
                                    <div class="customer-name"><?= htmlspecialchars($row['name']) ?></div>
                                    <div class="customer-sub">Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($row['phone'])): ?>
                                <span class="contact-pill"><i class="bi bi-telephone"></i><?= htmlspecialchars($row['phone']) ?></span>
                            <?php else: ?>
                                <span class="empty-dash">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['email'])): ?>
                                <span class="contact-pill"><i class="bi bi-envelope"></i><?= htmlspecialchars($row['email']) ?></span>
                            <?php else: ?>
                                <span class="empty-dash">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-group">
                                <button onclick="viewCustomer(<?= $row['id'] ?>)" class="btn-icon view" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="editCustomer(<?= $row['id'] ?>)" class="btn-icon edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <?php if ($isAdmin): ?>
                                <button onclick="deleteCustomer(<?= $row['id'] ?>)" class="btn-icon delete" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
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

<!-- ==================== ADD CUSTOMER MODAL (Your original beautiful modal - unchanged) ==================== -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus-fill"></i> Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="save_customer.php">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 0723457915">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. customer@example.com" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-circle"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewCustomer(id) {
        window.location.href = 'view_customer.php?id=' + id;
    }

    function editCustomer(id) {
        window.location.href = 'edit_customer.php?id=' + id;
    }

    function deleteCustomer(id) {
        if (confirm("Are you sure you want to delete this customer?")) {
            window.location.href = 'delete_customer.php?id=' + id;
        }
    }

    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#customerTable tbody tr');
        let visible = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const show = text.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('countBadge').textContent = visible + ' records';
    }
</script>
</body>
</html>