# Product Requirements Document (PRD): FnB B2B White-Label Web & CMS Boilerplate (Kombo 1)

## 1. Project Overview
Proyek ini adalah **Master Boilerplate & Live Demo** untuk produk B2B Agensi Web yang menargetkan **Startup & Bisnis FnB** (Restoran, Coffee Shop, Cloud Kitchen, Franchise Kuliner). 

Tujuan utama arsitektur ini adalah **"Build Once, Clone Infinitely"**:
1. **Ultra-Lightweight:** Berjalan sangat cepat di VPS berspesifikasi rendah (misal: 1–2 CPU, 2GB RAM) menggunakan **PHP 8.3 Native (PDO)** dan **MySQL/MariaDB** tanpa overhead framework berat.
2. **Easy Rebranding (White-Label):** Mengubah identitas visual (warna, logo, font, konten, nomor WhatsApp) untuk klien baru hanya membutuhkan waktu kurang dari 15 menit melalui CMS atau file konfigurasi.
3. **High Conversion:** Dilengkapi fitur **Web-to-WhatsApp Cart Ordering** (tanpa komisi aplikasi pihak ketiga), **Digital QR Menu**, **Outlet Aggregator**, dan **Portal Akuisisi Kemitraan/Franchise**.

---

## 2. Reference GitHub Repositories (Source Assets)
AI Agent wajib mengacu pada repositori berikut untuk mengambil struktur UI/UX, aset statis, dan komponen tata letak:

1. **Frontend Option A (Fast-Casual / Street Food / Coffee Shop Demo):**
   - Repo: `https://github.com/codewithsadee/foodie`
   - Penggunaan: Ambil struktur HTML semantik, aset gambar demo, dan *layout* katalog menu, lalu refaktor menggunakan **Tailwind CSS** + **Alpine.js**.
2. **Frontend Option B (Fine Dining / Premium Bistro / Bakery Demo - Opsional Tema Kedua):**
   - Repo: `https://github.com/codewithsadee/grilli`
   - Penggunaan: Referensi komponen *hero slider*, reservasi meja, dan tipografi elegan.
3. **Admin CMS Dashboard UI:**
   - Repo: `https://github.com/TailAdmin/free-tailwind-admin-dashboard`
   - Penggunaan: Gunakan struktur layout *sidebar*, *header*, tabel data, dan komponen *form* berbasis Tailwind CSS + Alpine.js untuk halaman `/admin`.

---

## 3. Tech Stack & Environment Requirements
- **Backend:** PHP 8.3+ Native (Strict Types, OOP/Modular Procedural dengan PDO Prepared Statements). Tanpa framework berat.
- **Database:** MySQL 8.0+ / MariaDB (dengan dukungan fallback SQLite untuk *local preview* cepat).
- **Frontend Styling:** Tailwind CSS (via CLI build atau Play CDN terkontrol dengan CSS Variables dinamis agar warna tema bisa diubah langsung dari Admin CMS tanpa perlu *rebuild* CSS).
- **Frontend Interactivity:** Alpine.js v3.x (mengelola state keranjang belanja di `localStorage`, modal varian menu, filter kategori instan, dan *drawer* UI).
- **Image Processing:** PHP GD Extension (konversi otomatis foto menu yang diunggah staf dari JPG/PNG menjadi `.webp` terkompresi maksimal 800px).
- **Web Server Target:** Linux VPS (Ubuntu 24.04 LTS) + Nginx + PHP 8.3-FPM.

---

## 4. Directory Structure
AI Agent harus membangun struktur direktori yang bersih dan memisahkan *public web root* demi keamanan di Nginx:

