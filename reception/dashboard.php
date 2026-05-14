<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../index.php');
    exit;
}

// Fetch pending bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone 
    FROM bookings b 
    JOIN users u ON b.customer_id = u.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reception Dashboard - AutoPro Garage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; color: var(--text-light); }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--bg-dark); font-weight: 600; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.8rem; font-weight: 600; }
        .badge.pending { background: rgba(239, 172, 68, 0.2); color: #fbbf24; }
        .badge.approved { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .badge.completed { background: rgba(56, 189, 248, 0.2); color: var(--accent); }
    </style>
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
                <h1>Bookings Overview</h1>
            </div>

            <div class="card">
                <h2>Recent Customer Bookings</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): ?>
                        <tr>
                            <td>#<?php echo $b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['customer_name']); ?><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($b['phone']); ?></small></td>
                            <td><?php echo htmlspecialchars($b['vehicle_make'] . ' ' . $b['vehicle_model']); ?></td>
                            <td><span class="badge <?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                            <td>
                                <?php if($b['status'] == 'pending'): ?>
                                    <a href="assign_task.php?booking_id=<?php echo $b['id']; ?>" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-decoration: none; display: inline-block;">Review & Assign</a>
                                <?php elseif($b['status'] == 'approved' || $b['status'] == 'completed'): ?>
                                    <a href="pos.php?booking_id=<?php echo $b['id']; ?>" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-decoration: none; background: var(--success); display: inline-block;">Generate Invoice</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($bookings) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No bookings found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
