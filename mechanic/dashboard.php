<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    header('Location: ../index.php');
    exit;
}

$mechanic_id = $_SESSION['user_id'];

// Fetch tasks assigned to this mechanic
$stmt = $pdo->prepare("
    SELECT t.*, b.vehicle_make, b.vehicle_model, b.vehicle_year, b.description as customer_issue 
    FROM tasks t 
    JOIN bookings b ON t.booking_id = b.id 
    WHERE t.mechanic_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$mechanic_id]);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mechanic Dashboard - AutoPro Garage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Mechanic</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">My Queue</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>My Task Queue</h1>
            </div>

            <div class="card">
                <h2>Tasks Assigned To You</h2>
                <div style="display: grid; gap: 1.5rem; margin-top: 1.5rem;">
                    <?php foreach($tasks as $t): ?>
                    <div style="border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; background: var(--bg-dark);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <h3 style="margin: 0; color: var(--text-light);"><?php echo htmlspecialchars($t['vehicle_make'] . ' ' . $t['vehicle_model'] . ' (' . $t['vehicle_year'] . ')'); ?></h3>
                            <span class="badge" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.5rem 1rem; border-radius: 4px;">Status: <?php echo ucfirst($t['status']); ?></span>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"><strong>Customer Issue:</strong> <?php echo htmlspecialchars($t['customer_issue']); ?></p>
                        <p style="color: var(--accent); font-size: 0.9rem; margin-bottom: 1.5rem;"><strong>Reception Instructions:</strong> <?php echo nl2br(htmlspecialchars($t['description'])); ?></p>
                        
                        <div style="display: flex; gap: 1rem; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                            <form method="POST" action="update_status.php" style="margin: 0; display:flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                                <select name="status" style="padding: 0.5rem; border-radius: 4px; background: var(--bg-card); color: var(--text-light); border: 1px solid var(--border-color);">
                                    <option value="pending" <?php echo $t['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in-progress" <?php echo $t['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $t['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem; width: auto; font-size: 0.9rem;">Update Status</button>
                            </form>

                            <a href="inspection.php?task_id=<?php echo $t['id']; ?>" class="btn-primary" style="padding: 0.5rem 1rem; text-decoration: none; background: transparent; border: 1px solid var(--primary); color: var(--primary);">Multi-Point Inspection (Photos)</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($tasks) === 0): ?>
                        <p style="color: var(--text-muted);">You have no tasks assigned right now.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
