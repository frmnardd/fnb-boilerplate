<?php
declare(strict_types=1);

/**
 * FnB B2B White-Label Web & CMS Boilerplate
 * Front Controller & Complete Route Dispatcher (Public + Admin CMS)
 */

// Handle PHP built-in webserver static files
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

// Load core configurations and helpers
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

// App Environment & Debug Setup
$debug = (bool)get_config('app.debug', true);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Set Default Timezone
date_default_timezone_set(get_config('app.timezone', 'Asia/Jakarta'));

// Start secure session
start_session_safe();

// Parse Request URI & Method
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ===================================================================
// PUBLIC ROUTING (Customer & Health APIs)
// ===================================================================

// 1. Health Check Endpoint (JSON)
if ($requestUri === '/api/health') {
    try {
        $pdo = db();
        $driver = Database::getInstance()->getActiveDriver();
        $settings = get_setting();
        
        $categoriesCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $menusCount = (int)$pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
        $outletsCount = (int)$pdo->query("SELECT COUNT(*) FROM outlets")->fetchColumn();

        json_response([
            'status'     => 'healthy',
            'phase'      => 'Phase 3: Admin CMS Integration (Active)',
            'php_version'=> PHP_VERSION,
            'db_driver'  => $driver,
            'brand'      => $settings['site_name'] ?? 'Unknown',
            'counts'     => [
                'categories' => $categoriesCount,
                'menus'      => $menusCount,
                'outlets'    => $outletsCount,
            ],
            'timestamp'  => date('c'),
        ]);
    } catch (\Throwable $e) {
        json_response([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

// 2. Public Home Page
if ($requestUri === '/') {
    require dirname(__DIR__) . '/views/frontend/home.php';
    exit;
}

// 3. Short Table QR Direct Redirect (/t/meja-01 -> /?table=meja-01)
if (preg_match('#^/t/([a-zA-Z0-9_\-]+)$#', $requestUri, $matches)) {
    redirect('/?table=' . urlencode($matches[1]));
}

// 4. Public API: Call Waiter (/api/call-waiter)
if ($requestUri === '/api/call-waiter' && $requestMethod === 'POST') {
    $tableId = (int)($_POST['table_id'] ?? 0);
    $callType = trim($_POST['call_type'] ?? 'WATER_TISSUE');

    if ($tableId <= 0) {
        json_response(['success' => false, 'message' => 'Meja tidak valid.'], 400);
    }

    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO waiter_calls (table_id, call_type, status) VALUES (:table_id, :call_type, 'PENDING')");
    $stmt->execute([
        'table_id'  => $tableId,
        'call_type' => $callType,
    ]);

    json_response([
        'success' => true,
        'message' => 'Panggilan berhasil dikirim ke staf pelayan.',
    ]);
}

// 5. Public API: Create Order from Customer Web/QR (/api/order/create)
if ($requestUri === '/api/order/create' && $requestMethod === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = (!empty($rawInput) ? json_decode($rawInput, true) : null) ?: $_POST;
    
    $customerName = trim($input['name'] ?? $input['customer_name'] ?? 'Tamu');
    $customerPhone = trim($input['phone'] ?? $input['customer_phone'] ?? '');
    $orderType = strtoupper(trim($input['order_type'] ?? 'DINE_IN'));
    $tableSlug = trim($input['table'] ?? '');
    $items = $input['items'] ?? [];

    if (empty($items)) {
        json_response(['success' => false, 'message' => 'Keranjang pesanan kosong.'], 400);
    }

    $pdo = db();
    $tableId = null;
    if (!empty($tableSlug)) {
        $stmtT = $pdo->prepare("SELECT id FROM tables WHERE qr_slug = :slug LIMIT 1");
        $stmtT->execute(['slug' => $tableSlug]);
        $tableId = $stmtT->fetchColumn() ?: null;
    }

    $totals = calculate_order_totals($items);
    $orderNumber = generate_order_number();

    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (order_number, order_type, table_id, customer_name, customer_phone, status, subtotal, service_charge, tax_amount, grand_total, payment_method, payment_status, notes)
        VALUES (:order_number, :order_type, :table_id, :customer_name, :customer_phone, 'PENDING', :subtotal, :service_charge, :tax_amount, :grand_total, :payment_method, 'PENDING', :notes)
    ");
    $stmtOrder->execute([
        'order_number'   => $orderNumber,
        'order_type'     => $orderType,
        'table_id'       => $tableId,
        'customer_name'  => $customerName,
        'customer_phone' => !empty($customerPhone) ? $customerPhone : null,
        'subtotal'       => $totals['subtotal'],
        'service_charge' => $totals['service_charge'],
        'tax_amount'     => $totals['tax_amount'],
        'grand_total'    => $totals['grand_total'],
        'payment_method' => strtoupper(trim($input['payment_method'] ?? 'CASH')),
        'notes'          => trim($input['notes'] ?? ''),
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, menu_id, menu_name, quantity, unit_price, line_total, station, item_status, notes, variants_selected)
        VALUES (:order_id, :menu_id, :menu_name, :quantity, :unit_price, :line_total, :station, 'QUEUED', :notes, :variants_selected)
    ");

    foreach ($items as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (int)($item['price'] ?? $item['unit_price'] ?? 0);
        $stmtItem->execute([
            'order_id'          => $orderId,
            'menu_id'           => !empty($item['id']) ? (int)$item['id'] : null,
            'menu_name'         => trim($item['name'] ?? 'Item'),
            'quantity'          => $qty,
            'unit_price'        => $price,
            'line_total'        => $qty * $price,
            'station'           => strtoupper(trim($item['station'] ?? 'KITCHEN')),
            'notes'             => trim($item['notes'] ?? ''),
            'variants_selected' => !empty($item['variants']) ? (is_array($item['variants']) ? json_encode($item['variants']) : $item['variants']) : null,
        ]);
    }

    json_response([
        'success'      => true,
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'grand_total'  => $totals['grand_total'],
        'message'      => 'Pesanan berhasil dikirim ke kasir & dapur!',
    ]);
}

// ===================================================================
// AUTHENTICATION ROUTES (/admin/login, /admin/logout)
// ===================================================================

// Login Page (GET)
if ($requestUri === '/admin/login' && $requestMethod === 'GET') {
    if (is_logged_in()) {
        redirect('/admin');
    }
    require dirname(__DIR__) . '/views/admin/login.php';
    exit;
}

// Login Handler (POST)
if ($requestUri === '/admin/login' && $requestMethod === 'POST') {
    if (!verify_csrf_token()) {
        flash('error', 'Token keamanan tidak valid atau telah kedaluwarsa. Silakan coba lagi.');
        redirect('/admin/login');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session id to prevent session fixation attacks
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];

        // Update last_login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
        $updateStmt->execute(['id' => $user['id']]);

        flash('success', 'Selamat datang kembali, ' . $user['username'] . '!');
        redirect('/admin');
    } else {
        flash('error', 'Username atau password yang Anda masukkan salah.');
        redirect('/admin/login');
    }
}

// Logout Handler (GET)
if ($requestUri === '/admin/logout') {
    start_session_safe();
    unset($_SESSION['user']);
    session_destroy();
    redirect('/admin/login');
}

// ===================================================================
// ADMIN CMS DASHBOARD & STOCK APIS
// ===================================================================

// Dashboard Overview (GET)
if ($requestUri === '/admin') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/dashboard.php';
    exit;
}

// One-Click Stock Toggle API (POST /admin/api/toggle-stock)
if ($requestUri === '/admin/api/toggle-stock' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        json_response(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        json_response(['success' => false, 'message' => 'ID menu tidak valid.'], 400);
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT is_available FROM menus WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $current = $stmt->fetch();

    if (!$current) {
        json_response(['success' => false, 'message' => 'Menu tidak ditemukan.'], 404);
    }

    $newStatus = (int)$current['is_available'] === 1 ? 0 : 1;
    $updateStmt = $pdo->prepare("UPDATE menus SET is_available = :is_available WHERE id = :id");
    $updateStmt->execute([
        'is_available' => $newStatus,
        'id'           => $id,
    ]);

    json_response([
        'success'      => true,
        'id'           => $id,
        'is_available' => $newStatus,
        'message'      => $newStatus === 1 ? 'Menu berhasil diubah menjadi Tersedia.' : 'Menu berhasil diubah menjadi Habis.',
    ]);
}

// ===================================================================
// MENUS CRUD ROUTES
// ===================================================================

// Menus Index List (GET)
if ($requestUri === '/admin/menus') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/menus/index.php';
    exit;
}

// Menus Create Form (GET)
if ($requestUri === '/admin/menus/create' && $requestMethod === 'GET') {
    require_auth('admin');
    $menu = [];
    require dirname(__DIR__) . '/views/admin/menus/form.php';
    exit;
}

// Menus Create Handler (POST)
if ($requestUri === '/admin/menus/create' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/menus');
    }

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (int)($_POST['price'] ?? 0);
    $strikePrice = !empty($_POST['strike_price']) ? (int)$_POST['strike_price'] : null;
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $variantsJson = trim($_POST['variants_json'] ?? '');
    $station = strtoupper(trim($_POST['station'] ?? 'KITCHEN'));
    if (!in_array($station, ['KITCHEN', 'BAR'], true)) {
        $station = 'KITCHEN';
    }
    $costPrice = (int)($_POST['cost_price'] ?? 0);
    $slug = slugify($name);

    if (empty($name) || $categoryId <= 0) {
        flash('error', 'Nama menu dan kategori wajib diisi.');
        redirect('/admin/menus/create');
    }

    // Process photo upload if provided (Auto WebP 800px)
    $imageUrl = '/assets/images/food-menu-1.png';
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload = upload_image_webp($_FILES['image'], 'menus', 800, 80);
        if ($upload['success']) {
            $imageUrl = $upload['url'];
        } else {
            flash('error', 'Gagal unggah foto: ' . $upload['error']);
            redirect('/admin/menus/create');
        }
    }

    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO menus (category_id, name, slug, description, price, strike_price, cost_price, station, image_url, variants_json, is_available, is_featured, sort_order) 
        VALUES (:category_id, :name, :slug, :description, :price, :strike_price, :cost_price, :station, :image_url, :variants_json, :is_available, :is_featured, :sort_order)
    ");
    $stmt->execute([
        'category_id'   => $categoryId,
        'name'          => $name,
        'slug'          => $slug . '-' . time(),
        'description'   => $description,
        'price'         => $price,
        'strike_price'  => $strikePrice,
        'cost_price'    => $costPrice,
        'station'       => $station,
        'image_url'     => $imageUrl,
        'variants_json' => !empty($variantsJson) ? $variantsJson : null,
        'is_available'  => $isAvailable,
        'is_featured'   => $isFeatured,
        'sort_order'    => $sortOrder,
    ]);

    flash('success', 'Menu "' . $name . '" berhasil ditambahkan.');
    redirect('/admin/menus');
}

// Menus Edit Form (GET)
if ($requestUri === '/admin/menus/edit' && $requestMethod === 'GET') {
    require_auth();
    $id = (int)($_GET['id'] ?? 0);
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $menu = $stmt->fetch();

    if (!$menu) {
        flash('error', 'Menu tidak ditemukan.');
        redirect('/admin/menus');
    }

    require dirname(__DIR__) . '/views/admin/menus/form.php';
    exit;
}

// Menus Edit Handler (POST)
if ($requestUri === '/admin/menus/edit' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/menus');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (int)($_POST['price'] ?? 0);
    $strikePrice = !empty($_POST['strike_price']) ? (int)$_POST['strike_price'] : null;
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $variantsJson = trim($_POST['variants_json'] ?? '');
    $station = strtoupper(trim($_POST['station'] ?? 'KITCHEN'));
    if (!in_array($station, ['KITCHEN', 'BAR'], true)) {
        $station = 'KITCHEN';
    }
    $costPrice = (int)($_POST['cost_price'] ?? 0);

    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        flash('error', 'Menu tidak ditemukan.');
        redirect('/admin/menus');
    }

    $imageUrl = $existing['image_url'];
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload = upload_image_webp($_FILES['image'], 'menus', 800, 80);
        if ($upload['success']) {
            $imageUrl = $upload['url'];
        } else {
            flash('error', 'Gagal unggah foto: ' . $upload['error']);
            redirect('/admin/menus/edit?id=' . $id);
        }
    }

    $updateStmt = $pdo->prepare("
        UPDATE menus SET 
            category_id = :category_id,
            name = :name,
            description = :description,
            price = :price,
            strike_price = :strike_price,
            cost_price = :cost_price,
            station = :station,
            image_url = :image_url,
            variants_json = :variants_json,
            is_available = :is_available,
            is_featured = :is_featured,
            sort_order = :sort_order
        WHERE id = :id
    ");
    $updateStmt->execute([
        'category_id'   => $categoryId,
        'name'          => $name,
        'description'   => $description,
        'price'         => $price,
        'strike_price'  => $strikePrice,
        'cost_price'    => $costPrice,
        'station'       => $station,
        'image_url'     => $imageUrl,
        'variants_json' => !empty($variantsJson) ? $variantsJson : null,
        'is_available'  => $isAvailable,
        'is_featured'   => $isFeatured,
        'sort_order'    => $sortOrder,
        'id'            => $id,
    ]);

    flash('success', 'Perubahan menu "' . $name . '" berhasil disimpan.');
    redirect('/admin/menus');
}

