<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "yummiyo_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- DELETE ORDER FUNCTIONALITY ---
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Delete related order items first
    $conn->query("DELETE FROM order_items WHERE order_id = $delete_id");

    // Delete order
    $conn->query("DELETE FROM orders WHERE id = $delete_id");

    // Redirect to avoid resubmission and refresh list
    header("Location: admin_orders.php");
    exit;
}

// Fetch orders and user info
$sql = "SELECT o.id as order_id, o.order_date, o.total_price, o.payment_method, o.order_status, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Order Management | YummiYo Admin</title>
<style>
  /* Your CSS styles (unchanged) */
  * {
    box-sizing: border-box;
  }
  body, html {
    margin: 0; padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #fef6e4;
    color: #4b2e2e;
    min-height: 100vh;
  }
  .content {
    margin-left: 90px;
    padding: 20px 15px;
    min-height: 200vh;
    background: #fef6e4;
    color: #4b2e2e;
  }
  h1 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 600;
    color: #6d4c41;
  }
  .table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 0 10px rgb(109 76 65 / 0.1);
    background: #fff8f0;
    padding: 9px;
    width: 100%;
  }
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    border: 1px solid #d2b48c;
    padding: 12px 15px;
    text-align: left;
    white-space: nowrap;
  }
  th {
    background-color: #d2b48c;
    color: #3e2e1f;
    font-weight: 600;
  }
  tr:hover {
    background-color: #fff3e0;
  }
  a.delete-link {
    color: #b33a3a;
    font-weight: 600;
    text-decoration: none;
  }
  a.delete-link:hover {
    text-decoration: underline;
  }
  a.edit-link {
    color: #4b2e2e;
    font-weight: 600;
    text-decoration: none;
  }
  a.edit-link:hover {
    text-decoration: underline;
    color: #6d4c41;
  }
  .back-link {
    display: block;
    text-align: center;
    font-weight: 700;
    margin-top: 25px;
    color: #6d4c41;
    text-decoration: none;
  }
  .back-link:hover {
    color: #5d4037;
  }
</style>
</head>
<body>
<div class="content">
  <h1>Order Management</h1>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Email</th>
          <th>Order Date</th>
          <th>Total Price (₹)</th>
          <th>Payment Method</th>
          <th>Status</th>
          <th>Delete</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($order = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($order['order_id']) ?></td>
          <td><?= htmlspecialchars($order['username']) ?></td>
          <td><?= htmlspecialchars($order['email']) ?></td>
          <td><?= htmlspecialchars($order['order_date']) ?></td>
          <td>₹<?= number_format($order['total_price'], 2) ?></td>
          <td><?= htmlspecialchars($order['payment_method']) ?></td>
          <td><?= htmlspecialchars($order['order_status']) ?></td>
          <td>
            <a href="admin_orders.php?delete=<?= $order['order_id'] ?>" class="delete-link" onclick="return confirm('Are you sure to delete this order?');">Delete</a>
          </td>
          <td>
            <a href="order_edit.php?order_id=<?= $order['order_id'] ?>" class="edit-link">Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>

<?php
$conn->close();
?>
