<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

// Fetch all bookings for this customer
$stmt = $pdo->prepare("
    SELECT b.*, i.id as invoice_id, i.total_amount 
    FROM bookings b 
    LEFT JOIN invoices i ON b.id = i.booking_id 
    WHERE b.customer_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - AutoPro Garage</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; color: var(--text-light); }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--bg-dark); font-weight: 600; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.8rem; font-weight: 600; }
        .badge.pending { background: rgba(239, 172, 68, 0.2); color: #b45309; }
        .badge.approved { background: rgba(56, 189, 248, 0.2); color: #0369a1; }
        .badge.completed { background: rgba(16, 185, 129, 0.2); color: #047857; }
    </style>
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Garage</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Book a Service</a></li>
                    <li><a href="my_bookings.php" class="active">My Bookings</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>My Bookings & History</h1>
            </div>

            <div class="card">
                <h2>Booking History</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Issue Description</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($b['vehicle_make'] . ' ' . $b['vehicle_model']); ?></td>
                            <td><?php echo htmlspecialchars(substr($b['description'], 0, 50)) . '...'; ?></td>
                            <td><span class="badge <?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                            <td>
                                <?php if($b['invoice_id']): ?>
                                    <a href="../reception/view_invoice.php?id=<?php echo $b['invoice_id']; ?>" style="color: var(--accent); font-weight: 500; text-decoration: none;">View Receipt ($<?php echo number_format($b['total_amount'], 2); ?>)</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.9rem;">Not Billed Yet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($bookings) === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">You haven't made any bookings yet.</td>
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
