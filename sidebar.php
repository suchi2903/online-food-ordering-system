<?php
// sidebar.php
?>

<style>
  /* Sidebar container */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    background-color: #6d4c41;
    color: white;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
    z-index: 1000;
  }

  /* Sidebar links */
  .sidebar a {
    padding: 15px 20px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    border-left: 5px solid transparent;
    transition: background-color 0.3s, border-left-color 0.3s;
  }

  /* Hover and active states */
  .sidebar a:hover,
  .sidebar a.active {
    background-color: #5d4037;
    border-left-color: #f3c28d;
  }

  /* Content area margin to avoid overlap */
  .content {
    margin-left: 220px;
    padding: 20px;
    min-height: 100vh;
    background: #fef6e4;
    color: #4b2e2e;
    font-family: Arial, sans-serif;
  }
</style>

<div class="sidebar">
  <a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>">Admin Dashboard</a>
  <a href="admin_menu.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_menu.php' ? 'active' : '' ?>">Menu Management</a>
  <a href="user_management.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : '' ?>">User Management</a>
  <a href="admin_orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : '' ?>">Order Management</a>
  <a href="revenue_report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'revenue_report.php' ? 'active' : '' ?>">Revenue Report</a>
  <a href="logout.php">Logout</a>
</div>

<div class="content">
