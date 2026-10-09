<?php
// Enable error reporting for recovery debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
session_start();

// Handle Filter logic (Search variable removed as per request)
$filter_size = isset($_GET['size']) ? mysqli_real_escape_string($conn, $_GET['size']) : '';

$query = "SELECT * FROM cylinder_inventory WHERE stock_quantity > 0";

if (!empty($filter_size)) {
    $query .= " AND cylinder_size = '$filter_size'";
}

$query .= " ORDER BY cylinder_size ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamzam Gas Agency | Nairobi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --dark: #0f172a;
            --gray: #64748b;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            background: var(--light-bg);
            color: var(--dark);
            line-height: 1.6;
        }

        header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            padding: 12px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .brand { font-size: 24px; font-weight: 800; color: var(--dark); }
        .brand span { color: var(--primary); }

        .auth-container { display: flex; gap: 15px; align-items: center; }
        .auth-form { display: flex; gap: 8px; }
        .auth-form input {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            width: 110px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 2px solid var(--dark); color: var(--dark); background: transparent; }

        .staff-link { font-size: 11px; color: var(--gray); text-decoration: none; font-weight: 600; display: block; text-align: right; margin-top: 4px; }

        .filter-section {
            background: var(--white);
            margin: 30px 5%;
            padding: 20px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-section select {
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            min-width: 250px;
        }

        .filter-section .btn-search {
            background: var(--dark);
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
        }

        .hero { padding: 40px 5% 20px; text-align: center; }
        .hero h1 { font-size: 42px; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 0 5%; }
        .gas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; padding-bottom: 60px; }

        .gas-card {
            background: var(--white);
            border-radius: 24px;
            padding: 24px;
            transition: 0.4s ease;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow);
            text-align: center;
        }
        .gas-card:hover { transform: translateY(-8px); border-color: var(--primary); }
        .gas-card img { width: 100%; height: 180px; object-fit: contain; margin-bottom: 15px; }

        .badge { display: inline-block; background: #f1f5f9; color: var(--primary-dark); padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; }
        .price-tag { font-size: 24px; font-weight: 800; color: var(--dark); display: block; margin: 10px 0; }
        
        .btn-order { background: var(--dark); color: white; padding: 15px; border-radius: 12px; width: 100%; display: block; font-weight: 700; text-decoration: none; box-sizing: border-box; }

        footer { background: var(--dark); color: #94a3b8; padding: 60px 5% 30px; margin-top: 60px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto; }

        .whatsapp-float {
            position: fixed; bottom: 30px; right: 30px; background: #25d366; color: white; padding: 16px 25px; border-radius: 50px; font-weight: 800; text-decoration: none; box-shadow: 0 10px 25px rgba(37,211,102,0.4); z-index: 2000;
        }
    </style>
</head>
<body>

<header>
    <div class="brand">Zamzam <span>Gas Agency</span></div>
    
    <div class="auth-container">
        <div style="display: flex; gap: 8px;">
            <a href="client_login.php" class="btn btn-outline">My Points</a>
            <a href="register_client.php" class="btn btn-primary">Join Now</a>
        </div>

        <div style="height: 30px; width: 1px; background: #e2e8f0;"></div>

        <div>
            <form action="login_process.php" method="POST" class="auth-form">
                <input type="text" name="username" placeholder="Staff User" required>
                <input type="password" name="password" placeholder="Pass" required>
                <button type="submit" name="login" class="btn btn-primary" style="background:var(--dark)">Login</button>
            </form>
            <a href="signup.php" class="staff-link">New Staff? <span>Register Here</span></a>
        </div>
    </div>
</header>

<div class="hero">
    <h1>Quality Gas, <span>Faster Delivery.</span></h1>
    <p style="color:var(--gray)">Reliable energy for every Nairobi household.</p>
</div>

<div class="container">
    <form method="GET" class="filter-section">
        <select name="size">
            <option value="">All Cylinder Sizes</option>
            <option value="6" <?php if($filter_size == '6') echo 'selected'; ?>>6KG Refills</option>
            <option value="13" <?php if($filter_size == '13') echo 'selected'; ?>>13KG Refills</option>
            
        </select>
        
        <button type="submit" class="btn-search">Filter Results</button>
        
        <?php if(!empty($filter_size)): ?>
            <a href="index.php" style="font-size: 13px; color: var(--gray); text-decoration: none; font-weight: 700;">Clear All</a>
        <?php endif; ?>
    </form>
</div>

<div class="container">
    <div class="gas-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $imagePath = !empty($row['cylinder_image']) ? "uploads/".$row['cylinder_image'] : "uploads/default_gas.png";
                ?>
                <div class="gas-card">
                    <img src="<?php echo $imagePath; ?>" alt="Gas Cylinder" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2997/2997155.png'">
                    <span class="badge"><?php echo htmlspecialchars($row['cylinder_size']); ?>KG REFILL</span>
                    <span class="price-tag">KES <?php echo number_format($row['unit_price'], 2); ?></span>
                    <small style="color:var(--gray); display: block; margin-bottom: 15px;">Stock: <?php echo $row['stock_quantity']; ?> units</small>
                    <a href="place_order.php?cylinder_id=<?php echo $row['id']; ?>" class="btn-order">Order Now</a>
                </div>
                <?php
            }
        } else {
            echo "<div style='grid-column: 1/-1; text-align: center; padding: 60px;'><p>No cylinders matching your filter. <a href='index.php'>Show all inventory</a></p></div>";
        }
        ?>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <h3 style="color:var(--primary)">Zamzam Gas</h3>
            <p>Reliable energy solutions for Nairobi households. Safety first, always.</p>
        </div>
        <div class="footer-col">
            <h3>Visit Us</h3>
            <p>📍 Nairobi CBD, Kenya</p>
            <p>📞 +254 715 552960</p>
        </div>
    </div>
</footer>

<a href="https://wa.me/254715552960?text=I%20want%20to%20order%20gas." class="whatsapp-float">
    <span>Order via WhatsApp</span>
</a>

</body>
</html>