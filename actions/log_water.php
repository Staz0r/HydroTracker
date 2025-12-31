<?php
require_once '../config/init.php';

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
            
            // Send back the new data
            echo json_encode([
                'status' => 'success', 
                'new_total' => $new_total,
                'added_amount' => $amount_ml,
                'time' => $time
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