<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Phase 1 Verification Test Suite
 * Tests Database Connection, Migrations, Seeds, Helper Functions, WebP Resizer & Router
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

echo "====================================================\n";
echo "   FnB Boilerplate - Phase 1 Verification Suite     \n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name}" . ($details ? ": {$details}" : '') . "\n";
        $failed++;
    }
}

// ----------------------------------------------------
// Test 1: Configuration Loading
// ----------------------------------------------------
$env = get_config();
assert_test("Config loaded properly", is_array($env) && isset($env['database']), "env array missing database config");
assert_test("Database driver configured", in_array(get_config('database.driver'), ['mysql', 'sqlite']), "invalid driver");

// ----------------------------------------------------
// Test 2: Database Singleton & Connection
// ----------------------------------------------------
try {
    $dbInstance = Database::getInstance();
    $pdo = db();
    $driver = $dbInstance->getActiveDriver();
    assert_test("PDO instance created via singleton db()", $pdo instanceof PDO);
    assert_test("Active database driver detected ({$driver})", !empty($driver));
} catch (\Throwable $e) {
    assert_test("Database connection", false, $e->getMessage());
}

// Ensure SQLite has schema & seed initialized if sqlite
if ($driver === 'sqlite') {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='menus'")->fetch();
    if (!$tables) {
        $dbInstance->initializeSqlite();
    }
}

// ----------------------------------------------------
// Test 3: Table Existence & Row Counts
// ----------------------------------------------------
$expectedTables = ['settings', 'categories', 'menus', 'promos', 'outlets', 'users'];
foreach ($expectedTables as $tbl) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM `{$tbl}`");
        $count = (int)$stmt->fetch()['cnt'];
        assert_test("Table '{$tbl}' exists with data (count: {$count})", $count > 0);
    } catch (\Throwable $e) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM \"{$tbl}\"");
            $count = (int)$stmt->fetch()['cnt'];
            assert_test("Table '{$tbl}' exists with data (count: {$count})", $count > 0);
        } catch (\Throwable $e2) {
            assert_test("Table '{$tbl}' exists", false, $e2->getMessage());
        }
    }
}

// ----------------------------------------------------
// Test 4: Verify Seed Content ("Kopi & Rasa Nusantara")
// ----------------------------------------------------
$settings = get_setting();
assert_test("Settings site_name is 'Kopi & Rasa Nusantara'", ($settings['site_name'] ?? '') === 'Kopi & Rasa Nusantara', "Got: " . ($settings['site_name'] ?? 'null'));
assert_test("Settings primary_color is '#FF6B35'", ($settings['primary_color'] ?? '') === '#FF6B35');
assert_test("Settings secondary_color is '#1E293B'", ($settings['secondary_color'] ?? '') === '#1E293B');

// Categories count (expected 3)
$catStmt = $pdo->query("SELECT COUNT(*) as cnt FROM categories");
$catCount = (int)$catStmt->fetch()['cnt'];
assert_test("Categories count is exactly 3", $catCount === 3, "Got {$catCount}");

// Menus count (expected 8)
$menuStmt = $pdo->query("SELECT COUNT(*) as cnt FROM menus");
$menuCount = (int)$menuStmt->fetch()['cnt'];
assert_test("Menus count is exactly 8", $menuCount === 8, "Got {$menuCount}");

// Check Sold Out / Habis Menu
$soldOutStmt = $pdo->query("SELECT name, is_available FROM menus WHERE is_available = 0");
$soldOut = $soldOutStmt->fetch();
assert_test("Menu with 'Sold Out' status exists ('{$soldOut['name']}')", !empty($soldOut) && $soldOut['is_available'] == 0);

// Check Variants JSON
$variantStmt = $pdo->query("SELECT variants_json FROM menus WHERE variants_json IS NOT NULL LIMIT 1");
$variantRow = $variantStmt->fetch();
$variantDecoded = json_decode($variantRow['variants_json'] ?? '', true);
assert_test("Menu variants_json parses as valid JSON", is_array($variantDecoded) && isset($variantDecoded['options']));

// Outlets count (expected 3)
$outletStmt = $pdo->query("SELECT COUNT(*) as cnt FROM outlets");
$outletCount = (int)$outletStmt->fetch()['cnt'];
assert_test("Outlets count is exactly 3", $outletCount === 3, "Got {$outletCount}");

// Users verification & Password bcrypt check
$userStmt = $pdo->prepare("SELECT username, password_hash, role FROM users WHERE username = :username");
$userStmt->execute(['username' => 'admin']);
$adminUser = $userStmt->fetch();
assert_test("User 'admin' exists", !empty($adminUser));
assert_test("User 'admin' password matches 'admin123' via password_verify()", !empty($adminUser) && password_verify('admin123', $adminUser['password_hash']));

