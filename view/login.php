<?php
include '../actions/login-action.php';
require_once '../functions/auth-check.php';
redirectIfAuthenticated();

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <meta name = "viewport" content = "width=device-width, initial-scale = 1">
        <link rel = "stylesheet" href = "../assets/css/style.css">
        <link rel = "stylesheet" href = "../assets/css/signup-login-style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    </head>

    <body>
        <?php include 'navbar.php'; ?>
        

        <section class="signup-login">
            <form class="form" method="post" action="../actions/login-action.php">
                <h1>Login</h1>
                
                <div class = "input-field">     
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <span class="text-danger"><?= $username_error ?></span>

                <div class = "input-field">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <span class="text-danger"><?= $password_error ?></span>

                <div class="forgot-pass">
                    <a href="forgot-password.php"> I forgot my password </a>
                </div>

                <button type = "submit" class = "button">Log In</button>
                
                <div class="switch-login-signup">
                    <p>Don't have an account? <a href="signup.php"> Sign up here</a></p>
                </div>
            </form>
        </section>

        
        <script src="../assets/js/login-validation.js"></script>
    </body>
</html>