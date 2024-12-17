<?php
// Get unread notifications count if user is logged in
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $conn = getDatabaseConnection();
    $count_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_assoc()['count'];
}
?>

<header class="topnav">
    <h5 id="logo">Read<span>On</span></h5>
    <nav class="bar" id="mainNav">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="nav-links">
                <a href="all-books.php">Browse</a>
                <a href="write.php">Write</a>
                <div class="account-container">
                    <button class="account" id="accountBtn">
                        <img src="<?php echo htmlspecialchars($_SESSION['profile'] ?? '../assets/images/default-pfp.jpg'); ?>" 
                            alt="Profile" 
                            class="profile-pic-nav">
                        <span class="material-symbols-outlined">expand_more</span>
                    </button>
                    <div class="dropdown-content" id="accountDropdown">
                        <a href="profile.php">My Profile</a>
                        <a href="library.php">Library</a>
                        <a href="notifications.php" class="notification-link">
                            Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 2): ?>
                            <a href="admin-dashboard.php">Admin Dashboard</a>
                            <a href="manage-users.php">User Management</a>
                            <a href="manage-books.php">Book Management</a>
                        <?php endif; ?>
                        <a href="logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            </div>
        <?php endif; ?>
    </nav>
</header>

<script src="../assets/js/navbar-control.js"></script>