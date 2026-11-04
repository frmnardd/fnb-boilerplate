-- ===================================================================
-- FnB B2B White-Label Web & CMS Boilerplate
-- Seed Demo Data: "Kopi & Rasa Nusantara"
-- Compatible with MySQL 8.0+ and SQLite 3
-- ===================================================================

-- 1. Settings (Brand Identity, Colors, WhatsApp, Analytics)
INSERT INTO settings (
    id, site_name, tagline, logo_url, favicon_url, primary_color, secondary_color,
    whatsapp_order_number, whatsapp_franchise_number, whatsapp_greeting_template,
    announcement_text, is_announcement_active, meta_pixel_id, tiktok_pixel_id, google_analytics_id,
    hero_title, hero_subtitle, hero_banner_url
) VALUES (
    1,
    'Kopi & Rasa Nusantara',
    'Cita Rasa Otentik Kopi & Kuliner Warisan Nusantara',
    '/assets/images/logo.png',
    '/assets/images/favicon.ico',
    '#FF6B35',
    '#1E293B',
    '6281234567890',
    '6281298765432',
    'Halo Kopi & Rasa Nusantara, saya ingin memesan menu berikut:\n\n{order_details}\n\n*Total Bayar: {total}*\n*Tipe Pesanan: {order_type}*\n*Detail Pemesan:*\n{customer_details}\n\nMohon diproses, terima kasih!',
    'PROMO SPESIAL: Diskon 20% untuk semua menu Kopi Susu gula aren hari ini!',
    1,
    '123456789012345',
    'C9876543210ABCDEF',
    'G-XXXXXXXXXX',
    'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara',
    'Diseduh dari biji kopi pilihan petani lokal dan dipadukan dengan hidangan khas Nusantara bercita rasa legendaris.',
    '/assets/images/hero-banner.jpg'
);

-- 2. Categories (3 Kategori Inti)
INSERT INTO categories (id, name, slug, icon, sort_order, is_active) VALUES
(1, 'Kopi Nusantara', 'kopi-nusantara', 'coffee', 1, 1),
(2, 'Makanan Utama', 'makanan-utama', 'utensils', 2, 1),
(3, 'Camilan Tradisional', 'camilan-tradisional', 'cookie', 3, 1);

-- 3. Menus (8 Menu Pilihan dengan Varian JSON & Indikator Stok)
INSERT INTO menus (
    id, category_id, name, slug, description, price, strike_price, image_url, variants_json, is_available, is_featured, sort_order
) VALUES
(
    1,
    1,
    'Kopi Susu Gula Aren Senja',
    'kopi-susu-gula-aren-senja',
    'Espresso robusta Lampung premium dipadu susu segar creamy dan gula aren organik murni khas Lebak.',
    22000,
    28000,
    '/assets/images/menu-kopi-aren.jpg',
    '{"options":[{"name":"Ukuran","choices":[{"name":"Regular","extra_price":0},{"name":"Large (+5k)","extra_price":5000}]},{"name":"Level Manis","choices":[{"name":"Normal Sweet","extra_price":0},{"name":"Less Sugar (50%)","extra_price":0}]},{"name":"Es Batu","choices":[{"name":"Normal Ice","extra_price":0},{"name":"Less Ice","extra_price":0}]}]}',
    1,
    1,
    1
),
(
    2,
    1,
    'Manual Brew Kopi Gayo V60',
    'manual-brew-kopi-gayo-v60',
    'Single origin Arabika Gayo Aceh diseduh metode pour-over V60 dengan notes floral, citrus cerah, dan aftertaste manis.',
    28000,
    NULL,
    '/assets/images/menu-kopi-gayo.jpg',
    '{"options":[{"name":"Suhu Penyajian","choices":[{"name":"Hot (Hangat)","extra_price":0},{"name":"Iced (Dingin)","extra_price":2000}]}]}',
    1,
    1,
    2
),
(
    3,
    1,
    'Es Kopi Pandan Wangi',
    'es-kopi-pandan-wangi',
    'Kombinasi unik kopi susu lembut dengan aroma ekstrak daun pandan alami yang harum dan menyegarkan.',
    24000,
    30000,
    '/assets/images/menu-kopi-pandan.jpg',
    '{"options":[{"name":"Ukuran","choices":[{"name":"Regular","extra_price":0},{"name":"Large (+5k)","extra_price":5000}]}]}',
    1,
    0,
    3
),
(
    4,
    2,
    'Nasi Goreng Kampung Rempah',
    'nasi-goreng-kampung-rempah',
    'Nasi goreng bumbu racikan terasi bakar khas pesisir disajikan dengan ayam suwir, telur mata sapi, dan kerupuk udang.',
    35000,
    42000,
    '/assets/images/menu-nasgor.jpg',
    '{"options":[{"name":"Level Kepedasan","choices":[{"name":"Level 1 (Sedang)","extra_price":0},{"name":"Level 2 (Pedas)","extra_price":0},{"name":"Level 3 (Super Pedas)","extra_price":2000}]},{"name":"Topping Tambahan","choices":[{"name":"Tanpa Topping","extra_price":0},{"name":"Ekstra Telur Mata Sapi","extra_price":5000},{"name":"Ekstra Ayam Suwir Gurih","extra_price":8000}]}]}',
    1,
    1,
    4
),
(
    5,
    2,
    'Ayam Bakar Madu Taliwang',
    'ayam-bakar-madu-taliwang',
    'Ayam pejantan bakar bumbu pedas manis madu hutan khas Lombok disajikan dengan sambal terasi dan plecing kangkung.',
    42000,
    NULL,
    '/assets/images/menu-ayam-taliwang.jpg',
    '{"options":[{"name":"Pilihan Nasi","choices":[{"name":"Paket Nasi Putih Wangi","extra_price":5000},{"name":"Tanpa Nasi (Ayam Saja)","extra_price":0}]}]}',
    1,
    1,
    5
),
(
    6,
    2,
    'Mie Goreng Jawa Nyemek',
    'mie-goreng-jawa-nyemek',
    'Mie telur kuning dimasak basah kuah kental gurih berempah dengan irisan bakso sapi, kubis, dan taburan bawang merah goreng.',
    32000,
    NULL,
    '/assets/images/menu-mie-nyemek.jpg',
    '{"options":[{"name":"Level Pedas","choices":[{"name":"Tidak Pedas","extra_price":0},{"name":"Pedas Sedang","extra_price":0},{"name":"Pedas Mampus","extra_price":2000}]}]}',
    0,
    0,
    6
),
(
    7,
    3,
    'Pisang Goreng Wijen Madu',
    'pisang-goreng-wijen-madu',
    'Pisang raja matang legit digoreng krispi dengan balutan wijen sangrai gurih dan siraman madu randu asli.',
    20000,
    25000,
    '/assets/images/menu-pisang-goreng.jpg',
    '{"options":[{"name":"Saus Cocolan","choices":[{"name":"Original Madu Randu","extra_price":0},{"name":"Saus Cokelat Belgia","extra_price":3000},{"name":"Taburan Keju Cheddar","extra_price":4000}]}]}',
    1,
    1,
    7
),
(
    8,
    3,
    'Cireng Krispi Bumbu Rujak',
    'cireng-krispi-bumbu-rujak',
    'Cireng renyah di luar dan kenyal empuk di dalam, disajikan hangat dengan saus cocolan gula merah rujak pedas asam segar.',
    18000,
    NULL,
    '/assets/images/menu-cireng.jpg',
    '{"options":[{"name":"Porsi","choices":[{"name":"Porsi Regular (10 pcs)","extra_price":0},{"name":"Porsi Jumbo (15 pcs)","extra_price":7000}]}]}',
    1,
    0,
    8
);

