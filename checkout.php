<?php
session_start();
require 'db.php'; // your PDO connection

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    echo "<h2>Your cart is empty!</h2>";
    echo "<p><a href='menu.php'>Go to Menu</a></p>";
    exit;
}

$cart = $_SESSION['cart'];
$total_price = 0;
$errors = [];
$success = false;

// Validate cart items exist in menu_items
foreach ($cart as $index => $item) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$item['id']]);
    $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$menu_item) {
        $errors[] = "Menu item not found: " . htmlspecialchars($item['name']);
        // Remove invalid item from cart
        unset($cart[$index]);
    } else {
        // Update price from DB in case it changed
        $cart[$index]['price'] = $menu_item['price'];
        $total_price += $menu_item['price'] * $item['quantity'];
    }
}

// Update session cart after validation
$_SESSION['cart'] = $cart;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $street = trim($_POST['street'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if (empty($payment_method)) {
        $errors[] = "Please select a payment method.";
    }
    if (!$street || !$area || !$city || !$pincode) {
        $errors[] = "Please fill all delivery location fields.";
    }

    if (empty($errors) && !empty($cart)) {
        $delivery_location = "$street, $area, $city - $pincode";
        try {
            $pdo->beginTransaction();

            // Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_date, total_price, payment_method, delivery_location, order_status) VALUES (?, NOW(), ?, ?, ?, 'Preparing')");
            $stmt->execute([$_SESSION['user_id'], $total_price, $payment_method, $delivery_location]);

            $order_id = $pdo->lastInsertId();

            // Insert into order_items table
            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
            foreach ($cart as $item) {
                $stmt_item->execute([$order_id, $item['id'], $item['quantity']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $success = true;
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to place order: " . $e->getMessage();
        }
    } elseif (empty($cart)) {
        $errors[] = "Your cart has invalid items. Please update your cart.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Checkout | YummiYo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Minimal added CSS */
        .location-box {
            background: #fff8f0;
            border: 1px solid #d2b48c;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .location-box h2 {
            color: #4b2e2e;
            text-align: center;
            margin-bottom: 15px;
            user-select: none;
        }
        .location-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .location-field {
            flex: 1 1 48%;
            min-width: 200px;
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .total-row td:first-child {
            text-align: right;
            font-weight: bold;
            padding-right: 10px;
            background-color: #e0c7b2;
        }
        .total-row td:last-child {
            font-weight: bold;
            background-color: #e0c7b2;
        }
        /* Ensure inputs and select fill their containers */
        input[type=text], select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d2b48c;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

<h1>Checkout</h1>

<?php if ($success): ?>
    <h2>Order placed successfully! Thank you for ordering.</h2>
    <p><a href="menu.php" class="back">Back to Menu</a></p>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div style="color:red; max-width:600px; margin:10px auto;">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3>Order Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Price (₹)</th>
                <th>Quantity</th>
                <th>Subtotal (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">Total:</td>
                <td>₹<?= number_format($total_price, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <form method="POST" action="checkout.php">
        <p>
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">--Select--</option>
                <option value="Cash on Delivery" <?= (isset($_POST['payment_method']) && $_POST['payment_method']=='Cash on Delivery')?'selected':'' ?>>Cash on Delivery</option>
                <option value="Online Payment" <?= (isset($_POST['payment_method']) && $_POST['payment_method']=='Online Payment')?'selected':'' ?>>UPI Payment</option>
            </select>
        </p>

        <div class="location-box">
            <h2>Delivery Location</h2>
            <div class="location-fields">
                <div class="location-field">
                    <label for="street">Street Address:</label>
                    <input type="text" id="street" name="street" placeholder="123 Main St" required value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
                </div>
                <div class="location-field">
                    <label for="area">Area:</label>
                    <input type="text" id="area" name="area" placeholder="Downtown" required value="<?= htmlspecialchars($_POST['area'] ?? '') ?>">
                </div>
                <div class="location-field">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" placeholder="City Name" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
                <div class="location-field">
                    <label for="pincode">Pincode:</label>
                    <input type="text" id="pincode" name="pincode" placeholder="123456" required value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="checkout-btn">Place Order</button>
    </form>

    <p><a href="cart.php" class="back">Back to Cart</a></p>

<?php endif; ?>

</body>
</html>
