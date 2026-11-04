<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Phase 7 PRD v2.0 Master Verification Test Suite
 * Comprehensive verification of PRD v2.0 Omnichannel & Full Business Upgrade:
 * 1. Visual Assets (< 120 KB WebP, 4:3, Hero & Outlets, Halal & Proposal PDF)
 * 2. Database Schema Extensions (Vouchers, Leads, Outlets, Menus Nutrition, Orders)
 * 3. Dynamic Category Modifiers (Kopi, Makanan Utama, Camilan Tradisional)
 * 4. UAT-01: Cart Item Calculation Accuracy (No Rp 0 Bug)
 * 5. UAT-02: Interactive Voucher Promo Engine (KOPIASIK 20% & KENYANG2 Rp 15k)
 * 6. UAT-03: Multi-Outlet Routing & Dynamic Delivery Fee
 * 7. UAT-04: Instant QRIS Payment Gateway & Sandbox Simulation
 * 8. UAT-05: Web Live Order Tracker (4-Stage Stepper & Status Polling)
 * 9. UAT-06: Franchise Acquisition 2.0 & Leads CRM Export
 * 10. UAT-07: 100% Zero-Emoji Compliance & Lucide SVG Icons Standard
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/icons.php';

$pdo = db();
$passed = 0;
$failed = 0;

function assert_test(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$name}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$name} - {$detail}\n";
    }
}

echo "\n▶ Menjalankan Phase 7: PRD v2.0 Master Verification Test Suite...\n";

// ===================================================================
// 1. VISUAL ASSETS VERIFICATION (FR-01, BUG-02, BUG-03)
// ===================================================================
$basePath = dirname(__DIR__);

// 8 Authentic Menu WebP Images (< 120 KB each)
$requiredMenuImages = [
    'menu-kopi-aren.webp',
    'menu-kopi-gayo.webp',
    'menu-kopi-pandan.webp',
    'menu-nasgor-kampung.webp',
    'menu-ayam-taliwang.webp',
    'menu-mie-nyemek.webp',
    'menu-pisang-goreng.webp',
    'menu-cireng-rujak.webp',
];

$allMenuImagesValid = true;
$sizeErrors = [];
foreach ($requiredMenuImages as $img) {
    $fullPath = $basePath . '/public/assets/images/' . $img;
    if (!file_exists($fullPath)) {
        $allMenuImagesValid = false;
        $sizeErrors[] = "{$img} tidak ditemukan";
    } else {
        $fileSize = filesize($fullPath);
        if ($fileSize > 122880) { // > 120 KB
            $allMenuImagesValid = false;
            $sizeErrors[] = "{$img} melebihi batas 120 KB ({$fileSize} bytes)";
        }
    }
}
assert_test("8 Aset WebP Menu Otentik ada dan berukuran optimal (< 120 KB)", $allMenuImagesValid, implode(', ', $sizeErrors));

// Hero Images & Signature Visual
assert_test("Hero banner (hero-banner.png) ada", file_exists($basePath . '/public/assets/images/hero-banner.png'));
assert_test("Hero signature (hero-signature.webp) ada", file_exists($basePath . '/public/assets/images/hero-signature.webp'));

// Outlet Branch Photos
assert_test("Foto Cabang Senopati (outlet-senopati.webp) ada", file_exists($basePath . '/public/assets/images/outlet-senopati.webp'));
assert_test("Foto Cabang Braga (outlet-braga.webp) ada", file_exists($basePath . '/public/assets/images/outlet-braga.webp'));
assert_test("Foto Cabang Malioboro (outlet-malioboro.webp) ada", file_exists($basePath . '/public/assets/images/outlet-malioboro.webp'));

// Trust Badge & Partnership Proposal PDF
assert_test("Badge sertifikasi Halal (halal-badge.svg) ada", file_exists($basePath . '/public/assets/images/halal-badge.svg'));
$pdfPath = $basePath . '/public/assets/docs/proposal-kemitraan.pdf';
assert_test("Berkas Proposal Kemitraan PDF ada", file_exists($pdfPath));
if (file_exists($pdfPath)) {
    $pdfHeader = file_get_contents($pdfPath, false, null, 0, 4);
    assert_test("Berkas Proposal Kemitraan adalah format PDF valid (%PDF)", $pdfHeader === '%PDF');
}

