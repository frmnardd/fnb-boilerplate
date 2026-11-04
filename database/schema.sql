-- ===================================================================
-- FnB White-Label Boilerplate - Production MySQL Schema (PRD v2.0)
-- Multi-Outlet, Omnichannel POS, Table Studio, KDS, & Franchise CRM
-- ===================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `waiter_calls`;
DROP TABLE IF EXISTS `menu_recipes`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `tables`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `promos`;
DROP TABLE IF EXISTS `vouchers`;
DROP TABLE IF EXISTS `franchise_leads`;
DROP TABLE IF EXISTS `outlets`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------------
-- 1. Table: settings
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_name` VARCHAR(100) NOT NULL DEFAULT 'Kopi & Rasa Nusantara',
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
    `hero_banner_url` VARCHAR(255) DEFAULT '/assets/images/hero-banner.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 2. Table: categories
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
-- 3. Table: menus
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT,
    `price` INT NOT NULL DEFAULT 0,
    `cost_price` INT NOT NULL DEFAULT 0,
    `strike_price` INT DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `variants_json` LONGTEXT DEFAULT NULL,
    `station` VARCHAR(20) NOT NULL DEFAULT 'KITCHEN',
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `calories` INT DEFAULT 185,
    `nutrition_info` VARCHAR(255) DEFAULT 'Karbohidrat 22g, Protein 3g, Lemak 4g',
    `ingredients_text` TEXT,
    `rating` DECIMAL(3,2) DEFAULT 4.90,
    `review_count` INT DEFAULT 248,
    `halal_certified` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_menus_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 4. Table: tables
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_number` VARCHAR(20) NOT NULL,
    `area` VARCHAR(50) NOT NULL DEFAULT 'Indoor',
    `qr_slug` VARCHAR(50) NOT NULL UNIQUE,
    `pin` VARCHAR(10) DEFAULT '1234',
    `status` VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 5. Table: outlets
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `outlets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT 'Jakarta Selatan',
    `address` TEXT NOT NULL,
    `whatsapp_number` VARCHAR(30) NOT NULL DEFAULT '6281234567890',
    `open_hours` VARCHAR(100) DEFAULT '08:00 - 22:00 WIB',
    `image_url` VARCHAR(255) DEFAULT '/assets/images/outlet-senopati.webp',
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
-- 6. Table: vouchers
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(100) NOT NULL,
    `discount_type` VARCHAR(20) NOT NULL DEFAULT 'PERCENT',
    `discount_value` INT NOT NULL DEFAULT 0,
    `min_subtotal` INT NOT NULL DEFAULT 0,
    `applicable_category_id` INT DEFAULT NULL,
    `applicable_menu_slug` VARCHAR(100) DEFAULT NULL,
    `description` TEXT,
    `valid_until` DATE DEFAULT '2026-12-31',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 7. Table: franchise_leads
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `franchise_leads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `package_chosen` VARCHAR(100) NOT NULL,
    `whatsapp_number` VARCHAR(30) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'NEW',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 8. Table: orders
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_token` VARCHAR(100) DEFAULT NULL,
    `order_type` VARCHAR(20) NOT NULL DEFAULT 'dine_in',
    `outlet_id` INT DEFAULT 1,
    `outlet_name` VARCHAR(150) DEFAULT 'Cabang Senopati (Flagship Store)',
    `table_id` INT DEFAULT NULL,
    `table_number` VARCHAR(50) DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(30) DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING',
    `subtotal` INT NOT NULL DEFAULT 0,
    `voucher_code` VARCHAR(50) DEFAULT NULL,
    `voucher_discount` INT NOT NULL DEFAULT 0,
    `delivery_address` TEXT DEFAULT NULL,
    `delivery_notes` TEXT DEFAULT NULL,
    `delivery_fee` INT NOT NULL DEFAULT 0,
    `service_charge` INT NOT NULL DEFAULT 0,
    `tax_amount` INT NOT NULL DEFAULT 0,
    `grand_total` INT NOT NULL DEFAULT 0,
    `payment_method` VARCHAR(30) NOT NULL DEFAULT 'CASH',
    `payment_status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 9. Table: order_items
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_id` INT DEFAULT NULL,
    `menu_name` VARCHAR(150) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` INT NOT NULL DEFAULT 0,
    `line_total` INT NOT NULL DEFAULT 0,
    `station` VARCHAR(20) NOT NULL DEFAULT 'KITCHEN',
    `item_status` VARCHAR(20) NOT NULL DEFAULT 'QUEUED',
    `notes` TEXT DEFAULT NULL,
    `variants_selected` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 10. Table: waiter_calls
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `waiter_calls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_id` INT NOT NULL,
    `call_type` VARCHAR(30) NOT NULL DEFAULT 'WATER_TISSUE',
    `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_calls_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 11. Table: ingredients
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `unit` VARCHAR(20) NOT NULL DEFAULT 'GRAM',
    `current_stock` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `min_threshold` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
    `cost_per_unit` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 12. Table: menu_recipes
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu_recipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT NOT NULL,
    `ingredient_id` INT NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    CONSTRAINT `fk_recipes_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- 13. Table: promos
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
-- 14. Table: users
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
