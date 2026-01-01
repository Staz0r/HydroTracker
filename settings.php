<?php
require_once 'config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Settings - HydroTracker";
$user_id = $_SESSION['user_id'];

$footer_padding = 'py-16 pb-0';

// 1. Fetch Current Settings
$user = null;
$stmt = $conn->prepare("SELECT username, email, weight, activity_level, reminder_frequency, daily_goal FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen pb-20">

    <?php include 'includes/nav.php'; ?>

    <div class="max-w-2xl mx-auto p-6 mt-6">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Settings</h1>
            <a href="actions/logout.php"
                class="bg-red-50 text-red-500 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-100 transition">
                <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
            </a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'saved'): ?>
            <div
                class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-check-circle"></i>
                <span>Settings saved successfully!</span>
            </div>
        <?php endif; ?>

        <form action="actions/update_settings.php" method="POST" class="space-y-6">

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2">Account</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-500">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1">Username</label>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1">Email</label>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2">Hydration Plan</h2>

                <div class="mb-6">
                    <label class="block text-slate-700 font-bold mb-2">Weight (kg)</label>
                    <input type="number" name="weight" id="weight" required value="<?php echo $user['weight']; ?>"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-6">
                    <label class="block text-slate-700 font-bold mb-2">Activity Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <?php
                        $levels = ['Low', 'Medium', 'High'];
                        foreach ($levels as $lvl):
                            $checked = ($user['activity_level'] === $lvl) ? 'checked' : '';
                            ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="activity" value="<?php echo $lvl; ?>" class="peer sr-only" <?php echo $checked; ?>>
                                <div
                                    class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                                    <?php echo $lvl; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-slate-700 font-bold mb-2">Reminder Frequency</label>
                    <div class="relative">
                        <select name="reminder"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500 appearance-none cursor-pointer">
                            <option value="30" <?php echo ($user['reminder_frequency'] == 30) ? 'selected' : ''; ?>>30
                                Minutes</option>
                            <option value="60" <?php echo ($user['reminder_frequency'] == 60) ? 'selected' : ''; ?>>1 Hour
                            </option>
                            <option value="120" <?php echo ($user['reminder_frequency'] == 120) ? 'selected' : ''; ?>>2
                                Hours</option>
                            <option value="180" <?php echo ($user['reminder_frequency'] == 180) ? 'selected' : ''; ?>>3
                                Hours</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="block text-slate-700 font-bold mb-2">Daily Goal (ml)</label>
                    <div class="flex gap-4">
                        <input type="number" name="daily_goal" id="daily_goal" required
                            value="<?php echo $user['daily_goal']; ?>"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500 text-xl">

                        <button type="button" onclick="recalculateGoal()"
                            class="shrink-0 px-3 sm:px-4 py-2 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 transition text-sm">

                            <i class="fa-solid fa-calculator sm:mr-1"></i>

                            <span class="hidden min-[500px]:inline">Recalculate</span>

                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Click Recalculate to update goal based on new weight.</p>
                </div>

            </div>

            <div class="pt-4">
                <button type="submit"
                    class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl active:scale-95 transition-all">
                    Save Changes
                </button>
            </div>

        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function recalculateGoal() {
            const weight = parseInt(document.getElementById('weight').value) || 0;
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

            // Minimum floor
            if (goal < 1500) goal = 1500;

            // Update the input
            const goalInput = document.getElementById('daily_goal');

            // Animation flash
            goalInput.style.backgroundColor = '#dbeafe'; // light blue
            goalInput.value = Math.round(goal);
            setTimeout(() => {
                goalInput.style.backgroundColor = '#f8fafc'; // back to slate-50
            }, 300);
        }
    </script>
</body>

</html>