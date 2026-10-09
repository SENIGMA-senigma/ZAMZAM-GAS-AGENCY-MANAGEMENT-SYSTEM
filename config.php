<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'gas_agency';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Global Security Audit Function
function logAction($conn, $action) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $username = $_SESSION['username'] ?? 'System/Guest';
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, username, action_performed, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip);
    $stmt->execute();
}
?>