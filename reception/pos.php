<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'admin')) {
    die("Unauthorized");
}

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $b_id = $_POST['booking_id'];
    $amount = $_POST['total_amount'];
    $issue = $_POST['issue_description'];
    $fixes = $_POST['fixes_applied'];
    $future = $_POST['future_lookouts'];
    
    $stmt = $pdo->prepare("INSERT INTO invoices (booking_id, total_amount, issue_description, fixes_applied, future_lookouts, status) VALUES (?, ?, ?, ?, ?, 'paid')");
    $stmt->execute([$b_id, $amount, $issue, $fixes, $future]);
    $invoice_id = $pdo->lastInsertId();
    
    // Update booking status to completed
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
    $stmt->execute([$b_id]);
    
    echo "<script>alert('Invoice created successfully!'); window.location.href='view_invoice.php?id=".$invoice_id."';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS / Generate Invoice - AutoPro Garage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Reception</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Bookings</a></li>
                    <li><a href="pos.php" class="active">Point of Sale / Invoicing</a></li>
                    <li><a href="inventory.php">Inventory & Direct Sale</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>Generate Final Invoice</h1>
            </div>

            <div class="card" style="max-width: 800px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Booking ID (Reference)</label>
                        <input type="number" name="booking_id" required value="<?php echo htmlspecialchars($booking_id); ?>" placeholder="Enter Booking ID">
                    </div>
                    
                    <div class="form-group">
                        <label>What was the primary issue?</label>
                        <textarea name="issue_description" rows="2" required placeholder="e.g. Engine making rattling noise"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>What fixes were applied? (Parts & Labor)</label>
                        <textarea name="fixes_applied" rows="4" required placeholder="e.g. Replaced timing belt, oil change..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Future Lookouts (Advise for customer)</label>
                        <textarea name="future_lookouts" rows="2" required placeholder="e.g. Brake pads will need replacement in 3 months."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Total Amount ($)</label>
                        <input type="number" step="0.01" name="total_amount" required placeholder="0.00">
                    </div>

                    <button type="submit" class="btn-primary" style="background: var(--success);">Generate Invoice & Receipt</button>
                </form>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
