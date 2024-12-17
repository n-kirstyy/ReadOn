<?php
function redirectIfAuthenticated() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        // Redirect to all-books page
        header("Location: /ReadOn/view/all-books.php");
        exit();
    }
}
?>