-- ===================================================================
-- FnB White-Label Boilerplate - Production SQLite Schema (PRD v2.0)
-- Multi-Outlet, Omnichannel POS, Table Studio, KDS, & Franchise CRM
-- ===================================================================

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS "order_items";
DROP TABLE IF EXISTS "orders";
DROP TABLE IF EXISTS "waiter_calls";
DROP TABLE IF EXISTS "menu_recipes";
DROP TABLE IF EXISTS "ingredients";
DROP TABLE IF EXISTS "tables";
DROP TABLE IF EXISTS "menus";
DROP TABLE IF EXISTS "categories";
DROP TABLE IF EXISTS "promos";
DROP TABLE IF EXISTS "vouchers";
DROP TABLE IF EXISTS "franchise_leads";
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
    "hero_banner_url" TEXT DEFAULT '/assets/images/hero-banner.png',
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
    "cost_price" INTEGER NOT NULL DEFAULT 0,
    "strike_price" INTEGER DEFAULT NULL,
    "image_url" TEXT DEFAULT NULL,
    "variants_json" TEXT DEFAULT NULL,
    "station" TEXT NOT NULL DEFAULT 'KITCHEN',
    "is_available" INTEGER NOT NULL DEFAULT 1,
    "is_featured" INTEGER NOT NULL DEFAULT 0,
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "calories" INTEGER DEFAULT 185,
    "nutrition_info" TEXT DEFAULT 'Karbohidrat 22g, Protein 3g, Lemak 4g',
    "ingredients_text" TEXT DEFAULT '100% Kopi Arabika Gayo & Susu Segar Pasteurisasi',
    "rating" REAL DEFAULT 4.9,
    "review_count" INTEGER DEFAULT 248,
    "halal_certified" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("category_id") REFERENCES "categories" ("id") ON DELETE CASCADE
);

-- -------------------------------------------------------------------
-- 4. Table: tables
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "tables" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "table_number" TEXT NOT NULL,
    "area" TEXT NOT NULL DEFAULT 'Indoor',
    "qr_slug" TEXT NOT NULL UNIQUE,
    "pin" TEXT DEFAULT '1234',
    "status" TEXT NOT NULL DEFAULT 'AVAILABLE',
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 5. Table: outlets
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "outlets" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "slug" TEXT DEFAULT NULL,
    "city" TEXT DEFAULT 'Jakarta Selatan',
    "address" TEXT NOT NULL,
    "whatsapp_number" TEXT NOT NULL DEFAULT '6281234567890',
    "open_hours" TEXT DEFAULT '08:00 - 22:00 WIB',
    "image_url" TEXT DEFAULT '/assets/images/outlet-senopati.webp',
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
-- 6. Table: vouchers
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "vouchers" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "code" TEXT NOT NULL UNIQUE,
    "title" TEXT NOT NULL,
    "discount_type" TEXT NOT NULL DEFAULT 'PERCENT', -- 'PERCENT' or 'FLAT'
    "discount_value" INTEGER NOT NULL DEFAULT 0,
    "min_subtotal" INTEGER NOT NULL DEFAULT 0,
    "applicable_category_id" INTEGER DEFAULT NULL,
    "applicable_menu_slug" TEXT DEFAULT NULL,
    "description" TEXT,
    "valid_until" DATE DEFAULT '2026-12-31',
    "is_active" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 7. Table: franchise_leads
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "franchise_leads" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT NOT NULL,
    "city" TEXT NOT NULL,
    "package_chosen" TEXT NOT NULL,
    "whatsapp_number" TEXT NOT NULL,
    "notes" TEXT DEFAULT NULL,
    "status" TEXT NOT NULL DEFAULT 'NEW',
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 8. Table: orders
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "orders" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "order_number" TEXT NOT NULL UNIQUE,
    "order_token" TEXT DEFAULT NULL,
    "order_type" TEXT NOT NULL DEFAULT 'dine_in',
    "outlet_id" INTEGER DEFAULT 1,
    "outlet_name" TEXT DEFAULT 'Cabang Senopati (Flagship Store)',
    "table_id" INTEGER DEFAULT NULL,
    "table_number" TEXT DEFAULT NULL,
    "customer_name" TEXT NOT NULL,
    "customer_phone" TEXT DEFAULT NULL,
    "status" TEXT NOT NULL DEFAULT 'PENDING',
    "subtotal" INTEGER NOT NULL DEFAULT 0,
    "voucher_code" TEXT DEFAULT NULL,
    "voucher_discount" INTEGER NOT NULL DEFAULT 0,
    "delivery_address" TEXT DEFAULT NULL,
    "delivery_notes" TEXT DEFAULT NULL,
    "delivery_fee" INTEGER NOT NULL DEFAULT 0,
    "service_charge" INTEGER NOT NULL DEFAULT 0,
    "tax_amount" INTEGER NOT NULL DEFAULT 0,
    "grand_total" INTEGER NOT NULL DEFAULT 0,
    "payment_method" TEXT NOT NULL DEFAULT 'CASH',
    "payment_status" TEXT NOT NULL DEFAULT 'PENDING',
    "notes" TEXT DEFAULT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("table_id") REFERENCES "tables" ("id") ON DELETE SET NULL,
    FOREIGN KEY ("outlet_id") REFERENCES "outlets" ("id") ON DELETE SET NULL
);

-- -------------------------------------------------------------------
-- 9. Table: order_items
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "order_items" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "order_id" INTEGER NOT NULL,
    "menu_id" INTEGER DEFAULT NULL,
    "menu_name" TEXT NOT NULL,
    "quantity" INTEGER NOT NULL DEFAULT 1,
    "unit_price" INTEGER NOT NULL DEFAULT 0,
    "line_total" INTEGER NOT NULL DEFAULT 0,
    "station" TEXT NOT NULL DEFAULT 'KITCHEN',
    "item_status" TEXT NOT NULL DEFAULT 'QUEUED',
    "notes" TEXT DEFAULT NULL,
    "variants_selected" TEXT DEFAULT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("order_id") REFERENCES "orders" ("id") ON DELETE CASCADE
);

-- -------------------------------------------------------------------
-- 10. Table: waiter_calls
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "waiter_calls" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "table_id" INTEGER NOT NULL,
    "call_type" TEXT NOT NULL DEFAULT 'WATER_TISSUE',
    "status" TEXT NOT NULL DEFAULT 'PENDING',
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY ("table_id") REFERENCES "tables" ("id") ON DELETE CASCADE
);

-- -------------------------------------------------------------------
-- 11. Table: ingredients
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "ingredients" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "sku" TEXT NOT NULL UNIQUE,
    "name" TEXT NOT NULL,
    "unit" TEXT NOT NULL DEFAULT 'GRAM',
    "current_stock" REAL NOT NULL DEFAULT 0,
    "min_threshold" REAL NOT NULL DEFAULT 10,
    "cost_per_unit" REAL NOT NULL DEFAULT 0,
    "is_active" INTEGER NOT NULL DEFAULT 1,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------------
-- 12. Table: menu_recipes
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS "menu_recipes" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "menu_id" INTEGER NOT NULL,
    "ingredient_id" INTEGER NOT NULL,
    "quantity" REAL NOT NULL DEFAULT 1,
    FOREIGN KEY ("menu_id") REFERENCES "menus" ("id") ON DELETE CASCADE,
    FOREIGN KEY ("ingredient_id") REFERENCES "ingredients" ("id") ON DELETE CASCADE
);

-- -------------------------------------------------------------------
-- 13. Table: promos
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
-- 14. Table: users
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
