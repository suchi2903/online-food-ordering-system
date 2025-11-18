<?php
session_start();
require 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch logged-in user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | YummiYo</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: Arial, sans-serif; display: flex; background: #fef6e4; color: #4b2e2e; }

/* Sidebar */
.sidebar {
  width: 260px;
  background: #fff8f0;
  padding: 20px;
  height: 100vh;
  border-right: 2px solid #e0c097;
  position: fixed;
  overflow-y: auto;
}
.sidebar .profile { text-align: center; margin-bottom: 20px; }
.sidebar .profile h2 { font-size: 20px; color: #5d4037; }
.sidebar .profile p { font-size: 14px; color: #7b5e4c; margin: 5px 0; }
.sidebar nav a { display: block; padding: 10px 15px; margin: 6px 0; border-radius: 8px; text-decoration: none; color: #4b2e2e; font-weight: 600; transition: 0.3s; background: #f4e1d2; }
.sidebar nav a:hover { background: #6d4c41; color: white; }

/* Main content */
.main { margin-left: 260px; padding: 30px; flex-grow: 1; }
h1 { text-align: center; color: #3e2723; margin-bottom: 20px; font-size: 2rem; }

/* Banner */
.dashboard-banner { text-align: center; margin-top: 30px; }
.dashboard-banner img { width: 40%; max-width: 1000px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); object-fit: cover; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="profile">
    <h2>Welcome, <?= htmlspecialchars($user['username']) ?></h2>
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
  <h1>Welcome to YummiYo</h1>

  <div class="dashboard-banner">
    <img src="images/user1.jpg" alt="Welcome Banner">
  </div>
</div>

</body>
</html>
