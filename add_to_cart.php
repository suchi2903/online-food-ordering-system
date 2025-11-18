<?php
session_start();
require 'db.php'; // Your PDO connection

// Check if 'id' is provided
if (!isset($_GET['id'])) {
    header("Location: menu.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch item from DB
$stmt = $pdo->prepare("SELECT id, name, price FROM menu WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if item already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }

    // If item not in cart, add it
    if (!$found) {
        $product['quantity'] = 1;
        $_SESSION['cart'][] = $product;
    }
}

// Redirect to cart page (anyone can see the cart)
header("Location: cart.php");
exit;
?>

<html>
<head>
    <title>Menu | YummiYo</title>
    <link rel="stylesheet" href="style.css">
</head>
</html>