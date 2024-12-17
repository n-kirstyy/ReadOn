-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2024 at 11:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `readon`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(4) NOT NULL,
  `title` varchar(20) NOT NULL,
  `description` varchar(400) DEFAULT NULL,
  `author` int(4) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `genre` int(4) NOT NULL,
  `is_published` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `description`, `author`, `cover`, `genre`, `is_published`, `created_at`, `updated_at`) VALUES
(5, 'Holiday Magic', 'Read to Find Out', 1, '../assets/images/covers/cover_6760869a4578c.jpg', 1, 0, '2024-12-16 19:59:22', '2024-12-17 18:15:28'),
(6, 'As I get Older', 'A Story of Life Lessons.', 1, '../assets/images/covers/cover_6760891e915fc.jpg', 1, 1, '2024-12-16 20:03:41', '2024-12-16 20:03:41'),
(7, 'Story of a star', 'Once upon a time....', 4, '../assets/images/covers/cover_6761dc779521d.jpg', 1, 1, '2024-12-17 20:17:59', '2024-12-17 20:17:59'),
(8, 'Across the Void', '', 1, '../assets/images/covers/cover_6761e3a520d06.jpg', 4, 0, '2024-12-17 20:48:37', '2024-12-17 20:50:01');

-- --------------------------------------------------------

--
-- Table structure for table `book_likes`
--

