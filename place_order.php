<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Your cart is empty!");
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 1️⃣ Calculate total price from cart
    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $stmtPrice = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?");
        $stmtPrice->execute([$item['menu_item_id']]);
        $menuItem = $stmtPrice->fetch(PDO::FETCH_ASSOC);
        if (!$menuItem) {
            throw new Exception("Menu item not found: " . $item['menu_item_id']);
        }
        $total_price += $menuItem['price'] * $item['quantity'];
    }

    // 2️⃣ Insert into orders
    $delivery_location = "823, vijaynagar, Japan - 4567"; // Replace with actual user address
    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, order_date, total_price, order_status, delivery_location) VALUES (?, NOW(), ?, 'Preparing', ?)");
    $stmtOrder->execute([$user_id, $total_price, $delivery_location]);
    $order_id = $pdo->lastInsertId();

    // 3️⃣ Insert each item into order_items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmtItem->execute([$order_id, $item['menu_item_id'], $item['quantity']]);
    }

    $pdo->commit();

    // 4️⃣ Clear cart
    unset($_SESSION['cart']);

    // 5️⃣ Redirect to order_history.php to see the new order
    header("Location: order_history.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("❌ Error placing order: " . $e->getMessage());
}
