<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mechanic_id = $_SESSION['user_id'];
    $task_id = $_POST['task_id'];
    
    // Handle File Uploads
    if (!empty($_FILES['media']['name'][0])) {
        $file_count = count($_FILES['media']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            $tmp_name = $_FILES['media']['tmp_name'][$i];
            $name = $_FILES['media']['name'][$i];
            $error = $_FILES['media']['error'][$i];
            
            if ($error === UPLOAD_ERR_OK) {
                // Determine if image or video
                $mime = mime_content_type($tmp_name);
                $media_type = (strpos($mime, 'video') !== false) ? 'video' : 'image';
                
                $dir = $media_type === 'video' ? '../uploads/videos/' : '../uploads/images/';
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_inspection.' . $ext;
                $destination = $dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    // Save to DB under task_id instead of booking_id
                    $db_path = 'uploads/' . ($media_type === 'video' ? 'videos/' : 'images/') . $new_filename;
                    $stmt_media = $pdo->prepare('INSERT INTO media (task_id, file_path, media_type, uploaded_by) VALUES (?, ?, ?, ?)');
                    $stmt_media->execute([$task_id, $db_path, $media_type, $mechanic_id]);
                }
            }
        }
    }
    
    header('Location: inspection.php?task_id=' . $task_id);
}
