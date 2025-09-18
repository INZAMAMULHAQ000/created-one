<?php
session_start();

// Check if OTP data exists in session
if (!isset($_SESSION['otp_data']) || empty($_SESSION['otp_data']['email'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Please request an OTP first.'];
    header("location: forgot_password.php");
    exit;
}

$email = $_SESSION['otp_data']['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST["otp"]);

    $stored_otp = $_SESSION['otp_data']['otp'];
    $otp_expiry = $_SESSION['otp_data']['expiry'];
    $user_id = $_SESSION['otp_data']['user_id'];

    if (time() > $otp_expiry) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'OTP has expired. Please request a new one.'];
        unset($_SESSION['otp_data']); // Clear expired OTP
        header("location: forgot_password.php");
        exit;
    }

    if ($entered_otp === $stored_otp) {
        // OTP is valid, store user_id in session for password reset
        $_SESSION['reset_user_id'] = $user_id;
        unset($_SESSION['otp_data']); // Clear OTP data after successful verification
        $_SESSION['message'] = ['type' => 'success', 'text' => 'OTP verified successfully. You can now reset your password.'];
        header("location: reset_password.php");
        exit;
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid OTP. Please try again.'];
        header("location: verify_otp.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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
        <h2 class="card-title text-center main-text mb-4">Verify OTP</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message-box ' . $_SESSION['message']['type'] . '">' . $_SESSION['message']['text'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="otp" class="form-label main-text">Enter OTP sent to <?php echo htmlspecialchars($email); ?>:</label>
                <input type="text" name="otp" id="otp" class="form-control" required maxlength="6" pattern="[0-9]{6}" title="Please enter a 6-digit OTP.">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-accent btn-lg">Verify OTP</button>
            </div>
            <p class="text-center mt-3 main-text"><a href="forgot_password.php">Request a new OTP</a></p>
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