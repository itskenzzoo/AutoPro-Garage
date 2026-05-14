<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Analytics Queries
$total_revenue_stmt = $pdo->query("SELECT SUM(total_amount) as total FROM invoices WHERE status = 'paid'");
$total_revenue = $total_revenue_stmt->fetch()['total'] ?? 0;

$pending_revenue_stmt = $pdo->query("SELECT SUM(total_amount) as total FROM invoices WHERE status = 'unpaid'");
$pending_revenue = $pending_revenue_stmt->fetch()['total'] ?? 0;

$tasks_completed_stmt = $pdo->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'");
$tasks_completed = $tasks_completed_stmt->fetch()['count'] ?? 0;

$recent_invoices_stmt = $pdo->query("SELECT i.*, b.vehicle_make, u.name as customer_name FROM invoices i JOIN bookings b ON i.booking_id = b.id JOIN users u ON b.customer_id = u.id ORDER BY i.created_at DESC LIMIT 5");
$recent_invoices = $recent_invoices_stmt->fetchAll();

// Low stock count
$low_stock_stmt = $pdo->query("SELECT COUNT(*) as low_count FROM inventory WHERE quantity < 5");
$low_stock = $low_stock_stmt->fetch()['low_count'] ?? 0;

// Top selling parts
$top_parts_stmt = $pdo->query("SELECT i.part_name, SUM(ii.quantity) as sold_qty FROM invoice_items ii JOIN inventory i ON ii.inventory_id = i.id GROUP BY i.id ORDER BY sold_qty DESC LIMIT 4");
$top_parts = $top_parts_stmt->fetchAll();

