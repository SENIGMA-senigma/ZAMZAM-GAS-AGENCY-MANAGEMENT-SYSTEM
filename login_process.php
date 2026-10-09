<?php
session_start();
require_once 'config.php';

if (isset($_POST['login'])) {
    // Sanitize input to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Check the 'users' table (Staff & Managers)
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Route based on role
            if ($user['role'] === 'Manager') {
                header("Location: manager_dashboard.php");
            } else {
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            // Password mismatch
            header("Location: index.php?error=invalid_pass");
            exit();
        }
    } else {
        // User not found
        header("Location: index.php?error=user_not_found");
        exit();
    }
} else {
    // Direct access to this file is not allowed
    header("Location: index.php");
    exit();
}
?>