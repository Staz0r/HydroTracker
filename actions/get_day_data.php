<?php
// actions/get_day_data.php
require_once '../config/init.php';
require_once '../includes/hydration_utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 1. Fetch Daily Goal
$daily_goal = 2000; // Default
$stmt = $conn->prepare("SELECT daily_goal FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) $daily_goal = $r['daily_goal'];
}
$stmt->close();

// 2. Fetch Total Intake
$stmt = $conn->prepare("SELECT SUM(amount_ml) as total FROM water_logs WHERE user_id = ? AND DATE(log_time) = ?");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$res = $stmt->get_result();
$total_intake = $res->fetch_assoc()['total'] ?? 0;
$stmt->close();

// 3. Fetch Logs
$stmt = $conn->prepare("SELECT amount_ml, DATE_FORMAT(log_time, '%H:%i') as time, COUNT(*) as sip_count 
                        FROM water_logs WHERE user_id = ? AND DATE(log_time) = ? 
                        GROUP BY amount_ml, time ORDER BY log_time DESC");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 4. Get Status Message
$status_msg = get_hydration_message($total_intake, $daily_goal);

// 5. Calculate Previous/Next/Today dates
$prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($date . ' +1 day'));
$is_today = ($date === date('Y-m-d'));

echo json_encode([
    'status' => 'success',
    'date' => $date,
    'is_today' => $is_today,
    'formatted_date' => $is_today ? "Today's Log" : date('M j, Y', strtotime($date)),
    'prev_date' => $prev_date,
    'next_date' => $next_date,
    'total_intake' => $total_intake,
    'daily_goal' => $daily_goal,
    'status_msg' => $status_msg,
    'logs' => $logs
]);
?>