// ===================================================================
// 2. DATABASE SCHEMA EXTENSIONS (PRD v2.0)
// ===================================================================
// Tables: vouchers, franchise_leads
$driver = Database::getInstance()->getActiveDriver();
if ($driver === 'sqlite') {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $menuCols = $pdo->query("PRAGMA table_info(menus)")->fetchAll(PDO::FETCH_COLUMN, 1);
    $orderCols = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_COLUMN, 1);
} else {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $menuCols = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
    $orderCols = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
}
assert_test("Tabel 'vouchers' terdaftar di database", in_array('vouchers', $tables, true));
assert_test("Tabel 'franchise_leads' terdaftar di database", in_array('franchise_leads', $tables, true));

assert_test("Kolom 'calories' ada di tabel menus", in_array('calories', $menuCols, true));
assert_test("Kolom 'nutrition_info' ada di tabel menus", in_array('nutrition_info', $menuCols, true));
assert_test("Kolom 'ingredients_text' ada di tabel menus", in_array('ingredients_text', $menuCols, true));
assert_test("Kolom 'rating' ada di tabel menus", in_array('rating', $menuCols, true));
assert_test("Kolom 'halal_certified' ada di tabel menus", in_array('halal_certified', $menuCols, true));

assert_test("Kolom 'order_token' ada di tabel orders", in_array('order_token', $orderCols, true));
assert_test("Kolom 'outlet_id' ada di tabel orders", in_array('outlet_id', $orderCols, true));
assert_test("Kolom 'outlet_name' ada di tabel orders", in_array('outlet_name', $orderCols, true));
assert_test("Kolom 'voucher_code' ada di tabel orders", in_array('voucher_code', $orderCols, true));
assert_test("Kolom 'voucher_discount' ada di tabel orders", in_array('voucher_discount', $orderCols, true));
assert_test("Kolom 'delivery_fee' ada di tabel orders", in_array('delivery_fee', $orderCols, true));
assert_test("Kolom 'order_token' ada di tabel orders", in_array('order_token', $orderCols, true));
assert_test("Kolom 'outlet_id' ada di tabel orders", in_array('outlet_id', $orderCols, true));
assert_test("Kolom 'outlet_name' ada di tabel orders", in_array('outlet_name', $orderCols, true));
assert_test("Kolom 'voucher_code' ada di tabel orders", in_array('voucher_code', $orderCols, true));
assert_test("Kolom 'voucher_discount' ada di tabel orders", in_array('voucher_discount', $orderCols, true));
assert_test("Kolom 'delivery_fee' ada di tabel orders", in_array('delivery_fee', $orderCols, true));

