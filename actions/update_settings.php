<?php
// actions/update_settings.php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate inputs
    $new_username = trim($_POST['username']);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_INT);
    $activity = $_POST['activity']; 
    $reminder = filter_input(INPUT_POST, 'reminder', FILTER_VALIDATE_INT);
    $daily_goal = filter_input(INPUT_POST, 'daily_goal', FILTER_VALIDATE_INT);
    $sip_size= filter_input(INPUT_POST, 'sip_size', FILTER_VALIDATE_INT);
    $gulp_size= filter_input(INPUT_POST, 'gulp_size', FILTER_VALIDATE_INT);
    
    // Check if username is taken by another user
    $sql_check = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    if ($stmt = $conn->prepare($sql_check)) {
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Error: Username taken
            $stmt->close();
            header("Location: ../settings.php?error=username_taken");
            exit();
        }
        $stmt->close();
    }

    // Update Database
    $sql = "UPDATE users SET 
            username = ?,
            weight = ?, 
            activity_level = ?, 
            reminder_frequency = ?, 
            daily_goal = ?,
            sip_size = ?,
            gulp_size = ?
            WHERE user_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisiiiii", $new_username, $weight, $activity, $reminder, $daily_goal, $sip_size, $gulp_size, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username; // Update session username
            // Success
            header("Location: ../settings.php?status=saved");
        } else {
            header("Location: ../settings.php?error=db_error");
        }
        $stmt->close();
    }
}
?>