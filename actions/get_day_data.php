<?php
require_once '../config/init.php';
require_once '../includes/hydration_utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');

// 1. Fetch Daily Goal
$daily_goal = 2000; 
$goal_sql = "SELECT daily_goal FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($goal_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($g);
    if($stmt->fetch()) $daily_goal = $g;
    $stmt->close();
}

// 2. Fetch Total Intake for that Date
$total_intake = 0;
$sum_sql = "SELECT SUM(amount_ml) as total FROM water_logs WHERE user_id = ? AND DATE(log_time) = ?";
if ($stmt = $conn->prepare($sum_sql)) {
    $stmt->bind_param("is", $user_id, $date);
    $stmt->execute();
    $stmt->bind_result($t);
    if($stmt->fetch()) $total_intake = (int)$t;
    $stmt->close();
}

// 3. Fetch Detailed Logs
$logs = [];
$log_sql = "SELECT amount_ml, MAX(log_time) as log_time, COUNT(*) as sip_count 
            FROM water_logs 
            WHERE user_id = ? AND DATE(log_time) = ? 
            GROUP BY amount_ml, DATE_FORMAT(log_time, '%H:%i') 
            ORDER BY log_time DESC";

if ($stmt = $conn->prepare($log_sql)) {
    $stmt->bind_param("is", $user_id, $date);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['time'] = date('H:i', strtotime($row['log_time'])); // Format time
        $logs[] = $row;
    }
    $stmt->close();
}

// 4. Generate Message & Determine "Is Today"
$status_msg = get_hydration_message($total_intake, $daily_goal);
$is_today = ($date === date('Y-m-d'));

echo json_encode([
    'status' => 'success',
    'date' => $date,
    'formatted_date' => $is_today ? "Today's Log" : date('M j, Y', strtotime($date)),
    'is_today' => $is_today,
    'total_intake' => $total_intake,
    'daily_goal' => $daily_goal,
    'status_msg' => $status_msg,
    'logs' => $logs
]);
?>