<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['user_id'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $description = $_POST['description'];
    
    // Insert Booking
    $stmt = $pdo->prepare('INSERT INTO bookings (customer_id, vehicle_make, vehicle_model, vehicle_year, description) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$customer_id, $make, $model, $year, $description]);
    $booking_id = $pdo->lastInsertId();

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
                $new_filename = uniqid() . '.' . $ext;
                $destination = $dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    // Save to DB
                    $db_path = 'uploads/' . ($media_type === 'video' ? 'videos/' : 'images/') . $new_filename;
                    $stmt_media = $pdo->prepare('INSERT INTO media (booking_id, file_path, media_type, uploaded_by) VALUES (?, ?, ?, ?)');
                    $stmt_media->execute([$booking_id, $db_path, $media_type, $customer_id]);
                }
            }
        }
    }
    
    echo "<script>alert('Booking submitted successfully! We will review and let you know if a mobile mechanic is coming.'); window.location.href='dashboard.php';</script>";
}
