<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_active = isset($_GET['active']) ? (int)$_GET['active'] : 1;

if ($id) {
    $stmt = $pdo->prepare("UPDATE users SET active = ? WHERE id = ?");
    $stmt->execute([$new_active, $id]);
}

header('Location: user_management.php');
exit;
