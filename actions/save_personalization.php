<?php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Get User ID
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // 2. Sanitize Inputs
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_INT);
    $activity = $_POST['activity']; // Low, Medium, High
    $reminder = filter_input(INPUT_POST, 'reminder', FILTER_VALIDATE_INT);

    if (!$weight || !$activity || !$reminder) {
        // Basic validation fail
        header("Location: ../personalization.php?error=missing_fields");
        exit();
    }

    // 3. Calculate Daily Goal (Server Side)
    // Formula: Weight (kg) * 35ml
    $base_goal = $weight * 35;
    
    // Add activity bonus
    $activity_bonus = 0;
    if ($activity === 'Medium') $activity_bonus = 300;
    if ($activity === 'High') $activity_bonus = 500;

    $daily_goal = $base_goal + $activity_bonus;

    // Safety check: ensure reasonable limits (e.g., min 1500ml, max 5000ml)
    if ($daily_goal < 1500) $daily_goal = 1500;
    if ($daily_goal > 5000) $daily_goal = 5000;

    // 4. Update Database
    // We update weight, activity_level, reminder_frequency AND daily_goal
    $sql = "UPDATE users SET 
            weight = ?, 
            activity_level = ?, 
            reminder_frequency = ?, 
            daily_goal = ? 
            WHERE user_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isiii", $weight, $activity, $reminder, $daily_goal, $user_id);
        
        if ($stmt->execute()) {
            // 5. Success - Redirect to Dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }
}
?>