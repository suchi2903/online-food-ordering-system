<?php
session_start();
require 'db.php'; // PDO connection

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle Active toggle via GET param (simplest method)
if (isset($_GET['toggle_active']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];

    // Fetch current active value
    $stmt = $pdo->prepare("SELECT active FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newActive = $user['active'] ? 0 : 1;
        $update = $pdo->prepare("UPDATE users SET active = ? WHERE id = ?");
        $update->execute([$newActive, $userId]);
        header("Location: user_management.php");
        exit;
    }
}

// Fetch all users
$users = $pdo->query("SELECT id, username, email, role, active FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        a.button {
            background: #6d4c41; color: white; padding: 6px 12px;
            text-decoration: none; border-radius: 4px;
        }
        a.button:hover {
            background: #5d4037;
        }
    </style>
</head>
<body>

<h1>User Management</h1>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <a href="user_management.php?toggle_active=1&id=<?= $user['id'] ?>" class="button">
                    <?= $user['active'] ? 'Active' : 'Inactive' ?>
                </a>
            </td>
            <td><a href="edit_user.php?id=<?= $user['id'] ?>" class="button">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
