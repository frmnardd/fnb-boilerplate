-- ===================================================================
-- FnB White-Label Boilerplate - Production Demo Seed Data (PRD v2.0)
-- 100% Real Authentic Assets, Dynamic Category Modifiers, Multi-Outlet
-- ===================================================================

-- 1. Brand White-Label Settings
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
    '/assets/images/favicon.svg',
    '#FF6B35',
    '#1E293B',
    '6281211112222',
    '6281298765432',
    'Halo Kasir Kopi & Rasa Nusantara, saya ingin memesan menu lezat berikut:',
    'PROMO SPESIAL: Klaim voucher KOPIASIK diskon 20% & KENYANG2 hemat Rp 15.000 hari ini!',
    1,
    '123456789012345',
    'C9876543210ABCDEF',
    'G-XXXXXXXXXX',
    'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara',
    'Perpaduan biji kopi pilihan nusantara dengan kuliner legendaris Indonesia. Pesan mandiri tanpa antre, nikmati langsung di meja atau pesan antar ke rumah.',
    '/assets/images/hero-banner.png'
);

-- 2. Categories
INSERT INTO categories (id, name, slug, icon, sort_order, is_active) VALUES
(1, 'Kopi Nusantara', 'kopi-nusantara', 'coffee', 1, 1),
(2, 'Makanan Utama', 'makanan-utama', 'utensils', 2, 1),
(3, 'Camilan Tradisional', 'camilan-tradisional', 'cookie', 3, 1);

