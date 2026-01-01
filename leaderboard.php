<?php
require_once 'config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Leaderboard - HydroTracker";
$current_page = 'leaderboard.php'; // For nav highlighting

// FETCH TOP 10 USERS FOR TODAY
$today = date('Y-m-d');
$leaderboard = [];

$sql = "SELECT u.username, u.daily_goal, SUM(w.amount_ml) as total_intake 
        FROM users u 
        JOIN water_logs w ON u.user_id = w.user_id 
        WHERE DATE(w.log_time) = ? 
        GROUP BY u.user_id 
        ORDER BY total_intake DESC 
        LIMIT 10";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $leaderboard = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
    <?php include ROOT_PATH . '/includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen pb-20">
    <?php include ROOT_PATH . '/includes/nav.php'; ?>

    <div class="max-w-2xl mx-auto p-6 mt-6">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800 brand-font">Today's Top Hydrators</h1>
            <p class="text-slate-400 text-sm mt-1">Who is hitting their goals today?</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            
            <?php if (count($leaderboard) > 0): ?>
                
                <?php foreach ($leaderboard as $index => $row): ?>
                    <?php 
                        $rank = $index + 1;
                        
                        // Styling for Top 3
                        $rank_bg = "bg-slate-100 text-slate-500"; // Default
                        if ($rank == 1) $rank_bg = "bg-yellow-100 text-yellow-600 border border-yellow-200";
                        if ($rank == 2) $rank_bg = "bg-gray-100 text-slate-600 border border-slate-300";
                        if ($rank == 3) $rank_bg = "bg-orange-100 text-orange-600 border border-orange-200";

                        // Check if it's the current user (highlight them)
                        $is_me = ($row['username'] === $_SESSION['username']);
                        $row_class = $is_me ? "bg-blue-50/50" : "hover:bg-slate-50";
                    ?>

                    <div class="flex items-center justify-between p-4 border-b border-slate-100 last:border-0 transition-colors <?php echo $row_class; ?>">
                        
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg <?php echo $rank_bg; ?>">
                                <?php if($rank <= 3): ?>
                                    <i class="fa-solid fa-trophy text-sm"></i>
                                <?php else: ?>
                                    <?php echo $rank; ?>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h3 class="flex items-center font-bold text-slate-700 text-lg leading-tight">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                    <?php if($is_me): ?>
                                        <span class="ml-2 text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full uppercase tracking-wider">You</span>
                                    <?php endif; ?>
                                </h3>
                                
                                <div class="w-32 h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                    <?php 
                                        $percent = ($row['total_intake'] / $row['daily_goal']) * 100; 
                                        if($percent > 100) $percent = 100;
                                    ?>
                                    <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="block font-black text-xl text-blue-600"><?php echo number_format($row['total_intake']); ?></span>
                            <span class="text-xs text-slate-400 font-bold uppercase">ml</span>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                
                <div class="text-center py-12 px-6">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fa-solid fa-glass-water-droplet text-3xl"></i>
                    </div>
                    <h3 class="text-slate-600 font-bold">No logs yet today</h3>
                    <p class="text-slate-400 text-sm">Be the first to drink water and take the #1 spot!</p>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <?php include ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>