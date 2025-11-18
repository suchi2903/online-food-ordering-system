<?php
session_start();
require 'db.php'; // PDO or MySQLi connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch saved addresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Addresses</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; }
        .container { max-width: 600px; margin: 50px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; color: #333; }
        .address { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container">
    <h2>My Saved Addresses</h2>
    <?php if ($addresses): ?>
        <?php foreach ($addresses as $addr): ?>
            <div class="address">
                <p><strong>Address:</strong> <?= htmlspecialchars($addr['address']) ?></p>
                <p><strong>City:</strong> <?= htmlspecialchars($addr['city']) ?></p>
                <p><strong>State:</strong> <?= htmlspecialchars($addr['state']) ?></p>
                <p><strong>Zip:</strong> <?= htmlspecialchars($addr['zip']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No addresses saved yet. Go to checkout to add one.</p>
    <?php endif; ?>
</div>
</body>
</html>
