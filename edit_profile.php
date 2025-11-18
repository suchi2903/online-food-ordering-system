<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } else {
        $password_to_save = $new_password ? password_hash($new_password, PASSWORD_DEFAULT) : $user['password'];
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, mobile = ?, gender = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $mobile, $gender, $password_to_save, $user_id]);
        $success = "Profile updated successfully!";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile | YummiYo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3ece2;
            margin: 0;
            display: flex;
            color: #4b2e2e;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #fff8f0;
            padding: 20px;
            height: 100vh;
            border-right: 2px solid #e0c097;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .sidebar .profile {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar .profile h3 { color: #5d4037; margin-bottom: 5px; }
        .sidebar .profile p { color: #7b5e4c; font-size: 14px; }

        .sidebar nav a {
            display: block;
            padding: 10px 15px;
            margin: 6px 0;
            border-radius: 8px;
            text-decoration: none;
            color: #4b2e2e;
            font-weight: 600;
            background: #f4e1d2;
            transition: 0.3s;
        }
        .sidebar nav a:hover, .sidebar nav a.active {
            background: #6d4c41;
            color: white;
        }

        /* Main content */
        .main {
            flex-grow: 1;
            padding: 40px;
        }

        h2 {
            text-align: center;
            color: #5d4037;
        }

        form {
            max-width: 450px;
            margin: 20px auto;
            padding: 25px;
            border-radius: 12px;
            background: #fff8f0;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            border: 1px solid #e0c097;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #5d4037;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #d7ccc8;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #a1887f;
            box-shadow: 0 0 5px #d7ccc8;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #6d4c41;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #5d4037;
        }

        .message {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .error {
            text-align: center;
            color: #c62828;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile">
        <h3><?= htmlspecialchars($user['username']) ?></h3>
        <p><?= htmlspecialchars($user['email']) ?></p>
    </div>
    <nav>
    <a href="user_dashboard.php">Home</a>
    <a href="menu.php">📦Menu</a>
    <a href="order_history.php">📦 Recent Orders</a>
    <a href="cart.php">🛒 Cart</a>
    <a href="edit_profile.php" class="active">✏️ Edit Profile</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</div>

<!-- Main Content -->
<div class="main">
    <h2>Edit Profile</h2>

    <?php if(isset($success)) echo "<p class='message'>$success</p>"; ?>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="post">
        <label>Name</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Mobile Number</label>
        <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>">

        <label>Gender</label>
        <select name="gender">
            <option value="Male" <?= $user['gender']=='Male'?'selected':'' ?>>Male</option>
            <option value="Female" <?= $user['gender']=='Female'?'selected':'' ?>>Female</option>
            <option value="Other" <?= $user['gender']=='Other'?'selected':'' ?>>Other</option>
        </select>

        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="new_password">

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
