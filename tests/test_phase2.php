<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Phase 2 Verification Test Suite
 * Tests Frontend Rendering, Dynamic Theme Injection, Alpine.js Store, Cart Drawer & ROI Calculator
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

echo "====================================================\n";
echo "   FnB Boilerplate - Phase 2 Verification Suite     \n";
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
// Test 1: Verify Assets & JS Engine
// ----------------------------------------------------
$cartJsPath = dirname(__DIR__) . '/public/assets/js/cart.js';
assert_test("Alpine.js Cart Engine (public/assets/js/cart.js) exists", file_exists($cartJsPath));
$cartJsContent = file_get_contents($cartJsPath);
assert_test("Cart store defined in cart.js", str_contains($cartJsContent, "Alpine.store('cart'"));
assert_test("ROI calculator defined in cart.js", str_contains($cartJsContent, "Alpine.data('roiCalculator'"));
assert_test("WhatsApp deep link builder present in cart.js", str_contains($cartJsContent, "sendWhatsAppOrder"));

// Verify images
$requiredImages = [
    'hero-banner.png',
    'about-banner.png',
    'food-menu-1.png',
    'food-menu-2.png',
    'food-menu-3.png',
    'promo-1.png',
    'favicon.svg'
];
foreach ($requiredImages as $img) {
    $imgPath = dirname(__DIR__) . '/public/assets/images/' . $img;
    assert_test("Asset image '{$img}' exists", file_exists($imgPath));
}

// ----------------------------------------------------
// Test 2: Render Frontend Home Page via Output Buffering
// ----------------------------------------------------
ob_start();
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
require dirname(__DIR__) . '/views/frontend/home.php';
$html = ob_get_clean();

assert_test("Home page rendered non-empty HTML", !empty($html) && strlen($html) > 1000);

// ----------------------------------------------------
// Test 3: Dynamic Theme Engine (CSS Variables)
// ----------------------------------------------------
assert_test("CSS Variable --color-primary injected into <head>", str_contains($html, '--color-primary: #FF6B35'));
assert_test("CSS Variable --color-secondary injected into <head>", str_contains($html, '--color-secondary: #1E293B'));
assert_test("Tailwind CSS script included", str_contains($html, 'cdn.tailwindcss.com'));
assert_test("Alpine.js CDN included", str_contains($html, 'alpinejs@3.x.x'));

// ----------------------------------------------------
// Test 4: Tracking Pixels & Analytics Scripts
// ----------------------------------------------------
assert_test("Meta Pixel code included", str_contains($html, "fbq('init', '123456789012345')"));
assert_test("TikTok Pixel code included", str_contains($html, "ttq.load('C9876543210ABCDEF')"));
assert_test("Google Analytics code included", str_contains($html, "googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"));

// ----------------------------------------------------
// Test 5: Announcement Bar & Brand Info
// ----------------------------------------------------
assert_test("Announcement bar rendered", str_contains($html, "Diskon 20%"));
assert_test("Brand name rendered in header and footer", str_contains($html, "Kopi &amp; Rasa Nusantara") || str_contains($html, "Kopi & Rasa Nusantara"));

// ----------------------------------------------------
// Test 6: Interactive Menu & Sold Out Badging
// ----------------------------------------------------
assert_test("Menu catalog section exists (#food-menu)", str_contains($html, 'id="food-menu"'));
assert_test("Alpine.js allMenus payload embedded", str_contains($html, 'allMenus:'));
assert_test("Sold out badge logic included", str_contains($html, 'Habis / Sold Out'));
assert_test("Instant search bar present", str_contains($html, 'x-model="searchQuery"'));
assert_test("Category tabs present", str_contains($html, 'activeCategory'));

// ----------------------------------------------------
// Test 7: Web-to-WhatsApp Cart Drawer & Variant Modal
// ----------------------------------------------------
assert_test("Floating Cart Button included", str_contains($html, '$store.cart.openDrawer()'));
assert_test("Cart Drawer included", str_contains($html, '$store.cart.isOpen'));
assert_test("Dine-In order type tab present", str_contains($html, "orderType === 'dine_in'"));
assert_test("Pickup order type tab present", str_contains($html, "orderType === 'pickup'"));
assert_test("Delivery order type tab present", str_contains($html, "orderType === 'delivery'"));
assert_test("WhatsApp checkout CTA button present", str_contains($html, 'sendWhatsAppOrder'));
assert_test("Variant customization modal present", str_contains($html, '$store.cart.variantModal.isOpen'));

// ----------------------------------------------------
// Test 8: Franchise Section & Interactive ROI Calculator
// ----------------------------------------------------
assert_test("Franchise section exists (#franchise)", str_contains($html, 'id="franchise"'));
assert_test("ROI Calculator component loaded", str_contains($html, 'x-data="roiCalculator"'));
assert_test("Investment packages defined", str_contains($html, 'Pilih Paket Investasi Kemitraan'));
assert_test("Daily sales range slider present", str_contains($html, 'x-model.number="dailyCups"'));
assert_test("Average price range slider present", str_contains($html, 'x-model.number="avgPricePerItem"'));
assert_test("Payback period metric badge present", str_contains($html, 'paybackPeriodMonths'));
assert_test("Annual ROI metric badge present", str_contains($html, 'annualROI'));
assert_test("WhatsApp Business Development button present", str_contains($html, 'generateFranchiseWhatsAppUrl'));

// ----------------------------------------------------
// Test 9: Outlet Aggregator & Deep Links
// ----------------------------------------------------
assert_test("Outlets section exists (#outlets)", str_contains($html, 'id="outlets"'));
assert_test("Senopati Flagship outlet rendered", str_contains($html, 'Cabang Senopati'));
assert_test("Braga Heritage outlet rendered", str_contains($html, 'Cabang Braga'));
assert_test("Malioboro outlet rendered", str_contains($html, 'Cabang Malioboro'));
assert_test("Google Maps link rendered", str_contains($html, 'maps.google.com'));
assert_test("GoFood deep link rendered", str_contains($html, 'gofood.co.id'));
assert_test("GrabFood deep link rendered", str_contains($html, 'grab.onelink.me'));

// ----------------------------------------------------
// Summary
// ----------------------------------------------------
echo "\n====================================================\n";
echo "   Phase 2 Test Results: {$passed} Passed, {$failed} Failed\n";
echo "====================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