// Menus Delete Handler (POST)
if ($requestUri === '/admin/menus/delete' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/menus');
    }

    $id = (int)($_POST['id'] ?? 0);
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = :id");
    $stmt->execute(['id' => $id]);

    flash('success', 'Menu berhasil dihapus.');
    redirect('/admin/menus');
}

// ===================================================================
// CATEGORIES CRUD ROUTES
// ===================================================================

// Categories Index (GET)
if ($requestUri === '/admin/categories') {
    require_auth('admin');
    require dirname(__DIR__) . '/views/admin/categories/index.php';
    exit;
}

// Categories Save Handler (POST)
if ($requestUri === '/admin/categories/save' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/categories');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (empty($name)) {
        flash('error', 'Nama kategori wajib diisi.');
        redirect('/admin/categories');
    }

    $pdo = db();
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name, sort_order = :sort_order WHERE id = :id");
        $stmt->execute(['name' => $name, 'sort_order' => $sortOrder, 'id' => $id]);
        flash('success', 'Kategori berhasil diperbarui.');
    } else {
        $slug = slugify($name) . '-' . time();
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order, is_active) VALUES (:name, :slug, :sort_order, 1)");
        $stmt->execute(['name' => $name, 'slug' => $slug, 'sort_order' => $sortOrder]);
        flash('success', 'Kategori baru berhasil ditambahkan.');
    }

    redirect('/admin/categories');
}