CREATE TABLE `book_likes` (
  `like_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_likes`
--

INSERT INTO `book_likes` (`like_id`, `book_id`, `user_id`) VALUES
(3, 6, 1),
(4, 7, 4);

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `chapter_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `number` int(4) NOT NULL,
  `title` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `is_published` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`chapter_id`, `book_id`, `number`, `title`, `content`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'My Journey', '\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"\n\n1914 translation by H. Rackham\n\"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?\"', 1, '2024-12-16 20:17:17', '2024-12-16 20:17:17'),
(2, 6, 2, 'My Journey Pt 2', 'Section 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC\n\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"\n\n1914 translation by H. Rackham\n\"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?\"', 1, '2024-12-16 20:18:01', '2024-12-16 20:18:01'),
(4, 5, 1, 'Meh', '......', 0, '2024-12-16 20:50:20', '2024-12-16 20:50:20'),
(5, 6, 3, 'My Journey Pt3', '<May>', 1, '2024-12-17 20:03:44', '2024-12-17 20:03:44'),
(7, 7, 1, 'How it began', 'The standard Lorem Ipsum passage, used since the 1500s\n\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"\n\nSection 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC\n\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"', 1, '2024-12-17 20:19:05', '2024-12-17 20:19:05'),
(8, 7, 2, 'How it ended', 'The standard Lorem Ipsum passage, used since the 1500s\n\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"\n\nSection 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC\n\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"', 1, '2024-12-17 20:19:19', '2024-12-17 20:19:19'),
(9, 7, 3, 'The Journey Between', 'The standard Lorem Ipsum passage, used since the 1500s\n\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"\n\nSection 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC\n\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"', 1, '2024-12-17 20:19:33', '2024-12-17 20:19:33'),
(10, 8, 1, 'Starry Night', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', 0, '2024-12-17 20:49:35', '2024-12-17 20:49:35'),
(11, 7, 4, 'So they say', 'The standard Lorem Ipsum passage, used since the 1500s\n\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"\n\nSection 1.10.32 of \"de Finibus Bonorum et Malorum\", written by Cicero in 45 BC\n\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"', 1, '2024-12-17 21:03:33', '2024-12-17 21:03:33');

--
-- Triggers `chapters`
--
DELIMITER $$
CREATE TRIGGER `before_insert_chapters` BEFORE INSERT ON `chapters` FOR EACH ROW BEGIN
    DECLARE book_status BOOLEAN;
    
    SELECT is_published INTO book_status
    FROM books
    WHERE book_id = NEW.book_id;
    
    IF book_status = 0 AND NEW.is_published = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot publish chapter in unpublished book';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_update_chapters` BEFORE UPDATE ON `chapters` FOR EACH ROW BEGIN
    DECLARE book_status BOOLEAN;
    
    SELECT is_published INTO book_status
    FROM books
    WHERE book_id = NEW.book_id;
    
    IF book_status = 0 AND NEW.is_published = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot publish chapter in unpublished book';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_comments`
--

CREATE TABLE `chapter_comments` (
  `comment_id` int(4) NOT NULL,
  `chapter_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `parent_id` int(4) DEFAULT NULL,
  `comment` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapter_comments`
--

INSERT INTO `chapter_comments` (`comment_id`, `chapter_id`, `user_id`, `parent_id`, `comment`, `created_at`) VALUES
(1, 2, 4, NULL, 'Omg soo good', '2024-12-17 13:11:48'),
(2, 2, 1, 1, 'Thanks', '2024-12-17 13:25:50'),
(3, 2, 4, 1, '@althaea sure', '2024-12-17 13:52:43'),
(4, 1, 4, NULL, 'Hey. Have fun reading', '2024-12-17 20:13:55'),
(5, 1, 4, 4, 'I meant I had fun reading', '2024-12-17 20:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(4) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(1, 'Fantasy'),
(2, 'Non-Fiction'),
(3, 'Romance'),
(4, 'Sci-fi');

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

CREATE TABLE `library` (
  `entry_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library`
--

INSERT INTO `library` (`entry_id`, `user_id`, `book_id`, `added_at`) VALUES
(4, 1, 6, '2024-12-17 13:41:29'),
(5, 4, 6, '2024-12-17 19:45:48'),
(6, 4, 7, '2024-12-17 20:19:42'),
(7, 1, 7, '2024-12-17 21:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `list_books`
--

CREATE TABLE `list_books` (
  `item_id` int(4) NOT NULL,
  `list_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `list_books`
--

INSERT INTO `list_books` (`item_id`, `list_id`, `book_id`, `added_at`) VALUES
(6, 2, 5, '2024-12-17 13:56:05'),
(9, 5, 6, '2024-12-17 18:23:33'),
(11, 2, 6, '2024-12-17 20:02:36'),
(12, 5, 7, '2024-12-17 20:19:46'),
(13, 7, 7, '2024-12-17 20:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `chapter_id` int(4) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `setting_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `notify_updates` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reading_lists`
--

CREATE TABLE `reading_lists` (
  `list_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading_lists`
--

INSERT INTO `reading_lists` (`list_id`, `user_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(2, 1, 'Fun!!', 'Here For a Good Read!!', '2024-12-16 12:04:07', '2024-12-16 12:04:07'),
(5, 4, 'Best reads', 'You can finish these in a sitting', '2024-12-17 18:23:04', '2024-12-17 18:23:04'),
(7, 4, 'Fantasy', 'My fave fantasy books', '2024-12-17 20:20:32', '2024-12-17 20:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `reading_progress`
--

CREATE TABLE `reading_progress` (
  `progress_id` int(4) NOT NULL,
  `user_id` int(4) NOT NULL,
  `book_id` int(4) NOT NULL,
  `chapter_id` int(4) NOT NULL,
  `last_read` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading_progress`
--

INSERT INTO `reading_progress` (`progress_id`, `user_id`, `book_id`, `chapter_id`, `last_read`) VALUES
(1, 4, 6, 2, '2024-12-17 20:14:44'),
(7, 1, 6, 1, '2024-12-17 20:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(4) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_joined` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `role` int(4) NOT NULL DEFAULT 1,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `date_joined`, `bio`, `profile`, `role`, `password_reset_token`, `password_reset_expires`) VALUES
(1, 'althaea', 'thea@gmail.com', '$2y$10$K.dIwHzX6jzWfx1k0wGlVeey6y9BNUD6s4tu9A9J6VMqt9AoPagCy', '2024-12-15 19:07:52', 'May I live beautifully', '../assets/images/profiles/profile_6761e20fc0112.jpg', 2, NULL, NULL),
(3, 'dream', 'dream@gmail.com', '$2y$10$SAQWmhe9OpptUL/A8E0.DefUGzpRAeQEPRyrHbGUvKagKZ1J7kCFa', '2024-12-16 16:41:47', NULL, '../assets/images/profiles/profile_67609aa30c8e6.jpg', 1, NULL, NULL),
(4, 'haley', 'haley@gmail.com', '$2y$10$4G3D1t5dxIDZGr29UA4WQu4kfMnXUdSkoitqmM/vCqP91Sh.vWTiy', '2024-12-17 13:56:08', 'Hiii! I\'m Haley.\nI love reading (Obviously).\nCheck out my reading lists. ', '../assets/images/profiles/profile_6761755d5d784.jpg', 1, NULL, NULL),
(5, 'kirsty', 'a.dancing.dream1@gmail.com', '$2y$10$yEl712xl6mcisje60EGZbO.ByRVPMj8xJWfTlvn/mac5gFWAUSz2i', '2024-12-17 21:23:58', NULL, NULL, 1, '3129edab4d8d01332a848e4e30d348cee5f8bb8a0a2d1641da935c9abba59780', '2024-12-17 22:28:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `valid_author` (`author`),
  ADD KEY `valid_genre` (`genre`);

--
-- Indexes for table `book_likes`
--
ALTER TABLE `book_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `valid_book2` (`book_id`),
  ADD KEY `valid_user` (`user_id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`chapter_id`),
  ADD KEY `valid_book` (`book_id`);

--
-- Indexes for table `chapter_comments`
--
ALTER TABLE `chapter_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `valid_chapter` (`chapter_id`),
  ADD KEY `valid_user2` (`user_id`),
  ADD KEY `response_chk` (`parent_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`);

--
-- Indexes for table `library`
--
ALTER TABLE `library`
  ADD PRIMARY KEY (`entry_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`book_id`),
  ADD KEY `valid_book3` (`book_id`);

--
-- Indexes for table `list_books`
--
ALTER TABLE `list_books`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `list_id_3` (`list_id`,`book_id`),
  ADD KEY `valid_book4` (`book_id`),
  ADD KEY `list_id` (`list_id`,`book_id`),
  ADD KEY `list_id_2` (`list_id`,`book_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_notifications` (`user_id`,`is_read`,`created_at`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `unique_user_book` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `reading_lists`
--
ALTER TABLE `reading_lists`
  ADD PRIMARY KEY (`list_id`),
  ADD KEY `valid_user4` (`user_id`);

--
-- Indexes for table `reading_progress`
--
ALTER TABLE `reading_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_user_book` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_chk` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `book_likes`
--
ALTER TABLE `book_likes`
  MODIFY `like_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `chapter_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `chapter_comments`
--
ALTER TABLE `chapter_comments`
  MODIFY `comment_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `library`
--
ALTER TABLE `library`
  MODIFY `entry_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `list_books`
--
ALTER TABLE `list_books`
  MODIFY `item_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `setting_id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reading_lists`
--
ALTER TABLE `reading_lists`
  MODIFY `list_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reading_progress`
--
ALTER TABLE `reading_progress`
  MODIFY `progress_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `valid_author` FOREIGN KEY (`author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_genre` FOREIGN KEY (`genre`) REFERENCES `genres` (`genre_id`);

--
-- Constraints for table `book_likes`
--
ALTER TABLE `book_likes`
  ADD CONSTRAINT `valid_book2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `valid_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `chapter_comments`
--
ALTER TABLE `chapter_comments`
  ADD CONSTRAINT `response_chk` FOREIGN KEY (`parent_id`) REFERENCES `chapter_comments` (`comment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`chapter_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_user2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `library`
--
ALTER TABLE `library`
  ADD CONSTRAINT `valid_book3` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_user3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `list_books`
--
ALTER TABLE `list_books`
  ADD CONSTRAINT `valid_book4` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `valid_list` FOREIGN KEY (`list_id`) REFERENCES `reading_lists` (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`chapter_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_settings_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `reading_lists`
--
ALTER TABLE `reading_lists`
  ADD CONSTRAINT `valid_user4` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reading_progress`
--
ALTER TABLE `reading_progress`
  ADD CONSTRAINT `reading_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_progress_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_progress_ibfk_3` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`chapter_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
