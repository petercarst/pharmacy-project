<?php 
require 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Sora', sans-serif; background:#f8fafc; }

        .card-box {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0f4c75, #1b6ca8);
            color: white;
            padding: 35px;
            text-align: center;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background: rgba(255,255,255,0.2);
            display:flex;
            align-items:center;
            justify-content:center;
            margin: auto;
            font-size: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="p-4">

<div class="card-box">

    <div class="header">
        <div class="avatar">
            <?= strtoupper(substr($user['full_name'],0,2)) ?>
        </div>
        <h3 class="mt-3"><?= htmlspecialchars($user['full_name']) ?></h3>
        <p><?= htmlspecialchars($user['role']) ?></p>
    </div>

    <div class="p-4">
        <p><b>Username:</b> <?= htmlspecialchars($user['username']) ?></p>
        <p><b>Full Name:</b> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><b>Role:</b> <?= htmlspecialchars($user['role']) ?></p>

        <a href="manage_user.php" class="btn btn-secondary">Back</a>
        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Edit</a>
    </div>

</div>

</div>

</body>
</html>