// Categories Delete Handler (POST)
if ($requestUri === '/admin/categories/delete' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/categories');
    }

    $id = (int)($_POST['id'] ?? 0);
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);

    flash('success', 'Kategori berhasil dihapus.');
    redirect('/admin/categories');
}

// ===================================================================
// OUTLETS CRUD ROUTES
// ===================================================================

// Outlets Index (GET)
if ($requestUri === '/admin/outlets') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/outlets/index.php';
    exit;
}

// Outlets Save Handler (POST)
if ($requestUri === '/admin/outlets/save' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/outlets');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $openHours = trim($_POST['open_hours'] ?? '08:00 - 22:00 WIB');
    $gmapsLink = trim($_POST['gmaps_link'] ?? '');
    $gofoodUrl = trim($_POST['gofood_url'] ?? '');
    $grabfoodUrl = trim($_POST['grabfood_url'] ?? '');
    $shopeefoodUrl = trim($_POST['shopeefood_url'] ?? '');

    if (empty($name) || empty($address)) {
        flash('error', 'Nama dan alamat cabang wajib diisi.');
        redirect('/admin/outlets');
    }

    $pdo = db();
    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE outlets SET 
                name = :name, 
                address = :address, 
                open_hours = :open_hours, 
                gmaps_link = :gmaps_link, 
                gofood_url = :gofood_url, 
                grabfood_url = :grabfood_url, 
                shopeefood_url = :shopeefood_url 
            WHERE id = :id
        ");
        $stmt->execute([
            'name'           => $name,
            'address'        => $address,
            'open_hours'     => $openHours,
            'gmaps_link'     => !empty($gmapsLink) ? $gmapsLink : null,
            'gofood_url'     => !empty($gofoodUrl) ? $gofoodUrl : null,
            'grabfood_url'   => !empty($grabfoodUrl) ? $grabfoodUrl : null,
            'shopeefood_url' => !empty($shopeefoodUrl) ? $shopeefoodUrl : null,
            'id'             => $id,
        ]);
        flash('success', 'Data cabang berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO outlets (name, address, open_hours, gmaps_link, gofood_url, grabfood_url, shopeefood_url, is_active) 
            VALUES (:name, :address, :open_hours, :gmaps_link, :gofood_url, :grabfood_url, :shopeefood_url, 1)
        ");
        $stmt->execute([
            'name'           => $name,
            'address'        => $address,
            'open_hours'     => $openHours,
            'gmaps_link'     => !empty($gmapsLink) ? $gmapsLink : null,
            'gofood_url'     => !empty($gofoodUrl) ? $gofoodUrl : null,
            'grabfood_url'   => !empty($grabfoodUrl) ? $grabfoodUrl : null,
            'shopeefood_url' => !empty($shopeefoodUrl) ? $shopeefoodUrl : null,
        ]);
        flash('success', 'Cabang baru berhasil ditambahkan.');
    }

    redirect('/admin/outlets');
}

