<?php
session_start();
require 'db.php'; // your PDO connection

// Check admin login here (add your auth check)

// Get Today's Orders data
$sqlToday = "SELECT 
                DATE(order_date) as order_date,
                COUNT(*) as num_orders,
                SUM(total_price) as revenue
            FROM orders
            WHERE DATE(order_date) = CURDATE()
            GROUP BY DATE(order_date)";
$stmtToday = $pdo->query($sqlToday);
$todayData = $stmtToday->fetch(PDO::FETCH_ASSOC);

// Get Overall Orders data
$sqlOverall = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue
            FROM orders
            WHERE order_status = 'Delivered'";
$stmtOverall = $pdo->query($sqlOverall);
$overallData = $stmtOverall->fetch(PDO::FETCH_ASSOC);

// Get counts by status for today's report box
$sqlStatus = "SELECT order_status, COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE() GROUP BY order_status";
$stmtStatus = $pdo->query($sqlStatus);
$statusCounts = [];
while ($row = $stmtStatus->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['order_status']] = $row['count'];
}

// Prepare counts with default 0
$pendingCount = $statusCounts['Pending'] ?? 0;
$completedCount = $statusCounts['Completed'] ?? 0;
$cancelledCount = $statusCounts['Cancelled'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: auto; padding: 20px; }
        h1 { text-align: center; margin-bottom: 40px; }
        .reports { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        .box {
            background: #fff8f0;
            border: 2px solid #ffb347;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
        }
        .box:hover {
            background-color: #ffecce;
        }
        .box h3 {
            color: #ff6600;
            margin-bottom: 15px;
        }
        .stats-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 16px;
            text-align: left;
        }
        .stats-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ffddb3;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }
        .stats-list li span {
            color: #ff6600;
        }
        a.box-link {
            color: inherit;
            text-decoration: none;
            display: block;
        }
    </style>
</head>
<body>

<h1>Admin Dashboard</h1>

<div class="reports">
    <!-- Today's Report Box -->
    <a href="todays_orders.php" class="box box-link" title="Click to see full today's orders report">
        <h3>Today's Report (<?= date('d M Y') ?>)</h3>
        <ul class="stats-list">
            <li>Pending Orders <span><?= $pendingCount ?></span></li>
            <li>Completed Orders <span><?= $completedCount ?></span></li>
            <li>Cancelled Orders <span><?= $cancelledCount ?></span></li>
            <li>Total Orders <span><?= $todayData['num_orders'] ?? 0 ?></span></li>
            <li>Total Revenue <span>₹<?= number_format($todayData['revenue'] ?? 0, 2) ?></span></li>
        </ul>
    </a>

    <!-- Overall Report Box -->
    <a href="overall_report.php" class="box box-link" title="Click to see full overall report">
        <h3>Overall Report</h3>
        <ul class="stats-list">
            <li>Total Orders <span><?= $overallData['total_orders'] ?? 0 ?></span></li>
            <li>Total Revenue (Delivered) <span>₹<?= number_format($overallData['total_revenue'] ?? 0, 2) ?></span></li>
        </ul>
    </a>
</div>

</body>
</html>
