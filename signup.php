<?php
// 1. ENABLE ERROR REPORTING (To find out why it's blank)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$error = "";
$success = "";

/** HANDLE ADMIN/STAFF REGISTRATION **/
if (isset($_POST['register'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // Manager or Staff

    // Check if username exists
    $check = $conn->query("SELECT * FROM users WHERE username = '$user'");
    
    if ($check && $check->num_rows > 0) {
        $error = "The username '$user' is already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $pass, $role);
        
        if ($stmt->execute()) {
            // Log the security event
            logAction($conn, "Admin Account Created: $user ($role)");
            $success = "Account created! You can now login.";
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Signup | S ENIGMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --bg: #f1f5f9; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg);
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; margin: 0; 
        }
        .card { 
            background: white; padding: 40px; border-radius: 30px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            width: 100%; max-width: 400px; text-align: center;
        }
        h2 { letter-spacing: -1px; margin-bottom: 5px; }
        p { color: #64748b; font-size: 14px; margin-bottom: 25px; }
        input, select { 
            width: 100%; padding: 14px; margin-bottom: 15px; 
            border: 1px solid #e2e8f0; border-radius: 12px; 
            box-sizing: border-box; font-family: inherit;
        }
        .btn { 
            width: 100%; padding: 15px; background: var(--primary); color: white; 
            border: none; border-radius: 12px; font-weight: 800; cursor: pointer; 
        }
        .msg { padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; font-weight: 600; }
        .error { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .success { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    </style>
</head>
<body>

<div class="card">
    <h2>Admin Signup</h2>
    <p>Create a Manager or Staff account.</p>

    <?php if($error): ?> <div class="msg error"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="msg success"><?php echo $success; ?></div> <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="Staff">Staff Member</option>
            <option value="Manager">Manager</option>
        </select>
        <button type="submit" name="register" class="btn">Create Account</button>
        <p style="margin-top:20px;"><a href="index.php" style="text-decoration:none; color:var(--primary); font-weight:700;">← Back to Home</a></p>
    </form>
</div>

</body>
</html>