// Outlets Delete Handler (POST)
if ($requestUri === '/admin/outlets/delete' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/outlets');
    }

    $id = (int)($_POST['id'] ?? 0);
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM outlets WHERE id = :id");
    $stmt->execute(['id' => $id]);

    flash('success', 'Cabang berhasil dihapus.');
    redirect('/admin/outlets');
}

// ===================================================================
// WHITE-LABEL BRAND SETTINGS ROUTES
// ===================================================================

// Settings Index (GET)
if ($requestUri === '/admin/settings' && $requestMethod === 'GET') {
    require_auth('admin');
    require dirname(__DIR__) . '/views/admin/settings/index.php';
    exit;
}

// Settings Save Handler (POST)
if ($requestUri === '/admin/settings' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/settings');
    }

    $siteName = trim($_POST['site_name'] ?? 'Kopi & Rasa Nusantara');
    $tagline = trim($_POST['tagline'] ?? '');
    $primaryColor = trim($_POST['primary_color'] ?? '#FF6B35');
    $secondaryColor = trim($_POST['secondary_color'] ?? '#1E293B');
    $waOrder = clean_phone_number($_POST['whatsapp_order_number'] ?? '');
    $waFranchise = clean_phone_number($_POST['whatsapp_franchise_number'] ?? '');
    $waGreeting = trim($_POST['whatsapp_greeting_template'] ?? '');
    $announcementActive = isset($_POST['is_announcement_active']) ? 1 : 0;
    $announcementText = trim($_POST['announcement_text'] ?? '');
    $metaPixel = trim($_POST['meta_pixel_id'] ?? '');
    $tiktokPixel = trim($_POST['tiktok_pixel_id'] ?? '');
    $googleAnalytics = trim($_POST['google_analytics_id'] ?? '');

    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE settings SET 
            site_name = :site_name,
            tagline = :tagline,
            primary_color = :primary_color,
            secondary_color = :secondary_color,
            whatsapp_order_number = :whatsapp_order_number,
            whatsapp_franchise_number = :whatsapp_franchise_number,
            whatsapp_greeting_template = :whatsapp_greeting_template,
            is_announcement_active = :is_announcement_active,
            announcement_text = :announcement_text,
            meta_pixel_id = :meta_pixel_id,
            tiktok_pixel_id = :tiktok_pixel_id,
            google_analytics_id = :google_analytics_id
        WHERE id = 1
    ");
    $stmt->execute([
        'site_name'                  => $siteName,
        'tagline'                    => $tagline,
        'primary_color'              => $primaryColor,
        'secondary_color'            => $secondaryColor,
        'whatsapp_order_number'      => $waOrder,
        'whatsapp_franchise_number'  => !empty($waFranchise) ? $waFranchise : null,
        'whatsapp_greeting_template' => !empty($waGreeting) ? $waGreeting : null,
        'is_announcement_active'     => $announcementActive,
        'announcement_text'          => !empty($announcementText) ? $announcementText : null,
        'meta_pixel_id'              => !empty($metaPixel) ? $metaPixel : null,
        'tiktok_pixel_id'            => !empty($tiktokPixel) ? $tiktokPixel : null,
        'google_analytics_id'        => !empty($googleAnalytics) ? $googleAnalytics : null,
    ]);

    flash('success', 'Konfigurasi Brand (White-Label) berhasil diperbarui!');
    redirect('/admin/settings');
}