-- 3. Menus with Authentic WebP Photos & Dynamic Category Modifiers
INSERT INTO menus (
    id, category_id, name, slug, description, price, cost_price, strike_price,
    image_url, variants_json, station, is_available, is_featured, sort_order,
    calories, nutrition_info, ingredients_text, rating, review_count, halal_certified
) VALUES
(
    1,
    1,
    'Kopi Susu Gula Aren Senja',
    'kopi-susu-gula-aren-senja',
    'Espresso house blend robusta dampit dan arabika gayo berpadu susu segar pasteurisasi serta sirup gula aren murni Sukabumi yang legit dan gurih.',
    22000,
    7500,
    28000,
    '/assets/images/menu-kopi-aren.webp',
    '{"options":[{"name":"Suhu Penyajian","choices":[{"name":"Es (Dingin)","extra_price":0},{"name":"Panas (Hangat)","extra_price":0}]},{"name":"Ukuran Cup","choices":[{"name":"Regular","extra_price":0},{"name":"Large (+5k)","extra_price":5000}]},{"name":"Level Manis","choices":[{"name":"Normal Sweet","extra_price":0},{"name":"Less Sugar (50%)","extra_price":0},{"name":"No Sugar","extra_price":0}]},{"name":"Takaran Es","choices":[{"name":"Normal Ice","extra_price":0},{"name":"Less Ice","extra_price":0}]},{"name":"Extra Shot","choices":[{"name":"Tanpa Extra Shot","extra_price":0},{"name":"Extra Espresso Shot (+6k)","extra_price":6000}]}]}',
    'BAR',
    1,
    1,
    1,
    185,
    'Karbohidrat 24g, Protein 4g, Lemak 5g',
    '100% Kopi Arabika Gayo & Robusta Dampit, Susu Segar Pasteurisasi, Gula Aren Murni Organik',
    4.9,
    312,
    1
),
(
    2,
    1,
    'Manual Brew Kopi Gayo V60',
    'manual-brew-kopi-gayo-v60',
    'Single origin Arabika Gayo Aceh diseduh metode pour-over V60 dengan notes floral, citrus cerah, dan aftertaste manis madu hutan.',
    28000,
    9000,
    NULL,
    '/assets/images/menu-kopi-gayo.webp',
    '{"options":[{"name":"Suhu Penyajian","choices":[{"name":"Panas (Hangat)","extra_price":0},{"name":"Es (Dingin)","extra_price":0}]},{"name":"Profil Seduh","choices":[{"name":"Light Floral & Fruity","extra_price":0},{"name":"Medium Balanced","extra_price":0}]},{"name":"Extra Shot","choices":[{"name":"Tanpa Extra Shot","extra_price":0},{"name":"Extra Espresso Shot (+6k)","extra_price":6000}]}]}',
    'BAR',
    1,
    1,
    2,
    5,
    'Karbohidrat 0g, Protein 0g, Kalori 5 kkal',
    '100% Single Origin Arabika Gayo Aceh Specialty Grade 1, Air Filtrasi Mineral Murni',
    4.95,
    184,
    1
),
(
    3,
    1,
    'Es Kopi Pandan Wangi',
    'es-kopi-pandan-wangi',
    'Kombinasi unik kopi susu lembut dengan aroma ekstrak daun pandan suji alami yang harum dan aftertaste creamy menyegarkan.',
    24000,
    8000,
    30000,
    '/assets/images/menu-kopi-pandan.webp',
    '{"options":[{"name":"Suhu Penyajian","choices":[{"name":"Es (Dingin)","extra_price":0},{"name":"Panas (Hangat)","extra_price":0}]},{"name":"Ukuran Cup","choices":[{"name":"Regular","extra_price":0},{"name":"Large (+5k)","extra_price":5000}]},{"name":"Level Manis","choices":[{"name":"Normal Sweet","extra_price":0},{"name":"Less Sugar (50%)","extra_price":0}]},{"name":"Takaran Es","choices":[{"name":"Normal Ice","extra_price":0},{"name":"Less Ice","extra_price":0}]},{"name":"Extra Shot","choices":[{"name":"Tanpa Extra Shot","extra_price":0},{"name":"Extra Espresso Shot (+6k)","extra_price":6000}]}]}',
    'BAR',
    1,
    0,
    3,
    195,
    'Karbohidrat 26g, Protein 4g, Lemak 4g',
    'Biji Kopi Arabika, Susu Segar, Sari Daun Pandan Suji Asli, Gula Tebu Alami',
    4.85,
    156,
    1
),
(
    4,
    2,
    'Nasi Goreng Kampung Rempah',
    'nasi-goreng-kampung-rempah',
    'Nasi goreng bumbu racikan terasi bakar khas pesisir disajikan dengan ayam suwir, telur mata sapi, dan kerupuk udang renyah.',
    35000,
    13500,
    42000,
    '/assets/images/menu-nasgor-kampung.webp',
    '{"options":[{"name":"Level Pedas","choices":[{"name":"Tidak Pedas","extra_price":0},{"name":"Sedang","extra_price":0},{"name":"Pedas","extra_price":0}]},{"name":"Topping Tambahan","choices":[{"name":"Tanpa Topping","extra_price":0},{"name":"Telur Ceplok (+5k)","extra_price":5000},{"name":"Kerupuk Udang (+4k)","extra_price":4000},{"name":"Nasi Putih Ekstra (+6k)","extra_price":6000}]}]}',
    'KITCHEN',
    1,
    1,
    4,
    540,
    'Karbohidrat 68g, Protein 22g, Lemak 18g',
    'Beras Pandan Wangi, Bumbu Rempah Bawang Merah Brebes, Terasi Juwana, Ayam Kampung, Telur Ayam',
    4.9,
    428,
    1
),
(
    5,
    2,
    'Ayam Bakar Madu Taliwang',
    'ayam-bakar-madu-taliwang',
    'Ayam pejantan bakar bumbu pedas manis madu hutan khas Lombok disajikan dengan sambal terasi segar dan lalapan plecing.',
    42000,
    18000,
    NULL,
    '/assets/images/menu-ayam-taliwang.webp',
    '{"options":[{"name":"Level Pedas","choices":[{"name":"Sedang","extra_price":0},{"name":"Pedas Nampol","extra_price":0}]},{"name":"Topping Tambahan","choices":[{"name":"Tanpa Topping","extra_price":0},{"name":"Telur Ceplok (+5k)","extra_price":5000},{"name":"Nasi Putih Ekstra (+6k)","extra_price":6000}]}]}',
    'KITCHEN',
    1,
    1,
    5,
    620,
    'Karbohidrat 12g, Protein 48g, Lemak 22g',
    'Ayam Pejantan Pilihan, Madu Hutan Sumbawa, Cabai Rawit Merah, Bawang Merah, Terasi Khas Lombok',
    4.92,
    275,
    1
),
(
    6,
    2,
    'Mie Goreng Jawa Nyemek',
    'mie-goreng-jawa-nyemek',
    'Mie telur kuning dimasak basah kuah kental gurih berempah dengan irisan bakso sapi, sayuran segar, dan taburan bawang merah goreng.',
    32000,
    11000,
    NULL,
    '/assets/images/menu-mie-nyemek.webp',
    '{"options":[{"name":"Level Pedas","choices":[{"name":"Tidak Pedas","extra_price":0},{"name":"Sedang","extra_price":0},{"name":"Pedas","extra_price":0}]},{"name":"Topping Tambahan","choices":[{"name":"Tanpa Topping","extra_price":0},{"name":"Telur Ceplok (+5k)","extra_price":5000},{"name":"Kerupuk Udang (+4k)","extra_price":4000}]}]}',
    'KITCHEN',
    0,
    0,
    6,
    480,
    'Karbohidrat 62g, Protein 16g, Lemak 14g',
    'Mie Telur Berkualitas, Bakso Sapi Asli, Telur Bebek, Kubis, Sawi Hijau, Bumbu Rempah Tradisional',
    4.8,
    198,
    1
),
(
    7,
    3,
    'Pisang Goreng Wijen Madu',
    'pisang-goreng-wijen-madu',
    'Pisang raja matang digoreng celup adonan krispi taburan wijen sangrai disiram madu murni dan lelehan susu manis.',
    20000,
    6000,
    25000,
    '/assets/images/menu-pisang-goreng.webp',
    '{"options":[{"name":"Pilihan Saus / Taburan","choices":[{"name":"Bumbu Standar (Madu Wijen)","extra_price":0},{"name":"Ekstra Keju Tabur (+4k)","extra_price":4000},{"name":"Susu Cokelat (+4k)","extra_price":4000}]}]}',
    'KITCHEN',
    1,
    1,
    7,
    310,
    'Karbohidrat 48g, Protein 3g, Lemak 9g',
    'Pisang Raja Pilihan, Tepung Beras Krispi, Biji Wijen Sangrai, Madu Hutan Alami',
    4.88,
    310,
    1
),
(
    8,
    3,
    'Cireng Krispi Bumbu Rujak',
    'cireng-krispi-bumbu-rujak',
    'Camilan tapioka gurih renyah di luar kenyal lembut di dalam, disajikan dengan cocolan saus rujak pedas manis asam segar.',
    18000,
    5000,
    NULL,
    '/assets/images/menu-cireng-rujak.webp',
    '{"options":[{"name":"Pilihan Saus / Taburan","choices":[{"name":"Bumbu Rujak Standar","extra_price":0},{"name":"Ekstra Bumbu Rujak (+4k)","extra_price":4000}]}]}',
    'KITCHEN',
    1,
    0,
    8,
    260,
    'Karbohidrat 52g, Protein 1g, Lemak 5g',
    'Tepung Tapioka Pilihan, Daun Bawang Segar, Bawang Putih, Gula Merah Aren, Asam Jawa, Cabai Rawit',
    4.82,
    230,
    1
);

