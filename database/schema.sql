-- ===================================================================
-- FnB B2B White-Label Web & CMS Boilerplate
-- Master Database Schema (MySQL 8.0+ / MariaDB)
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ===================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `menu_recipes`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `waiter_calls`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `tables`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `promos`;
DROP TABLE IF EXISTS `outlets`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------------
-- 1. Table: settings (White-Label Branding & General Configurations)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_name` VARCHAR(150) NOT NULL DEFAULT 'Kopi & Rasa Nusantara',
    `tagline` VARCHAR(255) DEFAULT 'Cita Rasa Otentik Kopi & Kuliner Warisan Nusantara',
    `logo_url` VARCHAR(255) DEFAULT '/assets/images/logo.png',
    `favicon_url` VARCHAR(255) DEFAULT '/assets/images/favicon.ico',
    `primary_color` VARCHAR(10) NOT NULL DEFAULT '#FF6B35',
    `secondary_color` VARCHAR(10) NOT NULL DEFAULT '#1E293B',
    `whatsapp_order_number` VARCHAR(30) NOT NULL DEFAULT '6281234567890',
    `whatsapp_franchise_number` VARCHAR(30) DEFAULT '6281298765432',
    `whatsapp_greeting_template` TEXT,
    `announcement_text` VARCHAR(255) DEFAULT 'PROMO SPESIAL: Diskon 20% untuk semua menu Kopi Susu gula aren hari ini!',
    `is_announcement_active` TINYINT(1) NOT NULL DEFAULT 1,
    `meta_pixel_id` VARCHAR(50) DEFAULT NULL,
    `tiktok_pixel_id` VARCHAR(50) DEFAULT NULL,
    `google_analytics_id` VARCHAR(50) DEFAULT NULL,
    `hero_title` VARCHAR(255) DEFAULT 'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara',
    `hero_subtitle` TEXT,
    `hero_banner_url` VARCHAR(255) DEFAULT '/assets/images/hero-banner.jpg',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 2. Table: categories (Menu Categories)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'fa-utensils',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 3. Table: menus (Products, Variants & Dynamic Stock Availability)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT,
    `price` INT NOT NULL DEFAULT 0,
    `cost_price` INT NOT NULL DEFAULT 0 COMMENT 'HPP / Cost of Goods Sold',
    `strike_price` INT DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `variants_json` LONGTEXT DEFAULT NULL,
    `station` VARCHAR(20) NOT NULL DEFAULT 'KITCHEN' COMMENT 'KITCHEN or BAR',
    `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Available, 0 = Sold Out / Habis',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_menus_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 4. Table: tables (Restaurant Dine-in Tables & QR Studio)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_number` VARCHAR(20) NOT NULL,
    `area` VARCHAR(50) NOT NULL DEFAULT 'Indoor',
    `qr_slug` VARCHAR(50) NOT NULL UNIQUE,
    `pin` VARCHAR(10) DEFAULT '1234',
    `status` VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE' COMMENT 'AVAILABLE, OCCUPIED, NEEDS_CLEANING',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 5. Table: orders (POS & Customer Orders Master)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_type` VARCHAR(20) NOT NULL DEFAULT 'DINE_IN' COMMENT 'DINE_IN, PICKUP, DELIVERY',
    `table_id` INT DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(30) DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING, CONFIRMED, COOKING, READY, SERVED, COMPLETED, CANCELLED',
    `subtotal` INT NOT NULL DEFAULT 0,
    `service_charge` INT NOT NULL DEFAULT 0,
    `tax_amount` INT NOT NULL DEFAULT 0,
    `grand_total` INT NOT NULL DEFAULT 0,
    `payment_method` VARCHAR(30) NOT NULL DEFAULT 'CASH' COMMENT 'CASH, QRIS, TRANSFER',
    `payment_status` VARCHAR(20) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING, PAID',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 6. Table: order_items (Items per Order for KDS & Receipt)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_id` INT DEFAULT NULL,
    `menu_name` VARCHAR(150) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` INT NOT NULL DEFAULT 0,
    `line_total` INT NOT NULL DEFAULT 0,
    `station` VARCHAR(20) NOT NULL DEFAULT 'KITCHEN' COMMENT 'KITCHEN or BAR',
    `item_status` VARCHAR(20) NOT NULL DEFAULT 'QUEUED' COMMENT 'QUEUED, COOKING, READY, SERVED, VOIDED',
    `notes` TEXT DEFAULT NULL,
    `variants_selected` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 7. Table: waiter_calls (Customer Call Waiter Service)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `waiter_calls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_id` INT NOT NULL,
    `call_type` VARCHAR(50) NOT NULL DEFAULT 'WATER_TISSUE' COMMENT 'WATER_TISSUE, CLEAN_TABLE, ASK_MENU, REQUEST_BILL',
    `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING, RESOLVED',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_calls_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 8. Table: ingredients (Raw Materials & Inventory Management)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `unit` VARCHAR(20) NOT NULL DEFAULT 'GRAM' COMMENT 'GRAM, ML, PIECE, PACK, SLICE',
    `current_stock` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `min_threshold` DECIMAL(10,2) NOT NULL DEFAULT 10,
    `cost_per_unit` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 9. Table: menu_recipes (Bill of Materials - Recipe per Menu)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu_recipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
    CONSTRAINT `fk_recipes_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 10. Table: promos (Special Offers & Vouchers)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `promos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `banner_url` VARCHAR(255) DEFAULT NULL,
    `promo_code` VARCHAR(50) DEFAULT NULL,
    `valid_until` DATE DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 11. Table: outlets (Store Branches & Food Delivery Deep Links)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `outlets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `address` TEXT NOT NULL,
    `open_hours` VARCHAR(100) DEFAULT '08:00 - 22:00 WIB',
    `gmaps_embed_url` TEXT,
    `gmaps_link` VARCHAR(255) DEFAULT NULL,
    `gofood_url` VARCHAR(255) DEFAULT NULL,
    `grabfood_url` VARCHAR(255) DEFAULT NULL,
    `shopeefood_url` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 12. Table: users (Staff & Admin Access)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin' COMMENT 'admin, kasir, dapur, waiter',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