// ===================================================================
// TABLE MANAGEMENT & QR STUDIO ROUTES (/admin/tables)
// ===================================================================

// Tables Index (GET)
if ($requestUri === '/admin/tables' && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/tables/index.php';
    exit;
}

// Tables Save Handler (POST)
if ($requestUri === '/admin/tables/save' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/tables');
    }

    $id = (int)($_POST['id'] ?? 0);
    $tableNumber = trim($_POST['table_number'] ?? '');
    $area = trim($_POST['area'] ?? 'Indoor');
    $pin = trim($_POST['pin'] ?? '1234');
    $qrSlug = 'meja-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $tableNumber));

    if (empty($tableNumber)) {
        flash('error', 'Nomor meja wajib diisi.');
        redirect('/admin/tables');
    }

    $pdo = db();
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE tables SET table_number = :table_number, area = :area, pin = :pin WHERE id = :id");
        $stmt->execute(['table_number' => $tableNumber, 'area' => $area, 'pin' => $pin, 'id' => $id]);
        flash('success', 'Data Meja ' . $tableNumber . ' berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO tables (table_number, area, qr_slug, pin, status) VALUES (:table_number, :area, :qr_slug, :pin, 'AVAILABLE')");
        $stmt->execute(['table_number' => $tableNumber, 'area' => $area, 'qr_slug' => $qrSlug . '-' . time(), 'pin' => $pin]);
        flash('success', 'Meja baru berhasil ditambahkan.');
    }

    redirect('/admin/tables');
}