-- 4. Tables (Restaurant Dine-in Tables)
INSERT INTO tables (id, table_number, area, qr_slug, pin, status) VALUES
(1, '01', 'Indoor', 'meja-01', '1234', 'AVAILABLE'),
(2, '02', 'Indoor', 'meja-02', '1234', 'AVAILABLE'),
(3, '03', 'Indoor', 'meja-03', '1234', 'AVAILABLE'),
(4, '04', 'Indoor VIP', 'meja-04', '1234', 'AVAILABLE'),
(5, '05', 'Outdoor Garden', 'meja-05', '1234', 'AVAILABLE'),
(6, '06', 'Outdoor Terrace', 'meja-06', '1234', 'AVAILABLE'),
(7, '07', 'Outdoor Rooftop', 'meja-07', '1234', 'AVAILABLE'),
(8, '08', 'Indoor Bar', 'meja-08', '1234', 'AVAILABLE');

-- 5. Outlets (Multi-Branch Delivery & Routing)
INSERT INTO outlets (
    id, name, slug, city, address, whatsapp_number, open_hours, image_url,
    gmaps_embed_url, gmaps_link, gofood_url, grabfood_url, shopeefood_url, is_active
) VALUES
(
    1,
    'Cabang Senopati (Flagship Store)',
    'senopati',
    'Jakarta Selatan',
    'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
    '6281211112222',
    '08:00 - 23:00 WIB',
    '/assets/images/outlet-senopati.webp',
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
    'braga',
    'Bandung',
    'Jl. Braga No. 88, Sumur Bandung, Kota Bandung',
    '6281233334444',
    '07:30 - 22:30 WIB',
    '/assets/images/outlet-braga.webp',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.78!2d107.609!3d-6.917!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNTUnMDEuMiJTIDEwN8KwMzYnMzIuNCJF!5e0!3m2!1sid!2sid!4v1',
    'https://maps.google.com/?q=Braga+Bandung',
    'https://gofood.co.id/bandung/restaurant/kopi-rasa-nusantara-braga',
    'https://grab.onelink.me/food/kopi-rasa-braga',
    'https://shopee.co.id/universal-link/now-food/shop/12346',
    1
),
(
    3,
    'Cabang Malioboro Heritage',
    'malioboro',
    'Yogyakarta',
    'Jl. Malioboro No. 120, Gedong Tengen, Kota Yogyakarta',
    '6281255556666',
    '08:00 - 23:30 WIB',
    '/assets/images/outlet-malioboro.webp',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.95!2d110.365!3d-7.792!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNDcnMzEuMiJTIDExMMKwMjEnNTQuMCJF!5e0!3m2!1sid!2sid!4v1',
    'https://maps.google.com/?q=Malioboro+Yogyakarta',
    'https://gofood.co.id/jogja/restaurant/kopi-rasa-nusantara-malioboro',
    'https://grab.onelink.me/food/kopi-rasa-malioboro',
    'https://shopee.co.id/universal-link/now-food/shop/12347',
    1
);

