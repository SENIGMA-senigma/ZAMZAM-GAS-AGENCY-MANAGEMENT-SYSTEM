<?php
session_start();
require_once 'config.php';

// RBAC: Manager Only
if ($_SESSION['user_role'] !== 'Manager') { header("Location: index.php"); exit(); }

if (isset($_POST['send_broadcast'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['announcement_text']);
    
    // Clear old announcements first so clients only see the latest one
    $conn->query("DELETE FROM system_announcements"); 
    
    $stmt = $conn->prepare("INSERT INTO system_announcements (message) VALUES (?)");
    $stmt->bind_param("s", $msg);
    
    if ($stmt->execute()) {
        $success = "Broadcast sent successfully to all client dashboards!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Broadcast | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 40px; }
        .card { background: white; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        textarea { width: 100%; height: 150px; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; font-family: inherit; box-sizing: border-box; resize: none; }
        .btn-send { background: #9b59b6; color: white; border: none; padding: 15px; width: 100%; border-radius: 15px; font-weight: 800; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-send:hover { background: #8e44ad; transform: translateY(-2px); }
        .alert { background: #f0fdf4; color: #16a34a; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; text-align: center; }
    </style>
</head>
<body>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="margin:0;">📢 Bulk Broadcast</h2>
        <a href="manager_dashboard.php" style="text-decoration:none; color:#64748b; font-weight:700;">← Back</a>
    </div>

    <?php if(isset($success)) echo "<div class='alert'>$success</div>"; ?>

    <form method="POST">
        <label style="display:block; margin-bottom:10px; font-weight:700; color:#64748b;">Your Message</label>
        <textarea name="announcement_text" placeholder="Example: We have a special offer! 6kg refills are now KES 1,000 only for this weekend!" required></textarea>
        <button type="submit" name="send_broadcast" class="btn-send">Send Broadcast Now</button>
    </form>
</div>

</body>
</html>