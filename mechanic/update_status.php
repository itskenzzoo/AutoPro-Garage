<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND mechanic_id = ?");
    $stmt->execute([$status, $task_id, $_SESSION['user_id']]);
    
    header('Location: dashboard.php');
}
