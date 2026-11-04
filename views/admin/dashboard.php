<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - TailAdmin CMS Professional Dashboard View
 * Completely emoji-free. Uses Lucide SVG icons and Chart.js analytics.
 */

$title = 'Dashboard Operasional';
$subtitle = 'Ringkasan performa penjualan, ketersediaan stok menu, dan analitik operasional harian.';
require __DIR__ . '/layout/header.php';

$pdo = db();

// Fetch catalog statistics
$totalMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$availableMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus WHERE is_available = 1")->fetchColumn();
$soldOutMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus WHERE is_available = 0")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOutlets = (int)$pdo->query("SELECT COUNT(*) FROM outlets")->fetchColumn();

// Fetch order statistics (safely)
try {
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'PENDING'")->fetchColumn();
    $activeOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('CONFIRMED', 'COOKING', 'READY')")->fetchColumn();
    $totalRevenue = (int)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'PAID'")->fetchColumn();
} catch (\Throwable $e) {
    $totalOrders = 0;
    $pendingOrders = 0;
    $activeOrders = 0;
    $totalRevenue = 0;
}

// Fetch 6 recent menus with category
$recentMenus = $pdo->query("
    SELECT m.*, c.name as category_name 
    FROM menus m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY m.updated_at DESC, m.id DESC 
    LIMIT 6
")->fetchAll();

// Fetch 5 recent orders
try {
    $recentOrders = $pdo->query("
        SELECT o.*, t.table_number 
        FROM orders o 
        LEFT JOIN tables t ON o.table_id = t.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ")->fetchAll();
} catch (\Throwable $e) {
    $recentOrders = [];
}
?>

<!-- 1. Stats Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <!-- Card 1: Omzet Penjualan -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Omzet Terbayar</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= format_rupiah($totalRevenue) ?></span>
            <span class="text-xs font-semibold text-emerald-600 mt-1 flex items-center gap-1">
                <?= icon('trending-up', 'w-3.5 h-3.5') ?>
                <span><?= $totalOrders ?> Total Transaksi</span>
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <?= icon('dollar-sign', 'w-7 h-7') ?>
        </div>
    </div>

    <!-- Card 2: Pesanan Aktif / Antrean -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Antrean Operasional</span>
            <span class="text-2xl sm:text-3xl font-black <?= ($pendingOrders + $activeOrders) > 0 ? 'text-amber-600' : 'text-slate-900' ?> mt-1 block">
                <?= $pendingOrders + $activeOrders ?> Pesanan
            </span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">
                <?= $pendingOrders ?> Menunggu, <?= $activeOrders ?> Proses
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <?= icon('receipt', 'w-7 h-7') ?>
        </div>
    </div>

    <!-- Card 3: Katalog Menu -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Katalog Menu</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= $totalMenus ?> Item</span>
            <span class="text-xs font-semibold text-emerald-600 mt-1 block">
                <?= $availableMenus ?> Siap Dipesan
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <?= icon('utensils', 'w-7 h-7') ?>
        </div>
    </div>

    <!-- Card 4: Status Stok Habis -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Menu Stok Habis</span>
            <span class="text-2xl sm:text-3xl font-black <?= $soldOutMenus > 0 ? 'text-rose-600' : 'text-slate-900' ?> mt-1 block">
                <?= $soldOutMenus ?> Menu
            </span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">
                <?= $soldOutMenus > 0 ? 'Perlu perhatian kasir' : 'Semua stok menu aman' ?>
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl <?= $soldOutMenus > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' ?> flex items-center justify-center">
            <?= icon('alert-triangle', 'w-7 h-7') ?>
        </div>
    </div>
</div>

<!-- 2. Interactive Charts & Operational Quick Actions Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart: Trend Pendapatan Harian (Chart.js) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <?= icon('bar-chart-2', 'w-5 h-5 text-primary') ?>
                    <span>Tren Penjualan & Transaksi Harian</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Grafik dinamika omzet harian 14 hari terakhir.</p>
            </div>
            <a href="/admin/analytics" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                <span>Detail Analitik</span>
                <?= icon('arrow-right', 'w-3.5 h-3.5') ?>
            </a>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="dashboardRevenueChart"></canvas>
        </div>
    </div>

    <!-- Right Quick Actions & Live KDS Status -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4">
        <div>
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <?= icon('zap', 'w-5 h-5 text-amber-500') ?>
                <span>Akses Cepat Operasional</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Pintasan modul sistem untuk efisiensi staf kasir & manajer.</p>
        </div>

        <div class="space-y-2.5">
            <a href="/admin/orders" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                        <?= icon('receipt', 'w-4 h-4') ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Antrean Kasir & POS</span>
                        <span class="text-[11px] text-slate-500 block">Proses pembayaran & nota</span>
                    </div>
                </div>
                <?= icon('chevron-right', 'w-4 h-4 text-slate-400') ?>
            </a>

            <a href="/admin/kds" target="_blank" class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50/60 hover:bg-emerald-100/60 transition border border-emerald-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center">
                        <?= icon('monitor', 'w-4 h-4') ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Layar Dapur Realtime (KDS)</span>
                        <span class="text-[11px] text-emerald-700 block">Mode monitor tiket masak</span>
                    </div>
                </div>
                <span class="text-[10px] font-black uppercase px-2 py-0.5 bg-emerald-600 text-white rounded-full">Live</span>
            </a>

            <a href="/admin/tables" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                        <?= icon('table-2', 'w-4 h-4') ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Studio Meja & QR Code</span>
                        <span class="text-[11px] text-slate-500 block">Cetak QR Code meja</span>
                    </div>
                </div>
                <?= icon('chevron-right', 'w-4 h-4 text-slate-400') ?>
            </a>

            <a href="/admin/inventory" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                        <?= icon('package', 'w-4 h-4') ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Inventori Bahan & HPP</span>
                        <span class="text-[11px] text-slate-500 block">Kalkulasi modal resep</span>
                    </div>
                </div>
                <?= icon('chevron-right', 'w-4 h-4 text-slate-400') ?>
            </a>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>Versi PHP Server</span>
                <span class="font-mono font-bold text-slate-700"><?= PHP_VERSION ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 3. Quick Mobile Stock Manager & Recent Items -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="stockManager()">
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <?= icon('toggle-right', 'w-5 h-5 text-primary') ?>
                <span>Manajemen Stok Cepat (One-Click Stock Toggle)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Staf kasir dapat mematikan dan menyalakan ketersediaan stok menu dalam 1 detik secara instan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="/admin/menus" 
                class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition flex items-center gap-1.5"
            >
                <span>Lihat Semua Menu (<?= $totalMenus ?>)</span>
                <?= icon('arrow-right', 'w-3.5 h-3.5') ?>
            </a>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a 
                    href="/admin/menus/create" 
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-primary text-white shadow-md shadow-primary/20 hover:opacity-95 transition flex items-center gap-1.5"
                >
                    <?= icon('plus', 'w-3.5 h-3.5') ?>
                    <span>Tambah Menu</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table of Recent Menus -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="py-4 px-6">Menu</th>
                    <th class="py-4 px-6">Kategori</th>
                    <th class="py-4 px-6">Harga</th>
                    <th class="py-4 px-6 text-center">Status Stok</th>
                    <th class="py-4 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($recentMenus as $item): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <img 
                                    src="<?= e($item['image_url'] ?: '/assets/images/food-menu-1.png') ?>" 
                                    alt="<?= e($item['name']) ?>"
                                    class="w-12 h-12 rounded-2xl object-cover border border-slate-200"
                                    onerror="this.src='/assets/images/food-menu-1.png'"
                                >
                                <div>
                                    <span class="font-bold text-slate-900 block"><?= e($item['name']) ?></span>
                                    <span class="text-xs text-slate-400 line-clamp-1"><?= e($item['description'] ?? '') ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-semibold text-xs">
                                <?= e($item['category_name']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-extrabold text-slate-900"><?= format_rupiah($item['price']) ?></span>
                            <?php if (!empty($item['strike_price'])): ?>
                                <span class="text-xs text-slate-400 line-through block"><?= format_rupiah($item['strike_price']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <!-- Reactive Stock Switch -->
                            <button 
                                type="button"
                                @click="toggleStock(<?= $item['id'] ?>, $event)"
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition shadow-sm"
                                :class="stocks[<?= $item['id'] ?>] === 1 ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200'"
                                title="Klik untuk ubah stok dalam 1 detik"
                            >
                                <span class="w-2 h-2 rounded-full" :class="stocks[<?= $item['id'] ?>] === 1 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span x-text="stocks[<?= $item['id'] ?>] === 1 ? 'Tersedia' : 'Habis / Sold Out'"></span>
                            </button>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a 
                                href="/admin/menus/edit?id=<?= $item['id'] ?>" 
                                class="text-primary hover:underline font-bold text-xs inline-flex items-center gap-1"
                            >
                                <?= icon('edit', 'w-3.5 h-3.5') ?>
                                <span>Edit Detail</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 4. Recent Orders Table -->
<?php if (!empty($recentOrders)): ?>
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <?= icon('receipt', 'w-5 h-5 text-primary') ?>
                <span>Pesanan Terbaru Masuk</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">5 transaksi pesanan terakhir dari QR meja atau kasir.</p>
        </div>
        <a href="/admin/orders" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
            <span>Lihat Semua Pesanan</span>
            <?= icon('arrow-right', 'w-3.5 h-3.5') ?>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="py-3.5 px-6">No. Nota</th>
                    <th class="py-3.5 px-6">Pelanggan</th>
                    <th class="py-3.5 px-6">Meja / Layanan</th>
                    <th class="py-3.5 px-6">Total Tagihan</th>
                    <th class="py-3.5 px-6 text-center">Status Pesanan</th>
                    <th class="py-3.5 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($recentOrders as $ro): ?>
                    <?php
                    $statusBadge = match($ro['status']) {
                        'PENDING'   => 'bg-amber-100 text-amber-800',
                        'CONFIRMED' => 'bg-blue-100 text-blue-800',
                        'COOKING'   => 'bg-cyan-100 text-cyan-800',
                        'READY'     => 'bg-indigo-100 text-indigo-800',
                        'COMPLETED' => 'bg-emerald-100 text-emerald-800',
                        default     => 'bg-slate-100 text-slate-800',
                    };
                    ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                            <?= e($ro['order_number']) ?>
                        </td>
                        <td class="py-3.5 px-6 font-semibold text-slate-800">
                            <?= e($ro['customer_name'] ?? 'Tamu') ?>
                        </td>
                        <td class="py-3.5 px-6 text-slate-600">
                            <?= !empty($ro['table_number']) ? 'Meja ' . e($ro['table_number']) : e($ro['order_type']) ?>
                        </td>
                        <td class="py-3.5 px-6 font-extrabold text-slate-900">
                            <?= format_rupiah($ro['grand_total']) ?>
                        </td>
                        <td class="py-3.5 px-6 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase <?= $statusBadge ?>">
                                <?= e($ro['status']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <a href="/admin/orders/receipt?id=<?= $ro['id'] ?>" target="_blank" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
                                <?= icon('printer', 'w-3.5 h-3.5') ?>
                                <span>Cetak Nota</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function stockManager() {
    return {
        stocks: {
            <?php foreach ($recentMenus as $item): ?>
                <?= $item['id'] ?>: <?= (int)$item['is_available'] ?>,
            <?php endforeach; ?>
        },
        async toggleStock(id, e) {
            const current = this.stocks[id];
            const next = current === 1 ? 0 : 1;
            this.stocks[id] = next;

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('csrf_token', getCsrfToken());

                const res = await fetch('/admin/api/toggle-stock', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'Gagal mengubah status stok');
                    this.stocks[id] = current;
                }
            } catch (err) {
                console.error(err);
                alert('Koneksi terputus ke server.');
                this.stocks[id] = current;
            }
        }
    };
}

// Chart.js Revenue Line Chart initialization
document.addEventListener('DOMContentLoaded', async () => {
    const canvas = document.getElementById('dashboardRevenueChart');
    if (!canvas) return;

    try {
        const response = await fetch('/admin/dashboard/stats');
        const data = await response.json();

        const labels = data.labels || [];
        const revenues = data.revenues || [];

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Omzet (Rp)',
                    data: revenues,
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#FF6B35'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Omzet: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#64748B' }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            font: { family: 'Inter', size: 10 },
                            color: '#64748B',
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error('Failed to render dashboard chart:', e);
    }
});
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