-- 6. Interactive Vouchers
INSERT INTO vouchers (
    id, code, title, discount_type, discount_value, min_subtotal, applicable_category_id, applicable_menu_slug, description, valid_until, is_active
) VALUES
(
    1,
    'KOPIASIK',
    'Diskon 20% Pembelian Pertama Kopi Susu',
    'PERCENT',
    20,
    0,
    1,
    'kopi-susu-gula-aren-senja',
    'Potongan 20% khusus untuk semua varian Kopi Susu Nusantara tanpa minimum pembelian.',
    '2026-12-31',
    1
),
(
    2,
    'KENYANG2',
    'Hemat Rp 15.000 Kuliner Kenyang',
    'FLAT',
    15000,
    60000,
    NULL,
    NULL,
    'Potongan langsung Rp 15.000 untuk transaksi makanan dan minuman dengan minimum subtotal Rp 60.000.',
    '2026-12-31',
    1
);

-- 7. Franchise Leads (CRM)
INSERT INTO franchise_leads (
    id, name, city, package_chosen, whatsapp_number, notes, status
) VALUES
(1, 'Hendra Gunawan', 'Surabaya', 'Coffee Shop Standard (Rp 65 Juta)', '6281234567890', 'Lokasi strategis dekat kampus ITS', 'CONTACTED'),
(2, 'Siti Nurhaliza', 'Semarang', 'Booth Express (Rp 35 Juta)', '6281987654321', 'Rencana buka di foodcourt mall Paragon', 'NEW');

-- 8. Promos (Marketing Banner Display)
INSERT INTO promos (id, title, banner_url, promo_code, valid_until, is_active) VALUES
(1, 'Diskon 20% Pembelian Pertama Kopi Susu', '/assets/images/promo-1.jpg', 'KOPIASIK', '2026-12-31', 1),
(2, 'Paket Kenyang Berdua Hemat Rp 15.000', '/assets/images/promo-2.jpg', 'KENYANG2', '2026-12-31', 1);

