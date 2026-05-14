<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'admin')) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inventory_id = $_POST['inventory_id'];
    $quantity = (int)$_POST['quantity'];
    $customer_id = $_POST['customer_id'];
    
    // Check current quantity & price
    $stmt = $pdo->prepare("SELECT quantity, price, part_name FROM inventory WHERE id = ?");
    $stmt->execute([$inventory_id]);
    $item = $stmt->fetch();
    
    if ($item && $item['quantity'] >= $quantity) {
        $pdo->beginTransaction();
        
        try {
            // Deduct
            $update = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
            $update->execute([$quantity, $inventory_id]);
            
            // Create "Walk-in" booking
            $b_stmt = $pdo->prepare("INSERT INTO bookings (customer_id, vehicle_make, vehicle_model, description, service_type, status) VALUES (?, 'Direct', 'Sale', 'Over-the-counter part sale', 'in-house', 'completed')");
            $b_stmt->execute([$customer_id]);
            $booking_id = $pdo->lastInsertId();
            
            // Create Invoice
            $total = $item['price'] * $quantity;
            $i_stmt = $pdo->prepare("INSERT INTO invoices (booking_id, total_amount, issue_description, fixes_applied, future_lookouts, status) VALUES (?, ?, ?, ?, ?, 'paid')");
            $desc = "Purchased Part: " . $item['part_name'] . " (x" . $quantity . ")";
            $i_stmt->execute([$booking_id, $total, "Direct Sale", $desc, "None", 'paid']);
            $invoice_id = $pdo->lastInsertId();
            
            // Create Invoice Item
            $ii_stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, inventory_id, quantity, price) VALUES (?, ?, ?, ?)");
            $ii_stmt->execute([$invoice_id, $inventory_id, $quantity, $item['price']]);
            
            $pdo->commit();
            echo "<script>alert('Sale successful! Invoice generated.'); window.location.href='view_invoice.php?id=".$invoice_id."';</script>";
        } catch (\Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Error processing sale.'); window.location.href='inventory.php';</script>";
        }
    } else {
        echo "<script>alert('Error: Not enough stock available!'); window.location.href='inventory.php';</script>";
    }
}
