<?php
session_start();
require 'db.php'; // your PDO connection setup

// Update quantities when form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            // Remove item if quantity is zero or less
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }
        } else {
            // Update quantity
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $id) {
                    $item['quantity'] = $qty;
                    break;
                }
            }
            unset($item);
        }
    }
    // Re-index array to keep indexes sequential
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    header("Location: cart.php");
    exit;
}

// If cart is empty or not set, show message and exit
if (empty($_SESSION['cart'])) {
    echo "<h2>Your cart is empty!</h2>";
    echo "<p><a href='menu.php'>Go to Menu</a></p>";
    exit;
}

// Filter to keep only valid items (in case of corruption)
$_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) {
    return is_array($item) && isset($item['id'], $item['name'], $item['price'], $item['quantity']);
});

// Calculate total
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Your Cart</h1>

<form method="POST" action="cart.php">
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price (₹)</th>
                <th>Quantity</th>
                <th>Subtotal (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <?php
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td>
                        <input 
                            type="number" 
                            name="qty[<?= (int)$item['id'] ?>]" 
                            value="<?= (int)$item['quantity'] ?>" 
                            min="0" 
                            onchange="this.form.submit()" 
                            style="width: 60px;"
                        >
                    </td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" align="right"><strong>Total: ₹</strong></td>
                <td><strong><?= number_format($total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</form>

<div class="cart-navigation">
    <a href="menu.php">← Continue Order</a>
    <a href="checkout.php">Proceed to Checkout →</a>
</div>

</body>
</html>
