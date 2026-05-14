<?php
session_start();
require_once '../includes/db.php';
if (!isset($_GET['id'])) die("Invoice not found.");
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT i.*, b.vehicle_make, b.vehicle_model, u.name as customer_name, u.phone 
                       FROM invoices i 
                       JOIN bookings b ON i.booking_id = b.id 
                       JOIN users u ON b.customer_id = u.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if (!$inv) die("Invoice not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $inv['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; color: #0f172a; padding: 2rem; margin: 0; }
        .receipt { max-width: 800px; margin: 0 auto; background: white; padding: 3rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .receipt-header { text-align: center; margin-bottom: 3rem; }
        .receipt-header h1 { font-size: 2.5rem; margin: 0; color: #2563eb; }
        .receipt-header p { margin: 0.5rem 0; color: #64748b; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 8px; }
        .section { margin-bottom: 2rem; }
        .section h3 { border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1rem; color: #334155; }
        .section p { color: #475569; line-height: 1.6; }
        .total-box { margin-top: 3rem; padding: 1.5rem; background: #eff6ff; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .total-box h2 { margin: 0; color: #1e3a8a; }
        .total-amount { font-size: 2rem; font-weight: bold; color: #2563eb; }
        
        @media print { 
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none; } 
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>AutoPro Garage</h1>
            <p>123 Mechanic Lane, Auto City, AC 12345</p>
            <p style="font-weight: 600; margin-top: 1rem;">Official Invoice / Receipt</p>
            <p>Invoice #INV-<?php echo str_pad($inv['id'], 5, '0', STR_PAD_LEFT); ?> | Date: <?php echo date('F d, Y', strtotime($inv['created_at'])); ?></p>
        </div>
        
        <div class="details-grid">
            <div>
                <h4 style="margin-top:0; color:#94a3b8; text-transform:uppercase; font-size:0.8rem;">Billed To</h4>
                <strong><?php echo htmlspecialchars($inv['customer_name']); ?></strong><br>
                <?php echo htmlspecialchars($inv['phone']); ?>
            </div>
            <div style="text-align: right;">
                <h4 style="margin-top:0; color:#94a3b8; text-transform:uppercase; font-size:0.8rem;">Vehicle Details</h4>
                <strong><?php echo htmlspecialchars($inv['vehicle_make'] . ' ' . $inv['vehicle_model']); ?></strong>
            </div>
        </div>
        
        <div class="section">
            <h3>Diagnostic & Primary Issue</h3>
            <p><?php echo nl2br(htmlspecialchars($inv['issue_description'])); ?></p>
        </div>
        
        <div class="section">
            <h3>Fixes Applied (Parts & Labor)</h3>
            <p><?php echo nl2br(htmlspecialchars($inv['fixes_applied'])); ?></p>
        </div>
        
        <div class="section">
            <h3 style="color: #ea580c; border-bottom-color: #ffedd5;">⚠️ Future Lookouts</h3>
            <p style="background: #fff7ed; padding: 1rem; border-radius: 8px; color: #c2410c;">
                <?php echo nl2br(htmlspecialchars($inv['future_lookouts'])); ?>
            </p>
        </div>
        
        <div class="total-box">
            <h2>Total Amount Paid</h2>
            <div class="total-amount">$<?php echo number_format($inv['total_amount'], 2); ?></div>
        </div>
        
        <div class="no-print" style="margin-top: 3rem; text-align: center;">
            <button onclick="window.print()" style="padding: 0.75rem 2rem; background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 8px; font-weight: 600; font-size: 1rem;">Print Receipt</button>
            <br><br>
            <a href="javascript:history.back()" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">&larr; Go Back</a>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
