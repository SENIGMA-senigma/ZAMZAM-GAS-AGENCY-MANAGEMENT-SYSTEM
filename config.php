<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = getenv('DB_USER') ?: '3WS1sv3ZSjr5BPb.root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_NAME') ?: 'gas_agency';
$port = (int)(getenv('DB_PORT') ?: 4000);

// Initialize mysqli for secure cloud connection
$conn = mysqli_init();

// Optional: Enable SSL certification if required by your cluster
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
