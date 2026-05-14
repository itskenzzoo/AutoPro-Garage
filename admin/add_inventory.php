<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $part_num = $_POST['part_number'];
    $part_name = $_POST['part_name'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO inventory (part_number, part_name, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$part_num, $part_name, $qty, $price]);
        echo "<script>alert('Part added successfully!'); window.location.href='inventory.php';</script>";
    } catch (\PDOException $e) {
        echo "<script>alert('Error: Part number might already exist.'); window.location.href='inventory.php';</script>";
    }
}