// Tables Quick Status Toggle (POST)
if ($requestUri === '/admin/tables/status' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/tables');
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? 'AVAILABLE');

    $pdo = db();
    $stmt = $pdo->prepare("UPDATE tables SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);

    flash('success', 'Status meja berhasil diperbarui menjadi ' . $status);
    redirect('/admin/tables');
}

// ===================================================================
// KITCHEN DISPLAY SYSTEM (KDS) ROUTES (/admin/kds & /kds)
// ===================================================================

// KDS View (GET)
if (($requestUri === '/admin/kds' || $requestUri === '/kds') && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/kds/index.php';
    exit;
}

// KDS Item Status Bump API (POST /admin/kds/api/bump)
if ($requestUri === '/admin/kds/api/bump' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        json_response(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
    }

    $itemId = (int)($_POST['item_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'COOKING');

    $pdo = db();
    $stmt = $pdo->prepare("UPDATE order_items SET item_status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $itemId]);

    json_response(['success' => true, 'item_id' => $itemId, 'status' => $status]);
}

// ===================================================================
// CASHIER POS & ORDERS ROUTES (/admin/orders)
// ===================================================================

// Orders List (GET)
if ($requestUri === '/admin/orders' && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/orders/index.php';
    exit;
}

// Order Status & Payment Update (POST)
if ($requestUri === '/admin/orders/status' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        if (isset($_POST['format']) && $_POST['format'] === 'json') {
            json_response(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
        }
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/orders');
    }

    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'COMPLETED');
    $paymentStatus = trim($_POST['payment_status'] ?? '');

    $pdo = db();
    if (!empty($paymentStatus)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status, payment_status = :payment_status WHERE id = :id");
        $stmt->execute(['status' => $status, 'payment_status' => $paymentStatus, 'id' => $orderId]);
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $orderId]);
    }

    if (isset($_POST['format']) && $_POST['format'] === 'json') {
        json_response(['success' => true, 'order_id' => $orderId, 'status' => $status]);
    }

    flash('success', 'Status pesanan berhasil diperbarui menjadi ' . $status);
    redirect('/admin/orders');
}

