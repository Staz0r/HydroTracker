<?php
require_once 'config/init.php';
require_once 'includes/hydration_utils.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Dashboard - HydroTracker";

$user_id = $_SESSION['user_id'];
$daily_goal = 0;
$username = $_SESSION['username'];

// Check if date is passed in URL, otherwise use Today
$url_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validation: Don't allow future dates
if ($url_date > date('Y-m-d')) {
    $url_date = date('Y-m-d');
}

$current_view_date = $url_date;
$is_today = ($current_view_date === date('Y-m-d'));

// Calculate Previous / Next Links
$prev_date = date('Y-m-d', strtotime($current_view_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($current_view_date . ' +1 day'));


// Fetch today's water intake
$total_query = 'SELECT SUM(amount_ml) AS total_intake FROM water_logs
                WHERE user_id = ? AND DATE(log_time) = ?';

$stmt = $conn->prepare($total_query);
$stmt->bind_param("is", $user_id, $current_view_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_intake = $row['total_intake'] ?? 0;
$stmt->close();

// Fetch today's intake logs (Grouped)
$log_query = 'SELECT 
                amount_ml,
                MAX(log_time) as log_time,
                COUNT(*) as sip_count
              FROM water_logs
              WHERE user_id = ? AND DATE(log_time) = ? 
              GROUP BY amount_ml, DATE_FORMAT(log_time, "%H:%i")
              ORDER BY log_time DESC';

$stmt = $conn->prepare($log_query);
$stmt->bind_param("is", $user_id, $current_view_date);
$stmt->execute();
$result = $stmt->get_result();
$today_logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch daily goal
$sql = "SELECT daily_goal FROM users WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $daily_goal);
        mysqli_stmt_fetch($stmt);
    }
    mysqli_stmt_close($stmt);
}

// Default fallback
if (!$daily_goal) {
    $daily_goal = 2000;
}

$status_msg = get_hydration_message($total_intake, $daily_goal);

?>

<!DOCTYPE html>
<html lang="en">

