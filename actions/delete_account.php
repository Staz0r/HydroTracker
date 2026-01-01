<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Delete Water Logs first (Foreign Key Constraint)
$sql_logs = "DELETE FROM water_logs WHERE user_id = ?";
if ($stmt = $conn->prepare($sql_logs)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// 2. Delete User Account
$sql_user = "DELETE FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($sql_user)) {
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // 3. Destroy Session
        session_unset();
        session_destroy();
        
        // 4. Redirect to Index with message
        header("Location: ../index.php?msg=account_deleted");
        exit();
    } else {
        die("Error deleting account: " . $conn->error);
    }
    $stmt->close();
}
?>