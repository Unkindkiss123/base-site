-- ============================================
-- Base Site Database Schema
-- MySQL 5.7+ / MariaDB 10.4+
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS `base_site` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `base_site`;

-- ============================================
-- ROLES TABLE
-- ============================================
CREATE TABLE `roles` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) UNIQUE NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERMISSIONS TABLE
-- ============================================
CREATE TABLE `permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ROLE_PERMISSIONS JUNCTION TABLE
-- ============================================
CREATE TABLE `role_permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100),
    `last_name` VARCHAR(100),
    `phone` VARCHAR(20),
    `avatar` VARCHAR(255),
    `role_id` INT NOT NULL DEFAULT 3,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
    INDEX `idx_email` (`email`),
    INDEX `idx_username` (`username`),
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASSWORD RESETS TABLE
-- ============================================
CREATE TABLE `password_resets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) UNIQUE NOT NULL,
    `token_hash` VARCHAR(255) UNIQUE NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token_hash` (`token_hash`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CATEGORIES TABLE
-- ============================================
CREATE TABLE `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(50),
    `color` VARCHAR(7),
    `display_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAGES TABLE
-- ============================================
CREATE TABLE `pages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `content` LONGTEXT NOT NULL,
    `excerpt` VARCHAR(500),
    `featured_image` VARCHAR(255),
    `meta_title` VARCHAR(255),
    `meta_description` VARCHAR(500),
    `meta_keywords` VARCHAR(500),
    `og_image` VARCHAR(255),
    `canonical_url` VARCHAR(500),
    `author_id` INT,
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `view_count` INT DEFAULT 0,
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SERVICES TABLE
-- ============================================
CREATE TABLE `services` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` LONGTEXT NOT NULL,
    `icon` VARCHAR(255),
    `image` VARCHAR(255),
    `price` DECIMAL(10, 2),
    `category_id` INT,
    `display_order` INT DEFAULT 0,
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BLOG POSTS TABLE
-- ============================================
CREATE TABLE `blog_posts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `content` LONGTEXT NOT NULL,
    `excerpt` VARCHAR(500),
    `featured_image` VARCHAR(255),
    `category_id` INT,
    `author_id` INT NOT NULL,
    `meta_title` VARCHAR(255),
    `meta_description` VARCHAR(500),
    `meta_keywords` VARCHAR(500),
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `view_count` INT DEFAULT 0,
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GALLERY TABLE
-- ============================================
CREATE TABLE `gallery` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255),
    `category_id` INT,
    `alt_text` VARCHAR(255),
    `display_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TESTIMONIALS TABLE
-- ============================================
CREATE TABLE `testimonials` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `author_name` VARCHAR(100) NOT NULL,
    `author_title` VARCHAR(100),
    `author_image` VARCHAR(255),
    `content` TEXT NOT NULL,
    `rating` INT DEFAULT 5,
    `company` VARCHAR(100),
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FAQ TABLE
-- ============================================
CREATE TABLE `faq` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `category_id` INT,
    `question` VARCHAR(500) NOT NULL,
    `answer` LONGTEXT NOT NULL,
    `display_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LEADS TABLE
-- ============================================
CREATE TABLE `leads` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `subject` VARCHAR(255),
    `message` LONGTEXT NOT NULL,
    `source` VARCHAR(50),
    `status` ENUM('new', 'contacted', 'qualified', 'rejected', 'converted') DEFAULT 'new',
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `assigned_to` INT,
    `notes` LONGTEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_assigned_to` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SETTINGS TABLE
-- ============================================
CREATE TABLE `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    `value` LONGTEXT,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT,
    `is_public` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE `activity_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `status` ENUM('success', 'failed') DEFAULT 'success',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT ROLES
-- ============================================
INSERT INTO `roles` (`name`, `display_name`, `description`) VALUES
('super_admin', 'Super Admin', 'Full access to all system features'),
('admin', 'Administrator', 'Content and user management'),
('editor', 'Editor', 'Content creation and editing only'),
('guest', 'Guest', 'Read-only access');

-- ============================================
-- INSERT DEFAULT PERMISSIONS
-- ============================================
INSERT INTO `permissions` (`name`, `display_name`, `description`) VALUES
('manage_users', 'Manage Users', 'Create, edit, delete users'),
('manage_roles', 'Manage Roles', 'Manage user roles and permissions'),
('manage_pages', 'Manage Pages', 'Create, edit, delete pages'),
('manage_services', 'Manage Services', 'Create, edit, delete services'),
('manage_blog', 'Manage Blog', 'Create, edit, delete blog posts'),
('manage_gallery', 'Manage Gallery', 'Manage gallery images'),
('manage_testimonials', 'Manage Testimonials', 'Manage testimonials'),
('manage_faq', 'Manage FAQ', 'Manage FAQ items'),
('manage_leads', 'Manage Leads', 'View and manage leads'),
('manage_settings', 'Manage Settings', 'Configure site settings'),
('view_analytics', 'View Analytics', 'View site analytics'),
('view_logs', 'View Logs', 'View activity logs');

-- ============================================
-- ASSIGN PERMISSIONS TO SUPER_ADMIN
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT (SELECT id FROM roles WHERE name='super_admin'), id FROM permissions;

-- ============================================
-- ASSIGN PERMISSIONS TO ADMIN
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT (SELECT id FROM roles WHERE name='admin'), id FROM permissions
WHERE name NOT IN ('manage_roles', 'manage_settings');

-- ============================================
-- ASSIGN PERMISSIONS TO EDITOR
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT (SELECT id FROM roles WHERE name='editor'), id FROM permissions
WHERE name IN ('manage_pages', 'manage_services', 'manage_blog', 'manage_gallery', 'manage_testimonials', 'manage_faq');

-- ============================================
-- INSERT DEFAULT ADMIN USER
-- ============================================
INSERT INTO `users` (`email`, `username`, `password`, `first_name`, `last_name`, `role_id`, `status`, `email_verified_at`)
VALUES (
    'admin@example.com',
    'admin',
    '$2y$12$G8C3bE4Xx8/cZ9KvI8E5Oe2L8V3xR6W9P4Q5S6T7U8V9W0X1Y2Z3',
    'Admin',
    'User',
    (SELECT id FROM roles WHERE name='super_admin'),
    'active',
    NOW()
);

-- ============================================
-- INSERT DEFAULT SETTINGS
-- ============================================
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('site_name', 'Base Site', 'string', 'Website name', TRUE),
('site_description', 'Professional website template', 'string', 'Website description', TRUE),
('site_email', 'info@example.com', 'string', 'Contact email', TRUE),
('site_phone', '+1 (555) 000-0000', 'string', 'Contact phone', TRUE),
('site_address', '123 Main Street, City, State', 'string', 'Physical address', TRUE),
('facebook_url', '', 'string', 'Facebook URL', TRUE),
('twitter_url', '', 'string', 'Twitter URL', TRUE),
('instagram_url', '', 'string', 'Instagram URL', TRUE),
('linkedin_url', '', 'string', 'LinkedIn URL', TRUE),
('logo_url', '/assets/images/logo.png', 'string', 'Logo URL', TRUE),
('favicon_url', '/assets/images/favicon.ico', 'string', 'Favicon URL', TRUE);

-- ============================================
-- INSERT DEFAULT CATEGORIES
-- ============================================
INSERT INTO `categories` (`name`, `slug`, `display_order`) VALUES
('General', 'general', 1),
('Technology', 'technology', 2),
('Business', 'business', 3),
('Lifestyle', 'lifestyle', 4);

-- ============================================
-- FINAL STATEMENTS
-- ============================================
ALTER DATABASE `base_site` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
