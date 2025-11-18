<?php
require 'db.php'; // your PDO connection setup

// Get today's orders
$sql = "SELECT * FROM orders WHERE DATE(order_date) = CURDATE() ORDER BY order_date DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Today's Orders</title>
    <style>
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        a.back-link { display: block; width: 90%; margin: 10px auto; text-decoration: none; color: #007BFF; }
    </style>
</head>
<body>

<h1 style="text-align:center;">Orders Placed Today (<?= date('d M Y') ?>)</h1>

<?php if (count($orders) === 0): ?>
    <p style="text-align:center; font-size: 1.2em;">No orders found for today.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Order Date & Time</th>
                <th>Total Price (₹)</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Delivery Location</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['user_id']) ?></td>
                <td><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></td>
                <td>₹<?= number_format($order['total_price'], 2) ?></td>
                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                <td><?= htmlspecialchars($order['order_status']) ?></td>
                <td><?= htmlspecialchars($order['delivery_location']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="dashboard.php" class="back-link">← Back to Dashboard</a>

</body>
</html>