// 7-Day Income Analytics
$last_7_days = [];
for($i=6; $i>=0; $i--){
    $last_7_days[date('Y-m-d', strtotime("-$i days"))] = 0;
}
$income_chart_stmt = $pdo->query("SELECT DATE(created_at) as date, SUM(total_amount) as daily_total FROM invoices WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at)");
$daily_income = $income_chart_stmt->fetchAll();
foreach($daily_income as $row) {
    if(isset($last_7_days[$row['date']])) {
        $last_7_days[$row['date']] = (float)$row['daily_total'];
    }
}
$chart_labels = json_encode(array_map(function($d){ return date('M d', strtotime($d)); }, array_keys($last_7_days)));
$chart_data = json_encode(array_values($last_7_days));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Pro Web - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --brand-blue: #0A4b78;
            --brand-light: #f4f7f9;
            --card-bg: #ffffff;
            --text-dark: #333333;
            --text-muted: #666666;
            --border-light: #e0e0e0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }
        body { background-color: var(--brand-light); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--brand-blue); color: #fff; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px; }
        .nav-menu { flex: 1; padding: 10px 0; }
        .nav-link { padding: 15px 20px; display: flex; align-items: center; gap: 15px; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; border-left: 4px solid #38bdf8; }
        
        /* Main Layout */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Top Navbar */
        .topbar { height: 60px; background: var(--card-bg); border-bottom: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .search-bar { background: var(--brand-light); border: 1px solid var(--border-light); padding: 8px 15px; border-radius: 20px; width: 300px; outline: none; }
        .user-profile { display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .avatar { width: 35px; height: 35px; background: var(--brand-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        
        /* Content */
        .content { flex: 1; overflow-y: auto; padding: 30px; }
        .page-title { font-size: 1.5rem; margin-bottom: 20px; color: var(--text-dark); }
        
        /* Widgets */
        .widget-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .widget { background: var(--card-bg); border-radius: 8px; padding: 20px; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .widget-title { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 10px; }
        .widget-value { font-size: 1.8rem; font-weight: 700; color: var(--brand-blue); }
        .widget-icon { align-self: flex-end; font-size: 2rem; opacity: 0.2; margin-top: -30px; }
        
        /* Panels */
        .panel-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;}
        .panel { background: var(--card-bg); border-radius: 8px; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .panel-header { padding: 15px 20px; border-bottom: 1px solid var(--border-light); background: #f8fafc; font-weight: 600; color: var(--text-dark); display:flex; justify-content: space-between;}
        .panel-body { padding: 20px; }
        
        /* Tables */
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-light); font-size: 0.9rem; }
        .table th { color: var(--text-muted); font-weight: 500; background: #fafafa; }
        .table tr:hover { background: #f9f9f9; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.unpaid { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            ⚙️ AutoPro Admin
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="../reception/dashboard.php" class="nav-link">📝 Work Orders</a>
            <a href="inventory.php" class="nav-link">📦 Inventory</a>
            <a href="#" class="nav-link">🚚 Fleet / Assets</a>
            <a href="#" class="nav-link">📈 Reports</a>
            <a href="#" class="nav-link">⚙️ Settings</a>
        </div>
        <div style="padding: 20px;">
            <button id="themeToggleBtn" class="theme-toggle-btn" style="margin-bottom: 20px; background:transparent; border:1px solid rgba(255,255,255,0.2); color:#cbd5e1;">🌙 Dark Mode</button>
            <a href="../logout.php" style="color: #cbd5e1; text-decoration: none; font-size: 0.9rem;">🚪 Sign Out</a>
        </div>
    </div>
    
    <div class="main-wrapper">
        <div class="topbar">
            <input type="text" class="search-bar" placeholder="Search equipment, work orders...">
            <div class="user-profile">
                <span>Admin User</span>
                <div class="avatar">A</div>
            </div>
        </div>
        
        <div class="content">
            <h1 class="page-title">Financial & Analytics Overview</h1>
            
            <div class="widget-grid">
                <div class="widget">
                    <span class="widget-title">Total Revenue (Paid)</span>
                    <span class="widget-value">$<?php echo number_format($total_revenue, 2); ?></span>
                    <div class="widget-icon">💵</div>
                </div>
                <div class="widget">
                    <span class="widget-title">Pending Payments</span>
                    <span class="widget-value" style="color: #dc2626;">$<?php echo number_format($pending_revenue, 2); ?></span>
                    <div class="widget-icon">⏳</div>
                </div>
                <div class="widget">
                    <span class="widget-title">Completed Work Orders</span>
                    <span class="widget-value"><?php echo $tasks_completed; ?></span>
                    <div class="widget-icon">✅</div>
                </div>
                <div class="widget">
                    <span class="widget-title">Inventory Alerts</span>
                    <span class="widget-value" style="color: #ea580c;"><?php echo $low_stock; ?> Items</span>
                    <div class="widget-icon">⚠️</div>
                </div>
            </div>
            
            <!-- Income Trend Chart Panel -->
            <div class="panel" style="margin-bottom: 20px;">
                <div class="panel-header">7-Day Income Analytics</div>
                <div class="panel-body">
                    <canvas id="incomeChart" height="80"></canvas>
                </div>
            </div>

            <div class="panel-grid">
                <div class="panel">
                    <div class="panel-header">
                        Recent Work Order Invoices
                        <a href="../reception/dashboard.php" style="font-size:0.8rem; text-decoration:none; color:var(--brand-blue);">View All</a>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>WO #</th>
                                    <th>Asset / Vehicle</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_invoices as $inv): ?>
                                <tr>
                                    <td><a href="../reception/view_invoice.php?id=<?php echo $inv['id']; ?>" style="color: var(--brand-blue); text-decoration: none; font-weight: 500;">WO-<?php echo str_pad($inv['id'], 5, '0', STR_PAD_LEFT); ?></a></td>
                                    <td><?php echo htmlspecialchars($inv['vehicle_make']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                                    <td>$<?php echo number_format($inv['total_amount'], 2); ?></td>
                                    <td><span class="badge <?php echo $inv['status']; ?>"><?php echo strtoupper($inv['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(count($recent_invoices) === 0): ?>
                                <tr><td colspan="5" style="text-align: center;">No recent invoices found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-header">Top Selling Parts</div>
                    <div class="panel-body">
                        <?php if(count($top_parts) > 0): ?>
                            <ul style="list-style: none;">
                                <?php foreach($top_parts as $tp): ?>
                                    <li style="margin-bottom: 15px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 5px;">
                                        <span style="font-weight: 500; color: var(--text-dark);"><?php echo htmlspecialchars($tp['part_name']); ?></span>
                                        <span style="color: var(--brand-blue); font-weight: bold;"><?php echo $tp['sold_qty']; ?> sold</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--text-muted); margin-top: 20px;">No parts sold yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Income Line Chart
        const incomeCtx = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(incomeCtx, {
            type: 'line',
            data: {
                labels: <?php echo $chart_labels; ?>,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: <?php echo $chart_data; ?>,
                    borderColor: '#0A4b78',
                    backgroundColor: 'rgba(10, 75, 120, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0A4b78'
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e0e0e0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