```text
fnb-boilerplate/
├── config/
│   ├── env.example.php       # Template konfigurasi database & environment
│   ├── database.php          # Koneksi PDO Singleton
│   └── helpers.php           # Fungsi sanitasi, CSRF, format Rupiah, upload WebP
├── database/
│   ├── schema.sql            # Struktur tabel MySQL
│   └── seed_demo.sql         # Data dummy siap pakai untuk Demo Pitching Klien
├── storage/
│   └── uploads/              # Lokasi penyimpanan file gambar (.webp)
├── views/
│   ├── layouts/              # Header, Footer, Floating WA Cart, Pixel Scripts
│   ├── frontend/             # Komponen halaman publik (Hero, Menu, Outlets, Franchise)
│   └── admin/                # Halaman CMS berbasis TailAdmin UI
├── public/                   # Document Root untuk Nginx
│   ├── index.php             # Front Controller / Simple Router
│   ├── assets/               # CSS, JS, dan gambar statis dari repo foodie/grilli
│   └── uploads/              # Symlink ke ../storage/uploads/
├── scripts/
│   ├── clone-client.sh       # Bash script untuk duplikasi instance klien baru di VPS
│   └── nginx-vhost.conf      # Template konfigurasi blok server Nginx
└── PRD.md
```

---

## 5. Database Schema (`database/schema.sql`)
Buat 6 tabel inti berikut agar ringan namun mencakup seluruh kebutuhan bisnis FnB:

1. **`settings` (Key-Value atau Single-Row Config):**
   - `site_name`, `tagline`, `logo_url`, `favicon_url`
   - `primary_color` (Hex, default: `#FF6B35`), `secondary_color` (Hex, default: `#1E293B`)
   - `whatsapp_order_number` (Format `628...`), `whatsapp_franchise_number`
   - `whatsapp_greeting_template` (Teks pembuka chat otomatis)
   - `announcement_text`, `is_announcement_active` (Boolean)
   - `meta_pixel_id`, `tiktok_pixel_id`, `google_analytics_id`
   - `hero_title`, `hero_subtitle`, `hero_banner_url`
2. **`categories`:**
   - `id`, `name`, `slug`, `icon`, `sort_order`, `is_active`
3. **`menus`:**
   - `id`, `category_id` (FK), `name`, `slug`, `description`
   - `price` (INT), `strike_price` (INT, nullable untuk harga coret/diskon)
   - `image_url`, `variants_json` (JSON untuk pilihan: Level Pedas, Topping, Ukuran beserta harga tambahan)
   - `is_available` (TINYINT 1/0 - **Fitur krusial untuk toggle stok habis**), `is_featured` (TINYINT 1/0)
4. **`promos`:**
   - `id`, `title`, `banner_url`, `promo_code`, `valid_until`, `is_active`
5. **`outlets`:**
   - `id`, `name`, `address`, `open_hours`, `gmaps_embed_url`, `gmaps_link`
   - `gofood_url`, `grabfood_url`, `shopeefood_url`, `is_active`
6. **`users`:**
   - `id`, `username`, `password_hash` (bcrypt), `role` (`admin`, `kasir`), `last_login`

---

## 6. Core Functional Specifications

### A. Frontend (Public Demo & Customer Ordering)
1. **Dynamic Theme Engine (CSS Variables):**
   - Injeksi nilai `primary_color` dan `secondary_color` dari tabel `settings` ke dalam `:root { --color-primary: ...; }` di `<head>`. Klien baru bisa ganti warna tema langsung dari CMS.
2. **Interactive Menu & Category Filter (Alpine.js):**
   - Filter kategori menu tanpa *reload* halaman.
   - Menu dengan `is_available = 0` otomatis menampilkan *badge* **"Habis / Sold Out"**, gambar sedikit redup (`grayscale`), dan tombol tambah ke keranjang dinonaktifkan.
3. **Web-to-WhatsApp Cart Drawer (Zero Commission Ordering):**
   - State disimpan di `localStorage` via Alpine.js (`x-data="cartStore()"`).
   - Mendukung pemilihan catatan per item (misal: *"Es sedikit, pedas level 2"*).
   - Mendukung pilihan **Tipe Pesanan**: *Dine-In* (Input Nomor Meja), *Takeaway / Pickup* (Input Jam Ambil), atau *Delivery* (Input Alamat Lengkap).
   - Saat klik **"Pesan via WhatsApp"**, sistem memformat pesan teks rapi berisi daftar pesanan, rincian harga, tipe pesanan, dan total bayar, lalu mengarahkan ke `https://wa.me/{whatsapp_order_number}?text={encoded_text}`.
4. **Outlet Aggregator & Deep Links:**
   - Menampilkan daftar cabang beserta tombol langsung ke GoFood, GrabFood, ShopeeFood, dan petunjuk arah Google Maps.
