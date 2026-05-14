<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'admin')) {
    die("Unauthorized");
}

if (!isset($_GET['booking_id'])) {
    header('Location: dashboard.php');
    exit;
}

$booking_id = $_GET['booking_id'];

// Get Booking
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

// Get attached media
$media_stmt = $pdo->prepare("SELECT * FROM media WHERE booking_id = ?");
$media_stmt->execute([$booking_id]);
$media_files = $media_stmt->fetchAll();

// Get Mechanics
$mech_stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'mechanic'");
$mechanics = $mech_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mechanic_id = $_POST['mechanic_id'];
    $task_desc = $_POST['task_description'];
    
    // Create Task
    $stmt = $pdo->prepare("INSERT INTO tasks (booking_id, mechanic_id, description, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$booking_id, $mechanic_id, $task_desc]);
    
    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    echo "<script>alert('Task assigned to mechanic successfully!'); window.location.href='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Task - AutoPro Garage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Reception</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Bookings</a></li>
                    <li><a href="pos.php">Point of Sale / Invoicing</a></li>
                    <li><a href="inventory.php">Inventory & Direct Sale</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>Assign Mechanic to Booking #<?php echo htmlspecialchars($booking_id); ?></h1>
                <a href="dashboard.php" style="color: var(--accent); text-decoration: none;">&larr; Back</a>
            </div>

            <div class="card" style="max-width: 600px;">
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model'] . ' (' . $booking['vehicle_year'] . ')'); ?></p>
                <p><strong>Customer Issue:</strong> <br><?php echo nl2br(htmlspecialchars($booking['description'])); ?></p>
                
                <?php if(count($media_files) > 0): ?>
                    <div style="margin-top: 1rem;">
                        <strong>Customer Uploads:</strong><br>
                        <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
                            <?php foreach($media_files as $media): ?>
                                <?php if($media['media_type'] == 'image'): ?>
                                    <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <video src="../<?php echo htmlspecialchars($media['file_path']); ?>" controls style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px;"></video>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <hr style="border:0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
                
                <form method="POST">
                    <div class="form-group">
                        <label>Select Mechanic</label>
                        <select name="mechanic_id" required>
                            <option value="">-- Choose a Mechanic --</option>
                            <?php foreach($mechanics as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Instructions for Mechanic (Will be seen on their dashboard)</label>
                        <textarea name="task_description" rows="3" required placeholder="Specific instructions..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Assign Task</button>
                </form>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
