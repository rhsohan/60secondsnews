-- Create the Database
CREATE DATABASE IF NOT EXISTS `60secnews` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `60secnews`;

-- 1. Roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `login_attempts` INT DEFAULT 0,
    `lockout_time` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

-- 3. Role Permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_string` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`role_id`, `permission_string`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
);

-- 4. Categories
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Media
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `folder` VARCHAR(100) DEFAULT 'general',
    `alt_text` VARCHAR(255),
    `uploader_id` INT,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`uploader_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- 6. Articles
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `summary` TEXT NOT NULL,
    `content` TEXT NOT NULL,
    `author_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `featured_image_id` INT DEFAULT NULL,
    `status` ENUM('draft', 'pending', 'published', 'embargoed', 'trashed') DEFAULT 'draft',
    `is_breaking` BOOLEAN DEFAULT FALSE,
    `is_pinned` BOOLEAN DEFAULT FALSE,
    `fact_checked` BOOLEAN DEFAULT FALSE,
    `publish_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    FOREIGN KEY (`featured_image_id`) REFERENCES `media`(`id`) ON DELETE SET NULL
);

-- 7. Article Versions
CREATE TABLE IF NOT EXISTS `article_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `editor_id` INT NOT NULL,
    `content_diff` TEXT,
    `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`editor_id`) REFERENCES `users`(`id`)
);

-- 8. Comments
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `user_ip` VARCHAR(45),
    `content` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
);

-- 9. Bookmarks (Session based or User ID but keeping it simple with session ID)
CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(128) NOT NULL,
    `article_id` INT NOT NULL,
    `type` ENUM('like', 'bookmark') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_bookmark` (`session_id`, `article_id`, `type`),
    FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
);

-- 10. Views
CREATE TABLE IF NOT EXISTS `views` (
    `article_id` INT NOT NULL,
    `view_date` DATE NOT NULL,
    `view_count` INT DEFAULT 1,
    PRIMARY KEY (`article_id`, `view_date`),
    FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
);

-- 11. Audit Logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    `table_name` VARCHAR(50),
    `record_id` INT,
    `ip_address` VARCHAR(45),
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- 12. Newsletter Subscribers
CREATE TABLE IF NOT EXISTS `subscribers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEED DATA
INSERT IGNORE INTO `roles` (`id`, `name`) VALUES 
(1, 'Admin'), 
(2, 'Publisher'), 
(3, 'Editor'), 
(4, 'Writer'), 
(5, 'Media');

-- Passwords are '1234567890' for admin, 'password123' for others
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`) VALUES
(1, 'sohan', 'admin@60secnews.com', '$2y$10$H2l3G.y1D.cKjE2e1E3m1.vUvF7Lq/k.2O.G.9l.\/A.y.d.Y.', 1),
(2, 'lead_publisher', 'publisher@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
(3, 'senior_editor', 'editor@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
(4, 'field_writer', 'writer@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(5, 'photo_guy', 'media@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5);

-- Categories
INSERT IGNORE INTO `categories` (`name`, `slug`) VALUES
('World', 'world'),
('Tech', 'tech'),
('Business', 'business'),
('Business News', 'business-news'),
('Science', 'science'),
('Entertainment', 'entertainment'),
('Entertainment Shows', 'entertainment-shows'),
('Shows', 'shows'),
('News', 'news');

-- Admin Permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(1, 'manage_users'), (1, 'manage_roles'), (1, 'manage_settings'),
(1, 'view_logs'), (1, 'manage_categories'),
(1, 'create_article'), (1, 'edit_any_article'), (1, 'publish_article'), (1, 'delete_article'),
(1, 'upload_media'), (1, 'manage_comments');

-- Publisher Permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(2, 'publish_article'), (2, 'edit_any_article'), (2, 'manage_categories');

-- Editor Permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(3, 'edit_any_article'), (3, 'manage_comments'), (3, 'create_article');

-- Writer Permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(4, 'create_article'), (4, 'edit_own_article');

-- Media Permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(5, 'upload_media');
