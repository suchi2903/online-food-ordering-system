<?php
session_start();
require 'db.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    die("Invalid User ID");
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $active = isset($_POST['active']) ? 1 : 0;

    if (!$username) {
        $errors[] = "Username is required.";
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, active = ? WHERE id = ?");
        $update->execute([$username, $email, $role, $active, $userId]);

        header("Location: user_management.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .edit-user-box {
        max-width: 420px;
        margin: 60px auto;
        padding: 30px 25px;
        background: #fff8f0;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(109, 76, 65, 0.15);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #4b2e2e;
      }
      .edit-user-box h1 {
        text-align: center;
        margin-bottom: 20px;
        font-weight: 700;
      }
      .edit-user-box form p {
        margin-bottom: 18px;
      }
      .edit-user-box label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
      }
      .edit-user-box input[type="text"],
      .edit-user-box input[type="email"],
      .edit-user-box select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d2b48c;
        border-radius: 6px;
        font-size: 1rem;
        color: #4b2e2e;
      }
      .edit-user-box input[type="checkbox"] {
        width: 16px;
        height: 16px;
        vertical-align: middle;
        margin-right: 8px;
      }
      .edit-user-box .checkbox-label {
        display: inline-block;
        vertical-align: middle;
        font-weight: 600;
        cursor: pointer;
      }
      .edit-user-box button {
        width: 100%;
        background-color: #6d4c41;
        color: white;
        padding: 10px 0;
        font-weight: 700;
        border: none;
        border-radius: 6px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
      }
      .edit-user-box button:hover {
        background-color: #5d4037;
      }
      .edit-user-box .errors {
        background-color: #ffdede;
        border: 1px solid #d26969;
        padding: 12px 15px;
        margin-bottom: 18px;
        border-radius: 6px;
        color: #8b0000;
        font-weight: 600;
      }
      .edit-user-box a.back-link {
        display: block;
        margin-top: 20px;
        text-align: center;
        color: #6d4c41;
        font-weight: 600;
        text-decoration: none;
      }
      .edit-user-box a.back-link:hover {
        text-decoration: underline;
      }
    </style>
</head>
<body>

<div class="edit-user-box">
  <h1>Edit User #<?= htmlspecialchars($userId) ?></h1>

  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <p>
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>" required>
    </p>
    <p>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
    </p>
    <p>
      <label for="role">Role:</label>
      <select id="role" name="role" required>
        <option value="user" <?= (($user['role'] ?? '') === 'user') ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= (($user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
      </select>
    </p>
    <p>
      <label>
        <input type="checkbox" name="active" id="active" value="1" <?= (($_POST['active'] ?? $user['active']) ? 'checked' : '') ?>>
        <span class="checkbox-label">Active</span>
      </label>
    </p>
    <button type="submit">Update User</button>
  </form>

  <a href="user_management.php" class="back-link">← Back to User Management</a>
</div>

</body>
</html>
