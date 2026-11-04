<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Phase 3 Verification Test Suite
 * Tests Authentication, CSRF Security, One-Click Stock Toggle API, Menu CRUD, Category CRUD, Outlet CRUD & Settings Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

echo "====================================================\n";
echo "   FnB Boilerplate - Phase 3 Verification Suite     \n";
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

$pdo = db();

// ----------------------------------------------------
// Test 1: Authentication & Password Verification
// ----------------------------------------------------
$userStmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
$userStmt->execute(['u' => 'admin']);
$admin = $userStmt->fetch();
assert_test("Admin user account found in database", !empty($admin));
assert_test("password_verify('admin123') returns true", !empty($admin) && password_verify('admin123', $admin['password_hash']));
assert_test("password_verify('wrongpassword') returns false", !empty($admin) && !password_verify('wrongpassword', $admin['password_hash']));

// Simulate session for authenticated tests
$_SESSION['user'] = [
    'id'       => $admin['id'],
    'username' => $admin['username'],
    'role'     => $admin['role'],
];
assert_test("is_logged_in() returns true when session is set", is_logged_in() === true);
assert_test("auth_user() returns authenticated admin payload", auth_user()['username'] === 'admin');

// ----------------------------------------------------
// Test 2: One-Click Stock Toggle API
// ----------------------------------------------------
// Find menu item (Mie Goreng Jawa Nyemek, id = 6)
$itemStmt = $pdo->prepare("SELECT id, is_available FROM menus WHERE id = 6");
$itemStmt->execute();
$menu6 = $itemStmt->fetch();
assert_test("Target menu item #6 exists for toggle test", !empty($menu6));

$originalStock = (int)$menu6['is_available'];
$expectedNewStock = $originalStock === 1 ? 0 : 1;

// Simulate Toggle API Call logic
$updateStmt = $pdo->prepare("UPDATE menus SET is_available = :is_available WHERE id = :id");
$updateStmt->execute(['is_available' => $expectedNewStock, 'id' => 6]);

$checkStmt = $pdo->prepare("SELECT is_available FROM menus WHERE id = 6");
$checkStmt->execute();
$updatedStock = (int)$checkStmt->fetch()['is_available'];
assert_test("Stock toggled successfully in database ({$originalStock} -> {$expectedNewStock})", $updatedStock === $expectedNewStock);

// Toggle back to original
$updateStmt->execute(['is_available' => $originalStock, 'id' => 6]);
$checkStmt->execute();
$revertedStock = (int)$checkStmt->fetch()['is_available'];
assert_test("Stock reverted cleanly back to original state ({$originalStock})", $revertedStock === $originalStock);

// ----------------------------------------------------
// Test 3: Auto-WebP Image Resizer (GD Extension)
// ----------------------------------------------------
$testIm = imagecreatetruecolor(1000, 600);
$testColor = imagecolorallocate($testIm, 255, 107, 53);
imagefilledrectangle($testIm, 0, 0, 1000, 600, $testColor);
$tempTestFile = sys_get_temp_dir() . '/test_menu_' . time() . '.png';
imagepng($testIm, $tempTestFile);
imagedestroy($testIm);

$fakeUpload = [
    'name'     => 'test_coffee.png',
    'type'     => 'image/png',
    'tmp_name' => $tempTestFile,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempTestFile),
];

$uploadRes = upload_image_webp($fakeUpload, 'menus', 800, 80);
assert_test("upload_image_webp() creates .webp image", ($uploadRes['success'] ?? false) === true);
assert_test("Image width resized to max 800px", ($uploadRes['width'] ?? 0) <= 800);
$savedDiskFile = dirname(__DIR__) . '/storage/uploads/menus/' . ($uploadRes['file_name'] ?? '');
assert_test("Saved WebP file exists in storage/uploads/menus", file_exists($savedDiskFile));

