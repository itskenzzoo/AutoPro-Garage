<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard - AutoPro Garage</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body style="align-items: flex-start;">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h2>AutoPro Garage</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Book a Service</a></li>
                    <li><a href="my_bookings.php">My Bookings</a></li>
                    <li><button id="themeToggleBtn" class="theme-toggle-btn" style="margin-top: 20px;">🌙 Dark Mode</button></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header-top">
                <h1>Welcome to AutoPro Garage, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            </div>

            <div class="card">
                <h2>Book a Consultation</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Describe your vehicle's issue. Please upload photos or videos so we can determine if we should send a mobile mechanic or if you need to bring it in.</p>
                
                <form action="process_booking.php" method="POST" enctype="multipart/form-data">
                    <div style="display:flex; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group" style="flex:1; margin-bottom:0;">
                            <label>Vehicle Make</label>
                            <input type="text" name="make" required placeholder="e.g. Toyota">
                        </div>
                        <div class="form-group" style="flex:1; margin-bottom:0;">
                            <label>Vehicle Model</label>
                            <input type="text" name="model" required placeholder="e.g. Camry">
                        </div>
                        <div class="form-group" style="flex:1; margin-bottom:0;">
                            <label>Year</label>
                            <input type="number" name="year" required placeholder="2020">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description of Issue</label>
                        <textarea name="description" rows="4" required placeholder="What seems to be the problem? Hear a weird noise?"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Photos/Videos of Damage</label>
                        <p style="font-size: 0.8rem; color: var(--accent); cursor: pointer; margin-bottom: 0.5rem;" onclick="document.getElementById('photo-modal').classList.add('active')">
                            ℹ️ Click here to see how to take a good damage photo
                        </p>
                        <!-- accept image and video, enable camera capture on mobile -->
                        <input type="file" name="media[]" accept="image/*,video/*" multiple capture="environment">
                    </div>

                    <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem;">Submit Booking Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Example Modal -->
    <div class="modal" id="photo-modal">
        <div class="modal-content">
            <h3>How to take a damage photo</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">Stand about 5 feet away and ensure the area is well lit. Take one wide shot, and one close up.</p>
            <!-- A placeholder gradient to represent an example image -->
            <div style="width:100%; height: 200px; background: linear-gradient(45deg, #1e293b, #38bdf8); border-radius: 8px; margin: 1rem 0; display:flex; align-items:center; justify-content:center; color: white; font-weight: 600;">Example Wide Shot (Visual Guide)</div>
            <button class="close-modal" onclick="document.getElementById('photo-modal').classList.remove('active')">Got it, close</button>
        </div>
    </div>
<script src="/AutoPro Garage/assets/js/theme.js"></script>
</body>
</html>
