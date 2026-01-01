<?php
require_once 'config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Settings - HydroTracker";
$user_id = $_SESSION['user_id'];
$footer_padding = 'py-16 pb-0';

// Fetch user's current settings
$user = null;
$stmt = $conn->prepare("SELECT username, email, weight, activity_level, reminder_frequency, daily_goal, sip_size, gulp_size FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Defaults
if (!$user['sip_size']) $user['sip_size'] = 100;
if (!$user['gulp_size']) $user['gulp_size'] = 250;
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen pb-20">

    <?php include 'includes/nav.php'; ?>

    <div class="max-w-2xl mx-auto p-6 mt-6">

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="dashboard.php"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all"
                    title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-slate-800">Settings</h1>
            </div>

            <a href="actions/logout.php"
                class="bg-red-50 text-red-500 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-100 transition">
                <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
            </a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'saved'): ?>
            <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 animate-fade-in">
                <i class="fa-solid fa-check-circle"></i>
                <span>Settings saved successfully!</span>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 'username_taken'): ?>
            <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 animate-fade-in">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>That username is already taken.</span>
            </div>
        <?php endif; ?>

        <form action="actions/update_settings.php" method="POST" class="space-y-6">

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-blue-500"></i> Account
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                        <div class="relative">
                            <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="username" required
                                value="<?php echo htmlspecialchars($user['username']); ?>"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 ml-1">Visible on the leaderboard.</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email</label>
                        <div class="relative opacity-60">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <div class="w-full pl-10 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-bold cursor-not-allowed">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 ml-1">Email cannot be changed.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-glass-water text-blue-500"></i> Hydration Plan
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Weight (kg)</label>
                        <div class="relative">
                            <i class="fa-solid fa-weight-scale absolute left-4 top-1/2 -translate-y-1/2 text-blue-400"></i>
                            <input type="number" name="weight" id="weight" required 
                                value="<?php echo $user['weight']; ?>"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Reminder Frequency</label>
                        <input type="hidden" name="reminder" id="reminder_input" value="<?php echo $user['reminder_frequency']; ?>">
                        
                        <button type="button" onclick="toggleDropdown()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-left text-slate-700 font-bold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all flex items-center justify-between group">
                            <span id="reminder_display">
                                <?php
                                $freq = $user['reminder_frequency'];
                                if ($freq == 30) echo "30 Minutes";
                                elseif ($freq == 60) echo "1 Hour";
                                elseif ($freq == 120) echo "2 Hours";
                                elseif ($freq == 180) echo "3 Hours";
                                else echo "1 Hour";
                                ?>
                            </span>
                            <i id="dropdown-arrow" class="fa-solid fa-chevron-down text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                        </button>

                        <div id="reminder_options" class="hidden absolute z-50 mt-2 w-full bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden animate-fade-in">
                            <?php
                            function renderOption($val, $label, $currentVal) {
                                $isActive = ($val == $currentVal);
                                $bgClass = $isActive ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600';
                                $icon = $isActive ? '<i class="fa-solid fa-check text-blue-500 text-xs"></i>' : '<i class="fa-regular fa-clock text-xs opacity-50"></i>';
                                echo '<div onclick="selectOption(\'' . $val . '\', \'' . $label . '\')" class="px-4 py-3 font-medium cursor-pointer transition-colors border-b border-gray-50 last:border-0 flex items-center gap-2 ' . $bgClass . '">' . $icon . ' ' . $label . '</div>';
                            }
                            renderOption(30, '30 Minutes', $user['reminder_frequency']);
                            renderOption(60, '1 Hour', $user['reminder_frequency']);
                            renderOption(120, '2 Hours', $user['reminder_frequency']);
                            renderOption(180, '3 Hours', $user['reminder_frequency']);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Activity Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <?php
                        $levels = ['Low', 'Medium', 'High'];
                        foreach ($levels as $lvl):
                            $checked = ($user['activity_level'] === $lvl) ? 'checked' : '';
                            ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="activity" value="<?php echo $lvl; ?>" class="peer sr-only" <?php echo $checked; ?>>
                                <div class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                                    <?php echo $lvl; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-8 pb-8 border-b border-slate-100">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Daily Goal (ml)</label>
                    <div class="flex gap-4">
                        <div class="relative w-full">
                            <i class="fa-solid fa-bullseye absolute left-4 top-1/2 -translate-y-1/2 text-blue-400"></i>
                            <input type="number" name="daily_goal" id="daily_goal" required
                                value="<?php echo $user['daily_goal']; ?>"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-xl">
                        </div>

                        <button type="button" onclick="recalculateGoal()"
                            class="shrink-0 px-4 py-2 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 border border-blue-100 transition shadow-sm">
                            <i class="fa-solid fa-calculator sm:mr-1"></i>
                            <span class="hidden min-[500px]:inline">Recalculate</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 ml-1">Calculated based on weight & activity.</p>
                </div>

                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-sliders"></i> Button Customization
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-2">Sip Button</label>
                            <div class="relative">
                                <i class="fa-solid fa-droplet absolute left-4 top-1/2 -translate-y-1/2 text-blue-400"></i>
                                <input type="number" name="sip_size" required value="<?php echo $user['sip_size']; ?>"
                                    class="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-blue-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-2">Gulp Button</label>
                            <div class="relative">
                                <i class="fa-solid fa-glass-water absolute left-4 top-1/2 -translate-y-1/2 text-blue-500"></i>
                                <input type="number" name="gulp_size" required value="<?php echo $user['gulp_size']; ?>"
                                    class="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-blue-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl active:scale-95 transition-all">
                    Save Changes
                </button>
            </div>

        </form>

        <div class="mt-12 pt-8 border-t-2 border-slate-100 text-center">
            <h3 class="text-slate-300 text-[10px] font-bold uppercase tracking-[0.2em] mb-4">Danger Zone</h3>

            <button onclick="confirmDelete()"
                class="group flex items-center justify-center gap-2 mx-auto px-6 py-3 rounded-xl bg-red-50 text-red-500 font-bold hover:bg-red-100 transition-colors border border-transparent hover:border-red-200">
                <i class="fa-solid fa-trash-can transition-transform group-hover:scale-110"></i>
                Delete My Account
            </button>

            <p class="text-[10px] text-red-300 mt-3">
                This action is permanent and cannot be undone.
            </p>
        </div>
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

            let goal = (weight * 35) + activityMultiplier;
            if (goal < 1500) goal = 1500;

            const goalInput = document.getElementById('daily_goal');
            goalInput.style.backgroundColor = '#dbeafe'; 
            goalInput.value = Math.round(goal);
            setTimeout(() => {
                goalInput.style.backgroundColor = '#f8fafc'; 
            }, 300);
        }

        function confirmDelete() {
            if (confirm("Are you sure you want to delete your account?")) {
                if (confirm("All your data will be permanently lost. Proceed?")) {
                    window.location.href = 'actions/delete_account.php';
                }
            }
        }

        function toggleDropdown() {
            const menu = document.getElementById('reminder_options');
            menu.classList.toggle('hidden');
        }

        function selectOption(value, text) {
            document.getElementById('reminder_input').value = value;
            document.getElementById('reminder_display').innerText = text;
            document.getElementById('reminder_options').classList.add('hidden');
        }

        document.addEventListener('click', function (e) {
            const menu = document.getElementById('reminder_options');
            const button = document.querySelector('button[onclick="toggleDropdown()"]');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>