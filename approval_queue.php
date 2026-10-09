<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Staff')) {
    header("Location: index.php"); exit();
}

/** 1. HANDLE APPROVAL (Dispatch) **/
if (isset($_POST['approve_order'])) {
    $order_id = intval($_POST['order_id']);
    $rider_id = intval($_POST['rider_id']);

    // Update status and assign rider
    $conn->query("UPDATE order_records SET approval_status = 'Dispatched', assigned_rider_id = $rider_id WHERE id = $order_id");
    
    // Set Rider to Busy
    $conn->query("UPDATE delivery_riders SET current_status = 'Busy' WHERE id = $rider_id");

    logAction($conn, "Approved Order #$order_id");
    
    // IMPORTANT: Redirect to prevent duplicate "POST" on refresh
    header("Location: approval_queue.php?msg=dispatched");
    exit();
}

/** 2. HANDLE REJECTION (Delete Duplicates) **/
if (isset($_GET['reject_id'])) {
    $order_id = intval($_GET['reject_id']);
    
    // Optional: Delete the file from the server to save space
    $file_res = $conn->query("SELECT payment_proof FROM order_records WHERE id = $order_id");
    $file_data = $file_res->fetch_assoc();
    if($file_data && $file_data['payment_proof'] != 'no_receipt.png') {
        @unlink("uploads/payments/" . $file_data['payment_proof']);
    }

    $conn->query("DELETE FROM order_records WHERE id = $order_id");
    
    logAction($conn, "Rejected/Deleted Order #$order_id");
    
    header("Location: approval_queue.php?msg=deleted");
    exit();
}

$pending_orders = $conn->query("SELECT * FROM order_records WHERE approval_status = 'Pending' ORDER BY id DESC");
$available_riders = $conn->query("SELECT * FROM delivery_riders WHERE current_status = 'Available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approval Queue | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --danger: #ef4444; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .order-card { background: white; padding: 25px; border-radius: 24px; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; border-left: 6px solid #f1c40f; }
        .receipt-link { color: #3498db; text-decoration: none; font-weight: 700; font-size: 13px; display: inline-block; margin-top: 10px; }
        .rider-select { padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; margin-right: 10px; font-family: inherit; }
        .btn-dispatch { background: var(--accent); color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-dispatch:hover { transform: scale(1.02); background: #27ae60; }
        
        /* New Reject Button Style */
        .btn-reject { 
            background: #fef2f2; color: var(--danger); text-decoration: none; 
            padding: 12px 18px; border-radius: 12px; font-weight: 800; 
            font-size: 13px; transition: 0.3s; margin-left: 10px;
            border: 1px solid #fee2e2;
        }
        .btn-reject:hover { background: var(--danger); color: white; }
    </style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="letter-spacing: -1px;">✅ Pending Approvals</h1>
        <a href="staff_dashboard.php" style="text-decoration:none; font-weight:700; color:var(--primary); background:white; padding:10px 20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">← Back to Hub</a>
    </div>

    <?php if($pending_orders->num_rows > 0): ?>
        <?php while($order = $pending_orders->fetch_assoc()): ?>
            <div class="order-card">
                <div>
                    <h3 style="margin:0;">Order #<?php echo $order['id']; ?> - <?php echo $order['cylinder_type']; ?>kg</h3>
                    <p style="margin:5px 0; color:#64748b;">Customer: <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                    
                    <a href="uploads/payments/<?php echo $order['payment_proof']; ?>" target="_blank" class="receipt-link">🖼️ View Payment Receipt</a>
                </div>

                <div style="display:flex; align-items:center;">
                    <form method="POST" style="display:flex; align-items:center;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="rider_id" class="rider-select" required>
                            <option value="">Select Rider</option>
                            <?php 
                            $available_riders->data_seek(0);
                            while($rider = $available_riders->fetch_assoc()) {
                                echo "<option value='".$rider['id']."'>".$rider['rider_name']."</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="approve_order" class="btn-dispatch">Dispatch</button>
                    </form>

                    <a href="?reject_id=<?php echo $order['id']; ?>" 
                       class="btn-reject" 
                       onclick="return confirm('Reject and delete this order? This cannot be undone.')">
                       Reject
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align:center; padding:100px; background:white; border-radius:30px; border: 2px dashed #e2e8f0;">
            <p style="color:#94a3b8; font-weight: 600;">No pending orders to verify right now.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>