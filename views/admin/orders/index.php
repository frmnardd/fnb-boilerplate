<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Cashier POS & Order Management
 */

$title = 'Pesanan & Kasir POS';
$subtitle = 'Manajemen antrean pesanan pelanggan, validasi pembayaran, dan cetak struk kasir.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$settings = get_setting();

$statusFilter = trim($_GET['status'] ?? '');
if (!empty($statusFilter)) {
    $stmt = $pdo->prepare("
        SELECT o.*, t.table_number, t.area 
        FROM orders o 
        LEFT JOIN tables t ON o.table_id = t.id 
        WHERE o.status = :status 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(['status' => $statusFilter]);
    $orders = $stmt->fetchAll();
} else {
    $orders = $pdo->query("
        SELECT o.*, t.table_number, t.area 
        FROM orders o 
        LEFT JOIN tables t ON o.table_id = t.id 
        ORDER BY o.created_at DESC
    ")->fetchAll();
}

// Fetch items for each order
foreach ($orders as &$ord) {
    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
    $stmtItems->execute(['order_id' => $ord['id']]);
    $ord['items'] = $stmtItems->fetchAll();
}
unset($ord);

// Total counts
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'PENDING'")->fetchColumn();
$activeOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('CONFIRMED', 'COOKING', 'READY')")->fetchColumn();
$todayRevenue = (int)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'PAID'")->fetchColumn();
?>

<div class="space-y-6">

    <!-- Metrics Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pesanan</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block"><?= $totalOrders ?> Nota</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Menunggu Konfirmasi</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block"><?= $pendingOrders ?> Menunggu</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Sedang Diproses</span>
            <span class="text-2xl font-black text-cyan-600 mt-1 block"><?= $activeOrders ?> Aktif</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Omzet Penjualan</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block"><?= format_rupiah($todayRevenue) ?></span>
        </div>
    </div>

    <!-- Filters & Tabs -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
            <a 
                href="/admin/orders" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= empty($statusFilter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                Semua (<?= $totalOrders ?>)
            </a>
            <a 
                href="/admin/orders?status=PENDING" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= $statusFilter === 'PENDING' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                ⏳ Menunggu (<?= $pendingOrders ?>)
            </a>
            <a 
                href="/admin/orders?status=COOKING" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= $statusFilter === 'COOKING' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                👨‍🍳 Sedang Masak
            </a>
            <a 
                href="/admin/orders?status=READY" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= $statusFilter === 'READY' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                🔔 Siap Saji
            </a>
            <a 
                href="/admin/orders?status=COMPLETED" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= $statusFilter === 'COMPLETED' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                ✓ Selesai
            </a>
        </div>

        <a 
            href="/admin/kds" 
            target="_blank" 
            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold shadow hover:bg-slate-800 transition flex items-center justify-center gap-2"
        >
            <span>👨‍🍳 Buka Layar KDS Dapur</span>
            <span class="text-[10px] bg-emerald-500 text-slate-950 font-black px-1.5 py-0.5 rounded">Live</span>
        </a>
    </div>

    <!-- Orders Table List -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-4 px-6">No. Pesanan & Waktu</th>
                        <th class="py-4 px-6">Pelanggan / Meja</th>
                        <th class="py-4 px-6">Rincian Menu</th>
                        <th class="py-4 px-6">Total Bayar</th>
                        <th class="py-4 px-6">Pembayaran</th>
                        <th class="py-4 px-6">Status Pesanan</th>
                        <th class="py-4 px-6 text-right">Aksi Kasir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                Tidak ada data pesanan pada kriteria ini.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($orders as $order): ?>
                        <?php
                        $statusBadge = 'bg-slate-100 text-slate-700';
                        if ($order['status'] === 'PENDING') $statusBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                        elseif ($order['status'] === 'COOKING') $statusBadge = 'bg-cyan-50 text-cyan-700 border-cyan-200';
                        elseif ($order['status'] === 'READY') $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        elseif ($order['status'] === 'COMPLETED') $statusBadge = 'bg-slate-100 text-slate-600 border-slate-200';
                        elseif ($order['status'] === 'CANCELLED') $statusBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                        ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Order Number & Time -->
                            <td class="py-4 px-6">
                                <span class="font-mono font-bold text-slate-900 block"><?= e($order['order_number']) ?></span>
                                <span class="text-slate-400 text-[11px] block mt-0.5">
                                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                </span>
                            </td>

                            <!-- Customer & Table -->
                            <td class="py-4 px-6">
                                <strong class="text-slate-900 font-bold block"><?= e($order['customer_name']) ?></strong>
                                <span class="inline-block px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-bold mt-1">
                                    <?= !empty($order['table_number']) ? 'Meja ' . e($order['table_number']) . ' (' . e($order['area']) . ')' : e($order['order_type']) ?>
                                </span>
                            </td>

                            <!-- Items List -->
                            <td class="py-4 px-6 max-w-xs">
                                <div class="space-y-1">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="text-[11px] text-slate-700">
                                            <strong><?= $item['quantity'] ?>x</strong> <?= e($item['menu_name']) ?>
                                            <?php if (!empty($item['variants_selected'])): ?>
                                                <span class="text-slate-400 italic">(<?= e($item['variants_selected']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <!-- Total -->
                            <td class="py-4 px-6">
                                <span class="text-sm font-black text-slate-900 block">
                                    <?= format_rupiah($order['grand_total']) ?>
                                </span>
                                <span class="text-[10px] text-slate-400 block">
                                    Subtotal: <?= format_rupiah($order['subtotal']) ?>
                                </span>
                            </td>

                            <!-- Payment Method & Status -->
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-800 uppercase block"><?= e($order['payment_method']) ?></span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-extrabold <?= $order['payment_status'] === 'PAID' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= $order['payment_status'] === 'PAID' ? 'Lunas (PAID)' : 'Belum Bayar' ?>
                                </span>
                            </td>

                            <!-- Order Status Dropdown -->
                            <td class="py-4 px-6">
                                <form action="/admin/orders/status" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select 
                                        name="status" 
                                        onchange="this.form.submit()" 
                                        class="text-xs font-bold rounded-xl border border-slate-200 py-1.5 px-2.5 bg-slate-50 focus:bg-white"
                                    >
                                        <option value="PENDING" <?= $order['status'] === 'PENDING' ? 'selected' : '' ?>>Menunggu</option>
                                        <option value="CONFIRMED" <?= $order['status'] === 'CONFIRMED' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                        <option value="COOKING" <?= $order['status'] === 'COOKING' ? 'selected' : '' ?>>Sedang Masak</option>
                                        <option value="READY" <?= $order['status'] === 'READY' ? 'selected' : '' ?>>Siap Saji</option>
                                        <option value="COMPLETED" <?= $order['status'] === 'COMPLETED' ? 'selected' : '' ?>>Selesai</option>
                                        <option value="CANCELLED" <?= $order['status'] === 'CANCELLED' ? 'selected' : '' ?>>Batal</option>
                                    </select>
                                </form>
                            </td>

                            <!-- Cashier Actions -->
                            <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                                <!-- Print Thermal Receipt Link -->
                                <a 
                                    href="/admin/orders/receipt?id=<?= $order['id'] ?>" 
                                    target="_blank" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition" 
                                    title="Cetak Struk Thermal"
                                >
                                    <span>🖨️</span>
                                    <span>Struk</span>
                                </a>

                                <?php if ($order['payment_status'] !== 'PAID'): ?>
                                    <form action="/admin/orders/status" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="status" value="COMPLETED">
                                        <input type="hidden" name="payment_status" value="PAID">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition"
                                        >
                                            <span>✓</span>
                                            <span>Bayar Lunas</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
