<?php
require_once 'config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$page_title = "Personalize Plan - HydroTracker";
$footer_padding = 'py-16 pb-0';

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

    <div class="max-w-2xl mx-auto p-6 mt-6">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden">

            <div class="bg-blue-600 p-8 text-center">
                <h1 class="font-hand text-3xl text-white font-bold mb-2">Let's get to know you</h1>
                <p class="description-font text-blue-100 text-sm">We'll customize your hydration plan based on this.</p>
            </div>

            <form action="actions/save_personalization.php" method="POST" class="p-8 space-y-6">

                <div>
                    <label class="block text-slate-700 font-bold mb-2 ml-1">Weight (kg)</label>
                    <div class="relative">
                        <input type="number" name="weight" id="weight" placeholder="e.g. 70" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:border-blue-500 transition-colors"
                            oninput="calculateGoal()">
                        <span
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">KG</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-2 ml-1">Activity Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="activity" value="Low" class="peer sr-only"
                                onchange="calculateGoal()" checked>
                            <div
                                class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                                Low
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="activity" value="Medium" class="peer sr-only"
                                onchange="calculateGoal()">
                            <div
                                class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                                Med
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="activity" value="High" class="peer sr-only"
                                onchange="calculateGoal()">
                            <div
                                class="text-center py-3 border-2 border-slate-100 rounded-xl text-slate-500 font-bold hover:border-blue-200 peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 transition-all">
                                High
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-6 relative">
                    <label class="block text-slate-700 font-bold mb-2">Reminder Frequency</label>

                    <input type="hidden" name="reminder" id="reminder_input" value="60">

                    <button type="button" onclick="toggleDropdown()"
                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-left text-slate-700 font-bold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all flex items-center justify-between group">
                        <span id="reminder_display">1 Hour</span>
                        <i
                            class="fa-solid fa-chevron-down text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                    </button>

                    <div id="reminder_options"
                        class="hidden absolute z-50 mt-2 w-full bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden animate-fade-in">

                        <div onclick="selectOption('30', '30 Minutes', this)"
                            class="reminder-option px-4 py-3 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium cursor-pointer transition-colors border-b border-gray-50 last:border-0 flex items-center gap-2">
                            <span class="icon-wrapper"><i class="fa-regular fa-clock text-xs opacity-50"></i></span>
                            30 Minutes
                        </div>

                        <div onclick="selectOption('60', '1 Hour', this)"
                            class="reminder-option px-4 py-3 bg-blue-50 text-blue-600 font-medium cursor-pointer transition-colors border-b border-gray-50 last:border-0 flex items-center gap-2">
                            <span class="icon-wrapper"><i class="fa-solid fa-check text-blue-500 text-xs"></i></span>
                            1 Hour
                        </div>

                        <div onclick="selectOption('120', '2 Hours', this)"
                            class="reminder-option px-4 py-3 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium cursor-pointer transition-colors border-b border-gray-50 last:border-0 flex items-center gap-2">
                            <span class="icon-wrapper"><i class="fa-regular fa-clock text-xs opacity-50"></i></span>
                            2 Hours
                        </div>

                        <div onclick="selectOption('180', '3 Hours', this)"
                            class="reminder-option px-4 py-3 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium cursor-pointer transition-colors flex items-center gap-2">
                            <span class="icon-wrapper"><i class="fa-regular fa-clock text-xs opacity-50"></i></span>
                            3 Hours
                        </div>
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

        <button type="submit"
            class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl active:scale-95 transition-all">
            Start Tracking
        </button>
        </form>


    </div>

    <?php include ROOT_PATH . '/includes/footer.php'; ?>
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

        function toggleDropdown() {
            const menu = document.getElementById('reminder_options');
            menu.classList.toggle('hidden');
        }

        function selectOption(value, text, clickedElement) {
            // Update Data
            document.getElementById('reminder_input').value = value;
            document.getElementById('reminder_display').innerText = text;

            // Reset all options to "Inactive"
            const allOptions = document.querySelectorAll('.reminder-option');
            const checkIcon = '<i class="fa-solid fa-check text-blue-500 text-xs"></i>';
            const clockIcon = '<i class="fa-regular fa-clock text-xs opacity-50"></i>';

            allOptions.forEach(option => {
                const iconWrapper = option.querySelector('.icon-wrapper');
                // Remove Active Styles
                option.classList.remove('bg-blue-50', 'text-blue-600');
                // Add Inactive Styles
                option.classList.add('text-slate-600', 'hover:bg-slate-50', 'hover:text-blue-600');
                // Reset Icon
                iconWrapper.innerHTML = clockIcon;
            });

            // Highlight the clicked one
            clickedElement.classList.add('bg-blue-50', 'text-blue-600');
            clickedElement.classList.remove('text-slate-600', 'hover:bg-slate-50', 'hover:text-blue-600');
            clickedElement.querySelector('.icon-wrapper').innerHTML = checkIcon;

            // Close Menu
            toggleDropdown();
        }

        // Optional: Close dropdown if clicking outside
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