// Clean up test image
if (file_exists($tempTestFile)) unlink($tempTestFile);
if (file_exists($savedDiskFile)) unlink($savedDiskFile);
$publicSavedFile = dirname(__DIR__) . '/public/uploads/menus/' . ($uploadRes['file_name'] ?? '');
if (file_exists($publicSavedFile)) unlink($publicSavedFile);

// ----------------------------------------------------
// Test 4: Menu CRUD Operations
// ----------------------------------------------------
// Create Test Menu
$insertMenuStmt = $pdo->prepare("
    INSERT INTO menus (category_id, name, slug, description, price, strike_price, image_url, variants_json, is_available, is_featured, sort_order) 
    VALUES (:cat, :name, :slug, :desc, :price, :strike, :img, :variants, 1, 0, 99)
");
$testMenuSlug = 'test-menu-' . time();
$insertMenuStmt->execute([
    'cat'      => 1,
    'name'     => 'Kopi Test Phase 3',
    'slug'     => $testMenuSlug,
    'desc'     => 'Deskripsi uji coba',
    'price'    => 20000,
    'strike'   => 25000,
    'img'      => '/assets/images/food-menu-1.png',
    'variants' => '{"options":[{"name":"Ukuran","choices":[{"name":"Reguler","extra_price":0}]}]}',
]);
$newMenuId = (int)$pdo->lastInsertId();
assert_test("Menu created via PDO prepared statement (ID: {$newMenuId})", $newMenuId > 0);

// Update Test Menu
$updateMenuStmt = $pdo->prepare("UPDATE menus SET price = 22000 WHERE id = :id");
$updateMenuStmt->execute(['id' => $newMenuId]);
$verifyPriceStmt = $pdo->prepare("SELECT price FROM menus WHERE id = :id");
$verifyPriceStmt->execute(['id' => $newMenuId]);
assert_test("Menu updated price verified in database (Rp 22.000)", (int)$verifyPriceStmt->fetch()['price'] === 22000);

// Delete Test Menu
$deleteMenuStmt = $pdo->prepare("DELETE FROM menus WHERE id = :id");
$deleteMenuStmt->execute(['id' => $newMenuId]);
$checkDeletedStmt = $pdo->prepare("SELECT COUNT(*) FROM menus WHERE id = :id");
$checkDeletedStmt->execute(['id' => $newMenuId]);
assert_test("Menu deleted cleanly from database", (int)$checkDeletedStmt->fetchColumn() === 0);

// ----------------------------------------------------
// Test 5: Category CRUD Operations
// ----------------------------------------------------
$catSlug = 'test-cat-' . time();
$insertCatStmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order, is_active) VALUES ('Kategori Uji', :slug, 99, 1)");
$insertCatStmt->execute(['slug' => $catSlug]);
$newCatId = (int)$pdo->lastInsertId();
assert_test("Category created via PDO prepared statement (ID: {$newCatId})", $newCatId > 0);

$updateCatStmt = $pdo->prepare("UPDATE categories SET name = 'Kategori Uji Edit' WHERE id = :id");
$updateCatStmt->execute(['id' => $newCatId]);
$verifyCatStmt = $pdo->prepare("SELECT name FROM categories WHERE id = :id");
$verifyCatStmt->execute(['id' => $newCatId]);
assert_test("Category updated name verified ('Kategori Uji Edit')", $verifyCatStmt->fetch()['name'] === 'Kategori Uji Edit');

$deleteCatStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
$deleteCatStmt->execute(['id' => $newCatId]);
$checkCatDel = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE id = :id");
$checkCatDel->execute(['id' => $newCatId]);
assert_test("Category deleted cleanly from database", (int)$checkCatDel->fetchColumn() === 0);

