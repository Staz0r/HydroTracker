<?php
require_once '../config/init.php';
header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Fetch Daily Goal
$daily_goal = 2000; // Default
$stmt = $conn->prepare("SELECT daily_goal FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($daily_goal);
$stmt->fetch();
$stmt->close();

// 2. Fetch History (Last 30 Days)
$history = [];
$sql = "SELECT DATE(log_time) as log_date, SUM(amount_ml) as total 
        FROM water_logs 
        WHERE user_id = ? AND log_time >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        GROUP BY DATE(log_time)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $history[$row['log_date']] = (int)$row['total'];
    }
    $stmt->close();
}

// 3. Calculate Streak
$streak = 0;
$check_date = new DateTime(); // Today
$today_str = $check_date->format('Y-m-d');

// Check Today
if (isset($history[$today_str]) && $history[$today_str] >= $daily_goal) {
    $streak++;
}

// Check Backwards
$check_date->modify('-1 day'); 
for ($i = 0; $i < 30; $i++) {
    $date_str = $check_date->format('Y-m-d');
    if (isset($history[$date_str]) && $history[$date_str] >= $daily_goal) {
        $streak++;
        $check_date->modify('-1 day');
    } else {
        break; 
    }
}

// 4. Return Result
echo json_encode([
    'status' => 'success', 
    'streak' => $streak
]);
?>