<?php 
include '../actions/signup-action.php'; 
require_once '../functions/auth-check.php';
redirectIfAuthenticated();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Signup</title>
        <meta name = "viewport" content = "width=device-width, initial-scale = 1">
        <link rel = "stylesheet" href = "../assets/css/style.css">
        <link rel = "stylesheet" href = "../assets/css/signup-login-style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    </head>

    <body>
        <?php include 'navbar.php'; ?>

        <section class="signup-login">
            <form class="form" method="post">
                <h1>Signup</h1>

                <div class = "input-field">     
                    <input type="text" id="username" name="username" placeholder="Username" required value="<?= $username ?>">
                </div>
                <span class="text-danger"><?= $username_error ?></span>

                <div class = "input-field">
                    <input type="email" id="email" name="email" placeholder="Email" required value="<?= $email ?>">
                </div>
                <span class="text-danger"><?= $email_error ?></span>

                <div class = "input-field">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <span class="text-danger"><?= $password_error ?></span>

                <div class = "input-field">
                    <input type="password" id="password-confirm" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <span class="text-danger"><?= $confirm_password_error ?></span>

                <button type = "submit" class = "button">Sign Up</button>

                <div class="switch-login-signup">
                    <p>Already have an account? <a href="login.php"> Log in here</a></p>
                </div>
            </form>
        </section>

        <script src="../assets/js/signup-validation.js"></script>
    </body>
</html>