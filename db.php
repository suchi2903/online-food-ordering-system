<?php
$host = "localhost";
$dbname = 'yummiyo_db';  // ✅ Updated to your real database name
$username = 'root';
$password = ''; // No password for XAMPP by default

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