$userStmt->execute(['username' => 'kasir']);
$kasirUser = $userStmt->fetch();
assert_test("User 'kasir' exists", !empty($kasirUser));
assert_test("User 'kasir' password matches 'kasir123' via password_verify()", !empty($kasirUser) && password_verify('kasir123', $kasirUser['password_hash']));

// ----------------------------------------------------
// Test 5: Helper Functions (XSS Sanitization, Currency, Slug, Phone, WhatsApp)
// ----------------------------------------------------
// e() XSS sanitization
$xssInput = '<script>alert("XSS")</script>&"\'';
$escaped = e($xssInput);
assert_test("e() escapes XSS payloads", !str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'));

// format_rupiah()
assert_test("format_rupiah(25000) returns 'Rp 25.000'", format_rupiah(25000) === 'Rp 25.000');
assert_test("format_rupiah(1500000, false) returns '1.500.000'", format_rupiah(1500000, false) === '1.500.000');

// clean_phone_number()
assert_test("clean_phone_number('0812-3456-7890') returns '6281234567890'", clean_phone_number('0812-3456-7890') === '6281234567890');

// format_whatsapp_url()
$waUrl = format_whatsapp_url('081234567890', "Pesanan Baru\nTotal: 25.000");
assert_test("format_whatsapp_url() builds proper wa.me URL", str_starts_with($waUrl, 'https://wa.me/6281234567890?text='));

// slugify()
assert_test("slugify('Kopi Susu Gula Aren!') returns 'kopi-susu-gula-aren'", slugify('Kopi Susu Gula Aren!') === 'kopi-susu-gula-aren');

// CSRF Token & Validation
$token = csrf_token();
assert_test("csrf_token() generates 64-char hex string", is_string($token) && strlen($token) === 64);
assert_test("verify_csrf_token() validates matching token", verify_csrf_token($token));
assert_test("verify_csrf_token() rejects invalid token", !verify_csrf_token('invalid-token-12345'));

// ----------------------------------------------------
// Test 6: Auto-WebP Image Resizer & Upload Test (GD)
// ----------------------------------------------------
// Create a temporary 1200x800 test PNG image
$testWidth = 1200;
$testHeight = 800;
$im = imagecreatetruecolor($testWidth, $testHeight);
$bg = imagecolorallocate($im, 255, 107, 53); // #FF6B35
imagefilledrectangle($im, 0, 0, $testWidth, $testHeight, $bg);
$textColor = imagecolorallocate($im, 255, 255, 255);
imagestring($im, 5, 20, 20, "FnB Phase 1 Test", $textColor);

$tempImgPath = sys_get_temp_dir() . '/test_fnb_' . time() . '.png';
imagepng($im, $tempImgPath);
imagedestroy($im);

// Simulate $_FILES array
$fakeFile = [
    'name'     => 'test_sample.png',
    'type'     => 'image/png',
    'tmp_name' => $tempImgPath,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempImgPath),
];

$uploadResult = upload_image_webp($fakeFile, 'test', 800, 80);

assert_test("upload_image_webp() succeeds", ($uploadResult['success'] ?? false) === true);
assert_test("Uploaded image resized to max 800px width", ($uploadResult['width'] ?? 0) <= 800);
assert_test("Uploaded file extension is .webp", str_ends_with($uploadResult['url'] ?? '', '.webp'));

// Verify file exists on disk
$expectedDiskPath = dirname(__DIR__) . '/storage/uploads/test/' . ($uploadResult['file_name'] ?? '');
assert_test("WebP file saved in storage/uploads/test", file_exists($expectedDiskPath));

// Clean up temporary test files
if (file_exists($tempImgPath)) {
    unlink($tempImgPath);
}
if (file_exists($expectedDiskPath)) {
    unlink($expectedDiskPath);
}
$publicDiskPath = dirname(__DIR__) . '/public/uploads/test/' . ($uploadResult['file_name'] ?? '');
if (file_exists($publicDiskPath)) {
    unlink($publicDiskPath);
}

// ----------------------------------------------------
// Test 7: SQL Injection Resistance (Prepared Statements)
// ----------------------------------------------------
$maliciousSlug = "' OR '1'='1";
$sqlInjStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug");
$sqlInjStmt->execute(['slug' => $maliciousSlug]);
$sqlInjResult = $sqlInjStmt->fetchAll();
assert_test("PDO Prepared Statement safely handles SQL injection payload", count($sqlInjResult) === 0);

// ----------------------------------------------------
// Test 8: Front Controller / Router Test
// ----------------------------------------------------
assert_test("Front Controller (public/index.php) exists", file_exists(dirname(__DIR__) . '/public/index.php'));

// ----------------------------------------------------
// Summary
// ----------------------------------------------------
echo "\n====================================================\n";
echo "   Test Results: {$passed} Passed, {$failed} Failed\n";
echo "====================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