<?php include ROOT_PATH . '/includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen" data-daily-goal="<?php echo $daily_goal; ?>">

    <?php include ROOT_PATH . '/includes/nav.php'; ?>


    <div class="max-w-4xl mx-auto p-6 mt-6">

        <div class="text-center">
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">
                <?php echo date('l, M j'); ?>
            </p>
            <div class="text-center">
                <h2 id="header-status-msg" class="text-xl font-bold text-slate-700">
                    <?php echo $status_msg; ?>
                </h2>
                
                <p class="text-slate-400 text-sm">
                    <span id="header-total-intake"><?php echo number_format($total_intake); ?></span> 
                    / <?php echo number_format($daily_goal); ?> ml
                </p>
            </div>
        </div>

        <!-- Water bottle -->
        <div class="flex flex-col items-center justify-center py-8">

            <div id="goal-success-banner"
                class="<?php echo ($total_intake >= $daily_goal) ? '' : 'hidden'; ?> mb-6 bg-green-500 text-white p-4 rounded-2xl shadow-lg shadow-green-200 animate-fade-in flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-trophy text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight">Goal Reached!</h3>
                        <p class="text-green-50 text-xs">Great job staying hydrated today.</p>
                    </div>
                </div>
                <i class="fa-solid fa-xmark cursor-pointer text-white/70 hover:text-white p-2"
                    onclick="this.parentElement.classList.add('hidden')"></i>
            </div>

            <?php
            $left = max(0, $daily_goal - $total_intake);

            $sip_amount = ($left > 0 && $left < 100) ? $left : 100;
            $gulp_amount = ($left > 0 && $left < 250) ? $left : 250;
            ?>

            <form action="<?php echo BASE_URL; ?>/actions/log_water.php" method="POST" class="ajax-form relative group">
                <input type="hidden" name="amount" value="<?php echo $sip_amount; ?>">

                <div id="sip-tooltip" class="absolute 
                    top-full mt-6 left-1/2 -translate-x-1/2 
                    md:top-1/2 md:-translate-y-1/2 md:left-full md:ml-8 md:translate-x-0 md:mt-0
                    opacity-0 group-hover:opacity-100 transition-opacity duration-300 
                    text-blue-500 font-medium text-sm whitespace-nowrap pointer-events-none 
                    bg-white px-4 py-2 rounded-xl shadow-lg shadow-blue-100 border border-blue-50 z-20">

                    <div
                        class="absolute w-3 h-3 bg-white border-blue-50 rotate-45 transform z-10
                        -top-1.5 left-1/2 -translate-x-1/2 border-t border-l
                        md:top-1/2 md:-translate-y-1/2 md:left-0 md:-translate-x-1/2 md:border-t-0 md:border-l md:border-b md:border-r-0">
                    </div>
                    Tap to sip (100ml)
                </div>

                <button type="submit"
                    class="relative block transition-transform active:scale-95 duration-150 focus:outline-none">
                    <div
                        class="w-16 h-8 mx-auto bg-blue-100 border-x-4 border-t-4 border-blue-200 rounded-t-xl translate-y-1">
                    </div>

                    <div
                        class="w-44 h-72 bg-white/50 backdrop-blur-sm border-4 border-blue-200 rounded-[3rem] relative overflow-hidden shadow-[0_20px_50px_-12px_rgba(59,130,246,0.3)]">

                        <div id="water-level-fill"
                            class="absolute bottom-0 w-full bg-blue-500/80 transition-all duration-1000 ease-in-out"
                            style="height: <?php echo (($daily_goal - $total_intake) / $daily_goal) * 100; ?>%;">
                            <div class="w-full h-2 bg-blue-400/50 absolute top-0"></div>
                        </div>

                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-700 z-10">
                            <?php
                            $left = max(0, $daily_goal - $total_intake);
                            // "Near Goal" Logic
                            if ($left > 0 && $left <= 150) {
                                // Case A: Very close (<= 150ml)
                                echo '<span id="ml-left-display" class="text-5xl font-black text-blue-600 drop-shadow-md filter">' . $left . '</span>';
                                echo '<span id="ml-left-label" class="text-xs font-bold text-blue-500 uppercase tracking-widest mt-1 animate-pulse">Take the last sip!</span>';
                            } else {
                                // Case B: Normal or Done
                                echo '<span id="ml-left-display" class="text-5xl font-black text-slate-800 drop-shadow-md filter">' . $left . '</span>';
                                echo '<span id="ml-left-label" class="text-xs font-bold text-slate-600 uppercase tracking-widest mt-1">ml left</span>';
                            }
                            ?>
                        </div>
                    </div>
                </button>
            </form>

            <!-- Quick log buttons -->
            <?php if ($is_today): ?>

                <div
                    class="w-full max-w-md mx-auto mt-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="grid grid-cols-2 gap-4 w-full">

                        <form action="<?php echo BASE_URL; ?>/actions/log_water.php" method="POST" class="ajax-form">
                            <input type="hidden" name="amount" value="<?php echo $gulp_amount; ?>">

                            <button type="submit"
                                class="w-full flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-blue-600 border border-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700 hover:border-blue-700 hover:shadow-xl transition-all active:scale-95 h-full">
                                <i class="fa-solid fa-glass-water text-2xl mb-1"></i>
                                <div class="leading-tight text-center">
                                    <span class="block font-bold">Big Gulp</span>
                                    <span class="block text-xs opacity-80">250ml</span>
                                </div>
                            </button>
                        </form>

                        <button onclick="openManualModal()" type="button"
                            class="flex flex-col items-center justify-center gap-2 p-4 rounded-2xl bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-200 hover:shadow-md transition-all active:scale-95 h-full w-full">
                            <i class="fa-solid fa-pen-to-square text-2xl mb-1"></i>
                            <div class="leading-tight text-center">
                                <span class="block font-bold">Manual</span>
                                <span class="block text-xs opacity-70">Custom</span>
                            </div>
                        </button>

                    </div>
                </div>

            <?php else: ?>

                <div
                    class="w-full max-w-md mx-auto mt-8 p-6 bg-slate-50 border border-slate-200 rounded-2xl text-center text-slate-500">
                    <i class="fa-solid fa-calendar-check text-2xl mb-2 text-slate-400"></i>
                    <p>You are viewing a past log.</p>
                    <a href="dashboard.php" class="text-blue-600 font-bold text-sm hover:underline mt-2 inline-block">Return
                        to Today</a>
                </div>

            <?php endif; ?>

        </div>

        <!-- Stats Overview -->
        <div class="w-full max-w-md mx-auto mt-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">

            <!-- Daily Stats Card -->
            <div class="grid grid-cols-2 gap-4 mb-12">

                <div
                    class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex flex-col items-center justify-center gap-1 text-center h-full">
                    <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mb-1">
                        <i class="fa-solid fa-bullseye text-lg"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold text-blue-600"><?php echo $daily_goal; ?>
                            <span class="text-sm text-gray-400 font-normal">ml</span>
                        </span>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Daily Goal</span>
                    </div>
                </div>

                <div
                    class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex flex-col items-center justify-center gap-1 text-center h-full">
                    <div
                        class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-500 mb-1">
                        <i class="fa-solid fa-glass-water text-lg"></i>
                    </div>
                    <div>
                        <span
                            class="block text-2xl font-bold <?php echo ($total_intake >= $daily_goal) ? 'text-green-500' : 'text-slate-800'; ?>">
                            <span id="total-drunk-display"><?php echo $total_intake; ?></span>
                            <span class="text-sm text-gray-400 font-normal">ml</span>
                        </span>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Consumed</span>
                    </div>
                </div>

            </div>

            <!-- Today's Logs -->
            <div class="w-full max-w-sm mx-auto mt-12">

                <div class="flex items-center justify-between mb-6 px-4">

                    <a href="dashboard.php?date=<?php echo $prev_date; ?>"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition shadow-sm">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>

                    <div class="text-center">
                        <h3 class="font-hand text-xl text-slate-700 font-bold">
                            <?php
                            if ($is_today) {
                                echo "Today's Log";
                            } else {
                                // Shows "Dec 30, 2025"
                                echo date('M j, Y', strtotime($current_view_date));
                            }
                            ?>
                        </h3>
                        <?php if (!$is_today): ?>
                            <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">History View</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$is_today): ?>
                        <a href="dashboard.php?date=<?php echo $next_date; ?>"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition shadow-sm">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <div class="w-10"></div>
                    <?php endif; ?>

                </div>

                <div class="space-y-3">
                    <?php if (count($today_logs) > 0): ?>

                        <?php foreach ($today_logs as $log): ?>
                            <div
                                class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm hover:border-blue-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <?php if ($log['amount_ml'] >= 250): ?>
                                        <i class="fa-solid fa-glass-water text-blue-500"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-droplet text-blue-400"></i>
                                    <?php endif; ?>

                                    <span class="font-bold text-slate-700">
                                        Drank <?php echo $log['amount_ml']; ?> ml
                                        <?php if ($log['sip_count'] > 1): ?>
                                            <span class="text-blue-500 text-sm ml-1">x<?php echo $log['sip_count']; ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                                    <?php echo date('H:i', strtotime($log['log_time'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <div class="text-center py-6 text-slate-400 italic">
                            <?php if ($is_today): ?>
                                No water drank yet today.<br>Take a sip!
                            <?php else: ?>
                                No records found for this date.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php include ROOT_PATH . '/includes/footer.php'; ?>
                        
        <input type="hidden" id="current-view-date" value="<?php echo $current_view_date; ?>">

        <div id="manual-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">

            <style>
                input::-webkit-outer-spin-button,
                input::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }

                input[type=number] {
                    -moz-appearance: textfield;
                }
            </style>

            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                onclick="closeManualModal()"></div>

            <div class="fixed inset-0 z-50 flex items-end justify-center md:items-center pointer-events-none">

                <div
                    class="pointer-events-auto w-full md:w-96 bg-white rounded-t-[2rem] md:rounded-3xl shadow-2xl transform transition-all duration-300 ease-out p-8 pb-10 max-h-[90vh] overflow-y-auto">

                    <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-6 md:hidden"></div>

                    <h3 class="text-xl font-bold text-gray-800 text-center mb-1">Custom Log</h3>
                    <p class="text-center text-gray-400 text-sm mb-6">Enter the amount you drank</p>

                    <form action="<?php echo BASE_URL; ?>/actions/log_water.php" method="POST" class="ajax-form"
                        id="manual-form">

                        <div class="relative mb-8 w-3/4 mx-auto">
                            <input type="number" name="amount" id="custom-amount"
                                class="peer w-full text-center text-5xl font-bold text-blue-600 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none py-2 placeholder-gray-200 bg-transparent"
                                placeholder="0" required autofocus>

                            <span
                                class="absolute -right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-lg pointer-events-none">ml</span>
                        </div>

                        <div class="flex justify-center gap-3 mb-8">
                            <button type="button" onclick="setAmount(150)"
                                class="px-4 py-2 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition active:scale-95">+150</button>
                            <button type="button" onclick="setAmount(300)"
                                class="px-4 py-2 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition active:scale-95">+300</button>
                            <button type="button" onclick="setAmount(500)"
                                class="px-4 py-2 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition active:scale-95">+500</button>
                        </div>

                        <div class="space-y-3">
                            <button type="submit"
                                class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl active:scale-95 transition-all">
                                Log Water
                            </button>

                            <button type="button" onclick="closeManualModal()"
                                class="w-full py-3 rounded-xl text-slate-400 font-bold hover:bg-slate-50 hover:text-slate-600 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="assets/js/dashboard.js"></script>
</body>

</html>