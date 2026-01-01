<?php
require_once 'config/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT daily_goal FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && $user['daily_goal'] > 0) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$page_title = "Personalize Plan - HydroTracker";
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden">
        
        <div class="bg-blue-600 p-8 text-center">
            <h1 class="font-hand text-3xl text-white font-bold mb-2">Let's get to know you</h1>
            <p class="text-blue-100 text-sm">We'll customize your hydration plan based on this.</p>
        </div>

        <form action="actions/save_personalization.php" method="POST" class="p-8 space-y-6">
            
            <div>
                <label class="block text-slate-700 font-bold mb-2 ml-1">Weight (kg)</label>
                <div class="relative">
                    <input type="number" name="weight" id="weight" placeholder="e.g. 70" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500 transition-colors"
                        oninput="calculateGoal()">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">KG</span>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-2 ml-1">Activity Level</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="activity" value="Low" class="peer sr-only" onchange="calculateGoal()" checked>
                        <div class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                            Low
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="activity" value="Medium" class="peer sr-only" onchange="calculateGoal()">
                        <div class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                            Med
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="activity" value="High" class="peer sr-only" onchange="calculateGoal()">
                        <div class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                            High
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-2 ml-1">Remind me every</label>
                <div class="relative">
                    <select name="reminder" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500 appearance-none cursor-pointer">
                        <option value="30">30 Minutes</option>
                        <option value="60" selected>1 Hour</option>
                        <option value="120">2 Hours</option>
                        <option value="180">3 Hours</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="text-center">
                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Recommended Goal</p>
                <div class="flex justify-center items-end gap-2 text-blue-600">
                    <span id="goal-preview" class="text-4xl font-black">2000</span>
                    <span class="text-lg font-bold mb-1 opacity-60">ml</span>
                </div>
            </div>

            <button type="submit" class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl active:scale-95 transition-all">
                Start Tracking
            </button>
        </form>
    </div>

    <script>
        function calculateGoal() {
            const weight = document.getElementById('weight').value || 0;
            const activityRadios = document.getElementsByName('activity');
            let activityMultiplier = 0;

            for (const radio of activityRadios) {
                if (radio.checked) {
                    if (radio.value === 'Low') activityMultiplier = 0;
                    if (radio.value === 'Medium') activityMultiplier = 300;
                    if (radio.value === 'High') activityMultiplier = 500;
                }
            }

            // Formula: Weight * 35ml + Activity Bonus
            let goal = (weight * 35) + activityMultiplier;
            
            // Minimum floor (don't go below 1500)
            if (goal < 1500) goal = 1500;

            document.getElementById('goal-preview').innerText = Math.round(goal);
        }
    </script>
</body>
</html>