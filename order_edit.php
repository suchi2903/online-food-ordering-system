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

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    die("Invalid Order ID");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update order status
    $new_status = $conn->real_escape_string($_POST['order_status']);
    $allowed_statuses = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];

    // Update user info
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);

    if (in_array($new_status, $allowed_statuses)) {
        $conn->query("UPDATE orders SET order_status = '$new_status' WHERE id = $order_id");
    }
    // Update user info linked to this order
    $user_id = (int)$_POST['user_id'];
    $conn->query("UPDATE users SET username = '$username', email = '$email' WHERE id = $user_id");

    header("Location:admin_orders.php");
    exit;
}

// Fetch order info + user info
$orderSql = "SELECT o.*, u.id as user_id, u.username, u.email 
             FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id";
$orderResult = $conn->query($orderSql);
$order = $orderResult->fetch_assoc();
if (!$order) die("Order not found");

// Fetch order items + images
$itemsSql = "SELECT oi.quantity, m.name, m.price, m.image FROM order_items oi
             JOIN menu m ON oi.menu_item_id = m.id
             WHERE oi.order_id = $order_id";
$itemsResult = $conn->query($itemsSql);
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Order #<?= $order_id ?></title>
<style>
  /* Box styling for layout */
  .container {
    margin-left: 25px; /* adjust if sidebar width */
    padding: 20px;
    max-width: 900px;
    background: #fff8f0;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(109, 76, 65, 0.15);
    font-family: Arial, sans-serif;
    color: #4b2e2e;
  }
  h2, h3 {
    color: #6d4c41;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
  }
  th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
  }
  img {
    max-width: 60px;
    max-height: 60px;
    object-fit: cover;
    border-radius: 5px;
  }
  label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
  }
  input[type=text], input[type=email], select {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }
  button {
    margin-top: 20px;
    padding: 10px 18px;
    background: #6d4c41;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
  }
  button:hover {
    background: #5d4037;
  }
  .back-link {
    margin-top: 20px;
    display: inline-block;
    color: #6d4c41;
    text-decoration: none;
  }
  .back-link:hover {
    color: #5d4037;
  }
</style>
</head>
<body>
<div class="container">
  <h2>Edit Order #<?= htmlspecialchars($order_id) ?></h2>

  <h3>User Details</h3>
  <form method="POST" action="">
    <input type="hidden" name="user_id" value="<?= htmlspecialchars($order['user_id']) ?>">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" value="<?= htmlspecialchars($order['username']) ?>" required>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($order['email']) ?>" required>

    <h3>Order Details</h3>
    <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
    <p><strong>Total Price:</strong> ₹<?= number_format($order['total_price'], 2) ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>

    <label for="order_status">Status</label>
    <select name="order_status" id="order_status" required>
      <?php
      $statuses = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];
      foreach ($statuses as $status) {
          $selected = ($order['order_status'] === $status) ? 'selected' : '';
          echo "<option value=\"$status\" $selected>$status</option>";
      }
      ?>
    </select>

    <h3>Order Items</h3>
    <table>
      <thead>
        <tr><th>Image</th><th>Name</th><th>Price (₹)</th><th>Quantity</th><th>Subtotal (₹)</th></tr>
      </thead>
      <tbody>
        <?php while ($item = $itemsResult->fetch_assoc()): ?>
          <tr>
            <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <button type="submit">Update Order & User</button>
  </form>

  <a href="admin_orders.php" class="back-link">← Back to Order Management</a>
</div>
</body>
</html>

<?php
$conn->close();
?>
