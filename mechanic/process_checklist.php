<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $items = $_POST['items'] ?? [];
    
    // Clear existing checklist for this task
    $stmt = $pdo->prepare("DELETE FROM task_checklists WHERE task_id = ?");
    $stmt->execute([$task_id]);
    
    $insert = $pdo->prepare("INSERT INTO task_checklists (task_id, item_name, status, notes) VALUES (?, ?, ?, ?)");
    
    foreach ($items as $name => $data) {
        $status = $data['status'];
        $notes = $data['notes'];
        $insert->execute([$task_id, $name, $status, $notes]);
    }
    
    echo "<script>alert('Checklist updated successfully!'); window.location.href='inspection.php?task_id=".$task_id."';</script>";
}
?>
