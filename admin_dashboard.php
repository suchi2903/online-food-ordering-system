<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require 'db.php';

// Date range for today
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Fetch today's order counts by status
$pendingOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending' AND order_date BETWEEN ? AND ?");
$pendingOrders->execute([$today_start, $today_end]);
$pendingCount = $pendingOrders->fetchColumn();

$completedOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered' AND order_date BETWEEN ? AND ?");
$completedOrders->execute([$today_start, $today_end]);
$completedCount = $completedOrders->fetchColumn();

$cancelledOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_status = 'Cancelled' AND order_date BETWEEN ? AND ?");
$cancelledOrders->execute([$today_start, $today_end]);
$cancelledCount = $cancelledOrders->fetchColumn();

// Overall stats
$totalOrdersStmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $totalOrdersStmt->fetchColumn();

$totalRevenueStmt = $pdo->query("SELECT SUM(total_price) FROM orders WHERE order_status = 'Delivered'");
$totalRevenue = $totalRevenueStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard | YummiYo</title>
<style>
  /* Reset and basics */
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fef6e4;
    color: #4b2e2e;
  }

  /* Sidebar */
  .sidebar {
    width: 230px;
    background-color: #6d4c41;
    color: #fff;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    padding-top: 40px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    user-select: none;
    z-index: 1000;
  }
  .sidebar a {
    color: #fff;
    text-decoration: none;
    padding: 15px 30px;
    font-weight: 600;
    font-size: 1rem;
    border-left: 4px solid transparent;
    transition: background-color 0.3s ease, border-left-color 0.3s ease;
  }
  .sidebar a:hover,
  .sidebar a:focus {
    background-color: #5d4037;
    border-left-color: #ffb74d;
    outline: none;
  }
  .sidebar a.active {
    background-color: #5d4037;
    border-left-color: #ffb74d;
  }

  /* Content */
  .content {
    margin-left: 230px;
    padding: 40px;
    background: #fff8f0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 40px;
  }

  /* Report container flex */
  .reports {
    display: flex;
    gap: 40px;
    justify-content: center;
    flex-wrap: wrap;
  }

  /* Report boxes */
  .box {
    background: #fff8f0;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(109, 76, 65, 0.15);
    width: 320px;
    user-select: none;
  }

  .box h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #5d4037;
    font-weight: 700;
    font-size: 1.5rem;
  }

  ul.stats-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
  }
  ul.stats-list li {
    font-size: 1.1rem;
    margin-bottom: 12px;
    color: #4b2e2e;
    font-weight: 600;
  }
  ul.stats-list li span {
    float: right;
    font-weight: 700;
  }
</style>
</head>
<body>

  <nav class="sidebar">
    <a href="admin_dashboard.php" class="active">Admin Dashboard</a>
    <a href="admin_menu.php">Menu Management</a>
    <a href="user_management.php">User Management</a>
    <a href="admin_orders.php">Order Management</a>
    <a href="revenue_report.php">Revenue Report</a>
    <a href="logout.php">Logout</a>
  </nav>
<main class="content">
  <div class="reports">

    <div class="box">
      <h3>Today's Report (<?= date('d M Y') ?>)</h3>
      <ul class="stats-list">
        <li>Pending Orders <span><?= $pendingCount ?></span></li>
        <li>Completed Orders <span><?= $completedCount ?></span></li>
        <li>Cancelled Orders <span><?= $cancelledCount ?></span></li>
      </ul>
      <p><a href="orders.php?filter=today" style="color:#ff6600; font-weight:bold;">View Details</a></p>
    </div>

    <div class="box">
      <h3>Overall Report</h3>
      <ul class="stats-list">
        <li>Total Orders <span><?= $totalOrders ?></span></li>
        <li>Total Revenue (Delivered) <span>₹<?= number_format($totalRevenue ?: 0, 2) ?></span></li>
      </ul>
      <p><a href="orders.php" style="color:#ff6600; font-weight:bold;">View Details</a></p>
    </div>

  </div>
</main>
</body>
</html>
