<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Staff')) {
    header("Location: index.php");
    exit();
}

/** 1. HANDLE NEW RIDER REGISTRATION **/
if (isset($_POST['add_rider'])) {
    $name = mysqli_real_escape_string($conn, $_POST['rider_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    
    $conn->query("INSERT INTO delivery_riders (rider_name, phone_number, current_status) VALUES ('$name', '$phone', 'Available')");
    $success = "Rider $name registered successfully!";
}

/** 2. HANDLE STATUS TOGGLE **/
if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);
    $current = $_GET['status'];
    $new_status = ($current == 'Available') ? 'Busy' : 'Available';
    $conn->query("UPDATE delivery_riders SET current_status = '$new_status' WHERE id = $id");
    header("Location: rider_management.php");
}

$riders = $conn->query("SELECT * FROM delivery_riders ORDER BY rider_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fleet Management | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2c3e50; --accent: #2ecc71; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); padding: 40px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; color: #94a3b8; font-size: 12px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .status { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; }
        .Available { background: #e8f5e9; color: #2ecc71; }
        .Busy { background: #fff3cd; color: #856404; }
        .btn-toggle { font-size: 12px; color: var(--primary); font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1>🏍️ Fleet Management</h1>
        <a href="staff_dashboard.php" style="text-decoration:none; font-weight:700; color:var(--primary);">← Dashboard</a>
    </div>

    <div class="card">
        <h3>Register New Rider</h3>
        <form method="POST" style="display:flex; gap:10px;">
            <input type="text" name="rider_name" placeholder="Rider Full Name" required style="flex:2; padding:12px; border-radius:10px; border:1px solid #ddd;">
            <input type="text" name="phone_number" placeholder="Phone (e.g. 07...)" required style="flex:1; padding:12px; border-radius:10px; border:1px solid #ddd;">
            <button type="submit" name="add_rider" style="background:var(--primary); color:white; border:none; padding:12px 25px; border-radius:10px; font-weight:700;">Add Rider</button>
        </form>
    </div>

    <div class="card">
        <h3>Current Fleet Status</h3>
        <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($r = $riders->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['rider_name']); ?></strong></td>
                    <td><?php echo $r['phone_number']; ?></td>
                    <td><span class="status <?php echo $r['current_status']; ?>"><?php echo $r['current_status']; ?></span></td>
                    <td><a href="?toggle_id=<?php echo $r['id']; ?>&status=<?php echo $r['current_status']; ?>" class="btn-toggle">Toggle Status</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>