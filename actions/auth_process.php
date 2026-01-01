<?php
require_once '../config/init.php';

// Initialize session arrays if they don't exist
if (!isset($_SESSION['errors'])) $_SESSION['errors'] = [];
if (!isset($_SESSION['old'])) $_SESSION['old'] = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Determine form type (login vs register)
    $form_type = $_POST['form_type'] ?? 'login';

    // ==========================================
    // LOGIN LOGIC
    // ==========================================
    if ($form_type === 'login') {
        
        // We now look for 'identifier' instead of 'email'
        $identifier = trim($_POST["identifier"]);
        $password = trim($_POST["password"]);
        
        $_SESSION['old']['login_id'] = $identifier;

        $has_error = false;
        if (empty($identifier)) {
            $_SESSION['errors']['login_id'] = "Please enter your email or username.";
            $has_error = true;
        }
        if (empty($password)) {
            $_SESSION['errors']['login_password'] = "Please enter your password.";
            $has_error = true;
        }

        if ($has_error) {
            header("location: " . BASE_URL . "/login.php?mode=login");
            exit;
        }

        // UPDATED SQL: Check if identifier matches Email OR Username
        $sql = "SELECT user_id, username, password, daily_goal FROM users WHERE email = ? OR username = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // We bind the same variable twice ($identifier, $identifier)
            mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password, $daily_goal);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // SUCCESS
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["username"] = $username;
                            
                            unset($_SESSION['errors']);
                            unset($_SESSION['old']);

                            // Gatekeeper Logic
                            if ($daily_goal > 0) {
                                header("Location: ../dashboard.php");
                            } else {
                                header("Location: ../personalization.php");
                            }
                            exit();
                        } else {
                            $_SESSION['errors']['login_err'] = "Invalid credentials.";
                        }
                    }
                } else {
                    $_SESSION['errors']['login_err'] = "Invalid credentials.";
                }
            } else {
                $_SESSION['errors']['login_err'] = "Oops! Something went wrong.";
            }
            mysqli_stmt_close($stmt);
        }
        
        header("location: " . BASE_URL . "/login.php?mode=login");
        exit;
    }

    // ==========================================
    // REGISTER LOGIC
    // ==========================================
    elseif ($form_type === 'register') {
        
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);

        // Save old input
        $_SESSION['old']['reg_username'] = $username;
        $_SESSION['old']['reg_email'] = $email;

        $has_error = false;

        // 1. Validate Username
        if (empty($username)) {
            $_SESSION['errors']['reg_username'] = "Please enter a username.";
            $has_error = true;
        } else {
            // Check Duplicate Username
            $sql = "SELECT user_id FROM users WHERE username = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $_SESSION['errors']['reg_username'] = "This username is already taken.";
                    $has_error = true;
                }
                mysqli_stmt_close($stmt);
            }
        }

        // 2. Validate Email
        if (empty($email)) {
            $_SESSION['errors']['reg_email'] = "Please enter an email.";
            $has_error = true;
        } else {
            // Check Duplicate Email
            $sql = "SELECT user_id FROM users WHERE email = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $_SESSION['errors']['reg_email'] = "This email is already registered.";
                    $has_error = true;
                }
                mysqli_stmt_close($stmt);
            }
        }

        // 3. Validate Password
        if (empty($password)) {
            $_SESSION['errors']['reg_password'] = "Please enter a password.";
            $has_error = true;
        } elseif (strlen($password) < 8) {
            $_SESSION['errors']['reg_password'] = "Password must be at least 8 characters.";
            $has_error = true;
        }

        // 4. Validate Confirm Password
        if (empty($confirm_password)) {
            $_SESSION['errors']['reg_confirm'] = "Please confirm password.";
            $has_error = true;
        } elseif ($password != $confirm_password) {
            $_SESSION['errors']['reg_confirm'] = "Passwords did not match.";
            $has_error = true;
        }

        // 5. Validate Terms
        if (!isset($_POST['terms'])) {
            $_SESSION['errors']['terms'] = "You must agree to the Terms & Conditions.";
            $has_error = true;
        }

        // If Error, Redirect Back
        if ($has_error) {
            header("location: " . BASE_URL . "/login.php?mode=register");
            exit;
        }

        // INSERT USER
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                // SUCCESS
                
                $new_user_id = mysqli_insert_id($conn); // Get the new ID from DB
                
                $_SESSION["user_id"] = $new_user_id;
                $_SESSION["username"] = $username;
                
                // Clear errors
                unset($_SESSION['errors']);
                unset($_SESSION['old']);

                // Redirect DIRECTLY to personalization
                header("Location: ../personalization.php");
                exit;

            } else {
                $_SESSION['errors']['login_err'] = "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
        
        header("location: " . BASE_URL . "/login.php?mode=register");
        exit;
    }
}
?>