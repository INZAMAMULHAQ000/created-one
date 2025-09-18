<?php
session_start();
require_once "config/database.php";

require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Validate email format (basic)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid email format.'];
        header("location: forgot_password.php");
        exit;
    }

    // Check if email exists in the database
    $sql = "SELECT id, username FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $username);
                mysqli_stmt_fetch($stmt);

                // Generate OTP (6 digits)
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otp_expiry = time() + (5 * 60); // OTP valid for 5 minutes

                // Store OTP in session (in a real app, you'd store this in a database table with user_id)
                $_SESSION['otp_data'] = [
                    'email' => $email,
                    'otp' => $otp,
                    'expiry' => $otp_expiry,
                    'user_id' => $user_id
                ];

                // Send OTP email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'ssenterpriseserp@gmail.com';
                    $mail->Password = 'pqrylezrrjqzuqkw';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('ssenterpriseserp@gmail.com', 'SS Enterprises');
                    $mail->addAddress($email, $username);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Password Reset';
                    $mail->Body    = 'Your OTP is: <b>' . $otp . '</b>. It is valid for 5 minutes.';

                    $mail->send();
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'An OTP has been sent to ' . htmlspecialchars($email) . '. Please check your inbox.'];
                } catch (Exception $e) {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Mailer Error: ' . $mail->ErrorInfo . ' (Exception: ' . $e->getMessage() . ')'];
                }
                
                header("location: verify_otp.php");
                exit;

            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Email address not found.'];
                header("location: forgot_password.php");
                exit;
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Oops! Something went wrong. Please try again later.'];
            header("location: forgot_password.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        <h2 class="card-title text-center main-text mb-4">Forgot Password</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message-box ' . $_SESSION['message']['type'] . '">' . $_SESSION['message']['text'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="email" class="form-label main-text">Enter your Email ID:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-accent btn-lg">Send OTP</button>
            </div>
            <p class="text-center mt-3 main-text">Remembered your password? <a href="index.php">Login here</a>.</p>
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