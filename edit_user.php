<?php 
require 'config.php';
requireLogin();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php");
    exit;
}

$msg = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role      = $_POST['role'];
    $password  = $_POST['password'] ?? '';

    if (empty($username) || empty($full_name)) {
        $error = "Username and Full Name are required!";
    } else {

        // ================= UPDATE WITH OR WITHOUT PASSWORD =================
        if (!empty($password)) {
            // update with password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE users 
                SET username=?, full_name=?, role=?, password=? 
                WHERE id=?
            ");

            $stmt->execute([$username, $full_name, $role, $hashed, $id]);

        } else {
            // update without password
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username=?, full_name=?, role=? 
                WHERE id=?
            ");

            $stmt->execute([$username, $full_name, $role, $id]);
        }

        $msg = "User updated successfully!";

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family: 'Sora', sans-serif;
    background:#f8fafc;
}

.card{
    border-radius:16px;
}

.form-label{
    font-size:0.75rem;
    font-weight:600;
    text-transform:uppercase;
    color:#64748b;
    margin-bottom:6px;
}

.form-control{
    border-radius:12px;
    padding:12px 14px;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="container p-4">

<div class="card p-4 shadow-sm" style="max-width:600px;margin:auto;">

<h4 class="mb-4">Edit User</h4>

<?php if($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<!-- Username -->
<div class="mb-3">
    <label class="form-label">Username</label>
    <input class="form-control" name="username"
           value="<?= htmlspecialchars($user['username']) ?>">
</div>

<!-- Full Name -->
<div class="mb-3">
    <label class="form-label">Full Name</label>
    <input class="form-control" name="full_name"
           value="<?= htmlspecialchars($user['full_name']) ?>">
</div>

<!-- Password (NEW) -->
<div class="mb-3">
    <label class="form-label">Password (leave blank to keep old password)</label>
    <input type="password" class="form-control" name="password">
</div>

<!-- Role -->
<div class="mb-4">
    <label class="form-label">User Role</label>
    <select class="form-control" name="role">
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        <option value="pharmacist" <?= $user['role']=='pharmacist'?'selected':'' ?>>Pharmacist</option>
    </select>
</div>

<div class="d-flex gap-2 mt-3">
    <a href="manage_user.php" class="btn btn-secondary flex-fill py-2">
        ← Back
    </a>

    <button class="btn btn-primary flex-fill py-2">
        Update User
    </button>
</div>

</form>

</div>

</div>

</body>
</html>