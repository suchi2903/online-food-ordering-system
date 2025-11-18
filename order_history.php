<?php
session_start();
require 'db.php'; // PDO connection (your db.php is correct)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f3ece7; /* light coffee cream */
            margin: 0;
            padding: 0;
            color: #3e2723; /* dark coffee brown */
        }

        .container {
            width: 85%;
            max-width: 900px;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            color: #4e342e;
            margin-bottom: 30px;
        }

        .order-card {
            background: #fff;
            border: 1px solid #d7ccc8;
            border-left: 6px solid #6d4c41; /* coffee brown accent */
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(109, 76, 65, 0.15);
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: scale(1.01);
            box-shadow: 0 6px 14px rgba(109, 76, 65, 0.25);
        }

        .order-card h2 {
            margin: 0 0 10px;
            color: #5d4037;
        }

        .order-card p {
            margin: 5px 0;
            font-size: 15px;
        }

        .items {
            margin-top: 12px;
            padding: 10px;
            background: #efebe9;
            border-radius: 8px;
        }

        .item {
            padding: 6px 0;
            border-bottom: 1px dashed #bcaaa4;
            font-size: 14px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .total {
            font-weight: bold;
            margin-top: 12px;
            color: #4e342e;
        }

        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-block;
        }

        .status.Preparing { background: #ffe0b2; color: #e65100; }
        .status.Completed { background: #c8e6c9; color: #2e7d32; }
        .status.Cancelled { background: #ffcdd2; color: #c62828; }
    </style>
</head>
<body>
    <div class="container">
        <h1>☕ Your Order History</h1>

        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h2>Order Number: #<?= htmlspecialchars($order['id']) ?></h2>
                    <p><strong>Order Date:</strong> <?= date("d M Y, h:i A", strtotime($order['order_date'])) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status <?= htmlspecialchars($order['order_status']) ?>">
                            <?= htmlspecialchars($order['order_status']) ?>
                        </span>
                    </p>
                    <p><strong>Delivery Location:</strong> <?= htmlspecialchars($order['delivery_location']) ?></p>

                    <div class="items">
                        <strong>Items:</strong>
                        <?php
                        // ✅ Fetch items from `menu`
                        $stmtItems = $pdo->prepare("
                            SELECT oi.quantity, m.name, m.price 
                            FROM order_items oi
                            JOIN menu m ON oi.menu_item_id = m.id
                            WHERE oi.order_id = ?
                        ");
                        $stmtItems->execute([$order['id']]);
                        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                        if ($items):
                            foreach ($items as $item):
                        ?>
                                <div class="item">
                                    (x<?= $item['quantity'] ?>) <?= htmlspecialchars($item['name']) ?> – ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </div>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                            <p style="color:red;">⚠ No items found for this order.</p>
                        <?php endif; ?>
                    </div>

                    <p class="total">Total Price: ₹<?= number_format($order['total_price'], 2) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No previous orders found ☹️</p>
        <?php endif; ?>
    </div>
</body>
</html>
