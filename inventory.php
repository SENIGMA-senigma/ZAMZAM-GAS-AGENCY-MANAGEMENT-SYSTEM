<?php
session_start();
require_once 'config.php';

// RBAC: Secure the door
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Staff')) {
    header("Location: index.php");
    exit();
}

$dashboard_link = ($_SESSION['user_role'] === 'Manager') ? 'manager_dashboard.php' : 'staff_dashboard.php';

/** 1. HANDLE NEW STOCK ADDITION **/
if (isset($_POST['add_inventory'])) {
    $size = intval($_POST['cylinder_size']);
    $price = floatval($_POST['unit_price']);
    $qty = intval($_POST['stock_quantity']);
    $threshold = intval($_POST['min_threshold']);
    
    $image_name = "default_gas.png";
    if (!empty($_FILES['cylinder_image']['name'])) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['cylinder_image']['name']);
        move_uploaded_file($_FILES['cylinder_image']['tmp_name'], "uploads/" . $image_name);
    }

    $stmt = $conn->prepare("INSERT INTO cylinder_inventory (cylinder_size, unit_price, stock_quantity, min_threshold, cylinder_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iddis", $size, $price, $qty, $threshold, $image_name);
    
    if ($stmt->execute()) {
        logAction($conn, "Added new inventory: " . $size . "kg cylinder");
        $success = "New inventory item added successfully!";
    }
}

/** 2. HANDLE PRICE/STOCK UPDATES **/
if (isset($_POST['update_stock'])) {
    $id = intval($_POST['item_id']);
    $new_price = floatval($_POST['new_price']);
    $new_qty = intval($_POST['new_qty']);
    
    $old = $conn->query("SELECT * FROM cylinder_inventory WHERE id = $id")->fetch_assoc();
    $stmt = $conn->prepare("UPDATE cylinder_inventory SET unit_price = ?, stock_quantity = ? WHERE id = ?");
    $stmt->bind_param("dii", $new_price, $new_qty, $id);
    
    if($stmt->execute()) {
        logAction($conn, "Updated " . $old['cylinder_size'] . "kg inventory. New Price: $new_price, New Qty: $new_qty");
        $success = "Inventory updated successfully!";
    }
}

/** 3. HANDLE DELETIONS **/
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $item = $conn->query("SELECT cylinder_size FROM cylinder_inventory WHERE id = $id")->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM cylinder_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        logAction($conn, "DELETED Inventory Item: " . $item['cylinder_size'] . "kg");
        header("Location: inventory.php?success=deleted");
        exit();
    }
}

/** 4. HANDLE DROPDOWN FILTER LOGIC **/
$filter_size = isset($_GET['filter_size']) ? mysqli_real_escape_string($conn, $_GET['filter_size']) : '';
$sql = "SELECT * FROM cylinder_inventory";
if (!empty($filter_size)) {
    $sql .= " WHERE cylinder_size = '$filter_size'";
}
$sql .= " ORDER BY cylinder_size ASC";
$inventory = $conn->query($sql);

// Fetch unique sizes for the dropdown
$sizes_res = $conn->query("SELECT DISTINCT cylinder_size FROM cylinder_inventory ORDER BY cylinder_size ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory | Zamzam Gas Agency</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --danger: #ef4444; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .filter-bar { 
            background: #fff; padding: 15px 25px; border-radius: 15px; 
            margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            display: flex; justify-content: space-between; align-items: center;
        }
        select.filter-select { 
            padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 10px; 
            width: 250px; font-family: inherit; cursor: pointer;
        }

        .img-thumb { 
            width: 45px; height: 45px; border-radius: 12px; 
            object-fit: cover; background: #f1f5f9; 
            border: 1px solid #e2e8f0; margin-right: 12px;
        }
        .item-name { display: flex; align-items: center; }

        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #94a3b8; font-size: 11px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .btn { padding: 10px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-update { background: var(--accent); color: white; }
        .btn-delete { color: var(--danger); text-decoration: none; font-size: 12px; font-weight: 800; margin-left: 10px; }
        input { padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #64748b; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="letter-spacing:-1px; margin:0;">📦 Inventory Management</h1>
        <a href="<?php echo $dashboard_link; ?>" style="text-decoration:none; color:var(--primary); font-weight:800;">← Back to Hub</a>
    </div>

    <?php if(isset($success)) echo "<div style='background:#f0fdf4; color:#16a34a; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600; border:1px solid #dcfce7;'>$success</div>"; ?>
    <?php if(isset($_GET['success']) && $_GET['success'] == 'deleted') echo "<div style='background:#fef2f2; color:#dc2626; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600; border:1px solid #fee2e2;'>Item removed successfully.</div>"; ?>

    <div class="filter-bar">
        <form method="GET" style="display: flex; gap: 10px;">
            <select name="filter_size" class="filter-select" onchange="this.form.submit()">
                <option value="">Show All Sizes</option>
                <?php while($s = $sizes_res->fetch_assoc()): ?>
                    <option value="<?php echo $s['cylinder_size']; ?>" <?php if($filter_size == $s['cylinder_size']) echo 'selected'; ?>>
                        <?php echo $s['cylinder_size']; ?>kg Cylinders
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if(!empty($filter_size)): ?>
                <a href="inventory.php" style="font-size: 13px; color: var(--danger); text-decoration: none; align-self: center; font-weight: 700;">Reset Filter</a>
            <?php endif; ?>
        </form>
        <div style="color: #94a3b8; font-size: 13px; font-weight: 600;">
            Displaying: <?php echo $inventory->num_rows; ?> cylinder types
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;">Add New Cylinder</h3>
            <form method="POST" enctype="multipart/form-data">
                <div style="margin-bottom:15px;">
                    <label>Size (KG)</label>
                    <input type="number" name="cylinder_size" style="width:100%;" placeholder="e.g. 6" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label>Price (KES)</label>
                    <input type="number" step="0.01" name="unit_price" style="width:100%;" placeholder="1200" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label>Initial Stock</label>
                    <input type="number" name="stock_quantity" style="width:100%;" placeholder="50" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label>Low Stock Alert Level</label>
                    <input type="number" name="min_threshold" style="width:100%;" value="5" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label>Cylinder Image</label>
                    <input type="file" name="cylinder_image" style="width:100%;" accept="image/*">
                </div>
                <button type="submit" name="add_inventory" class="btn" style="background:var(--primary); color:white; width:100%;">Save to Inventory</button>
            </form>
        </div>

        <div class="card">
            <h3>Current Stock</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cylinder Item</th>
                        <th>Price (KES)</th>
                        <th>Stock Units</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($inventory->num_rows > 0): ?>
                        <?php while($row = $inventory->fetch_assoc()): ?>
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                            <tr>
                                <td>
                                    <div class="item-name">
                                        <img src="uploads/<?php echo $row['cylinder_image']; ?>" 
                                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/2997/2997155.png'" 
                                             class="img-thumb">
                                        <strong><?php echo $row['cylinder_size']; ?>kg</strong>
                                    </div>
                                </td>
                                <td><input type="number" step="0.01" name="new_price" value="<?php echo $row['unit_price']; ?>" style="width:100px;"></td>
                                <td><input type="number" name="new_qty" value="<?php echo $row['stock_quantity']; ?>" style="width:70px;"></td>
                                <td>
                                    <button type="submit" name="update_stock" class="btn btn-update">Update</button>
                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Permanently delete this item from inventory?')">Delete</a>
                                </td>
                            </tr>
                        </form>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8;">No inventory found for this size.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>