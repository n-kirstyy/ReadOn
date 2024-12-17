<?php
session_start();
include '../db/db-config.php'; 
$dbConnection = getDatabaseConnection();

$username = "";
$password = "";

$username_error = "";
$password_error = "";

$error = false;
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)){
        $username_error = "Username is required";
        $error = true;
    } 
    if (empty($password)) {
        $password_error = "Password is required";
        $error = true;
    }
    if (!$error) {
        try {
            $dbConnection = getDatabaseConnection();

            // Query to fetch user details
            $query = "SELECT user_id, username, password, role FROM users WHERE username = ?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Regenerate session and set session variables
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                } else {
                    $password_error = "Invalid password.";
                }
            } else {
                $username_error = "Invalid username or password.";
            }
            $stmt->close();
            $dbConnection->close();
        } catch (Exception $e) {
            die("Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}


?>