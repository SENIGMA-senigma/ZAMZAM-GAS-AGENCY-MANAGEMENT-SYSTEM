<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = getenv('DB_USER') ?: '3WS1sv3ZSjr5BPb.root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_NAME') ?: 'gas_agency';
$port = (int)(getenv('DB_PORT') ?: 4000);

$conn = mysqli_init();

// Enable SSL for secure connection to TiDB Cloud
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}

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
