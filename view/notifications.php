<?php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDatabaseConnection();

// Function to format time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 7) {
        return date('M j, Y', strtotime($datetime));
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

// Fetch notifications
$notifications_query = "
    SELECT 
        n.*,
        b.title as book_title,
        u.username as author_name
    FROM notifications n
    JOIN books b ON n.book_id = b.book_id
    JOIN users u ON b.author = u.user_id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 50
";

$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unread count
$unread_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/notifications-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="notifications-container">
        <div class="notifications-header">
            <h1>Notifications</h1>
            <?php if ($unread_count > 0): ?>
                <button id="markAllRead" class="mark-all-read">
                    <span class="material-symbols-outlined">done_all</span>
                    Mark all as read
                </button>
            <?php endif; ?>
        </div>

        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <p>No notifications yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>"
                         data-notification-id="<?php echo $notification['notification_id']; ?>">
                        <div class="notification-icon">
                            <span class="material-symbols-outlined">
                                <?php echo $notification['type'] === 'new_chapter' ? 'book' : 'update'; ?>
                            </span>
                        </div>
                        <div class="notification-content">
                            <div class="notification-header">
                                <h3 class="book-title"><?php echo htmlspecialchars($notification['book_title']); ?></h3>
                                <span class="notification-time" title="<?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>">
                                    <?php echo timeAgo($notification['created_at']); ?>
                                </span>
                            </div>
                            <p class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </p>
                            <p class="notification-author">
                                by <?php echo htmlspecialchars($notification['author_name']); ?>
                            </p>
                        </div>
                        <a href="read.php?id=<?php echo $notification['chapter_id']; ?>" 
                           class="notification-action">
                            Read Chapter
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                <?php endforeach; ?>

                <?php if (count($notifications) >= 50): ?>
                    <div class="load-more">
                        <button id="loadMoreNotifications" class="load-more-btn">
                            Load More
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const markAllReadBtn = document.getElementById('markAllRead');
        
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', async function() {
                try {
                    const response = await fetch('../actions/mark-notifications-read.php', {
                        method: 'POST'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove all unread classes
                        document.querySelectorAll('.notification-item.unread')
                            .forEach(item => item.classList.remove('unread'));
                        
                        // Hide the mark all read button
                        markAllReadBtn.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error marking notifications as read:', error);
                }
            });
        }

        // Mark individual notification as read when clicking "Read Chapter"
        document.querySelectorAll('.notification-action').forEach(link => {
            link.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                if (notificationItem && notificationItem.classList.contains('unread')) {
                    const notificationId = notificationItem.dataset.notificationId;
                    
                    fetch('../actions/mark-notification-read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            notification_id: notificationId
                        })
                    });
                }
            });
        });

        // Load more notifications
        const loadMoreBtn = document.getElementById('loadMoreNotifications');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', async function() {
                const offset = document.querySelectorAll('.notification-item').length;
                
                try {
                    const response = await fetch(`../actions/get-notifications.php?offset=${offset}`);
                    const data = await response.json();
                    
                    if (data.success && data.notifications.length > 0) {
                        // Append new notifications
                        const notificationsList = document.querySelector('.notifications-list');
                        data.notifications.forEach(notification => {
                            // Create and append notification elements
                            const notificationElement = createNotificationElement(notification);
                            notificationsList.insertBefore(notificationElement, loadMoreBtn.parentElement);
                        });

                        // Hide load more button if no more notifications
                        if (data.notifications.length < 50) {
                            loadMoreBtn.parentElement.style.display = 'none';
                        }
                    }
                } catch (error) {
                    console.error('Error loading more notifications:', error);
                }
            });
        }

        function createNotificationElement(notification) {
            // Create notification element (similar to PHP template)
            const div = document.createElement('div');
            div.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
            div.dataset.notificationId = notification.notification_id;
            
            // Add notification content (icon, message, action button)
            div.innerHTML = `
                <div class="notification-icon">
                    <span class="material-symbols-outlined">
                        ${notification.type === 'new_chapter' ? 'book' : 'update'}
                    </span>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h3 class="book-title">${notification.book_title}</h3>
                        <span class="notification-time">${timeAgo(notification.created_at)}</span>
                    </div>
                    <p class="notification-message">${notification.message}</p>
                    <p class="notification-author">by ${notification.author_name}</p>
                </div>
                <a href="read.php?id=${notification.chapter_id}" class="notification-action">
                    Read Chapter
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            `;
            
            return div;
        }

        function timeAgo(datetime) {
            const now = new Date();
            const past = new Date(datetime);
            const diff = Math.floor((now - past) / 1000);

            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
            
            return past.toLocaleDateString();
        }
    });
    </script>
</body>
</html>