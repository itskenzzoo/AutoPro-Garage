<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM inventory ORDER BY part_name ASC");
$inventory = $stmt->fetchAll();

$c_stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name ASC");
$customers = $c_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory & Sales - AutoPro Reception</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; color: var(--text-light); }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--bg-dark); }
        .low-stock { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Reception</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Bookings</a></li>
                    <li><a href="pos.php">Point of Sale / Invoicing</a></li>
                    <li><a href="inventory.php" class="active">Inventory & Direct Sale</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>Inventory & Direct Sales</h1>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
                <div class="card">
                    <h2>Current Stock Levels</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Part Name</th>
                                <th>In Stock</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['part_name']); ?> <br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($item['part_number']); ?></small></td>
                                <td class="<?php echo $item['quantity'] < 5 ? 'low-stock' : ''; ?>">
                                    <?php echo $item['quantity']; ?> 
                                    <?php if($item['quantity'] < 5) echo '<small style="color:var(--danger); margin-left: 0.5rem;">(Low!)</small>'; ?>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($inventory) === 0): ?>
                            <tr><td colspan="3" style="text-align: center; padding: 2rem;">No parts in inventory.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card" style="height: fit-content;">
                    <h2>Process Direct Sale</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">Sell parts directly to customers. This will generate a receipt synced to their account.</p>
                    <form action="sell_part.php" method="POST">
                        <div class="form-group">
                            <label>Select Customer</label>
                            <select name="customer_id" required>
                                <option value="">-- Choose Customer --</option>
                                <?php foreach($customers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (' . $c['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Part</label>
                            <select name="inventory_id" required>
                                <option value="">-- Choose Part --</option>
                                <?php foreach($inventory as $item): ?>
                                    <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['part_name']); ?> ($<?php echo number_format($item['price'], 2); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity to Sell</label>
                            <input type="number" name="quantity" required min="1" value="1">
                        </div>
                        <button type="submit" class="btn-primary" style="background: var(--success);">Complete Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
