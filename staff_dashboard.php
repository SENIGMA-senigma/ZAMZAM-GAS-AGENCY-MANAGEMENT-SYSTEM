<?php
session_start();
require_once 'config.php';

// RBAC: Secure the door for Staff/Clerk only
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Staff' && $_SESSION['user_role'] !== 'Manager')) {
    header("Location: index.php");
    exit();
}

/** 1. FETCH LIVE TASK COUNTS **/
// Orders waiting for payment verification
$pending_res = $conn->query("SELECT COUNT(*) as count FROM order_records WHERE approval_status = 'Pending'");
$pending_count = ($pending_res) ? $pending_res->fetch_assoc()['count'] : 0;

// Riders currently available for work in Nairobi
$rider_res = $conn->query("SELECT COUNT(*) as count FROM delivery_riders WHERE current_status = 'Available'");
$available_riders = ($rider_res) ? $rider_res->fetch_assoc()['count'] : 0;

// Orders currently on the road
$transit_res = $conn->query("SELECT COUNT(*) as count FROM order_records WHERE approval_status = 'Dispatched'");
$in_transit = ($transit_res) ? $transit_res->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Portal | Zamzam Gas Agency</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #34495e; --accent: #2ecc71; --warning: #f39c12; 
            --danger: #e74c3c; --bg: #f4f7f6; --white: #ffffff; 
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: var(--primary); color: white; position: fixed; padding: 30px; box-sizing: border-box; }
        .sidebar .brand { font-weight: 800; font-size: 20px; margin-bottom: 40px; }
        .sidebar .brand span { color: var(--accent); }
        .nav-link { display: block; color: #bdc3c7; text-decoration: none; padding: 12px; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }

        /* Main Area */
        .main { margin-left: 260px; padding: 40px; width: 100%; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        
        .task-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .task-card { background: var(--white); padding: 30px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-decoration: none; color: inherit; transition: 0.3s; border-top: 5px solid #eee; }
        .task-card:hover { transform: translateY(-5px); }
        
        .val { font-size: 36px; font-weight: 800; display: block; margin: 10px 0; }
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #eee; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">Zamzam <span>Staff</span></div>
        <nav>
            <a href="staff_dashboard.php" class="nav-link active">🚀 Operations Room</a>
            <a href="approval_queue.php" class="nav-link">📝 Pending Approvals</a>
            <a href="dispatch_tracker.php" class="nav-link">📍 Live Map</a>
            <a href="inventory.php" class="nav-link">📦 Stock Check</a>
            <a href="logout.php" class="nav-link" style="margin-top: 50px; color: var(--danger);">Logout</a>
        </nav>
    </div>

    <div class="main">
        <div class="header-bar">
            <div>
                <h1 style="margin:0; letter-spacing: -1px;">Operations Room</h1>
                <p style="color: #7f8c8d; margin: 5px 0 0;">Welcome back, <?php echo $_SESSION['username'] ?? 'Staff'; ?></p>
            </div>
            <div style="text-align: right;">
                <span class="status-pill" style="background: #e8f5e9; color: #2ecc71;">● System Online</span>
            </div>
        </div>

        <div class="task-grid">
            <a href="approval_queue.php" class="task-card" style="border-top-color: var(--warning);">
                <small style="color: #7f8c8d; text-transform: uppercase; font-weight: 700;">Action Required</small>
                <span class="val" style="color: var(--warning);"><?php echo $pending_count; ?></span>
                <strong>Orders Pending Approval</strong>
                <p style="font-size: 13px; color: #95a5a6;">Review receipts and dispatch to riders.</p>
            </a>

            <a href="rider_management.php" class="task-card" style="border-top-color: var(--accent);">
                <small style="color: #7f8c8d; text-transform: uppercase; font-weight: 700;">Fleet Status</small>
                <span class="val" style="color: var(--accent);"><?php echo $available_riders; ?></span>
                <strong>Riders Available</strong>
                <p style="font-size: 13px; color: #95a5a6;">Ready to take on new Nairobi deliveries.</p>
            </a>

            <a href="dispatch_tracker.php" class="task-card" style="border-top-color: var(--primary);">
                <small style="color: #7f8c8d; text-transform: uppercase; font-weight: 700;">Real-time Monitoring</small>
                <span class="val" style="color: var(--primary);"><?php echo $in_transit; ?></span>
                <strong>Cylinders in Transit</strong>
                <p style="font-size: 13px; color: #95a5a6;">Track active deliveries to customers.</p>
            </a>
        </div>

        <div style="margin-top: 40px; padding: 25px; background: #fff; border-radius: 20px; border: 1px dashed #cbd5e0; text-align: center;">
            <p style="color: #718096; font-size: 14px; margin: 0;">
                Need to add a new customer order manually? <a href="place_order_admin.php" style="color: var(--accent); font-weight: bold;">Click here to create order.</a>
            </p>
        </div>
    </div>
</body>
</html>