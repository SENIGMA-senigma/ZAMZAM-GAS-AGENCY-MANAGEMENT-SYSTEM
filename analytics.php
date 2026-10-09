<?php
session_start();
require_once 'config.php';

// RBAC: Strictly Manager only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Manager') {
    header("Location: index.php");
    exit();
}

/** 1. DATA FOR TREND CHART (Sales per Day - Last 7 Days) **/
$days_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $res = $conn->query("SELECT COUNT(*) as count FROM order_records WHERE DATE(order_date) = '$date' AND approval_status = 'Delivered'");
    $count = $res->fetch_assoc()['count'] ?? 0;
    $days_data[date('D', strtotime($date))] = $count;
}

/** 2. DATA FOR CYLINDER POPULARITY (6kg vs 13kg) **/
$popularity = $conn->query("SELECT cylinder_type, COUNT(*) as volume, SUM(total_amount) as revenue 
                            FROM order_records 
                            WHERE approval_status = 'Delivered' 
                            GROUP BY cylinder_type");

/** 3. TOTAL SUMMARY **/
$summary = $conn->query("SELECT SUM(total_amount) as total_rev, COUNT(*) as total_orders FROM order_records WHERE approval_status = 'Delivered'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Intelligence | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        /* Simple CSS Chart */
        .chart-container { display: flex; align-items: flex-end; gap: 15px; height: 200px; padding-top: 20px; border-bottom: 2px solid #e2e8f0; }
        .bar { background: var(--accent); width: 100%; border-radius: 8px 8px 0 0; position: relative; min-height: 5px; }
        .bar:hover { background: #27ae60; }
        .bar::after { content: attr(data-label); position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); font-size: 12px; font-weight: 700; color: #64748b; }
        .bar::before { content: attr(data-value); position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 800; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .mini-card { background: #f1f5f9; padding: 20px; border-radius: 15px; }
        .mini-card h4 { margin: 0; color: #64748b; font-size: 12px; text-transform: uppercase; }
        .mini-card p { margin: 10px 0 0; font-size: 24px; font-weight: 800; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="letter-spacing:-1.5px;">Business Analytics</h1>
        <a href="manager_dashboard.php" style="text-decoration:none; color:var(--primary); font-weight:700;">← Back to Hub</a>
    </div>

    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="mini-card">
            <h4>Lifetime Revenue</h4>
            <p>KES <?php echo number_format($summary['total_rev'], 2); ?></p>
        </div>
        <div class="mini-card">
            <h4>Total Deliveries</h4>
            <p><?php echo $summary['total_orders']; ?> units</p>
        </div>
        <div class="mini-card">
            <h4>Average Order Val</h4>
            <p>KES <?php echo ($summary['total_orders'] > 0) ? number_format($summary['total_rev'] / $summary['total_orders'], 2) : 0; ?></p>
        </div>
    </div>

    <div class="card">
        <h3>Weekly Order Demand</h3>
        <p style="color:#64748b; font-size: 14px; margin-top: -10px;">Number of cylinders delivered in the last 7 days.</p>
        <div class="chart-container">
            <?php 
            $max_val = max($days_data) > 0 ? max($days_data) : 1; 
            foreach($days_data as $day => $val): 
                $height = ($val / $max_val) * 100;
            ?>
                <div class="bar" data-label="<?php echo $day; ?>" data-value="<?php echo $val; ?>" style="height: <?php echo $height; ?>%;"></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3>Popularity by Size</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; color: #94a3b8; font-size: 12px;">
                    <th style="padding-bottom:15px;">CYLINDER SIZE</th>
                    <th style="padding-bottom:15px;">VOLUME SOLD</th>
                    <th style="padding-bottom:15px;">TOTAL REVENUE</th>
                </tr>
            </thead>
            <tbody>
                <?php while($pop = $popularity->fetch_assoc()): ?>
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding:15px 0;"><strong><?php echo $pop['cylinder_type']; ?>KG Refill</strong></td>
                    <td style="padding:15px 0;"><?php echo $pop['volume']; ?> units</td>
                    <td style="padding:15px 0; font-weight:800;">KES <?php echo number_format($pop['revenue'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>