// Order Thermal Receipt (GET /admin/orders/receipt)
if ($requestUri === '/admin/orders/receipt' && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/orders/receipt.php';
    exit;
}

// ===================================================================
// INVENTORY & RECIPES (BOM / HPP) ROUTES (/admin/inventory)
// ===================================================================

// Inventory Index (GET)
if ($requestUri === '/admin/inventory' && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/inventory/index.php';
    exit;
}

// Inventory Save Handler (POST)
if ($requestUri === '/admin/inventory/save' && $requestMethod === 'POST') {
    require_auth('admin');
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/inventory');
    }

    $id = (int)($_POST['id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $unit = strtoupper(trim($_POST['unit'] ?? 'GRAM'));
    $costPerUnit = (int)($_POST['cost_per_unit'] ?? 0);
    $currentStock = (float)($_POST['current_stock'] ?? 0);
    $minThreshold = (float)($_POST['min_threshold'] ?? 10);

    if (empty($name) || empty($sku)) {
        flash('error', 'SKU dan Nama bahan baku wajib diisi.');
        redirect('/admin/inventory');
    }

    $pdo = db();
    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE ingredients SET 
                sku = :sku, 
                name = :name, 
                unit = :unit, 
                cost_per_unit = :cost_per_unit, 
                current_stock = :current_stock, 
                min_threshold = :min_threshold 
            WHERE id = :id
        ");
        $stmt->execute([
            'sku'           => $sku,
            'name'          => $name,
            'unit'          => $unit,
            'cost_per_unit' => $costPerUnit,
            'current_stock' => $currentStock,
            'min_threshold' => $minThreshold,
            'id'            => $id,
        ]);
        flash('success', 'Bahan baku "' . $name . '" berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO ingredients (sku, name, unit, cost_per_unit, current_stock, min_threshold, is_active) 
            VALUES (:sku, :name, :unit, :cost_per_unit, :current_stock, :min_threshold, 1)
        ");
        $stmt->execute([
            'sku'           => $sku,
            'name'          => $name,
            'unit'          => $unit,
            'cost_per_unit' => $costPerUnit,
            'current_stock' => $currentStock,
            'min_threshold' => $minThreshold,
        ]);
        flash('success', 'Bahan baku baru "' . $name . '" berhasil ditambahkan.');
    }

    redirect('/admin/inventory');
}

// Inventory Restock Action (POST /admin/inventory/restock)
if ($requestUri === '/admin/inventory/restock' && $requestMethod === 'POST') {
    require_auth();
    if (!verify_csrf_token()) {
        flash('error', 'Token CSRF tidak valid.');
        redirect('/admin/inventory');
    }

    $id = (int)($_POST['id'] ?? 0);
    $addAmount = (float)($_POST['add_amount'] ?? 0);

    if ($id > 0 && $addAmount > 0) {
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE ingredients SET current_stock = current_stock + :amount WHERE id = :id");
        $stmt->execute(['amount' => $addAmount, 'id' => $id]);
        flash('success', 'Stok bahan baku berhasil ditambah.');
    } else {
        flash('error', 'Kuantitas restock tidak valid.');
    }

    redirect('/admin/inventory');
}

// Inventory CSV Export (GET/POST /admin/inventory/export)
if ($requestUri === '/admin/inventory/export') {
    require_auth();
    $pdo = db();
    $ingredients = $pdo->query("SELECT sku, name, unit, cost_per_unit, current_stock, min_threshold, is_active FROM ingredients ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['SKU', 'Nama Bahan Baku', 'Satuan Unit', 'Harga Beli Satuan (Rp)', 'Stok Saat Ini', 'Batas Minimum', 'Status'];
    $rows = [];
    foreach ($ingredients as $ing) {
        $rows[] = [
            $ing['sku'],
            $ing['name'],
            $ing['unit'],
            $ing['cost_per_unit'],
            $ing['current_stock'],
            $ing['min_threshold'],
            $ing['is_active'] ? 'Aktif' : 'Non-Aktif',
        ];
    }
    export_csv('inventori-bahan-' . date('Ymd-His') . '.csv', $headers, $rows);
}

// Business Analytics Overview (GET /admin/analytics)
if ($requestUri === '/admin/analytics' && $requestMethod === 'GET') {
    require_auth();
    require dirname(__DIR__) . '/views/admin/analytics/index.php';
    exit;
}

// Dashboard 14-day Stats API for Chart.js (GET /admin/dashboard/stats)
if ($requestUri === '/admin/dashboard/stats' && $requestMethod === 'GET') {
    require_auth();
    $pdo = db();
    $days = [];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $days[$d] = 0;
    }
    try {
        $stmt = $pdo->query("
            SELECT DATE(created_at) as order_date, SUM(grand_total) as daily_total
            FROM orders
            WHERE payment_status = 'PAID' AND created_at >= date('now', '-14 days')
            GROUP BY DATE(created_at)
        ");
        while ($row = $stmt->fetch()) {
            if (isset($days[$row['order_date']])) {
                $days[$row['order_date']] = (int)$row['daily_total'];
            }
        }
    } catch (\Throwable $e) {}

    json_response([
        'labels' => array_map(fn($d) => date('d M', strtotime($d)), array_keys($days)),
        'revenues' => array_values($days),
    ]);
}

// KDS Smart Polling API (GET /admin/kds/api/orders)
if ($requestUri === '/admin/kds/api/orders' && $requestMethod === 'GET') {
    require_auth();
    $pdo = db();
    try {
        $ids = $pdo->query("
            SELECT id FROM orders 
            WHERE status IN ('PENDING', 'CONFIRMED', 'COOKING', 'READY')
            ORDER BY created_at ASC
        ")->fetchAll(PDO::FETCH_COLUMN);
        json_response([
            'success' => true,
            'order_ids' => array_map('intval', $ids ?: []),
            'count' => count($ids ?: []),
        ]);
    } catch (\Throwable $e) {
        json_response(['success' => false, 'order_ids' => [], 'count' => 0]);
    }
}

// Public Menu JSON API (GET /api/menu)
if ($requestUri === '/api/menu' && $requestMethod === 'GET') {
    $pdo = db();
    $menus = $pdo->query("
        SELECT m.id, m.name, m.slug, m.description, m.price, m.strike_price, m.image_url, m.station, m.is_available, m.is_featured, c.name as category_name
        FROM menus m
        JOIN categories c ON m.category_id = c.id
        WHERE m.is_available = 1
        ORDER BY c.sort_order ASC, m.sort_order ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    json_response([
        'success' => true,
        'count' => count($menus),
        'data' => $menus,
    ]);
}

// Fallback 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex items-center justify-center min-h-screen p-4">
    <div class="text-center space-y-4 max-w-sm">
        <div class="flex justify-center text-slate-400"><?= icon('coffee', 'w-16 h-16') ?></div>
        <h1 class="text-2xl font-black text-slate-900">404 - Halaman Tidak Ditemukan</h1>
        <p class="text-xs text-slate-500">Halaman yang Anda tuju mungkin telah dipindahkan atau tautan yang Anda klik tidak tepat.</p>
        <a href="/" class="inline-block px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow hover:bg-slate-800 transition">
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