-- 4. Promos (Penawaran Menarik)
INSERT INTO promos (id, title, banner_url, promo_code, valid_until, is_active) VALUES
(1, 'Diskon 20% Pembelian Pertama Kopi Susu', '/assets/images/promo-1.jpg', 'KOPIASIK', '2026-12-31', 1),
(2, 'Paket Kenyang Berdua Hemat Rp 15.000', '/assets/images/promo-2.jpg', 'KENYANG2', '2026-12-31', 1);

-- 5. Outlets (3 Cabang Strategis & Aggregator Delivery Link)
INSERT INTO outlets (
    id, name, address, open_hours, gmaps_embed_url, gmaps_link, gofood_url, grabfood_url, shopeefood_url, is_active
) VALUES
(
    1,
    'Cabang Senopati (Flagship Store)',
    'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
    '08:00 - 23:00 WIB',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.27318!2d106.808!3d-6.230!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTMnNDguMCJTIDEwNsKwNDgnMjguOCJF!5e0!3m2!1sid!2sid!4v1',
    'https://maps.google.com/?q=Senopati+Jakarta',
    'https://gofood.co.id/jakarta/restaurant/kopi-rasa-nusantara-senopati',
    'https://grab.onelink.me/food/kopi-rasa-senopati',
    'https://shopee.co.id/universal-link/now-food/shop/12345',
    1
),
(
    2,
    'Cabang Braga Heritage',
    'Jl. Braga No. 88, Sumur Bandung, Kota Bandung',
    '07:30 - 22:30 WIB',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.78!2d107.609!3d-6.917!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNTUnMDEuMiJTIDEwN8KwMzYnMzIuNCJF!5e0!3m2!1sid!2sid!4v1',
    'https://maps.google.com/?q=Braga+Bandung',
    'https://gofood.co.id/bandung/restaurant/kopi-rasa-nusantara-braga',
    'https://grab.onelink.me/food/kopi-rasa-braga',
    'https://shopee.co.id/universal-link/now-food/shop/12346',
    1
),
(
    3,
    'Cabang Malioboro Istimewa',
    'Jl. Malioboro No. 120, Gedong Tengen, Kota Yogyakarta',
    '08:00 - 24:00 WIB',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.97!2d110.365!3d-7.792!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNDcnMzEuMiJTIDExMMKwMjEnNTQuMCJF!5e0!3m2!1sid!2sid!4v1',
    'https://maps.google.com/?q=Malioboro+Yogyakarta',
    'https://gofood.co.id/jogja/restaurant/kopi-rasa-nusantara-malioboro',
    'https://grab.onelink.me/food/kopi-rasa-malioboro',
    'https://shopee.co.id/universal-link/now-food/shop/12347',
    1
);

-- 6. Users (Admin & Kasir)
-- Password for admin: admin123
-- Password for kasir: kasir123
INSERT INTO users (id, username, password_hash, role) VALUES
(1, 'admin', '$2y$10$brrL3.Uk1yQk5a/zGdrveOLuHxPLBwwffmitZjvZOtG7uh.h8yp9.', 'admin'),
(2, 'kasir', '$2y$10$QhbFz4T.m0OuzQMn5mL7EuLLOjWoKwhaRhA6t9fXtNoGjx8Tty85e', 'kasir');
