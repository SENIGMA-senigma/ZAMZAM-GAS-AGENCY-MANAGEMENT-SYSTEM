<?php
require_once 'config.php';
session_start();

$error = "";
$success = "";

/** 1. HANDLE REGISTRATION **/
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM customers WHERE customer_phone = '$phone'");
    if ($check->num_rows > 0) {
        $error = "This phone number is already registered!";
    } else {
        $sql = "INSERT INTO customers (customer_name, customer_phone, password) VALUES ('$name', '$phone', '$pass')";
        if ($conn->query($sql)) {
            $new_id = $conn->insert_id;
            $ref_code = "ZAM" . str_pad($new_id, 4, "0", STR_PAD_LEFT);
            $conn->query("UPDATE customers SET referral_code = '$ref_code' WHERE id = $new_id");
            
            logAction($conn, "New Account Created: $name");
            $success = "Account created! You can now login.";
        }
    }
}

/** 2. HANDLE LOGIN **/
if (isset($_POST['login'])) {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM customers WHERE customer_phone = '$phone'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['customer_name'];
            $_SESSION['user_role'] = 'Client';
            
            logAction($conn, "Client Logged In: " . $user['customer_name']);
            header("Location: client_dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with this phone number.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth Portal | Zamzam Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0f172a; --accent: #2ecc71; --bg: #f1f5f9; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; margin: 0; 
        }

        .auth-card { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px);
            padding: 40px; border-radius: 30px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            width: 100%; max-width: 400px; 
            text-align: center;
        }

        .toggle-box {
            display: flex; background: #f1f5f9; padding: 5px; border-radius: 15px; margin-bottom: 30px;
        }
        .toggle-btn {
            flex: 1; padding: 10px; border: none; border-radius: 10px; 
            cursor: pointer; font-weight: 700; background: transparent; color: #64748b; transition: 0.3s;
        }
        .toggle-btn.active { background: white; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        h2 { letter-spacing: -1px; margin-bottom: 10px; }
        p { color: #64748b; font-size: 14px; margin-bottom: 25px; }

        input { 
            width: 100%; padding: 14px; margin-bottom: 15px; 
            border: 1px solid #e2e8f0; border-radius: 12px; 
            box-sizing: border-box; font-family: inherit; font-size: 14px;
        }
        input:focus { outline: none; border-color: var(--accent); ring: 2px solid rgba(46, 204, 113, 0.1); }

        .btn { 
            width: 100%; padding: 15px; background: var(--primary); color: white; 
            border: none; border-radius: 12px; font-weight: 800; 
            cursor: pointer; transition: 0.3s; 
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2); }

        .msg { padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
        .error { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .success { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    </style>
</head>
<body>

<div class="auth-card">
    <h2 id="title">Welcome Back</h2>
    <p id="subtitle">Login to manage your gas refills.</p>

    <div class="toggle-box">
        <button class="toggle-btn active" id="loginTab" onclick="showAuth('login')">Login</button>
        <button class="toggle-btn" id="regTab" onclick="showAuth('register')">Register</button>
    </div>

    <?php if($error): ?> <div class="msg error"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="msg success"><?php echo $success; ?></div> <?php endif; ?>

    <form id="loginForm" method="POST">
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Sign In</button>
    </form>

    <form id="registerForm" method="POST" style="display:none;">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <button type="submit" name="register" class="btn" style="background:var(--accent);">Create Account</button>
    </form>
</div>

<script>
function showAuth(type) {
    const loginForm = document.getElementById('loginForm');
    const regForm = document.getElementById('registerForm');
    const loginTab = document.getElementById('loginTab');
    const regTab = document.getElementById('regTab');
    const title = document.getElementById('title');
    const subtitle = document.getElementById('subtitle');

    if(type === 'login') {
        loginForm.style.display = 'block';
        regForm.style.display = 'none';
        loginTab.classList.add('active');
        regTab.classList.remove('active');
        title.innerText = "Welcome Back";
        subtitle.innerText = "Login to manage your gas refills.";
    } else {
        loginForm.style.display = 'none';
        regForm.style.display = 'block';
        loginTab.classList.remove('active');
        regTab.classList.add('active');
        title.innerText = "Join Zamzam";
        subtitle.innerText = "Start earning loyalty points today.";
    }
}
</script>

</body>
</html>