<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    die("Invalid order ID");
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found");
}

// Fetch order items
$stmt_items = $pdo->prepare("SELECT oi.quantity, m.name, m.price FROM order_items oi JOIN menu m ON oi.menu_item_id = m.id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
$order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Simple box styling */
        .confirmation-box {
            max-width: 700px;
            background: #fff8f0;
            border: 1px solid #d2b48c;
            border-radius: 10px;
            padding: 25px 30px;
            margin: 30px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            font-family: Arial, sans-serif;
            color: #4b2e2e;
        }

        .confirmation-box h1 {
            text-align: center;
            color: #3e2723;
            margin-bottom: 25px;
            user-select: none;
        }

        .confirmation-box p, .confirmation-box ul {
            font-size: 1.1rem;
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .confirmation-box ul {
            padding-left: 20px;
        }

        .confirmation-box a {
            display: inline-block;
            margin-top: 15px;
            background-color: #6d4c41;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .confirmation-box a:hover {
            background-color: #5d4037;
        }
    </style>
</head>
<body>

<div class="confirmation-box">
    <h1>Order Confirmation</h1>
    <p><strong>Order Number:</strong> #<?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Order Date:</strong> <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
    <p><strong>Delivery Location:</strong><br><?= nl2br(htmlspecialchars($order['delivery_location'])) ?></p>

    <h3>Items:</h3>
    <ul>
        <?php foreach ($order_items as $item): ?>
            <li>(x<?= $item['quantity'] ?>) <?= htmlspecialchars($item['name']) ?> – ₹<?= number_format($item['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>

    <p><strong>Total Price:</strong> ₹<?= number_format($order['total_price'], 2) ?></p>

    <a href="menu.php">Back to Menu</a>
    <a href="order_history.php">Order History</a>
</div>

</body>
</html>
