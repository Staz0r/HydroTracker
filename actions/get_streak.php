<?php
require_once '../config/init.php';
require_once '../includes/hydration_utils.php'; // Import the helper

header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. We still need the Daily Goal to pass to the function
$daily_goal = 2000; 
$stmt = $conn->prepare("SELECT daily_goal FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($daily_goal);
$stmt->fetch();
$stmt->close();

// 2. Calculate using the reusable function
$streak = calculate_user_streak($conn, $user_id, $daily_goal);

// 3. Return Result
echo json_encode([
    'status' => 'success', 
    'streak' => $streak
]);
?>