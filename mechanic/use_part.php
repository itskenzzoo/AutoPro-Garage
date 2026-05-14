<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $inventory_id = $_POST['inventory_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Check current quantity
    $stmt = $pdo->prepare("SELECT quantity FROM inventory WHERE id = ?");
    $stmt->execute([$inventory_id]);
    $item = $stmt->fetch();
    
    if ($item && $item['quantity'] >= $quantity) {
        // Deduct inventory
        $update = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
        $update->execute([$quantity, $inventory_id]);
        
        // Log to task_parts
        $log_stmt = $pdo->prepare("INSERT INTO task_parts (task_id, inventory_id, quantity) VALUES (?, ?, ?)");
        $log_stmt->execute([$task_id, $inventory_id, $quantity]);
        
        echo "<script>alert('Part logged successfully to this task! Inventory updated.'); window.location.href='inspection.php?task_id=".$task_id."';</script>";
    } else {
        echo "<script>alert('Error: Not enough stock available!'); window.location.href='inspection.php?task_id=".$task_id."';</script>";
    }
}