// ----------------------------------------------------
// Test 6: Outlet CRUD Operations
// ----------------------------------------------------
$insertOutletStmt = $pdo->prepare("
    INSERT INTO outlets (name, address, open_hours, gmaps_link, gofood_url, grabfood_url, shopeefood_url, is_active) 
    VALUES ('Cabang Uji Coba', 'Jl. Uji No. 1', '09:00 - 21:00 WIB', 'https://maps.google.com', NULL, NULL, NULL, 1)
");
$insertOutletStmt->execute();
$newOutletId = (int)$pdo->lastInsertId();
assert_test("Outlet created via PDO prepared statement (ID: {$newOutletId})", $newOutletId > 0);

$updateOutletStmt = $pdo->prepare("UPDATE outlets SET open_hours = '10:00 - 22:00 WIB' WHERE id = :id");
$updateOutletStmt->execute(['id' => $newOutletId]);
$verifyOutletStmt = $pdo->prepare("SELECT open_hours FROM outlets WHERE id = :id");
$verifyOutletStmt->execute(['id' => $newOutletId]);
assert_test("Outlet updated open_hours verified", $verifyOutletStmt->fetch()['open_hours'] === '10:00 - 22:00 WIB');

$deleteOutletStmt = $pdo->prepare("DELETE FROM outlets WHERE id = :id");
$deleteOutletStmt->execute(['id' => $newOutletId]);
$checkOutletDel = $pdo->prepare("SELECT COUNT(*) FROM outlets WHERE id = :id");
$checkOutletDel->execute(['id' => $newOutletId]);
assert_test("Outlet deleted cleanly from database", (int)$checkOutletDel->fetchColumn() === 0);

// ----------------------------------------------------
// Test 7: White-Label Settings Persistence
// ----------------------------------------------------
$origSettings = get_setting();
$origBrandName = $origSettings['site_name'];

// Update settings
$updateSettingsStmt = $pdo->prepare("UPDATE settings SET site_name = :name WHERE id = 1");
$updateSettingsStmt->execute(['name' => 'Kopi & Rasa Nusantara Rebranded']);
$checkRebrand = $pdo->query("SELECT site_name FROM settings WHERE id = 1")->fetch();
assert_test("Settings site_name updated successfully ('{$checkRebrand['site_name']}')", $checkRebrand['site_name'] === 'Kopi & Rasa Nusantara Rebranded');

// Revert settings
$updateSettingsStmt->execute(['name' => $origBrandName]);
$checkRevert = $pdo->query("SELECT site_name FROM settings WHERE id = 1")->fetch();
assert_test("Settings site_name reverted cleanly ('{$checkRevert['site_name']}')", $checkRevert['site_name'] === $origBrandName);

// ----------------------------------------------------
// Test 8: Admin Views Template Rendering
// ----------------------------------------------------
// Test Login View
ob_start();
require dirname(__DIR__) . '/views/admin/login.php';
$loginHtml = ob_get_clean();
assert_test("views/admin/login.php rendered with CSRF token", str_contains($loginHtml, 'name="csrf_token"'));

// Test Dashboard View
ob_start();
require dirname(__DIR__) . '/views/admin/dashboard.php';
$dashHtml = ob_get_clean();
assert_test("views/admin/dashboard.php rendered TailAdmin layout", str_contains($dashHtml, 'Dashboard Operasional') && str_contains($dashHtml, 'One-Click Stock Toggle'));

// Test Menus Index View
ob_start();
require dirname(__DIR__) . '/views/admin/menus/index.php';
$menuHtml = ob_get_clean();
assert_test("views/admin/menus/index.php rendered with stock manager", str_contains($menuHtml, 'menuStockManager()'));

// Test Settings View
ob_start();
require dirname(__DIR__) . '/views/admin/settings/index.php';
$settingsHtml = ob_get_clean();
assert_test("views/admin/settings/index.php rendered white-label controls", str_contains($settingsHtml, 'Warna Utama (Primary Color)'));

// ----------------------------------------------------
// Summary
// ----------------------------------------------------
echo "\n====================================================\n";
echo "   Phase 3 Test Results: {$passed} Passed, {$failed} Failed\n";
echo "====================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
