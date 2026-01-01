<?php
function get_hydration_message($current, $goal)
{
    if ($goal <= 0)
        return "Set a goal to get started!";

    $pct = ($current / $goal) * 100;

    if ($pct >= 100)
        return "You are hydrated and awesome! 🎉";
    if ($pct >= 75)
        return "Almost there, finish strong! 🚀";
    if ($pct >= 50)
        return "Halfway through. Keep it up! 💪";
    if ($pct >= 25)
        return "Good start! Keep sipping. 💧";

    return "Time to start your hydration streak! ☀️";
}

/**
 * Calculates the user's streak with a Safety Net.
 * * Logic: A day counts as a "Streak" if:
 * 1. User met their personal goal for that day.
 * 2. OR User drank at least 1500ml (Health Minimum).
 * This prevents losing streaks if you raise your goal later.
 */
function calculate_user_streak($conn, $user_id, $daily_goal) {
    $streak = 0;
    $min_baseline = 1500; // The Safety Net

    // 1. Fetch History (Last 30 Days)
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

    // 2. Calculate Logic
    $check_date = new DateTime(); 
    $today_str = $check_date->format('Y-m-d');

    // Check Today (Strict Goal)
    if (isset($history[$today_str]) && $history[$today_str] >= $daily_goal) {
        $streak++;
    }

    // Check Yesterday and Backwards
    $check_date->modify('-1 day'); 
    
    for ($i = 0; $i < 30; $i++) {
        $date_str = $check_date->format('Y-m-d');
        
        if (isset($history[$date_str])) {
            $amount = $history[$date_str];
            
            // The "OR" logic saves the streak
            if ($amount >= $daily_goal || $amount >= $min_baseline) {
                $streak++;
                $check_date->modify('-1 day');
            } else {
                break;
            }
        } else {
            break;
        }
    }
    
    return $streak;
}
?>