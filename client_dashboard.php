<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Client') {
    header("Location: index.php");
    exit();
}

$client_id = $_SESSION['user_id'];
$user_data = $conn->query("SELECT * FROM customers WHERE id = $client_id")->fetch_assoc();
$phone = $user_data['customer_phone'];

/** 1. FETCH LATEST SYSTEM ANNOUNCEMENT **/
$announcement_res = $conn->query("SELECT message FROM system_announcements ORDER BY id DESC LIMIT 1");
$announcement = $announcement_res->fetch_assoc();

/** 2. FETCH UNREAD NOTIFICATIONS **/
$notifs = $conn->query("SELECT * FROM customer_notifications WHERE customer_phone = '$phone' AND is_read = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zamzam Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --purple: #8b5cf6; --bg: #f8fafc; --orange: #f97316; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--primary); margin: 0; padding: 0; }
        
        .navbar { background: white; padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        /* Announcement Banner Styling */
        .announcement-banner { 
            background: #fff7ed; border: 1px solid #ffedd5; 
            padding: 20px; border-radius: 25px; margin-bottom: 30px; 
            display: flex; align-items: center; gap: 15px;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Grid Layout */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }

        /* Hero Card */
        .hero-card { 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            color: white; padding: 40px; border-radius: 30px; 
            position: relative; overflow: hidden;
        }
        .hero-card::after { 
            content: ''; position: absolute; top: -50px; right: -50px; 
            width: 200px; height: 200px; background: rgba(46, 204, 113, 0.1); border-radius: 50%; 
        }

        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; }
        .stat-item { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); }
        
        /* Side Cards */
        .side-card { background: white; padding: 30px; border-radius: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.02); }
        .ref-badge { 
            background: #f5f3ff; color: var(--purple); padding: 15px; 
            border-radius: 15px; font-weight: 800; font-size: 20px; 
            display: block; text-align: center; border: 2px dashed var(--purple); margin: 20px 0;
        }

        .btn-action { 
            background: var(--accent); color: white; border: none; 
            padding: 15px 25px; border-radius: 15px; font-weight: 700; 
            width: 100%; cursor: pointer; transition: 0.3s;
        }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(46, 204, 113, 0.2); }

        /* Table Activity */
        .table-card { background: white; border-radius: 30px; padding: 30px; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #94a3b8; font-size: 12px; padding-bottom: 20px; }
        td { padding: 15px 0; border-top: 1px solid #f1f5f9; font-size: 14px; }
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; }
        .status-delivered { background: #f0fdf4; color: #16a34a; }

        /* MODAL STYLING */
        #orderModal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.6); z-index: 1000; backdrop-filter: blur(8px);
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; width: 90%; max-width: 500px; padding: 40px; border-radius: 35px;
            position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>

<div id="orderModal">
    <div class="modal-content">
        <button onclick="toggleOrderModal()" style="position:absolute; top:25px; right:25px; border:none; background:#f1f5f9; width:35px; height:35px; border-radius:50%; cursor:pointer; font-weight:800; color:#64748b;">✕</button>
        <h2 style="margin:0; letter-spacing:-1px;">New Gas Refill</h2>
        <p style="color:#64748b; margin:10px 0 30px;">Select your cylinder size to proceed with the order.</p>
        <iframe src="place_order.php" style="width:100%; height:450px; border:none; border-radius:20px;"></iframe>
    </div>
</div>

<nav class="navbar">
    <h2 style="margin:0; letter-spacing:-1px;">Zamzam<span>Gas System</span></h2>
    <div style="display:flex; align-items:center; gap:20px;">
        <span style="font-weight:600; font-size:14px; color:#64748b;">Welcome, <?php echo $user_data['customer_name']; ?></span>
        <a href="logout.php" style="color: #ef4444; font-weight:700; text-decoration:none; font-size:14px;">Logout</a>
    </div>
</nav>

<div class="container">

    <?php if($announcement): ?>
    <div class="announcement-banner">
        <span style="font-size: 24px;">🔔</span>
        <div>
            <small style="text-transform: uppercase; font-weight: 800; color: var(--orange); font-size: 10px; letter-spacing: 1px;">Announcement</small>
            <p style="margin:0; color: #9a3412; font-weight: 600; line-height: 1.4;"><?php echo htmlspecialchars($announcement['message']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div>
            <div class="hero-card">
                <h1 style="margin:0; font-size:32px;">Your Energy Hub</h1>
                <p style="color:#94a3b8; margin-top:10px;">Monitor your refills and loyalty rewards in real-time.</p>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <span style="color:#94a3b8; font-size:12px; text-transform:uppercase;">Loyalty Balance</span>
                        <div style="font-size:32px; font-weight:800; margin-top:5px;"><?php echo number_format($user_data['loyalty_points']); ?> <small style="font-size:14px; font-weight:400; color:var(--accent);">PTS</small></div>
                    </div>
                    <div class="stat-item">
                        <span style="color:#94a3b8; font-size:12px; text-transform:uppercase;">Total Orders</span>
                        <div style="font-size:32px; font-weight:800; margin-top:5px;">
                            <?php 
                                $count = $conn->query("SELECT COUNT(*) as total FROM order_records WHERE customer_phone = '$phone'")->fetch_assoc();
                                echo $count['total'];
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <h3 style="margin-top:0;">Recent Activity</h3>
                <table>
                    <thead>
                        <tr><th>DATE</th><th>DESCRIPTION</th><th>AMOUNT</th><th>STATUS</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $orders = $conn->query("SELECT * FROM order_records WHERE customer_phone = '$phone' ORDER BY id DESC LIMIT 5");
                        while($o = $orders->fetch_assoc()): 
                            $status_class = ($o['approval_status'] == 'Delivered') ? 'status-delivered' : '';
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                            <td><strong><?php echo $o['cylinder_type']; ?>kg Gas Refill</strong></td>
                            <td>KES <?php echo number_format($o['total_amount']); ?></td>
                            <td><span class="status-pill <?php echo $status_class; ?>"><?php echo $o['approval_status']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="side-card" style="margin-bottom:20px;">
                <h3 style="margin:0;">Quick Order</h3>
                <p style="color:#64748b; font-size:13px;">Running low? Get a refill delivered in under 45 mins.</p>
                <button class="btn-action" onclick="toggleOrderModal()">Order Now</button>
            </div>

            <div class="side-card">
                <h3 style="margin:0; color:var(--purple);">🎁 Share & Earn</h3>
                <p style="color:#64748b; font-size:13px; margin-top:10px;">Refer friends and get 50 points for every successful refill.</p>
                <div class="ref-badge"><?php echo $user_data['referral_code']; ?></div>
                <button class="btn-action" style="background:#f5f3ff; color:var(--purple); border:none;" onclick="copyRef()">Copy Code</button>
                
                <div style="margin-top:20px; background:#f8fafc; padding:15px; border-radius:15px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700;">
                        <span>Next Reward</span>
                        <span><?php echo $user_data['loyalty_points']; ?>/500 PTS</span>
                    </div>
                    <div style="height:6px; background:#e2e8f0; border-radius:10px; margin-top:8px;">
                        <div style="width:<?php echo min(($user_data['loyalty_points']/500)*100, 100); ?>%; height:100%; background:var(--purple); border-radius:10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOrderModal() {
    const modal = document.getElementById('orderModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; 
    } else {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) { toggleOrderModal(); }
}

function copyRef() {
    const code = "<?php echo $user_data['referral_code']; ?>";
    navigator.clipboard.writeText(code);
    alert("Referral code copied to clipboard!");
}
</script>

</body>
</html>