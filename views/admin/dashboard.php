<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - TailAdmin CMS Dashboard View
 */

$title = 'Dashboard Operasional';
$subtitle = 'Ringkasan performa katalog menu, status ketersediaan stok, dan konfigurasi brand.';
require __DIR__ . '/layout/header.php';

$pdo = db();

// Fetch statistics
$totalMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$availableMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus WHERE is_available = 1")->fetchColumn();
$soldOutMenus = (int)$pdo->query("SELECT COUNT(*) FROM menus WHERE is_available = 0")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOutlets = (int)$pdo->query("SELECT COUNT(*) FROM outlets")->fetchColumn();

// Fetch recent menus with categories for quick management
$recentMenus = $pdo->query("
    SELECT m.*, c.name as category_name 
    FROM menus m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY m.updated_at DESC, m.id DESC 
    LIMIT 6
")->fetchAll();
?>

<!-- 1. Stats Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <!-- Card 1: Total Menus -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Katalog Menu</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= $totalMenus ?> Item</span>
            <span class="text-xs font-semibold text-emerald-600 mt-1 block">
                <?= $availableMenus ?> Siap Dipesan
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl">
            🍲
        </div>
    </div>

    <!-- Card 2: Sold Out / Habis Alert -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Stok Habis</span>
            <span class="text-2xl sm:text-3xl font-black <?= $soldOutMenus > 0 ? 'text-rose-600' : 'text-slate-900' ?> mt-1 block">
                <?= $soldOutMenus ?> Menu
            </span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">
                <?= $soldOutMenus > 0 ? 'Perlu perhatian kasir' : 'Semua stok aman' ?>
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl <?= $soldOutMenus > 0 ? 'bg-rose-50 text-rose-500' : 'bg-slate-50 text-slate-400' ?> flex items-center justify-center text-2xl">
            ⚠️
        </div>
    </div>

    <!-- Card 3: Total Kategori -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kategori Aktif</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= $totalCategories ?> Grup</span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">
                Kopi, Makanan, Camilan
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
            📁
        </div>
    </div>

    <!-- Card 4: Cabang Outlet -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cabang Outlet</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= $totalOutlets ?> Cabang</span>
            <span class="text-xs font-semibold text-slate-500 mt-1 block">
                Terhubung Aggregator
            </span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
            📍
        </div>
    </div>
</div>

<!-- 2. Quick Mobile Stock Manager & Recent Items -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="stockManager()">
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>⚡</span>
                <span>One-Click Stock Toggle (Mobile Friendly)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Staf kasir dapat mematikan dan menyalakan ketersediaan stok menu dalam 1 detik tanpa memuat ulang halaman.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="/admin/menus" 
                class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition"
            >
                Lihat Semua Menu (<?= $totalMenus ?>) →
            </a>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a 
                    href="/admin/menus/create" 
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-primary text-white shadow-md shadow-primary/20 hover:opacity-95 transition"
                >
                    + Tambah Menu
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
                    <th class="py-4 px-6 text-center">Status Stok (Toggle Kasir)</th>
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
                                class="text-primary hover:underline font-bold text-xs"
                            >
                                Edit Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

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
            // Optimistic update
            this.stocks[id] = next;

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('csrf_token', window.getCsrfToken());

                const res = await fetch('/admin/api/toggle-stock', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'Gagal mengubah status stok');
                    this.stocks[id] = current; // Revert
                }
            } catch (err) {
                console.error(err);
                alert('Koneksi terputus ke server.');
                this.stocks[id] = current; // Revert
            }
        }
    };
}
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
