<?php
session_start();
require_once "config/database.php";

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Fetch user from database
    $sql = "SELECT id, password FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username; // Store username in session
        header("location: billing.php");
        exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket Billing System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #000;
            --text-color: #fff;
            --neon-color: #0ff;
            --login-bg: rgba(255, 255, 255, 0.1);
            --login-box-shadow: 0 0 20px var(--neon-color), inset 0 0 20px rgba(0, 255, 255, 0.5);
            --form-control-bg: rgba(255, 255, 255, 0.1);
            --form-control-border: #0ff;
            --form-control-focus-bg: rgba(255, 255, 255, 0.2);
            --form-control-focus-shadow: 0 0 10px var(--neon-color);
        }

        body.light-theme {
            --background-color: #f0f2f5;
            --text-color: #333;
            --neon-color: #007bff;
            --login-bg: rgba(255, 255, 255, 0.8);
            --login-box-shadow: 0 0 20px rgba(0, 123, 255, 0.3), inset 0 0 20px rgba(0, 123, 255, 0.2);
            --form-control-bg: rgba(255, 255, 255, 0.9);
            --form-control-border: #007bff;
            --form-control-focus-bg: rgba(255, 255, 255, 1);
            --form-control-focus-shadow: 0 0 10px var(--neon-color);
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .login-container {
            background: var(--login-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--login-box-shadow);
            max-width: 400px;
            width: 90%;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        .neon-text {
            color: var(--text-color);
            text-shadow: 0 0 5px var(--text-color),
                         0 0 10px var(--neon-color),
                         0 0 20px var(--neon-color),
                         0 0 40px var(--neon-color);
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }
        .form-control {
            background: var(--form-control-bg);
            border: 1px solid var(--form-control-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus {
            background: var(--form-control-focus-bg);
            border-color: var(--form-control-border);
            box-shadow: var(--form-control-focus-shadow);
            color: var(--text-color);
        }
        .btn-neon {
            background: transparent;
            border: 2px solid var(--neon-color);
            color: var(--text-color);
            text-shadow: 0 0 5px var(--neon-color);
            box-shadow: 0 0 10px var(--neon-color);
            transition: all 0.3s ease;
        }
        .btn-neon:hover {
            background: var(--neon-color);
            color: var(--background-color); /* text color changes to contrast with neon */
            box-shadow: 0 0 20px var(--neon-color);
        }
    </style>
</head>
<body class="dark-theme"> <!-- Default to dark theme -->
    <div class="login-container">
        <h2 class="text-center mb-4 neon-text">Login</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="index.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label neon-text">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label neon-text">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="login" class="btn btn-neon btn-lg">Login</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <button id="themeToggle" class="btn btn-secondary btn-sm">Toggle Theme</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Theme Toggle Logic
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
        });
    </script>
</body>
</html> 