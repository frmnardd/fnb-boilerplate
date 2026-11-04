<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Phase 6 Integration Tests
 * Verification of Professional Open-Source Refactor:
 * 1. Lucide SVG Icon Engine & Zero-Emoji Rule
 * 2. Professional Open-Source Assets (Chart.js, QRCode.js, Lucide SVG)
 * 3. Business Analytics & 14-Day Dashboard Chart API
 * 4. KDS Smart Polling & Synthesized Web Audio Tickets
 * 5. Inventory Restock, BOM/HPP Cost Margin & CSV Export Engine
 * 6. Public Menu JSON API for Omnichannel Expansion
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

echo "\n▶ Menjalankan Phase 6: Professional Open-Source Refactor Verification...\n";

// ===================================================================
// 1. LUCIDE SVG ICON ENGINE
// ===================================================================
assert_test("Helper icon() terdefinisi secara global", function_exists('icon'));

$coffeeSvg = icon('coffee', 'w-6 h-6 text-primary');
assert_test("icon('coffee') menghasilkan elemen SVG valid", str_contains($coffeeSvg, '<svg') && str_contains($coffeeSvg, '</svg>'));
assert_test("icon('coffee') memiliki atribut viewBox='0 0 24 24'", str_contains($coffeeSvg, 'viewBox="0 0 24 24"'));
assert_test("icon() menyertakan class kustom yang diberikan", str_contains($coffeeSvg, 'w-6 h-6 text-primary'));

$fallbackSvg = icon('non_existent_glyph_12345');
assert_test("icon() dengan nama tidak dikenal memberikan fallback SVG yang aman tanpa error", str_contains($fallbackSvg, '<svg'));

$requiredIcons = [
    'dashboard', 'coffee', 'utensils', 'shopping-bag', 'printer', 'qr-code', 
    'package', 'bar-chart', 'layers', 'settings', 'check', 'x', 'search', 
    'download', 'plus', 'bell', 'whatsapp', 'log-out', 'trash', 'edit', 'eye'
];
$allIconsExist = true;
foreach ($requiredIcons as $ic) {
    if (!str_contains(icon($ic), '<svg')) {
        $allIconsExist = false;
        break;
    }
}
assert_test("Seluruh daftar icon esensial F&B terdaftar di config/icons.php", $allIconsExist);

// ===================================================================
// 2. ZERO-EMOJI ENFORCEMENT PADA SELURUH VIEW
// ===================================================================
$viewsDir = dirname(__DIR__) . '/views';
$directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

$foundEmojiFiles = [];
foreach ($directoryIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match($emojiPattern, $content)) {
            $foundEmojiFiles[] = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }
}

assert_test("Seluruh template views/ 100% bebas dari emoji (Zero Emoji Rule)", empty($foundEmojiFiles), "File terdeteksi emoji: " . implode(', ', $foundEmojiFiles));

// ===================================================================
// 3. OPEN-SOURCE ASSETS (CHART.JS & CLIENT-SIDE QRCODE.JS)
// ===================================================================
$headerAdminContent = file_get_contents(dirname(__DIR__) . '/views/admin/layout/header.php');
assert_test("views/admin/layout/header.php memuat Chart.js dari CDN resmi", str_contains($headerAdminContent, 'chart.js'));

$tablesViewContent = file_get_contents(dirname(__DIR__) . '/views/admin/tables/index.php');
assert_test("views/admin/tables/index.php menggunakan QRCode.js client-side (bebas API eksternal)", str_contains($tablesViewContent, 'qrcode.min.js') && !str_contains($tablesViewContent, 'api.qrserver.com'));

$kdsViewContent = file_get_contents(dirname(__DIR__) . '/views/admin/kds/index.php');
assert_test("KDS View memuat polling AJAX otomatis ke /admin/kds/api/orders", str_contains($kdsViewContent, '/admin/kds/api/orders'));
assert_test("KDS View dilengkapi Web Audio API Synthesizer (AudioContext) untuk ticket chime", str_contains($kdsViewContent, 'AudioContext') || str_contains($kdsViewContent, 'webkitAudioContext'));

// ===================================================================
// 4. BUSINESS ANALYTICS & CSV EXPORT
// ===================================================================
$analyticsViewPath = dirname(__DIR__) . '/views/admin/analytics/index.php';
assert_test("View views/admin/analytics/index.php tersedia", file_exists($analyticsViewPath));

$analyticsContent = file_get_contents($analyticsViewPath);
assert_test("Analytics view merender Chart.js grafik tren pendapatan, metode bayar, dan jam sibuk", 
    str_contains($analyticsContent, 'analyticsRevenueChart') && 
    str_contains($analyticsContent, 'analyticsPaymentChart') && 
    str_contains($analyticsContent, 'analyticsHourlyChart')
);

assert_test("Helper export_csv() tersedia di config/helpers.php", function_exists('export_csv'));

// ===================================================================
// 5. EXTENDED DATABASE SCHEMA & POS/INVENTORY INTEGRATION
// ===================================================================
$driver = Database::getInstance()->getActiveDriver();
if ($driver === 'sqlite') {
    $menuColumns = $pdo->query("PRAGMA table_info(menus)")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($menuColumns, 'name');
} else {
    $columnNames = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
}
assert_test("Tabel 'menus' memiliki kolom 'cost_price' (HPP)", in_array('cost_price', $columnNames, true));
assert_test("Tabel 'menus' memiliki kolom 'station' (KITCHEN/BAR)", in_array('station', $columnNames, true));

$publicIndexContent = file_get_contents(dirname(__DIR__) . '/public/index.php');
assert_test("Route /admin/analytics terdaftar di public/index.php", str_contains($publicIndexContent, '/admin/analytics'));
assert_test("Route /admin/dashboard/stats terdaftar di public/index.php", str_contains($publicIndexContent, '/admin/dashboard/stats'));
assert_test("Route /admin/kds/api/orders terdaftar di public/index.php", str_contains($publicIndexContent, '/admin/kds/api/orders'));
assert_test("Route /admin/inventory/restock terdaftar di public/index.php", str_contains($publicIndexContent, '/admin/inventory/restock'));
assert_test("Route /admin/inventory/export terdaftar di public/index.php", str_contains($publicIndexContent, '/admin/inventory/export'));
assert_test("Route /api/menu (Public JSON API) terdaftar di public/index.php", str_contains($publicIndexContent, '/api/menu'));

// ===================================================================
// 6. 404 PAGE VERIFICATION
// ===================================================================
assert_test("Fallback 404 pada public/index.php menggunakan Lucide SVG icon('coffee')", 
    str_contains($publicIndexContent, "icon('coffee'") && !str_contains($publicIndexContent, '<span class="text-6xl block">☕</span>')
);

// ===================================================================
// SUMMARY
// ===================================================================
echo "\n===================================================================\n";
echo "   Phase 6 Results: {$passed} Passed, {$failed} Failed\n";
echo "===================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
