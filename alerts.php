<?php
session_start();
require_once 'config.php';

// RBAC: Manager access only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Manager') {
    header("Location: index.php");
    exit();
}

/** FETCH CRITICAL STOCK ONLY **/
$sql = "SELECT *, (min_threshold - stock_quantity) as shortage 
        FROM cylinder_inventory 
        WHERE stock_quantity <= min_threshold 
        ORDER BY shortage DESC";
$alerts = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Critical Stock Alerts | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --danger: #e74c3c; --warning: #f39c12; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        .container { max-width: 900px; margin: 0 auto; }
        .alert-banner { background: #fff5f5; border-left: 6px solid var(--danger); padding: 25px; border-radius: 15px; margin-bottom: 30px; }
        
        .card { background: white; padding: 30px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #94a3b8; font-size: 11px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; }
        
        .qty-badge { background: var(--danger); color: white; padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 13px; }
        .btn-restock { background: var(--primary); color: white; text-decoration: none; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="letter-spacing:-1.5px;">⚠️ Critical Alerts</h1>
        <a href="manager_dashboard.php" style="text-decoration:none; color:var(--primary); font-weight:700;">← Back to Hub</a>
    </div>

    <?php if ($alerts->num_rows > 0): ?>
        <div class="alert-banner">
            <h3 style="margin:0; color:var(--danger);">Restock Required Immediately</h3>
            <p style="margin:5px 0 0; color:#c53030; font-size:14px;">The following items have fallen below your safety threshold (<?php echo $alerts->num_rows; ?> items).</p>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Cylinder Size</th>
                        <th>Current Stock</th>
                        <th>Min. Threshold</th>
                        <th>Shortage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $alerts->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['cylinder_size']; ?>KG Refill</strong></td>
                        <td><span class="qty-badge"><?php echo $row['stock_quantity']; ?> units</span></td>
                        <td><?php echo $row['min_threshold']; ?></td>
                        <td style="color:var(--danger); font-weight:800;">-<?php echo $row['shortage']; ?> units</td>
                        <td><a href="inventory.php" class="btn-restock">Update Stock</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:100px; background:white; border-radius:24px;">
            <h2 style="color:#2ecc71;">✅ All Stock Levels Healthy</h2>
            <p style="color:#64748b;">No items are currently below the safety threshold.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>