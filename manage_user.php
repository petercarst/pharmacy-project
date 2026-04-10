<?php 
require 'config.php'; 
requireLogin();

// Restrict page to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $allowed_roles = ['admin', 'pharmacist'];

    if (empty($full_name) || empty($username) || empty($role) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif (!in_array($role, $allowed_roles)) {
        $error = "Invalid role selected!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $hashed_password, $role]);

            $success = "User account created successfully!";
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username already exists! Please choose another.";
            } else {
                $error = "Failed to create user. Please try again.";
            }
        }
    }
}

// Fetch users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
$total = count($users);
$total_admins = count(array_filter($users, fn($u) => isset($u['role']) && strtolower($u['role']) === 'admin'));
$total_pharmacists = count(array_filter($users, fn($u) => isset($u['role']) && strtolower($u['role']) === 'pharmacist'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BETHEL PHARMACY</title>
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

        .alert-custom {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-size: 0.9rem;
            font-weight: 500;
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

        .stat-info .label {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }

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

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table thead tr {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
        }

        .users-table thead th {
            padding: 13px 18px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .users-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .users-table tbody tr:last-child { border-bottom: none; }
        .users-table tbody tr:hover { background: #f8fbff; }

        .users-table td {
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

        .user-cell {
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

        .user-name {
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--text);
        }

        .user-sub {
            font-size: 0.73rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .username-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .role-admin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .role-pharmacist {
            background: #e6faf7;
            color: #00a98e;
        }

        .action-group {
            display: flex;
            gap: 6px;
        }

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

        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 25px 30px;
            border-bottom: none;
        }

        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .modal-body {
            padding: 35px 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.12);
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

    <div class="top-header">
        <div class="header-left">
            <h4>
                <span class="icon-wrap"><i class="bi bi-shield-lock-fill"></i></span>
                User Management
            </h4>
            <p>View, add, and manage system users</p>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill"></i> Add New User
        </button>
    </div>

    <div class="page-body">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-custom"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Total Users</div>
                    <div class="value"><?= $total ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-person-badge-fill"></i></div>
                <div class="stat-info">
                    <div class="label">Admins</div>
                    <div class="value"><?= $total_admins ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-capsule-pill"></i></div>
                <div class="stat-info">
                    <div class="label">Pharmacists</div>
                    <div class="value"><?= $total_pharmacists ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search users…" oninput="filterTable()">
                </div>
                <span class="toolbar-badge" id="countBadge"><?= $total ?> records</span>
            </div>

            <table class="users-table" id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                                    <h6>No users yet</h6>
                                    <p>Click "Add New User" to get started.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $row): 
                            $initials = strtoupper(substr($row['full_name'], 0, 1));
                            $words = explode(' ', trim($row['full_name']));
                            if (count($words) >= 2) {
                                $initials = strtoupper($words[0][0] . $words[1][0]);
                            }

                            $role = strtolower($row['role'] ?? '');
                            $roleClass = $role === 'admin' ? 'role-admin' : 'role-pharmacist';
                        ?>
                            <tr>
                                <td>
                                    <span class="id-badge">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </td>

                                <td>
                                    <div class="user-cell">
                                        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($row['full_name']) ?></div>
                                            <div class="user-sub">System User</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="username-pill">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($row['username']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="role-badge <?= $roleClass ?>">
                                        <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <button onclick="viewUser(<?= $row['id'] ?>)" class="btn-icon view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button onclick="editUser(<?= $row['id'] ?>)" class="btn-icon edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="deleteUser(<?= $row['id'] ?>)" class="btn-icon delete" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus-fill"></i> Add New User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe"
                               value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="e.g. johndoe"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Select role</option>
                            <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="pharmacist" <?= (isset($_POST['role']) && $_POST['role'] === 'pharmacist') ? 'selected' : '' ?>>Pharmacist</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Create password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-circle"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewUser(id) {
        window.location.href = 'view_user.php?id=' + id;
    }

    function editUser(id) {
        window.location.href = 'edit_user.php?id=' + id;
    }

    function deleteUser(id) {
        if (confirm("Are you sure you want to delete this user?")) {
            window.location.href = 'delete_user.php?id=' + id;
        }
    }

    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#userTable tbody tr');
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
