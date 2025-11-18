<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    die("Order ID missing.");
}

$order_id = (int)$_GET['order_id'];

$conn = new mysqli("localhost", "root", "", "yummiyo_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch order info with user details
$sqlOrder = "
    SELECT o.id, o.order_date, o.total_price, o.payment_method, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
";
$stmt = $conn->prepare($sqlOrder);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();
if ($orderResult->num_rows === 0) {
    die("Order not found.");
}
$order = $orderResult->fetch_assoc();

// Fetch order items with menu details
$sqlItems = "
    SELECT oi.quantity, m.name, m.price
    FROM order_items oi
    JOIN menu m ON oi.menu_item_id = m.id
    WHERE oi.order_id = ?
";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $order_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Order Details | YummiYo Admin</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #fef6e4; color: #4b2e2e; max-width: 800px; margin: auto;}
    h1, h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px;}
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background-color: #d2b48c; }
    .back-link {
        display: block;
        margin: 20px 0;
        text-align: center;
        font-weight: bold;
        text-decoration: none;
        color: #6d4c41;
    }
    .back-link:hover {
        color: #5d4037;
    }
</style>
</head>
<body>

<h1>Order Details</h1>

<p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
<p><strong>User:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
<p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
<p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>

<table>
    <thead>
        <tr>
            <th>Menu Item</th>
            <th>Price (₹)</th>
            <th>Quantity</th>
            <th>Subtotal (₹)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total = 0;
        while ($item = $itemsResult->fetch_assoc()): 
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td><?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
            <td><strong>₹<?= number_format($total, 2) ?></strong></td>
        </tr>
    </tbody>
</table>

<a href="admin_dashboard.php" class="back-link">← Back to Admin Dashboard</a>

</body>
</html>

<?php
$stmt->close();
$stmtItems->close();
$conn->close();
?>
