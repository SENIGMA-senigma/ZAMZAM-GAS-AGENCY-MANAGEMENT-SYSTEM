<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

/** 1. IDENTITY CHECK (Fixes the Warning) **/
// Check if session exists; if not, set as empty string for Guests
$is_logged_in = isset($_SESSION['user_role']);
$current_user_name = isset($_SESSION['username']) ? $_SESSION['username'] : '';

/** 2. FETCH CYLINDER DETAILS (If passed via URL) **/
$selected_cylinder = null;
if (isset($_GET['cylinder_id'])) {
    $c_id = intval($_GET['cylinder_id']);
    $res = $conn->query("SELECT * FROM cylinder_inventory WHERE id = $c_id");
    if ($res && $res->num_rows > 0) {
        $selected_cylinder = $res->fetch_assoc();
    }
}

/** 3. HANDLE ORDER SUBMISSION **/
if (isset($_POST['submit_order'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $c_type = mysqli_real_escape_string($conn, $_POST['cylinder_type']);
    $amount = floatval($_POST['total_price']);
    
    // Duplicate Prevention (1 Minute Interval)
    $check_dup = $conn->query("SELECT id FROM order_records 
                               WHERE customer_phone = '$c_phone' 
                               AND total_amount = $amount 
                               AND order_date > NOW() - INTERVAL 1 MINUTE");

    if ($check_dup->num_rows > 0) {
        echo "<script>alert('Duplicate Order! You already sent this request.'); window.parent.location.reload();</script>";
        exit();
    }

    // Handle Payment Proof
    $payment_proof = "no_receipt.png";
    if (!empty($_FILES['receipt']['name'])) {
        $target_dir = "uploads/payments/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $payment_proof = "PAY_" . time() . "_" . uniqid() . "." . $file_ext;
        move_uploaded_file($_FILES['receipt']['tmp_name'], $target_dir . $payment_proof);
    }

    $stmt = $conn->prepare("INSERT INTO order_records (customer_name, customer_phone, cylinder_type, total_amount, payment_proof, approval_status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sssis", $c_name, $c_phone, $c_type, $amount, $payment_proof);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Order Placed Successfully!');
            if (window.parent && window.parent !== window) {
                window.parent.location.reload(); 
            } else {
                window.location.href = 'index.php';
            }
        </script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: white; padding: 20px; color: #0f172a; margin: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: #64748b; }
        input, select { width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        .price-badge { background: #f0fdf4; color: #16a34a; padding: 15px; border-radius: 12px; font-weight: 800; text-align: center; margin-bottom: 20px; border: 1px solid #dcfce7; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 16px; width: 100%; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .guest-note { background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 10px; border-radius: 10px; font-size: 12px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php if(!$is_logged_in): ?>
        <div class="guest-note">💡 <strong>Guest Mode:</strong> You can order without an account.</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if($selected_cylinder): ?>
            <div class="price-badge">
                <?php echo $selected_cylinder['cylinder_size']; ?>kg Refill — KES <?php echo number_format($selected_cylinder['unit_price']); ?>
            </div>
            <input type="hidden" name="cylinder_type" value="<?php echo $selected_cylinder['cylinder_size']; ?>">
            <input type="hidden" name="total_price" value="<?php echo $selected_cylinder['unit_price']; ?>">
        <?php else: ?>
            <div class="form-group">
                <label>Select Gas Size</label>
                <select name="cylinder_type" id="cyl_select" required>
                    <option value="6" data-price="1200">6kg Refill (KES 1,200)</option>
                    <option value="13" data-price="2500">13kg Refill (KES 2,500)</option>
                </select>
                <input type="hidden" name="total_price" value="1200">
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Full Delivery Name</label>
            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($current_user_name); ?>" placeholder="Enter name" required>
        </div>

        <div class="form-group">
            <label>Phone Number (M-Pesa Number)</label>
            <input type="text" name="phone" placeholder="e.g. 07..." required>
        </div>

        <div class="form-group">
            <label>M-Pesa Payment Proof</label>
            <input type="file" name="receipt" accept="image/*" required>
        </div>

        <button type="submit" name="submit_order" class="btn-submit">Confirm & Place Order</button>
    </form>
</body>
</html>