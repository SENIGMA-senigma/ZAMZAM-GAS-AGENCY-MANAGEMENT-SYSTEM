<?php
session_start();
require_once 'config.php';

// RBAC: Secure the door
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Manager') {
    header("Location: index.php");
    exit();
}

/** LIVE DATA AGGREGATION **/
$sales_res = $conn->query("SELECT SUM(total_amount) as total FROM order_records WHERE approval_status = 'Delivered'");
$total_sales = ($sales_res) ? $sales_res->fetch_assoc()['total'] : 0;

$stock_res = $conn->query("SELECT COUNT(*) as count FROM cylinder_inventory WHERE stock_quantity <= min_threshold");
$low_stock = ($stock_res) ? $stock_res->fetch_assoc()['count'] : 0;

$pending_res = $conn->query("SELECT COUNT(*) as count FROM order_records WHERE approval_status = 'Pending'");
$pending_count = ($pending_res) ? $pending_res->fetch_assoc()['count'] : 0;

$dispatch_res = $conn->query("SELECT COUNT(*) as count FROM order_records WHERE approval_status = 'Dispatched'");
$active_dispatches = ($dispatch_res) ? $dispatch_res->fetch_assoc()['count'] : 0;

$cust_res = $conn->query("SELECT COUNT(*) as count FROM customers");
$customer_count = ($cust_res) ? $cust_res->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Hub | Zamzam Gas System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #0f172a; --accent: #2ecc71; --danger: #e74c3c; 
            --warning: #f1c40f; --info: #3498db; --bg: #f8fafc; 
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        /* Fixed Sidebar with Vertical Scroll */
        .sidebar { 
            width: 280px; height: 100vh; background: var(--primary); color: white; 
            position: fixed; padding: 30px; box-sizing: border-box; 
            display: flex; flex-direction: column;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .sidebar h2 { font-weight: 800; margin-bottom: 30px; flex-shrink: 0; }
        .sidebar h2 span { color: var(--accent); }
        
        .nav-container { flex-grow: 1; } 

        .nav-link { 
            display: flex; justify-content: space-between; align-items: center; 
            color: #94a3b8; text-decoration: none; padding: 14px; 
            border-radius: 12px; margin-bottom: 5px; transition: 0.3s; font-weight: 600; 
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 800; }
        
        .sidebar-hr { border: 0; height: 1px; background: rgba(255,255,255,0.15); margin: 20px 0; flex-shrink: 0; }

        .logout-wrapper { margin-top: 20px; padding-top: 20px; flex-shrink: 0; }
        .btn-logout { 
            display: block; background: rgba(231, 76, 60, 0.1); 
            color: var(--danger); border: 1px solid var(--danger); 
            padding: 12px; border-radius: 12px; text-decoration: none; 
            font-weight: 800; text-align: center; transition: 0.3s;
        }
        .btn-logout:hover { background: var(--danger); color: white; }
        
        .main { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.05); border-bottom: 4px solid var(--accent); }
        .stat-val { font-size: 28px; font-weight: 800; color: var(--primary); display: block; margin-top: 10px; }
        
        .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .action-card { 
            background: white; padding: 30px; border-radius: 20px; text-decoration: none; color: inherit; 
            border: 1px solid rgba(0,0,0,0.05); transition: 0.3s ease; position: relative;
        }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .badge { background: var(--danger); color: white; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; position: absolute; top: 20px; right: 20px; }

        /* Report Form Styling */
        .report-section { background: white; padding: 30px; border-radius: 25px; margin-top: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
        .report-form { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        .report-form select { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600; }
        .btn-download { background: var(--primary); color: white; border: none; padding: 14px 25px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-download:hover { background: #1e293b; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Zamzam <span>Gas System</span></h2>
        
        <div class="nav-container">
            <a href="manager_dashboard.php" class="nav-link active"><span>🏠 Dashboard</span></a>
            
            <a href="approval_queue.php" class="nav-link">
                <span>✅ Approvals</span>
                <?php if($pending_count > 0) echo "<span class='nav-badge'>$pending_count</span>"; ?>
            </a>
            
            <a href="alerts.php" class="nav-link">
                <span>⚠️ Stock Alerts</span>
                <?php if($low_stock > 0) echo "<span class='nav-badge'>$low_stock</span>"; ?>
            </a>

            <hr class="sidebar-hr">

            <a href="dispatch.php" class="nav-link"><span>🚚 Live Tracker</span></a>
            <a href="inventory.php" class="nav-link"><span>📦 Inventory</span></a>
            <a href="analytics.php" class="nav-link"><span>📊 Intelligence</span></a>
            <a href="audit_logs.php" class="nav-link"><span>🛡️ Security Audit</span></a>
            <a href="customer_followup.php" class="nav-link"><span>📢 Bulk Broadcast</span></a>
            <a href="rider_management.php" class="nav-link"><span>🏍️ Manage Fleet</span></a>
        </div>

        <div class="logout-wrapper">
            <hr class="sidebar-hr">
            <a href="logout.php" class="btn-logout">🚪 Secure Logout</a>
        </div>
    </div>

    <div class="main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-weight: 800; letter-spacing: -1px; margin: 0;">Management Overview</h1>
            <p style="color: var(--primary); font-weight: 600;">Welcome, <?php echo $_SESSION['username']; ?></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <span style="color: #64748b; font-weight: 600;">Total Revenue</span>
                <span class="stat-val">KES <?php echo number_format($total_sales, 2); ?></span>
            </div>
            <div class="stat-card">
                <span style="color: #64748b; font-weight: 600;">Active Customers</span>
                <span class="stat-val"><?php echo $customer_count; ?> Clients</span>
            </div>
            <div class="stat-card" style="<?php echo ($low_stock > 0) ? 'border-bottom-color:var(--danger);' : ''; ?>">
                <span style="color: #64748b; font-weight: 600;">System Attention</span>
                <span class="stat-val"><?php echo ($low_stock + $pending_count); ?> items</span>
            </div>
        </div>

        <h2 style="font-weight: 800; margin-bottom: 25px;">Operations Hub</h2>
        <div class="action-grid">
            <a href="approval_queue.php" class="action-card" style="border-left: 6px solid var(--info);">
                <?php if($pending_count > 0) echo "<span class='badge'>$pending_count Pending</span>"; ?>
                <h3 style="margin-top: 0;">Verify & Dispatch</h3>
                <p style="color: #64748b; font-size: 14px;">Process orders and assign riders.</p>
            </a>

            <a href="audit_logs.php" class="action-card" style="border-left: 6px solid var(--primary);">
                <h3 style="margin-top: 0;">Security Audit</h3>
                <p style="color: #64748b; font-size: 14px;">Monitor system activity and IP logs for accountability.</p>
            </a>

            <a href="analytics.php" class="action-card" style="border-left: 6px solid var(--info);">
                <h3 style="margin-top: 0;">Analytics Hub</h3>
                <p style="color: #64748b; font-size: 14px;">Deep dive into sales trends and cylinder popularity.</p>
            </a>

            <a href="customer_followup.php" class="action-card" style="border-left: 6px solid #9b59b6;">
                <h3 style="margin-top: 0;">Bulk Broadcast</h3>
                <p style="color: #64748b; font-size: 14px;">Manage client loyalty codes and broadcast announcements.</p>
            </a>
        </div>

        <div class="report-section">
            <h2 style="margin-top:0; letter-spacing:-1px;">📁 Export Business Reports</h2>
            <p style="color:#64748b; font-size:14px; margin-bottom:25px;">Generate reports for monthly or yearly accounting audits.</p>
            
            <form action="generate_report.php" method="POST" class="report-form">
                <div class="form-group">
                    <label>Report Type</label>
                    <select name="report_type" id="report_type" onchange="toggleMonthSelect()">
                        <option value="monthly">Monthly Report</option>
                        <option value="yearly">Yearly Report</option>
                    </select>
                </div>

                <div class="form-group" id="month_wrapper">
                    <label>Month</label>
                    <select name="month">
                        <?php 
                        for($m=1; $m<=12; $m++) {
                            $monthName = date('F', mktime(0, 0, 0, $m, 1));
                            echo "<option value='$m'>$monthName</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <select name="year">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                <button type="submit" name="download_report" class="btn-download">📥 Download CSV</button>
            </form>
        </div>
    </div>

    <script>
    function toggleMonthSelect() {
        const type = document.getElementById('report_type').value;
        const monthWrapper = document.getElementById('month_wrapper');
        monthWrapper.style.display = (type === 'yearly') ? 'none' : 'block';
    }
    </script>
</body>
</html>