-- ===================================================================
-- FnB B2B White-Label Web & CMS Boilerplate
-- SQLite Fallback Schema (Local Preview & Fast Development)
-- ===================================================================

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS "menus";
DROP TABLE IF EXISTS "categories";
DROP TABLE IF EXISTS "promos";
DROP TABLE IF EXISTS "outlets";
DROP TABLE IF EXISTS "users";
DROP TABLE IF EXISTS "settings";

-- -------------------------------------------------------------------
-- 1. Table: settings
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "settings" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "site_name" TEXT NOT NULL DEFAULT 'Kopi & Rasa Nusantara',
    "tagline" TEXT DEFAULT 'Cita Rasa Otentik Kopi & Kuliner Warisan Nusantara',
    "logo_url" TEXT DEFAULT '/assets/images/logo.png',
    "favicon_url" TEXT DEFAULT '/assets/images/favicon.ico',
    "primary_color" TEXT NOT NULL DEFAULT '#FF6B35',
    "secondary_color" TEXT NOT NULL DEFAULT '#1E293B',
    "whatsapp_order_number" TEXT NOT NULL DEFAULT '6281234567890',
    "whatsapp_franchise_number" TEXT DEFAULT '6281298765432',
    "whatsapp_greeting_template" TEXT,
    "announcement_text" TEXT DEFAULT 'PROMO SPESIAL: Diskon 20% untuk semua menu Kopi Susu gula aren hari ini!',
    "is_announcement_active" INTEGER NOT NULL DEFAULT 1,
    "meta_pixel_id" TEXT DEFAULT NULL,
    "tiktok_pixel_id" TEXT DEFAULT NULL,
    "google_analytics_id" TEXT DEFAULT NULL,
    "hero_title" TEXT DEFAULT 'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara',
    "hero_subtitle" TEXT,
    "hero_banner_url" TEXT DEFAULT '/assets/images/hero-banner.jpg',
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 2. Table: categories
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "categories" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL UNIQUE,
    "icon" TEXT DEFAULT 'fa-utensils',
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "is_active" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 3. Table: menus
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "menus" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "category_id" INTEGER NOT NULL,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL UNIQUE,
    "description" TEXT,
    "price" INTEGER NOT NULL DEFAULT 0,
    "strike_price" INTEGER DEFAULT NULL,
    "image_url" TEXT DEFAULT NULL,
    "variants_json" TEXT DEFAULT NULL,
    "is_available" INTEGER NOT NULL DEFAULT 1,
    "is_featured" INTEGER NOT NULL DEFAULT 0,
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("category_id") REFERENCES "categories" ("id") ON DELETE CASCADE
);

-- -------------------------------------------------------------------
-- 4. Table: promos
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "promos" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "title" TEXT NOT NULL,
    "banner_url" TEXT DEFAULT NULL,
    "promo_code" TEXT DEFAULT NULL,
    "valid_until" DATE DEFAULT NULL,
    "is_active" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 5. Table: outlets
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "outlets" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "address" TEXT NOT NULL,
    "open_hours" TEXT DEFAULT '08:00 - 22:00 WIB',
    "gmaps_embed_url" TEXT,
    "gmaps_link" TEXT DEFAULT NULL,
    "gofood_url" TEXT DEFAULT NULL,
    "grabfood_url" TEXT DEFAULT NULL,
    "shopeefood_url" TEXT DEFAULT NULL,
    "is_active" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 6. Table: users
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT NOT NULL UNIQUE,
    "password_hash" TEXT NOT NULL,
    "role" TEXT NOT NULL DEFAULT 'admin',
    "last_login" DATETIME DEFAULT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);
