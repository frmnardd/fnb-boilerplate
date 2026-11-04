<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Phase 5 Integration Tests
 * Verification of Merged Features from fnb-digital:
 * 1. Studio Meja & QR Table Ordering
 * 2. Kitchen Display System (KDS) & SLA Timers
 * 3. Thermal Receipt Generator (ESC/POS 58mm & 80mm)
 * 4. Cashier POS & Order Lifecycle
 * 5. Inventory & Recipe HPP (Bill of Materials)
 * 6. Customer Call Waiter Service
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

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

echo "\n▶ Menjalankan Phase 5: Merged Features Verification (Studio Meja, KDS, Thermal POS & Inventori HPP)...\n";

// ===================================================================
// 1. STUDIO MEJA & QR CODE TABLE ORDERING
// ===================================================================
$tablesCount = (int)$pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();
assert_test("Tabel 'tables' ada di database dan memiliki data", $tablesCount >= 5, "Total meja: {$tablesCount}");

$table01 = $pdo->query("SELECT * FROM tables WHERE qr_slug = 'meja-01' LIMIT 1")->fetch();
assert_test("Meja 01 memiliki qr_slug 'meja-01' dan area valid", $table01 && $table01['area'] === 'Indoor');

$validStatuses = ['AVAILABLE', 'OCCUPIED', 'NEEDS_CLEANING'];
assert_test("Status Meja 01 mematuhi enum status valid", in_array($table01['status'], $validStatuses, true));

// Test short-link pattern
$testUri = '/t/meja-01';
$matched = preg_match('#^/t/([a-zA-Z0-9_\-]+)$#', $testUri, $m);
assert_test("Regex route /t/{slug} mencocokkan URL scan QR meja", $matched === 1 && $m[1] === 'meja-01');

// ===================================================================
// 2. KITCHEN DISPLAY SYSTEM (KDS) & SLA TIMERS
// ===================================================================
$secGreen = 300; // 5 mins
$secAmber = 750; // 12.5 mins
$secRed = 1000;  // 16.6 mins

assert_test("SLA timer < 10 menit menghasilkan warna emerald (Hijau)", get_ticket_aging_color($secGreen) === 'emerald');
assert_test("SLA timer 10-15 menit menghasilkan warna amber (Kuning)", get_ticket_aging_color($secAmber) === 'amber');
assert_test("SLA timer > 15 menit menghasilkan warna rose (Merah/Overdue)", get_ticket_aging_color($secRed) === 'rose');

assert_test("format_ticket_aging memformat 5 menit menjadi '05:00'", format_ticket_aging(300) === '05:00');
assert_test("format_ticket_aging memformat 65 menit menjadi '1j 05m'", format_ticket_aging(3900) === '1j 05m');
assert_test("format_ticket_aging aman untuk nilai negatif", format_ticket_aging(-50) === '00:00');

// Station filtering on order items
$kitchenItems = $pdo->query("SELECT COUNT(*) FROM order_items WHERE station = 'KITCHEN'")->fetchColumn();
$barItems = $pdo->query("SELECT COUNT(*) FROM order_items WHERE station = 'BAR'")->fetchColumn();
assert_test("Item pesanan terpisah stasiun KITCHEN dan BAR untuk KDS", $kitchenItems > 0 && $barItems > 0);

// ===================================================================
// 3. THERMAL RECEIPT GENERATOR (ESC/POS 58mm & 80mm)
// ===================================================================
assert_test("Lebar kolom standar 58mm adalah 32 kolom", ReceiptPrinter::WIDTH_58MM === 32);
assert_test("Lebar kolom standar 80mm adalah 48 kolom", ReceiptPrinter::WIDTH_80MM === 48);

$rawText = "Kopi & Roti — “Nikmat” 2×3 • Murah";
$printable = ReceiptPrinter::toPrintableAscii($rawText);
assert_test("ReceiptPrinter::toPrintableAscii membersihkan non-ASCII printer", !str_contains($printable, '—') && str_contains($printable, '-'));

$padded = ReceiptPrinter::padColumns("Subtotal", "Rp 50.000", 32);
assert_test("padColumns menghasilkan panjang tepat 32 karakter", strlen($padded) === 32 && str_starts_with($padded, 'Subtotal') && str_ends_with($padded, 'Rp 50.000'));

