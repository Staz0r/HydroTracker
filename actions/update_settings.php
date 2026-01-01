<?php
// actions/update_settings.php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];

    // 1. Sanitize Inputs
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_INT);
    $activity = $_POST['activity']; 
    $reminder = filter_input(INPUT_POST, 'reminder', FILTER_VALIDATE_INT);
    $daily_goal = filter_input(INPUT_POST, 'daily_goal', FILTER_VALIDATE_INT);

    // 2. Update Database
    $sql = "UPDATE users SET 
            weight = ?, 
            activity_level = ?, 
            reminder_frequency = ?, 
            daily_goal = ? 
            WHERE user_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isiii", $weight, $activity, $reminder, $daily_goal, $user_id);
        
        if ($stmt->execute()) {
            // Success
            header("Location: ../settings.php?status=saved");
        } else {
            header("Location: ../settings.php?error=db_error");
        }
        $stmt->close();
    }
}
?>