<?php
session_start();
require_once 'config.php';
if ($_SESSION['user_role'] !== 'Manager') exit('Access Denied');

$logs = $conn->query("SELECT * FROM system_audit_logs ORDER BY timestamp DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Audit | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 40px; }
        .log-table { width: 100%; background: white; border-radius: 20px; border-collapse: collapse; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #0f172a; color: white; font-size: 12px; }
        tr:hover { background: #fdfdfd; }
    </style>
</head>
<body>
    <h1>🛡️ System Security Audit</h1>
    <table class="log-table">
        <thead>
            <tr><th>Time</th><th>User</th><th>Action</th><th>IP Address</th></tr>
        </thead>
        <tbody>
            <?php while($l = $logs->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('M d, H:i:s', strtotime($l['timestamp'])); ?></td>
                <td><strong><?php echo $l['username']; ?></strong></td>
                <td><?php echo htmlspecialchars($l['action_performed']); ?></td>
                <td style="color:#94a3b8; font-size:12px;"><?php echo $l['ip_address']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>