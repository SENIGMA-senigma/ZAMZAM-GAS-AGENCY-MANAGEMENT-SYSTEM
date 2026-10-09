<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Staff')) {
    header("Location: index.php");
    exit();
}

/** HANDLE DELIVERY COMPLETION & LOYALTY AWARD **/
if (isset($_GET['complete_id'])) {
    $order_id = intval($_GET['complete_id']);
    $rider_id = intval($_GET['rider_id']);
    
    // 1. Fetch order details to identify customer and amount
    $order_info = $conn->query("SELECT customer_phone, customer_name, total_amount FROM order_records WHERE id = $order_id")->fetch_assoc();
    $phone = $order_info['customer_phone'];
    $c_name = $order_info['customer_name'];
    $amount = $order_info['total_amount'];

    // 2. Calculate points (1 point per 100 KES)
    $points_to_award = floor($amount / 100);

    // 3. Mark order as Delivered
    $conn->query("UPDATE order_records SET approval_status = 'Delivered' WHERE id = $order_id");
    
    // 4. Update Customer Wallet (Award Points)
    $conn->query("UPDATE customers SET loyalty_points = loyalty_points + $points_to_award WHERE customer_phone = '$phone'");

    // 5. Send automated Notification (FIXED: Escaping the apostrophe)
    $raw_msg = "Confirmed! Your gas was delivered. You've earned $points_to_award loyalty points!";
    $notif_msg = mysqli_real_escape_string($conn, $raw_msg);
    
    $conn->query("INSERT INTO customer_notifications (customer_phone, message) VALUES ('$phone', '$notif_msg')");

    // 6. Set rider back to Available
    $conn->query("UPDATE delivery_riders SET current_status = 'Available' WHERE id = $rider_id");

    // 7. Log the action for the Security Audit
    logAction($conn, "Delivery Completed: Order #$order_id for $c_name. Awarded $points_to_award pts.");
    
    header("Location: dispatch_tracker.php?success=delivered");
    exit();
}

$active_sql = "SELECT o.*, r.rider_name, r.phone_number as rider_phone 
               FROM order_records o 
               JOIN delivery_riders r ON o.assigned_rider_id = r.id 
               WHERE o.approval_status = 'Dispatched' 
               ORDER BY o.order_date DESC";
$active_result = $conn->query($active_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Tracker | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --dispatch: #e67e22; --bg: #f8fafc; --accent: #2ecc71; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); padding: 40px; color: var(--primary); }
        .container { max-width: 900px; margin: 0 auto; }
        .track-card { background: #fff; padding: 25px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border-left: 6px solid var(--dispatch); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .track-card:hover { transform: translateY(-5px); }
        .btn-done { background: var(--accent); color: white; text-decoration: none; padding: 14px 28px; border-radius: 14px; font-weight: 800; font-size: 13px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-done:hover { background: #27ae60; box-shadow: 0 10px 20px rgba(46, 204, 113, 0.2); }
        .back-link { text-decoration: none; font-weight: 700; color: var(--primary); background: white; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px;">
        <h1 style="letter-spacing: -1.5px; margin:0;">🚚 Live Dispatch Tracker</h1>
        <a href="staff_dashboard.php" class="back-link">← Back to Hub</a>
    </div>

    <?php if ($active_result->num_rows > 0): ?>
        <?php while($row = $active_result->fetch_assoc()): ?>
            <div class="track-card">
                <div>
                    <h3 style="margin:0; letter-spacing:-0.5px;">Order #<?php echo $row['id']; ?></h3>
                    <p style="color:#64748b; margin:8px 0; font-weight: 600;">👤 <?php echo htmlspecialchars($row['customer_name']); ?> (<?php echo $row['customer_phone']; ?>)</p>
                    <p style="margin:0; font-size:14px; color: #1e293b;">🏍️ Assigned Rider: <strong><?php echo htmlspecialchars($row['rider_name']); ?></strong></p>
                </div>
                <a href="?complete_id=<?php echo $row['id']; ?>&rider_id=<?php echo $row['assigned_rider_id']; ?>" 
                   onclick="return confirm('Confirm delivery completion? Points will be awarded to customer.')" class="btn-done">Mark Delivered</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align:center; padding:100px; background:white; border-radius:30px; border: 2px dashed #e2e8f0;">
            <p style="color:#94a3b8; font-weight: 600; font-size: 18px;">All caught up! No active deliveries.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>