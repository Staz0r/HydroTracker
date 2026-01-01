<?php
require_once 'config/init.php';

$step = 1;
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verify Email
    if (isset($_POST['check_email'])) {
        $email = trim($_POST['email']);

        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Email found!
                $step = 2;
                $verified_email = $email; // Pass to next step
            } else {
                $error = "We couldn't find an account with that email.";
            }
            $stmt->close();
        }
    }

    // Reset Password
    if (isset($_POST['reset_password'])) {
        $target_email = $_POST['target_email'];
        $pass = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $sql = "SELECT password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $target_email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($prev_pass_hash);
            $stmt->fetch();

            if (strlen($pass) < 8) {
                $error = "Password must be at least 8 characters.";
                $step = 2; // Stay on step 2
                $verified_email = $target_email;
            } elseif ($pass !== $confirm) {
                $error = "Passwords do not match.";
                $step = 2;
                $verified_email = $target_email;
            } elseif (password_verify($pass, $prev_pass_hash)) {
                $error = "New password cannot be the same as the old password.";
                $step = 2;
                $verified_email = $target_email;
                $stmt->close();
            } else {
                // Update Database
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE email = ?";
                if ($stmt2 = $conn->prepare($sql)) {
                    $stmt2->bind_param("ss", $hashed, $target_email);
                    if ($stmt2->execute()) {
                        $success = "Password updated! You can now login.";
                        $step = 3; // Success screen
                    } else {
                        $error = "Database error. Please try again.";
                    }
                    $stmt2->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include ROOT_PATH . '/includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="mt-4 mb-8 text-center">
        <a href="<?php echo BASE_URL; ?>/index.php">
            <i class="fa-solid fa-droplet text-blue-500 text-4xl mb-2"></i>
            <h1 class="text-3xl font-bold text-blue-600 brand-font">HydroTracker</h1>
            <p class="description-font text-gray-400 text-sm">Gamify your hydration.</p>
        </a>
    </div>
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden p-8">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Reset Password</h1>
            <?php if ($step == 1): ?>
                <p class="description-font text-slate-400 text-sm mt-1">Enter your email to find your account.</p>
            <?php elseif ($step == 2): ?>
                <p class="text-slate-400 text-sm mt-1">
                    <span class="description-font">Create a new password for</span>
                    <strong><?php echo htmlspecialchars($verified_email); ?></strong></p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 text-red-600 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 text-green-600 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <a href="login.php"
                class="block w-full bg-blue-600 text-white text-center font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Back to Login
            </a>
        <?php endif; ?>


        <?php if ($step == 1 && !$success): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="check_email" value="1">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-4 text-gray-400"></i>
                        <input type="email" name="email" placeholder="you@example.com" required
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-slate-700">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Find Account
                </button>

                <div class="text-center mt-4">
                    <a href="login.php" class="text-slate-400 text-sm hover:text-blue-500 font-bold">Cancel</a>
                </div>
            </form>
        <?php endif; ?>


        <?php if ($step == 2 && !$success): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="reset_password" value="1">
                <input type="hidden" name="target_email" value="<?php echo htmlspecialchars($verified_email); ?>">

                <div class="bg-yellow-50 text-yellow-600 text-[10px] p-2 rounded-lg border border-yellow-100 mb-2">
                    <i class="fa-solid fa-lock-open mr-1"></i> <strong>Demo Mode:</strong> Verifying without email link.
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
                        <input type="password" name="password" placeholder="********" required
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-slate-700">
                        <p class="leading-tight text-xs text-gray-500 text-left mt-4">
                            Password must be at least 8 characters long.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Confirm Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
                        <input type="password" name="confirm_password" placeholder="********" required
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-slate-700">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
    <?php include ROOT_PATH . '/includes/footer.php'; ?>
</body>

</html>