<?php
include("../db/db-config.php");
$dbConnection = getDatabaseConnection();

session_start();

$username = "";
$email = "";

$username_error = "";
$email_error = "";
$password_error = "";
$confirm_password_error = "";

$error = false;
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate username: must only contain letters, numbers, underscores, and periods
    if (empty($username)) {
        $username_error = "Username is required";
        $error = true;
    } elseif (!preg_match("/^[a-zA-Z0-9_\.]+$/", $username)) {
        $username_error = "Username can only contain letters, numbers, underscores, and periods";
        $error = true;
    } elseif (strlen($username) < 3 || strlen($username) > 20) { // Check username length
        $username_error = "Username must be between 3 and 20 characters long";
        $error = true;
    }

    // Check if username already exists
    if (!$error) {
        $statement = $dbConnection->prepare("SELECT user_id FROM users WHERE username = ?");
        $statement->bind_param("s", $username);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows > 0) {
            $username_error = "Username is already taken";
            $error = true;
        }
        $statement->close();
    }

    // Validate email: must be a valid email format
    if (empty($email)) {
        $email_error = "Email is required";
        $error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Email format is not valid";
        $error = true;
    }

    // Check if email already exists
    if (!$error) {
        $statement = $dbConnection->prepare("SELECT user_id FROM users WHERE email = ?");
        $statement->bind_param("s", $email);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows > 0) {
            $email_error = "Email is already used";
            $error = true;
        }
        $statement->close();
    }

    // Validate password: must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number
    if (empty($password)) {
        $password_error = "Password is required";
        $error = true;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $password)) {
        $password_error = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number";
        $error = true;
    }

    // Validate confirm password: must match the password
    if ($confirm_password != $password) {
        $confirm_password_error = "Password and Confirm Password do not match";
        $error = true;
    }

    if (!$error) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $date_joined = date('Y-m-d H:i:s');

        $statement = $dbConnection->prepare(
            "INSERT INTO users (username, email, password, date_joined) VALUES (?, ?, ?, ?)"
        );
        $statement->bind_param('ssss', $username, $email, $hashed_password, $date_joined);
        $statement->execute();
        $insert_id = $statement->insert_id;
        $statement->close();

        // Store user data in session
        $_SESSION["user_id"] = $insert_id;
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["date_joined"] = $date_joined;

        $successMessage = "User registered successfully.";
        header("location: ../view/login.php");
        exit;
    }
}
?>
