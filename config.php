<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'gateway01.us-west-2.prod.aws.tidbcloud.com'; // Replace with your actual TiDB Cloud host endpoint
$user = 'your_tidb_username';                     // Replace with your TiDB username
$pass = 'your_tidb_password';                     // Replace with your TiDB password
$db   = 'gas_agency';
$port = 4000;

// Initialize mysqli for secure cloud connection
$conn = mysqli_init();

// Uncomment the line below if your cloud cluster requires SSL certification
// mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port)) {
    die("Connection failed: " . mysqli_connect_error());
}

// Global Security Audit Function
function logAction($conn, $action) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $username = $_SESSION['username'] ?? 'System/Guest';
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, username, action_performed, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip);
    $stmt->execute();
    $stmt->close();
}
?>