5. **Franchise / B2B Partnership Section:**
   - Bagian khusus untuk menarik mitra *franchise* lengkap dengan **Kalkulator Estimasi Balik Modal (ROI)** sederhana menggunakan Alpine.js dan tombol CTA ke nomor WhatsApp *Business Development*.

### B. Backend CMS (`/admin` - TailAdmin UI)
1. **Authentication & Security:**
   - Login menggunakan `password_verify()`, regenerasi session ID, dan proteksi token CSRF pada setiap form `POST`.
2. **One-Click Stock Toggle (Fitur Kasir Mobile-Friendly):**
   - Pada halaman daftar menu di CMS, sediakan tombol *toggle switch* (Alpine.js + `fetch` API ke endpoint `/admin/api/toggle-stock`). Kasir bisa mematikan/menyalakan stok menu yang habis dalam 1 detik langsung dari layar HP.
3. **Auto-WebP Image Uploader:**
   - Setiap unggahan foto menu atau banner divalidasi (MIME type image/jpeg, image/png, image/webp, maks 5MB), di-resize proporsional jika lebar lebih dari 800px, dan disimpan sebagai `.webp` kualitas 80% agar beban server dan *bandwidth* tetap minimal.
4. **White-Label Settings Panel:**
   - Halaman khusus untuk mengubah Nama Brand, Logo, Warna Tema, Nomor WhatsApp, dan ID Pixel Iklan.

---

## 7. AI Agent Execution Roadmap (Step-by-Step)

AI Agent harus mengeksekusi proyek ini secara berurutan per fase:

- **Phase 1: Scaffolding & Database Setup**
  1. Buat struktur folder sesuai Bagian 4.
  2. Buat `config/database.php`, `config/helpers.php`, `database/schema.sql`, dan `database/seed_demo.sql` (isi dengan data demo brand fiktif bernama **"Kopi & Rasa Nusantara"** lengkap dengan 8 menu, 3 kategori, dan 3 cabang).
- **Phase 2: Frontend Adaptation (`codewithsadee/foodie`)**
  1. Kloning atau ambil struktur komponen dari repo `codewithsadee/foodie`.
  2. Konversi halaman utama menjadi *view* PHP dinamis (`views/frontend/home.php`) yang terhubung ke database via PDO.
  3. Bangun modul Alpine.js untuk **Web-to-WhatsApp Cart Drawer** dan **Kalkulator ROI Franchise**.
- **Phase 3: Admin CMS Integration (`TailAdmin`)**
  1. Adaptasi layout *sidebar* dan *navbar* dari `TailAdmin/free-tailwind-admin-dashboard` ke `views/admin/`.
  2. Bangun halaman Login, Dashboard ringkas, CRUD Kategori, CRUD Menu (beserta fitur *One-Click Stock Toggle* dan *Auto-WebP Upload*), CRUD Cabang, serta Halaman Pengaturan Brand (*White-Label*).
- **Phase 4: Deployment & Multi-Client Automation Scripts**
  1. Buat file `scripts/nginx-vhost.conf` yang sudah dioptimasi untuk PHP 8.3-FPM, *gzip compression*, dan *cache control* untuk aset statis.
  2. Buat skrip bash `scripts/clone-client.sh` yang menerima parameter `nama_klien` dan `domain_klien` untuk menduplikasi folder proyek, membuat database baru, dan mengimpor skema secara otomatis di VPS.

---

## 8. Acceptance Criteria (Definition of Done)
- [ ] Seluruh query database menggunakan PDO Prepared Statements (0% celah SQL Injection).
- [ ] Seluruh output teks dari database di-escape menggunakan `htmlspecialchars()` (0% celah XSS).
- [ ] Keranjang belanja WhatsApp berfungsi mulus di browser mobile dan menghasilkan format teks WhatsApp yang rapi.
- [ ] Staf/Admin dapat mengubah status stok menu (*Tersedia/Habis*) tanpa *reload* halaman penuh.
- [ ] Mengubah warna utama dan nama brand di halaman `/admin/settings` langsung mengubah tampilan halaman publik secara *real-time*.
- [ ] Aplikasi berjalan lancar di PHP 8.3 tanpa *warning* atau *deprecated notice*.