<?php
session_start();

// Database connection details
require_once '../db/db-config.php';
$conn = getDatabaseConnection();

// Check if the token is present in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database and is not expired
    $sql = "SELECT user_id, password_reset_expires FROM users WHERE password_reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the token has expired
        if (strtotime($row['password_reset_expires']) > time()) {
            // Token is valid, display the password reset form
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Retrieve the new password from the form
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Validate the new password
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database
                    $sql = "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $hashed_password, $row['user_id']);
                    $stmt->execute();

                    echo "Your password has been reset successfully. You can now <a href='login.php'>login</a> with your new password.";
                } else {
                    echo "Passwords do not match.";
                }
            } else {
                // Display the password reset form
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Reset Password</title>
                    <link rel="stylesheet" href="../assets/css/signup-login-style.css">
                </head>
                <body>
                    <?php include 'navbar.php'; ?>
                    <div class="signup-login">
                        <form method="post">
                            <h1>Reset Password</h1>
                            <div class="input-field">
                                <input type="password" name="new_password" placeholder="New Password" required>
                            </div>
                            <div class="input-field">
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                            </div>
                            <button type="submit" class="button">Reset Password</button>
                        </form>
                    </div>
                </body>
                </html>
                <?php
            }
        } else {
            echo "Password reset token has expired. Please request a new one.";
        }
    } else {
        echo "Invalid password reset token.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Password reset token not found.";
}
?>