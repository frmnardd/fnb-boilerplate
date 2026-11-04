<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Thermal Receipt Viewer (58mm & 80mm ESC/POS)
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

start_session_safe();
require_auth();

require_once dirname(__DIR__, 3) . '/config/icons.php';

$id = (int)($_GET['id'] ?? 0);
$paperWidth = (int)($_GET['width'] ?? 58);
if ($paperWidth !== 80) {
    $paperWidth = 58;
}

$pdo = db();
$settings = get_setting();

$stmt = $pdo->prepare("
    SELECT o.*, t.table_number, t.area 
    FROM orders o 
    LEFT JOIN tables t ON o.table_id = t.id 
    WHERE o.id = :id
");
$stmt->execute(['id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo "Pesanan tidak ditemukan.";
    exit;
}

// Fetch items
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :id");
$stmtItems->execute(['id' => $id]);
$order['items'] = $stmtItems->fetchAll();

// Handle ESC/POS raw download
if (isset($_GET['format']) && $_GET['format'] === 'escpos') {
    $escposPayload = ReceiptPrinter::formatEscPos($order, $settings, $paperWidth);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="struk-' . $order['order_number'] . '.bin"');
    echo $escposPayload;
    exit;
}

// Generate formatted plain-text receipt
$receiptText = ReceiptPrinter::formatPlainText($order, $settings, $paperWidth);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - <?= e($order['order_number']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-box {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: <?= $paperWidth === 80 ? '78mm' : '56mm' ?> !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4 text-slate-800 font-mono">

    <!-- Top Toolbar (Hidden on Print) -->
    <div class="max-w-md mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <a 
                href="/admin/orders/receipt?id=<?= $order['id'] ?>&width=58" 
                class="px-3 py-1.5 rounded-lg text-xs font-bold font-sans <?= $paperWidth === 58 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                58 mm
            </a>
            <a 
                href="/admin/orders/receipt?id=<?= $order['id'] ?>&width=80" 
                class="px-3 py-1.5 rounded-lg text-xs font-bold font-sans <?= $paperWidth === 80 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                80 mm
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="/admin/orders/receipt?id=<?= $order['id'] ?>&width=<?= $paperWidth ?>&format=escpos" 
                class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold font-sans hover:bg-slate-50 transition inline-flex items-center gap-1.5" 
                title="Unduh Payload ESC/POS Mentah"
            >
                <?= icon('download', 'w-3.5 h-3.5') ?>
                <span>ESC/POS</span>
            </a>
            <button 
                onclick="window.print()" 
                class="px-4 py-1.5 rounded-xl bg-primary text-white text-xs font-bold font-sans shadow-md shadow-primary/20 hover:opacity-90 transition flex items-center gap-1.5"
            >
                <?= icon('printer', 'w-3.5 h-3.5') ?>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- Thermal Receipt Card -->
    <div class="receipt-box max-w-sm mx-auto bg-white p-6 rounded-3xl shadow-lg border border-slate-200">
        <pre class="whitespace-pre font-mono text-xs leading-relaxed text-slate-900 overflow-x-auto"><?= e($receiptText) ?></pre>
    </div>

</body>
</html>
