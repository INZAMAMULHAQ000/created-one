<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

$username = $_SESSION['username'];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Fetch current hashed password from database
    $sql = "SELECT password FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $hashed_password = $user['password'];

        // Verify old password
        if (password_verify($old_password, $hashed_password)) {
            // Check if new passwords match
            if ($new_password === $confirm_new_password) {
                // Hash the new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in database
                $update_sql = "UPDATE users SET password = '$new_hashed_password' WHERE username = '$username'";
                if (mysqli_query($conn, $update_sql)) {
                    $message = "Password changed successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating password: " . mysqli_error($conn);
                    $message_type = "danger";
                }
            } else {
                $message = "New password and confirm password do not match.";
                $message_type = "danger";
            }
        } else {
            $message = "Incorrect old password.";
            $message_type = "danger";
        }
    } else {
        $message = "User not found."; // Should not happen if session is valid
        $message_type = "danger";
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
            --form-focus-bg: #ffffff;
            --form-focus-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }

        body.dark-theme {
            --background-color: #212529; /* Dark gray */
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
            --form-focus-bg: #495057;
            --form-focus-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column; /* To allow footer at bottom */
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .container {
            padding: 2rem;
        }
        .password-form {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        .main-text {
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .form-control {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus {
            background: var(--form-focus-bg);
            border-color: var(--accent-color);
            box-shadow: var(--form-focus-shadow);
            color: var(--text-color);
        }
        .btn-accent {
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: #fff;
            transition: all 0.3s ease;
        }
        .btn-accent:hover {
            background: var(--accent-color);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .navbar {
            background-color: var(--form-bg);
            border-bottom: 1px solid var(--form-border);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
        }
        .navbar-brand,
        .nav-link {
            font-family: 'Segoe UI', Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: bold;
            color: var(--text-color) !important;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        /* Dynamic Bottom-Left Logo Container */
        #dynamicLogoContainer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 150px;
            height: 150px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0.1;
            transition: opacity 0.5s ease-in-out;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        #dynamicLogoContainer img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            border-radius: 50%;
        }

    </style>
</head>
<body class="dark-theme">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand main-text" href="billing.php">
                <img src="Sun.jpeg" alt="Company Logo" style="height: 90px; margin-right: 10px; vertical-align: middle;">
                Madhu PaperBags
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="materials.php">Manage Materials</a>
                <a class="nav-link" href="transport.php">Manage Transport</a>
                <a class="nav-link" href="change_password.php">Change Password</a>
                <button id="themeToggle" class="btn btn-secondary ms-2">Toggle Theme</button>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container password-form mt-5">
        <h2 class="text-center mb-4 main-text">Change Password</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> text-center" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="change_password.php" method="post">
            <div class="mb-3">
                <label for="old_password" class="form-label">Old Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
                <div id="oldPasswordFeedback" class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
                <div id="newPasswordFeedback" class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                <div id="confirmPasswordFeedback" class="invalid-feedback"></div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-accent btn-lg">Change Password</button>
            </div>
            <p class="text-center mt-3 main-text">Forgot your old password? <a href="forgot_password.php">Reset it here</a>.</p>
        </form>
    </div>

    <div id="dynamicLogoContainer">
        <img src="logo.png" alt="Company Logo">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Dynamic Logo Logic (copied from billing.php)
            let idleTimeout;
            const dynamicLogoContainer = $('#dynamicLogoContainer');
            const idleTime = 2000; // 2 seconds of inactivity before hiding

            function showLogo() {
                dynamicLogoContainer.css('opacity', '1');
                dynamicLogoContainer.css('pointer-events', 'auto');
                clearTimeout(idleTimeout);
                idleTimeout = setTimeout(hideLogo, idleTime);
            }

            function hideLogo() {
                dynamicLogoContainer.css('opacity', '0.1'); // Faded out
                dynamicLogoContainer.css('pointer-events', 'none');
            }

            // Set up event listeners for user activity
            $(document).on('mousemove scroll touchstart', showLogo);

            // Initial hide after page load
            idleTimeout = setTimeout(hideLogo, idleTime);

            // Theme Toggle Logic (copied from billing.php)
            $('#themeToggle').on('click', function() {
                $('body').toggleClass('light-theme dark-theme');
                // Save preference to localStorage
                if ($('body').hasClass('light-theme')) {
                    localStorage.setItem('theme', 'light');
                } else {
                    localStorage.setItem('theme', 'dark');
                }
            });

            // Load theme preference on page load
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                $('body').removeClass('light-theme dark-theme').addClass(savedTheme + '-theme');
            } else {
                // Default to dark if no preference saved
                $('body').addClass('dark-theme');
            }

            // --- Real-time Password Validation ---

            const oldPasswordInput = $('#old_password');
            const newPasswordInput = $('#new_password');
            const confirmNewPasswordInput = $('#confirm_new_password');
            const changePasswordForm = $('form');
            const submitButton = changePasswordForm.find('button[type="submit"]');

            let isOldPasswordValid = false;
            let isNewPasswordValid = false;
            let isConfirmPasswordValid = false;

            function validateForm() {
                submitButton.prop('disabled', !(isOldPasswordValid && isNewPasswordValid && isConfirmPasswordValid));
            }

            // Old Password Validation (AJAX)
            let oldPasswordTimer;
            oldPasswordInput.on('input', function() {
                clearTimeout(oldPasswordTimer);
                const oldPassword = $(this).val();
                const feedbackElement = $('#oldPasswordFeedback');

                if (oldPassword.length === 0) {
                    feedbackElement.text('Please enter your old password.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                    $(this).removeClass('is-valid').addClass('is-invalid');
                    isOldPasswordValid = false;
                    validateForm();
                    return;
                }

                oldPasswordTimer = setTimeout(function() {
                    $.ajax({
                        url: 'verify_old_password.php',
                        type: 'POST',
                        data: { old_password: oldPassword },
                        dataType: 'json',
                        success: function(response) {
                            if (response.valid) {
                                feedbackElement.text('Old password is correct.').removeClass('invalid-feedback').addClass('valid-feedback d-block');
                                oldPasswordInput.removeClass('is-invalid').addClass('is-valid');
                                isOldPasswordValid = true;
                            } else {
                                feedbackElement.text('Incorrect old password.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                                oldPasswordInput.removeClass('is-valid').addClass('is-invalid');
                                isOldPasswordValid = false;
                            }
                            validateForm();
                        },
                        error: function() {
                            feedbackElement.text('Error verifying old password.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                            oldPasswordInput.removeClass('is-valid').addClass('is-invalid');
                            isOldPasswordValid = false;
                            validateForm();
                        }
                    });
                }, 500); // Debounce for 500ms
            });

            // New Password Validation
            newPasswordInput.on('input', function() {
                const newPassword = $(this).val();
                const feedbackElement = $('#newPasswordFeedback');

                if (newPassword.length < 6) {
                    feedbackElement.text('Password must be at least 6 characters long.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                    $(this).removeClass('is-valid').addClass('is-invalid');
                    isNewPasswordValid = false;
                } else {
                    feedbackElement.text('Password is strong enough.').removeClass('invalid-feedback').addClass('valid-feedback d-block');
                    $(this).removeClass('is-invalid').addClass('is-valid');
                    isNewPasswordValid = true;
                }
                validateConfirmPassword(); // Re-validate confirm password whenever new password changes
                validateForm();
            });

            // Confirm New Password Validation
            confirmNewPasswordInput.on('input', validateConfirmPassword);

            function validateConfirmPassword() {
                const newPassword = newPasswordInput.val();
                const confirmPassword = confirmNewPasswordInput.val();
                const feedbackElement = $('#confirmPasswordFeedback');

                if (confirmPassword.length === 0) {
                    feedbackElement.text('Please confirm your new password.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                    confirmNewPasswordInput.removeClass('is-valid').addClass('is-invalid');
                    isConfirmPasswordValid = false;
                } else if (newPassword !== confirmPassword) {
                    feedbackElement.text('Passwords do not match.').removeClass('valid-feedback').addClass('invalid-feedback d-block');
                    confirmNewPasswordInput.removeClass('is-valid').addClass('is-invalid');
                    isConfirmPasswordValid = false;
                } else {
                    feedbackElement.text('Passwords match.').removeClass('invalid-feedback').addClass('valid-feedback d-block');
                    confirmNewPasswordInput.removeClass('is-invalid').addClass('is-valid');
                    isConfirmPasswordValid = true;
                }
                validateForm();
            }

            // Disable submit button on initial load
            validateForm();
        });
    </script>
</body>
</html> 