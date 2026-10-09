<?php
session_start();
require_once 'config.php';

// RBAC: Manager & Staff only
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Staff')) {
    header("Location: index.php");
    exit();
}

/** HANDLE MANUAL ORDER SUBMISSION **/
if (isset($_POST['create_order'])) {
    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $cust_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $inventory_id = intval($_POST['inventory_id']);

    // Fetch price and size for the selected cylinder
    $inv_res = $conn->query("SELECT * FROM cylinder_inventory WHERE id = $inventory_id");
    $inv_data = $inv_res->fetch_assoc();
    
    $price = $inv_data['unit_price'];
    $size = $inv_data['cylinder_size'];

    // Insert into orders
    $sql = "INSERT INTO order_records (customer_name, customer_phone, cylinder_type, total_amount, approval_status) 
            VALUES ('$cust_name', '$cust_phone', '$size', '$price', 'Pending')";
    
    if ($conn->query($sql)) {
        // Deduct stock immediately
        $conn->query("UPDATE cylinder_inventory SET stock_quantity = stock_quantity - 1 WHERE id = $inventory_id");
        $success = "Manual order for $cust_name created successfully!";
    }
}

/** FETCH AVAILABLE INVENTORY FOR THE DROPDOWN **/
$inventory_list = $conn->query("SELECT * FROM cylinder_inventory WHERE stock_quantity > 0 ORDER BY cylinder_size ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Create Order | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2c3e50; --accent: #2ecc71; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        h2 { margin-top: 0; letter-spacing: -1px; color: var(--primary); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #64748b; }
        input, select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        .btn-submit { background: var(--accent); color: white; border: none; padding: 15px; border-radius: 12px; width: 100%; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46, 204, 113, 0.2); }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>

<div class="form-card">
    <h2>Manual Order Entry</h2>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 30px;">Use this form for walk-in or phone-in orders.</p>

    <?php if(isset($success)) echo "<p style='color: #27ae60; font-weight: 800; background: #f0fdf4; padding: 10px; border-radius: 8px; text-align: center;'>$success</p>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Customer Name</label>
            <input type="text" name="customer_name" placeholder="e.g. John Doe" required>
        </div>

        <div class="form-group">
            <label>Customer Phone</label>
            <input type="text" name="customer_phone" placeholder="e.g. 07..." required>
        </div>

        <div class="form-group">
            <label>Select Cylinder</label>
            <select name="inventory_id" required>
                <option value="">-- Choose Cylinder Size --</option>
                <?php while($item = $inventory_list->fetch_assoc()): ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo $item['cylinder_size']; ?>kg Refill - KES <?php echo number_format($item['unit_price'], 2); ?> 
                        (Stock: <?php echo $item['stock_quantity']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" name="create_order" class="btn-submit">Create & Deduct Stock</button>
    </form>

    <a href="staff_dashboard.php" class="back-link">← Return to Operations</a>
</div>

</body>
</html>