<?php
require_once 'config/init.php';

if (isset($_SESSION["user_id"])) {
    // 1. Get the current user's ID
    $uid = $_SESSION["user_id"];

    // 2. Check their daily goal in the database
    // (We use a direct query here to be 100% sure of their status)
    $check_sql = "SELECT daily_goal FROM users WHERE user_id = ?";
    if ($check_stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($check_stmt, "i", $uid);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_bind_result($check_stmt, $db_daily_goal);
        mysqli_stmt_fetch($check_stmt);
        mysqli_stmt_close($check_stmt);

        // 3. Route them correctly
        if ($db_daily_goal > 0) {
            header("location: " . BASE_URL . "/dashboard.php");
        } else {
            header("location: " . BASE_URL . "/personalization.php");
        }
        exit;
    }
}

$page_title = "Login - HydroTracker";

$head_path = ROOT_PATH . '/includes/head.php';
$footer_path = ROOT_PATH . '/includes/footer.php';

// Determine active tab based on URL
$requested_tab = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';
?>

<!DOCTYPE html>
<html lang="en">
<?php include $head_path; ?>

<body class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="mt-4 mb-8 text-center">
        <a href="<?php echo BASE_URL; ?>/index.php">
            <i class="fa-solid fa-droplet text-blue-500 text-4xl mb-2"></i>
            <h1 class="text-3xl font-bold text-blue-600 brand-font">HydroTracker</h1>
            <p class="description-font text-gray-400 text-sm">Gamify your hydration.</p>
        </a>
    </div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="flex border-b border-gray-100">
            <button onclick="switchTab('login')" id="tab-login"
                class="flex-1 py-4 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 transition">
                LOGIN
            </button>
            <button onclick="switchTab('register')" id="tab-register"
                class="flex-1 py-4 text-sm font-bold text-gray-400 hover:text-blue-500 transition">
                REGISTER
            </button>
        </div>

        <!-- Login View -->
        <div id="view-login" class="p-8">
            <h2 class="text-center text-2xl font-bold text-gray-800 mb-1">Welcome Back</h2>
            <p class="description-font text-center text-gray-500 text-sm mb-6">Let's get you back hydrated!</p>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm text-center">Account created! Please log
                    in.</div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors']['login_err'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center">
                    <?php echo $_SESSION['errors']['login_err']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/actions/auth_process.php" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="login">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-4 text-gray-400"></i>
                        <input type="text" name="identifier"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['login_id']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="Username or Email"
                            value="<?php echo isset($_SESSION['old']['login_id']) ? htmlspecialchars($_SESSION['old']['login_id']) : ''; ?>">
                    </div>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['login_id'] ?? ''; ?></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
                        <input type="password" name="password"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['login_password']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="••••••••">
                    </div>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['login_password'] ?? ''; ?></span>
                </div>
                <div class="flex justify-end mb-4">
                    <a href="forgot_password.php" class="text-xs font-bold text-blue-500 hover:underline">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    LOG IN
                </button>
            </form>
        </div>

        <!-- Register View -->
        <div id="view-register" class="p-8 hidden">
            <h2 class="text-center text-2xl font-bold text-gray-800 mb-1">Create Account</h2>
            <p class="description-font text-center text-gray-500 text-sm mb-6">Start your hydration journey.</p>

            <form action="<?php echo BASE_URL; ?>/actions/auth_process.php" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="register">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3 top-3.5 text-gray-400"></i>
                        <input type="text" name="username"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['reg_username']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="HydroHero"
                            value="<?php echo isset($_SESSION['old']['reg_username']) ? htmlspecialchars($_SESSION['old']['reg_username']) : ''; ?>">
                    </div>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['reg_username'] ?? ''; ?></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-4 text-gray-400"></i>
                        <input type="email" name="email"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['reg_email']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="you@example.com"
                            value="<?php echo isset($_SESSION['old']['reg_email']) ? htmlspecialchars($_SESSION['old']['reg_email']) : ''; ?>">
                    </div>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['reg_email'] ?? ''; ?></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
                        <input type="password" name="password"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['reg_password']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="••••••••">
                    </div>
                    <p class="leading-tight text-xs text-gray-500 text-left mt-4">
                        Password must be at least 8 characters long.
                    </p>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['reg_password'] ?? ''; ?></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Confirm Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
                        <input type="password" name="confirm_password"
                            class="w-full pl-10 pr-3 py-3 border rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition <?php echo isset($_SESSION['errors']['reg_confirm']) ? 'border-red-500' : 'border-gray-200'; ?>"
                            placeholder="••••••••">
                    </div>
                    <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['reg_confirm'] ?? ''; ?></span>
                </div>

                <div class="mt-4">
                    <div class="flex items-center gap-3 max-w-sm mx-auto">
                        <input type="checkbox" name="terms" value="1"
                            class="w-4 h-4 text-blue-600 rounded cursor-pointer shrink-0">
                        <span class="text-xs text-gray-500 text-left">
                            By ticking this box, you agree to the
                            <a href="#" class="text-blue-500 font-bold hover:underline">Terms & Conditions</a>
                            and <a href="#" class="text-blue-500 font-bold hover:underline">Privacy Policy</a> set by
                            HydroTracker.
                        </span>
                    </div>
                    <div class="pl-7 text-left">
                        <span class="text-xs text-red-500"><?php echo $_SESSION['errors']['terms'] ?? ''; ?></span>
                    </div>


                </div>

                <button type="submit"
                    class="w-full bg-green-500 text-white font-bold py-3 rounded-xl hover:bg-green-600 transition shadow-lg shadow-green-200">
                    CREATE ACCOUNT
                </button>
                <div class="flex justify-end mt-4">
                    <a onclick="switchTab('login')"
                        class="text-xs font-bold text-blue-500 hover:underline hover:cursor-pointer">
                        Already have an account? Log In
                    </a>
                </div>
            </form>
        </div>

    </div>

    <?php
    $footer_padding = 'py-8';
    include $footer_path;

    // Clear session errors/old data so they don't persist on refresh
    unset($_SESSION['errors']);
    unset($_SESSION['old']);
    ?>

    <script>
        // Switch between Login and Register views
        function switchTab(tab) {
            const loginView = document.getElementById('view-login');
            const registerView = document.getElementById('view-register');
            const loginBtn = document.getElementById('tab-login');
            const registerBtn = document.getElementById('tab-register');

            if (tab === 'login') {
                loginView.classList.remove('hidden');
                registerView.classList.add('hidden');
                loginBtn.className = "flex-1 py-4 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 transition";
                registerBtn.className = "flex-1 py-4 text-sm font-bold text-gray-400 hover:text-blue-500 transition";
            } else {
                loginView.classList.add('hidden');
                registerView.classList.remove('hidden');
                registerBtn.className = "flex-1 py-4 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 transition";
                loginBtn.className = "flex-1 py-4 text-sm font-bold text-gray-400 hover:text-blue-500 transition";
            }

            // Update URL so reload works
            const url = new URL(window.location);
            url.searchParams.set('mode', tab);
            window.history.replaceState({}, '', url);
        }

        // This checks PHP's decision and forces the correct tab
        window.onload = function () {
            <?php if ($requested_tab === 'register'): ?>
                switchTab('register');
            <?php else: ?>
                switchTab('login');
            <?php endif; ?>
        };

        // Box styling on input - remove red border on typing
        const allInputs = document.querySelectorAll('input');

        allInputs.forEach(input => {
            input.addEventListener('input', function () {
                if (this.classList.contains('border-red-500')) {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-gray-200');
                }

                // Clear the error text span next to input
                const parentDiv = this.parentElement;
                const errorSpan = parentDiv.nextElementSibling;
                if (errorSpan && errorSpan.tagName === 'SPAN') {
                    errorSpan.textContent = '';
                }
            });
        });
    </script>
</body>

</html>