$testOrder = [
    'order_number'   => 'ORD-TEST-001',
    'created_at'     => '2026-11-04 12:00:00',
    'customer_name'  => 'Firman',
    'table_number'   => '01',
    'order_type'     => 'DINE_IN',
    'subtotal'       => 50000,
    'service_charge' => 2500,
    'tax_amount'     => 5250,
    'grand_total'    => 57750,
    'payment_method' => 'CASH',
    'payment_status' => 'PAID',
    'items'          => [
        ['menu_name' => 'Kopi Aren', 'quantity' => 2, 'unit_price' => 25000, 'line_total' => 50000]
    ]
];
$settings = get_setting();

$plainReceipt58 = ReceiptPrinter::formatPlainText($testOrder, $settings, 58);
assert_test("formatPlainText 58mm memuat No. Nota dan Total Bayar", str_contains($plainReceipt58, 'ORD-TEST-001') && str_contains($plainReceipt58, 'TOTAL BAYAR'));

$escposBinary = ReceiptPrinter::formatEscPos($testOrder, $settings, 58);
assert_test("formatEscPos memuat perintah ESC/POS Init (\\x1B@) dan Cut (\\x1DVA)", str_starts_with($escposBinary, "\x1B@") && str_contains($escposBinary, "\x1DVA\x00"));

// ===================================================================
// 4. CASHIER POS & ORDER LIFECYCLE
// ===================================================================
$calcItems = [
    ['quantity' => 2, 'unit_price' => 20000], // 40.000
    ['quantity' => 1, 'unit_price' => 60000], // 60.000
];
$totals = calculate_order_totals($calcItems, 0.05, 0.10);
// Subtotal = 100.000, Service 5% = 5.000, PB1 10% = 10.500, Grand Total = 115.500
assert_test("calculate_order_totals menghitung Subtotal 100.000", $totals['subtotal'] === 100000);
assert_test("calculate_order_totals menghitung Service Charge 5.000", $totals['service_charge'] === 5000);
assert_test("calculate_order_totals menghitung PB1 Resto 10.500", $totals['tax_amount'] === 10500);
assert_test("calculate_order_totals menghitung Grand Total 115.500", $totals['grand_total'] === 115500);

$orderNo = generate_order_number();
assert_test("generate_order_number berformat ORD-YYYYMMDD-XXXX", (bool)preg_match('/^ORD-\d{8}-[A-F0-9]{4}$/', $orderNo));

// ===================================================================
// 5. INVENTORY & RECIPE HPP (BILL OF MATERIALS)
// ===================================================================
$ingCount = (int)$pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
assert_test("Tabel 'ingredients' berisi data bahan baku", $ingCount >= 6);

$recipeCount = (int)$pdo->query("SELECT COUNT(*) FROM menu_recipes")->fetchColumn();
assert_test("Tabel 'menu_recipes' berisi relasi bahan resep", $recipeCount >= 6);

// Test recipe cost calculation for Kopi Aren (Menu ID 1)
$recipeLines = $pdo->query("
    SELECT mr.quantity, i.cost_per_unit 
    FROM menu_recipes mr 
    JOIN ingredients i ON mr.ingredient_id = i.id 
    WHERE mr.menu_id = 1
")->fetchAll();

$computedCost = 0;
foreach ($recipeLines as $line) {
    $computedCost += ((float)$line['quantity'] * (int)$line['cost_per_unit']);
}
assert_test("HPP Kopi Aren terhitung dari takaran bahan baku (BOM) > 0", $computedCost > 0);

// ===================================================================
// 6. CUSTOMER CALL WAITER SERVICE
// ===================================================================
$callCount = (int)$pdo->query("SELECT COUNT(*) FROM waiter_calls")->fetchColumn();
assert_test("Tabel 'waiter_calls' dapat mencatat panggilan meja", $callCount >= 1);

// Summary
echo "  ====================================================\n";
echo "     Phase 5 Test Results: {$passed} Passed, {$failed} Failed\n";
echo "  ====================================================\n\n";

if ($failed > 0) {
    exit(1);
}
