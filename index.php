<?php
require_once 'functions/auth-check.php';
redirectIfAuthenticated();
?>

<!DOCTYPE html>
<html lang ="eng">

<head>
    <title>ReadOn</title>
    <meta name = "viewport" content = "width=device-width, initial-scale = 1">
        <link rel = "stylesheet" href = "assets/css/style.css">
        <link rel = "stylesheet" href = "assets/css/index-style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>


<body>
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
                            <a href="#">Notifications</a>
                            <a href="logout.php">Log Out</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="view/login.php">Login</a>
                    <a href="view/signup.php">Sign Up</a>
                </div>
            <?php endif; ?>
        </nav>

    </header>
    <section class = "main">
        <div>
            <h1>ReadOn: <br><span>Your Free Book Community</span></h1>
            <p class = "intro_para">
                Welcome to ReadOn where you can find a community of<br>
                readers and aspiring authors<br>
                We started ReadOn as a way to connect readers to free books<br>
                and authors to willing audiences and it has been a lovely journey.
                <br>
                <br>
                Whether you are a reader looking for your next obsession,
                or an author looking for an audience, we've got you covered. What are you waiting for? 
            </p>

            <a href = "view/signup.php">Join Us Now</a>
        </div>

    </section>

</body>


