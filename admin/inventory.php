<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM inventory ORDER BY part_name ASC");
$inventory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - AutoPro Garage</title>
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
            <h2>AutoPro Admin</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Overview & Analytics</a></li>
                    <li><a href="inventory.php" class="active">Inventory Management</a></li>
                    <li><a href="../reception/dashboard.php">Reception Terminal</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>Inventory & Parts</h1>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
                <div class="card">
                    <h2>Stock Levels</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Part Number</th>
                                <th>Part Name</th>
                                <th>Quantity in Stock</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['part_number']); ?></td>
                                <td><?php echo htmlspecialchars($item['part_name']); ?></td>
                                <td class="<?php echo $item['quantity'] < 5 ? 'low-stock' : ''; ?>">
                                    <?php echo $item['quantity']; ?> 
                                    <?php if($item['quantity'] < 5) echo '<small style="color:var(--danger); margin-left: 0.5rem;">(Low!)</small>'; ?>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($inventory) === 0): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 2rem;">No parts in inventory.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card" style="height: fit-content;">
                    <h2>Add New Part</h2>
                    <form action="add_inventory.php" method="POST">
                        <div class="form-group">
                            <label>Part Number</label>
                            <input type="text" name="part_number" required placeholder="e.g. BRP-204">
                        </div>
                        <div class="form-group">
                            <label>Part Name</label>
                            <input type="text" name="part_name" required placeholder="e.g. Ceramic Brake Pads">
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" required placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Unit Price ($)</label>
                            <input type="number" step="0.01" name="price" required placeholder="0.00">
                        </div>
                        <button type="submit" class="btn-primary">Add to Inventory</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
