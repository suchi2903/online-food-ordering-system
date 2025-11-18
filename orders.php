<?php
session_start();
require 'db.php';

// Check admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$filter = $_GET['filter'] ?? null;
$where = '';
$params = [];

if ($filter === 'today') {
    $where = "WHERE DATE(order_date) = CURDATE()";
} 

// Fetch orders with optional filter
$sql = "SELECT o.id, u.username, o.order_date, o.total_price, o.order_status, o.delivery_location
        FROM orders o
        JOIN users u ON o.user_id = u.id
        $where
        ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders Detail</title>
    <link rel="stylesheet" href="style.css">
    <style>
      body { font-family: Arial, sans-serif; max-width: 900px; margin: auto; padding: 20px;}
      table { width: 100%; border-collapse: collapse; margin-top: 20px;}
      th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
      th { background-color: #ff6600; color: white; }
      a.back-link { margin-bottom: 15px; display: inline-block; }
      
    </style>
</head>
<body>

<h2>Orders <?php if ($filter) echo "(" . htmlspecialchars($filter) . ")"; ?></h2>

<?php if ($orders): ?>
<table>
  <thead>
    <tr>
      <th>Order ID</th>
      <th>User</th>
      <th>Order Date</th>
      <th>Total Price (₹)</th>
      <th>Status</th>
      <th>Delivery Location</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($orders as $order): ?>
    <tr>
      <td>#<?= $order['id'] ?></td>
      <td><?= htmlspecialchars($order['username']) ?></td>
      <td><?= date("d M Y, h:i A", strtotime($order['order_date'])) ?></td>
      <td>₹<?= number_format($order['total_price'], 2) ?></td>
      <td><?= htmlspecialchars($order['order_status']) ?></td>
      <td><?= htmlspecialchars($order['delivery_location']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p>No orders found.</p>
<?php endif; ?>
<br>

<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

</body>
</html>