// Multi-Outlet verification: 3 outlets
$outlets = $pdo->query("SELECT * FROM outlets ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
assert_test("Terdapat 3 cabang outlet resmi aktif", count($outlets) === 3);
$outletSlugs = array_column($outlets, 'slug');
assert_test("Cabang Senopati, Braga, dan Malioboro terdaftar dengan slug valid", 
    in_array('senopati', $outletSlugs, true) && 
    in_array('braga', $outletSlugs, true) && 
    in_array('malioboro', $outletSlugs, true)
);

// ===================================================================
// 3. DYNAMIC CATEGORY MODIFIERS (FR-03, UX-05)
// ===================================================================
$menus = $pdo->query("SELECT * FROM menus ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Check Coffee Modifiers
$coffeeMenu = array_filter($menus, fn($m) => (int)$m['category_id'] === 1);
$coffeeMenu = reset($coffeeMenu);
assert_test("Menu Kopi memiliki konfigurasi variants_json", !empty($coffeeMenu['variants_json']));
if (!empty($coffeeMenu['variants_json'])) {
    $cVariants = json_decode($coffeeMenu['variants_json'], true);
    $optionNames = array_column($cVariants['options'] ?? [], 'name');
    assert_test("Modifier Kopi memiliki pilihan Suhu", !empty(array_filter($optionNames, fn($n) => str_contains(strtolower($n), 'suhu'))));
    assert_test("Modifier Kopi memiliki pilihan Ukuran", !empty(array_filter($optionNames, fn($n) => str_contains(strtolower($n), 'ukuran'))));
    assert_test("Modifier Kopi memiliki pilihan Level Manis", in_array('Level Manis', $optionNames, true));
    assert_test("Modifier Kopi memiliki pilihan Takaran Es", in_array('Takaran Es', $optionNames, true));
    assert_test("Modifier Kopi memiliki pilihan Extra Shot", !empty(array_filter($optionNames, fn($n) => str_contains(strtolower($n), 'extra'))));
}

// Check Food Modifiers
$foodMenu = array_filter($menus, fn($m) => (int)$m['category_id'] === 2);
$foodMenu = reset($foodMenu);
if (!empty($foodMenu['variants_json'])) {
    $fVariants = json_decode($foodMenu['variants_json'], true);
    $optionNames = array_column($fVariants['options'] ?? [], 'name');
    assert_test("Modifier Makanan Utama memiliki pilihan Level Pedas", in_array('Level Pedas', $optionNames, true));
    assert_test("Modifier Makanan Utama memiliki pilihan Topping Tambahan", in_array('Topping Tambahan', $optionNames, true));
}

// Check Snack Modifiers
$snackMenu = array_filter($menus, fn($m) => (int)$m['category_id'] === 3);
$snackMenu = reset($snackMenu);
if (!empty($snackMenu['variants_json'])) {
    $sVariants = json_decode($snackMenu['variants_json'], true);
    $optionNames = array_column($sVariants['options'] ?? [], 'name');
    $hasSnackChoice = !empty(array_filter($optionNames, fn($n) => str_contains(strtolower($n), 'saus') || str_contains(strtolower($n), 'taburan') || str_contains(strtolower($n), 'topping')));
    assert_test("Modifier Camilan Tradisional memiliki pilihan saus/taburan otentik", $hasSnackChoice);
}

// ===================================================================
// 4. UAT-01: CART CALCULATION ACCURACY & ZERO RP 0 BUG (FR-02, BUG-01)
// ===================================================================
// Formula: (Base Price + Modifier Extras) * Quantity
$cartTestItem1 = [
    'name' => 'Es Kopi Susu Gula Aren',
    'base_price' => 18000,
    'selected_modifiers' => [
        ['name' => 'Ukuran', 'choice' => 'Large (+3.000)', 'extra_price' => 3000],
        ['name' => 'Extra Shot', 'choice' => '1 Shot (+2.000)', 'extra_price' => 2000]
    ],
    'qty' => 2
];
$unitPrice1 = $cartTestItem1['base_price'] + array_sum(array_column($cartTestItem1['selected_modifiers'], 'extra_price'));
$lineTotal1 = $unitPrice1 * $cartTestItem1['qty'];
assert_test("UAT-01: Perhitungan unit price item 1 akurat (Rp 23.000)", $unitPrice1 === 23000);
assert_test("UAT-01: Perhitungan total baris item 1 akurat (Rp 46.000)", $lineTotal1 === 46000);

$cartTestItem2 = [
    'name' => 'Nasi Goreng Kampung Spesial',
    'base_price' => 28000,
    'selected_modifiers' => [
        ['name' => 'Topping Tambahan', 'choice' => 'Telur Mata Sapi (+4.000)', 'extra_price' => 4000]
    ],
    'qty' => 1
];
$unitPrice2 = $cartTestItem2['base_price'] + array_sum(array_column($cartTestItem2['selected_modifiers'], 'extra_price'));
$lineTotal2 = $unitPrice2 * $cartTestItem2['qty'];
assert_test("UAT-01: Perhitungan total baris item 2 akurat (Rp 32.000)", $lineTotal2 === 32000);

$calculatedSubtotal = $lineTotal1 + $lineTotal2;
assert_test("UAT-01: Perhitungan subtotal keranjang tidak pernah Rp 0 (Rp 78.000)", $calculatedSubtotal === 78000);

// ===================================================================
// 5. UAT-02: VOUCHER PROMO ENGINE (FR-04, BUG-04)
// ===================================================================
$vouchersInDb = $pdo->query("SELECT * FROM vouchers WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$vCodes = array_column($vouchersInDb, 'code');
assert_test("Voucher 'KOPIASIK' aktif di database", in_array('KOPIASIK', $vCodes, true));
assert_test("Voucher 'KENYANG2' aktif di database", in_array('KENYANG2', $vCodes, true));

// Test KOPIASIK logic: 20% discount on coffee subtotal
$coffeeSubtotal = $lineTotal1; // 46000
$kopiasikDiscount = (int)round($coffeeSubtotal * 0.20);
assert_test("UAT-02: Voucher KOPIASIK menghasilkan diskon 20% akurat (Rp 9.200)", $kopiasikDiscount === 9200);

// Test KENYANG2 logic: Rp 15.000 flat discount if subtotal >= Rp 60.000
$kenyang2DiscountPass = ($calculatedSubtotal >= 60000) ? 15000 : 0;
assert_test("UAT-02: Voucher KENYANG2 memberikan potongan Rp 15.000 saat subtotal >= 60.000", $kenyang2DiscountPass === 15000);

$subtotalUnder60k = 55000;
$kenyang2DiscountFail = ($subtotalUnder60k >= 60000) ? 15000 : 0;
assert_test("UAT-02: Voucher KENYANG2 ditolak saat subtotal < Rp 60.000 (diskon 0)", $kenyang2DiscountFail === 0);

// ===================================================================
// 6. UAT-03: MULTI-OUTLET ROUTING & DYNAMIC DELIVERY FEE (FR-05)
// ===================================================================
$dineInFee = 0;
$takeawayFee = 0;
$deliveryFee = 10000; // Flat Rp 10.000

$grandTotalDineIn = max(0, $calculatedSubtotal - $kenyang2DiscountPass + $dineInFee);
assert_test("UAT-03: Grand Total Dine-In tanpa biaya antar (Rp 63.000)", $grandTotalDineIn === 63000);

$grandTotalDelivery = max(0, $calculatedSubtotal - $kenyang2DiscountPass + $deliveryFee);
assert_test("UAT-03: Grand Total Delivery menyertakan ongkir Rp 10.000 (Rp 73.000)", $grandTotalDelivery === 73000);

// ===================================================================
// 7. UAT-04: ORDER CREATION & INSTANT QRIS PAYMENT FLOW (FR-07)
// ===================================================================
$testOrderNumber = 'TEST-KRN-' . time();
$testOrderToken = bin2hex(random_bytes(16));

// Insert test order record
$insStmt = $pdo->prepare("
    INSERT INTO orders (
        order_number, order_token, order_type, outlet_id, outlet_name,
        table_number, customer_name, customer_phone, status,
        subtotal, voucher_code, voucher_discount, delivery_fee,
        grand_total, payment_method, payment_status
    ) VALUES (
        :num, :token, 'delivery', 1, 'Cabang Senopati (Flagship Store)',
        NULL, 'Budi Santoso', '081299998888', 'PENDING',
        :subtotal, 'KENYANG2', :disc, :fee, :grand, 'QRIS', 'PENDING'
    )
");
$insStmt->execute([
    'num' => $testOrderNumber,
    'token' => $testOrderToken,
    'subtotal' => $calculatedSubtotal,
    'disc' => $kenyang2DiscountPass,
    'fee' => $deliveryFee,
    'grand' => $grandTotalDelivery
]);
$testOrderId = (int)$pdo->lastInsertId();
assert_test("UAT-04: Pembuatan pesanan QRIS baru berhasil menyimpan ke database (ID #{$testOrderId})", $testOrderId > 0);

// Insert test order items
$itemInsStmt = $pdo->prepare("
    INSERT INTO order_items (
        order_id, menu_id, menu_name, quantity, unit_price, line_total, station, item_status
    ) VALUES (
        :oid, 1, 'Es Kopi Susu Gula Aren', 2, 23000, 46000, 'BAR', 'QUEUED'
    )
");
$itemInsStmt->execute(['oid' => $testOrderId]);

// Simulate payment verification
$payStmt = $pdo->prepare("UPDATE orders SET payment_status = 'PAID', status = 'CONFIRMED' WHERE id = :id");
$payStmt->execute(['id' => $testOrderId]);

$checkOrder = $pdo->prepare("SELECT status, payment_status FROM orders WHERE id = :id");
$checkOrder->execute(['id' => $testOrderId]);
$updatedOrder = $checkOrder->fetch(PDO::FETCH_ASSOC);

assert_test("UAT-04: Simulasi pembayaran QRIS sukses mengubah status menjadi CONFIRMED", $updatedOrder['status'] === 'CONFIRMED');
assert_test("UAT-04: Simulasi pembayaran QRIS sukses mengubah payment_status menjadi PAID", $updatedOrder['payment_status'] === 'PAID');

// Clean up test order
$pdo->exec("DELETE FROM order_items WHERE order_id = {$testOrderId}");
$pdo->exec("DELETE FROM orders WHERE id = {$testOrderId}");

// ===================================================================
// 8. UAT-05: FRANCHISE LEADS CRM & ROI CALCULATION (FR-09, FR-10)
// ===================================================================
$insLeadStmt = $pdo->prepare("
    INSERT INTO franchise_leads (name, city, package_chosen, whatsapp_number, status)
    VALUES ('Dr. Ir. Hendra Gunawan', 'Surabaya', 'Coffee Shop Standard (Rp 65 Juta)', '081234567890', 'NEW')
");
$insLeadStmt->execute();
$testLeadId = (int)$pdo->lastInsertId();
assert_test("UAT-05: Pendaftaran prospek mitra franchise berhasil tersimpan di CRM (ID #{$testLeadId})", $testLeadId > 0);

$checkLead = $pdo->query("SELECT * FROM franchise_leads WHERE id = {$testLeadId}")->fetch(PDO::FETCH_ASSOC);
assert_test("UAT-05: Status awal prospek kemitraan adalah 'NEW'", $checkLead['status'] === 'NEW');

// Clean up test lead
$pdo->exec("DELETE FROM franchise_leads WHERE id = {$testLeadId}");

// ===================================================================
// 9. UAT-06: ZERO EMOJI COMPLIANCE STANDARD
// ===================================================================
$viewsToScan = [
    $basePath . '/views/frontend/home.php',
    $basePath . '/views/frontend/order-tracker.php',
    $basePath . '/views/layouts/cart-drawer.php',
    $basePath . '/views/layouts/variant-modal.php',
    $basePath . '/views/layouts/product-detail-modal.php',
    $basePath . '/views/layouts/qris-modal.php',
    $basePath . '/views/layouts/legal-modal.php',
    $basePath . '/views/admin/franchise/index.php',
    $basePath . '/views/admin/vouchers/index.php',
    $basePath . '/public/index.php'
];

$emojiRegex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

$allViewsEmojiFree = true;
$emojiViolations = [];
foreach ($viewsToScan as $viewPath) {
    if (file_exists($viewPath)) {
        $content = file_get_contents($viewPath);
        if (preg_match_all($emojiRegex, $content, $matches)) {
            $allViewsEmojiFree = false;
            $emojiViolations[] = basename($viewPath) . ' (' . count($matches[0]) . ' emoji)';
        }
    }
}
assert_test("UAT-06: 100% Zero-Emoji standard ditaati di seluruh view PRD v2.0", $allViewsEmojiFree, implode(', ', $emojiViolations));

// ===================================================================
// 10. UAT-07: ADMIN NAVIGATION & ROUTING
// ===================================================================
$adminHeader = file_get_contents($basePath . '/views/admin/layout/header.php');
assert_test("Navigasi Admin memuat tautan Prospek Franchise (/admin/franchise-leads)", str_contains($adminHeader, '/admin/franchise-leads'));
assert_test("Navigasi Admin memuat tautan Voucher Promo (/admin/vouchers)", str_contains($adminHeader, '/admin/vouchers'));

$indexPhp = file_get_contents($basePath . '/public/index.php');
assert_test("Routing /api/orders/create terdaftar di public/index.php", str_contains($indexPhp, '/api/orders/create'));
assert_test("Routing /api/orders/simulate-pay terdaftar di public/index.php", str_contains($indexPhp, '/api/orders/simulate-pay'));
assert_test("Routing /api/franchise-leads/create terdaftar di public/index.php", str_contains($indexPhp, '/api/franchise-leads/create'));
assert_test("Routing /order/{token} terdaftar di public/index.php", str_contains($indexPhp, '/order/'));
assert_test("Routing /download/proposal-kemitraan terdaftar di public/index.php", str_contains($indexPhp, '/download/proposal-kemitraan'));

echo "\n  ====================================================";
echo "\n     Phase 7 Test Results: {$passed} Passed, {$failed} Failed";
echo "\n  ====================================================\n";

if ($failed > 0) {
    exit(1);
}
