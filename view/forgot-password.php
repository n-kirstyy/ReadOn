<?php
session_start();

// Database connection details
require_once '../db/db-config.php';
$conn = getDatabaseConnection();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the email address from the form
    $email = $_POST['email'];

    // Check if the email exists in the database
    $sql = "SELECT user_id, email, password_reset_token, password_reset_expires FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Generate a unique password reset token
        $password_reset_token = bin2hex(random_bytes(32));
        $password_reset_expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update the password reset token and expiration date in the database
        $sql = "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $password_reset_token, $password_reset_expires, $row['user_id']);
        $stmt->execute();

        // Send the password reset email
        $to = $row['email'];
        $subject = "Password Reset";
        $message = "Click the following link to reset your password:\n\n";
        $message .= "http://localhost/readon/view/reset-password.php?token=" . $password_reset_token;
        $headers = "From: noreply@readon.com";

        ini_set('SMTP', 'smtp.gmail.com');
        ini_set('smtp_port', 587);
        ini_set('sendmail_from', 'noreply@readon.com');

        if (mail($to, $subject, $message, $headers)) {
            echo "Password reset instructions have been sent to your email address.";
        } else {
            echo "Failed to send password reset email.";
        }
    } else {
        echo "No user found with the provided email address.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/signup-login-style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="signup-login">
        <form method="post">
            <h1>Forgot Password</h1>
            <div class="input-field">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <button type="submit" class="button">Reset Password</button>
            <div class="switch-login-signup">
                <p>Remember your password? <a href="login.php">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>