-- 9. Ingredients (BOM)
INSERT INTO ingredients (id, sku, name, unit, current_stock, min_threshold, cost_per_unit, is_active) VALUES
(1, 'ING-BEANS-ROB', 'Biji Kopi Robusta Dampit', 'GRAM', 12500.0, 2000.0, 150.0, 1),
(2, 'ING-BEANS-GAYO', 'Biji Kopi Arabika Gayo Specialty', 'GRAM', 8200.0, 1500.0, 280.0, 1),
(3, 'ING-MILK-FRESH', 'Susu Segar Pasteurisasi', 'ML', 24000.0, 5000.0, 22.0, 1),
(4, 'ING-PALM-SUGAR', 'Gula Aren Murni Cair Sukabumi', 'ML', 10500.0, 2000.0, 45.0, 1),
(5, 'ING-RICE', 'Beras Pulen Pandan Wangi', 'GRAM', 45000.0, 10000.0, 16.0, 1),
(6, 'ING-CHICKEN', 'Daging Ayam Fillet Segar', 'GRAM', 14000.0, 3000.0, 65.0, 1),
(7, 'ING-EGG', 'Telur Ayam Segar', 'PCS', 180.0, 30.0, 2200.0, 1),
(8, 'ING-NOODLE', 'Mie Telur Kuning Basah', 'GRAM', 12000.0, 2500.0, 30.0, 1),
(9, 'ING-BANANA', 'Pisang Raja Matang Pilihan', 'GRAM', 15000.0, 3000.0, 25.0, 1),
(10, 'ING-TAPIOCA', 'Tepung Tapioka Premium', 'GRAM', 18000.0, 4000.0, 18.0, 1);

-- 10. Menu Recipes
INSERT INTO menu_recipes (id, menu_id, ingredient_id, quantity) VALUES
(1, 1, 1, 15.0),
(2, 1, 3, 120.0),
(3, 1, 4, 30.0),
(4, 2, 2, 18.0),
(5, 4, 5, 200.0),
(6, 4, 6, 80.0),
(7, 4, 7, 1.0),
(8, 5, 6, 250.0),
(9, 6, 8, 150.0),
(10, 6, 7, 1.0),
(11, 7, 9, 180.0),
(12, 8, 10, 120.0);

-- 11. Initial Orders & Items
INSERT INTO orders (
    id, order_number, order_token, order_type, outlet_id, outlet_name, table_id,
    customer_name, customer_phone, status, subtotal, voucher_code, voucher_discount,
    delivery_address, delivery_notes, delivery_fee, service_charge, tax_amount, grand_total,
    payment_method, payment_status, notes
) VALUES
(
    1,
    'ORD-20261101-0001',
    'tok-senopati-0001',
    'dine_in',
    1,
    'Cabang Senopati (Flagship Store)',
    1,
    'Budi Santoso',
    '081234567890',
    'COMPLETED',
    57000,
    NULL,
    0,
    NULL,
    NULL,
    0,
    2850,
    5985,
    65835,
    'QRIS',
    'PAID',
    'Es batu dipisah'
),
(
    2,
    'ORD-20261101-0002',
    'tok-senopati-0002',
    'dine_in',
    1,
    'Cabang Senopati (Flagship Store)',
    2,
    'Rina Wijaya',
    '081987654321',
    'CONFIRMED',
    42000,
    NULL,
    0,
    NULL,
    NULL,
    0,
    2100,
    4410,
    48510,
    'CASH',
    'PAID',
    'Pedas sedang'
);

INSERT INTO order_items (
    id, order_id, menu_id, menu_name, quantity, unit_price, line_total, station, item_status, notes, variants_selected
) VALUES
(1, 1, 1, 'Kopi Susu Gula Aren Senja', 1, 22000, 22000, 'BAR', 'READY', 'Less Sugar', 'Regular, Less Sugar'),
(2, 1, 4, 'Nasi Goreng Kampung Rempah', 1, 35000, 35000, 'KITCHEN', 'COOKING', 'Level 2', 'Sedang, Tanpa Topping'),
(3, 2, 5, 'Ayam Bakar Madu Taliwang', 1, 42000, 42000, 'KITCHEN', 'QUEUED', 'Plecing pedas', 'Sedang, Tanpa Topping');

-- 12. Waiter Calls
INSERT INTO waiter_calls (id, table_id, call_type, status) VALUES
(1, 1, 'WATER_TISSUE', 'PENDING');

-- 13. Users (Password: admin123, kasir123)
INSERT INTO users (id, username, password_hash, role) VALUES
(1, 'admin', '$2y$10$5QEIZ71pz/nu8/uX0jQbbONjnE2KP1bIzXuxOp.W.e57Yho9TAFGu', 'admin'),
(2, 'kasir', '$2y$10$qqX7NF16e7F2.s3tB8Wz1u0sspCHibHJoH6gGts0T/P16CjruThEi', 'kasir');
