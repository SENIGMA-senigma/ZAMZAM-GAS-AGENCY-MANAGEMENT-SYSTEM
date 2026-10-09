<?php
session_start();
require_once 'config.php';
if (isset($_POST['login'])) {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM customers WHERE customer_phone = '$phone'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['customer_name'];
            $_SESSION['user_role'] = 'Client';
            header("Location: client_dashboard.php");
            exit();
        }
    }
    $error = "Invalid phone number or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Login | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; border-top: 6px solid #2ecc71; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin-top:0;">Client Login</h2>
        <?php if(isset($_GET['success'])) echo "<p style='color:green; font-size:13px;'>Registration successful! Please login.</p>"; ?>
        <?php if(isset($error)) echo "<p style='color:red; font-size:13px;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Access My Points</button>
        </form>
    </div>
</body>
</html>