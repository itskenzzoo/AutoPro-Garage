<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mechanic') {
    die("Unauthorized");
}

if (!isset($_GET['task_id'])) {
    header('Location: dashboard.php');
    exit;
}

$task_id = $_GET['task_id'];

// Get Task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND mechanic_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) die("Task not found or unauthorized.");

// Get previous uploads
$media_stmt = $pdo->prepare("SELECT * FROM media WHERE task_id = ? ORDER BY created_at DESC");
$media_stmt->execute([$task_id]);
$media_files = $media_stmt->fetchAll();

// Fetch inventory for mechanic to select
$inv_stmt = $pdo->query("SELECT * FROM inventory ORDER BY part_name ASC");
$inventory = $inv_stmt->fetchAll();

// Get logged parts for this task
$parts_stmt = $pdo->prepare("SELECT tp.quantity, tp.logged_at, i.part_name, i.part_number FROM task_parts tp JOIN inventory i ON tp.inventory_id = i.id WHERE tp.task_id = ?");
$parts_stmt->execute([$task_id]);
$logged_parts = $parts_stmt->fetchAll();

// Get checklist data
$checklist_stmt = $pdo->prepare("SELECT * FROM task_checklists WHERE task_id = ?");
$checklist_stmt->execute([$task_id]);
$saved_checklist = [];
while($row = $checklist_stmt->fetch()) {
    $saved_checklist[$row['item_name']] = $row;
}

$default_items = [
    'Engine Oil & Filter',
    'Brakes & Rotors',
    'Tire Tread & Pressure',
    'Battery & Cables',
    'Lights & Signals',
    'Fluid Levels (Coolant, Trans)'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multi-Point Inspection - AutoPro Garage</title>
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
                <h1>Multi-Point Inspection for Task #<?php echo htmlspecialchars($task_id); ?></h1>
                <a href="dashboard.php" style="color: var(--accent); text-decoration: none;">&larr; Back to Queue</a>
            </div>

            <div class="card" style="max-width: 800px;">
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Use this tool to capture condition photos (before/after), record part numbers, or document damage during your inspection.</p>
                
                <form action="process_inspection.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                    
                    <div class="form-group">
                        <label>Capture Image / Video (Use mobile camera)</label>
                        <input type="file" name="media[]" accept="image/*,video/*" multiple capture="environment" required>
                    </div>

                    <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem;">Upload Inspection Media</button>
                </form>

                <hr style="border:0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

                <h3>12-Point Vehicle Inspection Checklist</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Mark the condition of each component. This will be attached to the final customer report.</p>
                <form action="process_checklist.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                        <?php foreach($default_items as $item): 
                            $status = $saved_checklist[$item]['status'] ?? 'unchecked';
                            $notes = $saved_checklist[$item]['notes'] ?? '';
                        ?>
                        <div style="display: flex; gap: 1rem; align-items: center; background: var(--bg-dark); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                            <div style="flex: 2; font-weight: 500; color: var(--text-light);"><?php echo $item; ?></div>
                            <div style="flex: 1;">
                                <select name="items[<?php echo $item; ?>][status]" style="padding: 0.5rem; border-radius: 4px; width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-light);">
                                    <option value="unchecked" <?php echo $status=='unchecked'?'selected':''; ?>>Unchecked</option>
                                    <option value="pass" <?php echo $status=='pass'?'selected':''; ?>>✅ Pass</option>
                                    <option value="warning" <?php echo $status=='warning'?'selected':''; ?>>⚠️ Warning</option>
                                    <option value="fail" <?php echo $status=='fail'?'selected':''; ?>>❌ Fail</option>
                                </select>
                            </div>
                            <div style="flex: 2;">
                                <input type="text" name="items[<?php echo $item; ?>][notes]" value="<?php echo htmlspecialchars($notes); ?>" placeholder="Notes..." style="padding: 0.5rem; width: 100%; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-light);">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem;">Save Checklist</button>
                </form>

                <hr style="border:0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

                <h3>Record Parts Used</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">Select parts you've taken from the inventory for this task. This automatically updates the stock.</p>
                <form action="use_part.php" method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                    <div class="form-group" style="margin-bottom: 0; flex: 2;">
                        <label>Select Part</label>
                        <select name="inventory_id" required>
                            <option value="">-- Choose Part --</option>
                            <?php foreach($inventory as $item): ?>
                                <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['part_name']); ?> (Stock: <?php echo $item['quantity']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" required min="1" value="1">
                    </div>
                    <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 1.5rem; height: fit-content;">Log Part</button>
                </form>

                <hr style="border:0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

                <h3>Parts Assigned to This Car</h3>
                <div style="margin-top: 1rem;">
                    <?php if(count($logged_parts) > 0): ?>
                        <ul style="color: var(--text-light); margin-left: 1.5rem; line-height: 1.8;">
                            <?php foreach($logged_parts as $lp): ?>
                                <li><strong><?php echo $lp['quantity']; ?>x</strong> <?php echo htmlspecialchars($lp['part_name']); ?> <span style="color: var(--text-muted); font-size: 0.8rem;">(Logged: <?php echo date('M d, H:i', strtotime($lp['logged_at'])); ?>)</span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No parts have been assigned to this car yet.</p>
                    <?php endif; ?>
                </div>

                <hr style="border:0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

                <h3>Uploaded Inspection Files</h3>
                <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                    <?php foreach($media_files as $media): ?>
                        <div style="position: relative;">
                            <?php if($media['media_type'] == 'image'): ?>
                                <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                            <?php else: ?>
                                <video src="../<?php echo htmlspecialchars($media['file_path']); ?>" controls style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);"></video>
                            <?php endif; ?>
                            <span style="position:absolute; bottom:5px; left:5px; background:rgba(0,0,0,0.7); color:white; font-size:0.7rem; padding:2px 5px; border-radius:4px;"><?php echo date('M d, H:i', strtotime($media['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if(count($media_files) === 0): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No media uploaded for this task yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
