<?php
session_start();
require_once "config/database.php";

// Check if user is authorized to reset password (via OTP verification)
if (!isset($_SESSION['reset_user_id'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Unauthorized access to password reset.'];
    header("location: index.php");
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err)) {
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            $param_password = $hashed_password;
            $param_id = $user_id;

            if (mysqli_stmt_execute($stmt)) {
                // Password updated successfully. Clear session and redirect to login page.
                unset($_SESSION['reset_user_id']);
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Your password has been reset successfully. Please login with your new password.'];
                header("location: index.php");
                exit;
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Oops! Something went wrong. Please try again later.'];
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .card {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 400px;
        }
        .form-control, .form-select {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background: var(--form-focus-bg);
            border-color: var(--accent-color);
            box-shadow: var(--form-focus-shadow);
            color: var(--text-color);
        }
        .btn-accent {
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        .btn-accent:hover {
            background: var(--btn-hover-bg);
            color: var(--btn-hover-color);
            box-shadow: var(--btn-hover-shadow);
        }
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .message-box.success {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green text */
            border: 1px solid #c3e6cb; /* Green border */
        }

        .message-box.error {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red text */
            border: 1px solid #f5c6cb; /* Red border */
        }

        /* Apply theme-specific colors for message boxes */
        body.dark-theme .message-box.error {
            background-color: #721c24;
            color: #f8d7da;
            border-color: #f5c6cb;
        }

        body.dark-theme .message-box.success {
            background-color: #155724;
            color: #d4edda;
            border-color: #c3e6cb;
        }

        body.dark-theme {
            --background-color: #212529; /* Dark gray for the main page background */
            --text-color: #e2e6ea;
            --accent-color: #66b3ff; /* Lighter blue for dark theme */
            --form-bg: #495057;
            --form-border: #6c757d;
            --form-focus-bg: #495057;
            --form-focus-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
        }
        body.light-theme {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff; /* Professional blue for light theme */
            --form-bg: #ffffff;
            --form-border: #dee2e6;
            --form-focus-bg: #ffffff;
            --form-focus-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            --btn-text-shadow: none;
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
    </style>
</head>
<body class="dark-theme">
    <div class="card">
        <h2 class="card-title text-center main-text mb-4">Reset Password</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message-box ' . $_SESSION['message']['type'] . '">' . $_SESSION['message']['text'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3 <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                <label for="new_password" class="form-label main-text">New Password:</label>
                <input type="password" name="new_password" id="new_password" class="form-control" value="<?php echo $new_password; ?>">
                <span class="help-block" style="color: red;"><?php echo $new_password_err; ?></span>
            </div>
            <div class="mb-3 <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label for="confirm_password" class="form-label main-text">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                <span class="help-block" style="color: red;"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-accent btn-lg">Reset Password</button>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Load theme preference on page load
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            $('body').removeClass('light-theme dark-theme').addClass(savedTheme + '-theme');
        } else {
            // Default to dark if no preference saved
            $('body').addClass('dark-theme');
        }
    </script>
</body>
</html> 