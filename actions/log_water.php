<?php
require_once '../config/init.php';
require_once '../includes/hydration_utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {

    $user_id = $_SESSION['user_id'];
    $amount_ml = intval($_POST['amount']);
    $today = date('Y-m-d');
    $time = date('H:i');

    if ($amount_ml > 0) {
        $insert_query = 'INSERT INTO water_logs (user_id, amount_ml) VALUES (?, ?)';
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $amount_ml);

        if ($stmt->execute()) {
            $total_query = 'SELECT SUM(amount_ml) AS total_intake FROM water_logs WHERE user_id = ? AND DATE(log_time) = ?';
            $sum_stmt = $conn->prepare($total_query);
            $sum_stmt->bind_param("is", $user_id, $today);
            $sum_stmt->execute();
            $result = $sum_stmt->get_result()->fetch_assoc();
            $new_total = $result['total_intake'] ?? 0;
            
            $daily_goal = 2000; // Default fallback
            $goal_sql = "SELECT daily_goal FROM users WHERE user_id = ?";
            if ($goal_stmt = $conn->prepare($goal_sql)) {
                $goal_stmt->bind_param("i", $user_id);
                $goal_stmt->execute();
                $goal_stmt->bind_result($db_goal);
                if($goal_stmt->fetch()) {
                    $daily_goal = $db_goal;
                }
                $goal_stmt->close();
            }
            
            $status_msg = get_hydration_message($new_total, $daily_goal);
            
            // Send back the new data
            echo json_encode([
                'status' => 'success', 
                'new_total' => $new_total,
                'added_amount' => $amount_ml,
                'time' => $time,
                'status_msg' => $status_msg
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
    }
} else {
    header("Location:" . BASE_URL . "/dashboard.php");
    exit();
}
exit();
?>