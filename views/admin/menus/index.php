<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Menus Management & Stock Control View
 */

$title = 'Kelola Menu & Stok';
$subtitle = 'Atur katalog makanan & minuman, ketersediaan stok, harga coret, serta varian produk.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();

// Fetch filter params
$categoryFilter = $_GET['category_id'] ?? '';
$searchFilter = trim($_GET['q'] ?? '');

$query = "
    SELECT m.*, c.name as category_name 
    FROM menus m 
    JOIN categories c ON m.category_id = c.id 
    WHERE 1=1
";
$params = [];

if (!empty($categoryFilter)) {
    $query .= " AND m.category_id = :category_id";
    $params['category_id'] = $categoryFilter;
}

if (!empty($searchFilter)) {
    $query .= " AND (m.name LIKE :search OR m.description LIKE :search)";
    $params['search'] = "%{$searchFilter}%";
}

$query .= " ORDER BY m.sort_order ASC, m.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$menus = $stmt->fetchAll();

// Fetch all categories for filter dropdown
$allCategories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
?>

<!-- Header Actions & Search Bar -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <!-- Filter & Search Form -->
    <form action="/admin/menus" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <input 
            type="search" 
            name="q" 
            value="<?= e($searchFilter) ?>" 
            placeholder="Cari menu..." 
            class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary shadow-sm w-full sm:w-60"
        >
        <select 
            name="category_id" 
            onchange="this.form.submit()"
            class="text-xs px-3 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary shadow-sm"
        >
            <option value="">Semua Kategori</option>
            <?php foreach ($allCategories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($searchFilter) || !empty($categoryFilter)): ?>
            <a href="/admin/menus" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                Reset
            </a>
        <?php endif; ?>
    </form>

    <?php if (($user['role'] ?? '') === 'admin'): ?>
        <a 
            href="/admin/menus/create" 
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/95 text-white font-bold text-xs shadow-md shadow-primary/20 transition text-center flex items-center justify-center gap-2"
        >
            <span>+ Tambah Menu Baru</span>
        </a>
    <?php endif; ?>
</div>

<!-- Menus Table Container with Alpine.js Stock Manager -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="menuStockManager()">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="py-4 px-6">Foto & Nama Menu</th>
                    <th class="py-4 px-6">Kategori</th>
                    <th class="py-4 px-6">Harga</th>
                    <th class="py-4 px-6">Varian</th>
                    <th class="py-4 px-6 text-center">Status Stok (1-Click Toggle)</th>
                    <th class="py-4 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($menus)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                            Tidak ada menu yang ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($menus as $item): ?>
                    <?php
                        $variants = $item['variants_json'] ? json_decode($item['variants_json'], true) : null;
                        $hasVariants = !empty($variants['options']);
                    ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <img 
                                    src="<?= e($item['image_url'] ?: '/assets/images/food-menu-1.png') ?>" 
                                    alt="<?= e($item['name']) ?>"
                                    class="w-14 h-14 rounded-2xl object-cover border border-slate-200 shrink-0"
                                    onerror="this.src='/assets/images/food-menu-1.png'"
                                >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-slate-900"><?= e($item['name']) ?></span>
                                        <?php if (!empty($item['is_featured'])): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-800">
                                                ★ Favorit
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs text-slate-400 line-clamp-1 mt-0.5"><?= e($item['description'] ?? '') ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-semibold text-xs">
                                <?= e($item['category_name']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-black text-slate-900"><?= format_rupiah($item['price']) ?></span>
                            <?php if (!empty($item['strike_price'])): ?>
                                <span class="text-xs text-slate-400 line-through block"><?= format_rupiah($item['strike_price']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6">
                            <?php if ($hasVariants): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px]">
                                    <?= count($variants['options']) ?> Varian
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <!-- One-Click Stock Toggle Switch -->
                            <button 
                                type="button"
                                @click="toggleStock(<?= $item['id'] ?>)"
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition shadow-sm active:scale-95"
                                :class="stocks[<?= $item['id'] ?>] === 1 ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200'"
                                title="Klik untuk ubah stok dalam 1 detik"
                            >
                                <span class="w-2 h-2 rounded-full" :class="stocks[<?= $item['id'] ?>] === 1 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span x-text="stocks[<?= $item['id'] ?>] === 1 ? 'Tersedia' : 'Habis / Sold Out'"></span>
                            </button>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a 
                                    href="/admin/menus/edit?id=<?= $item['id'] ?>" 
                                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition"
                                    title="Edit Menu"
                                >
                                    ✏️ Edit
                                </a>

                                <?php if (($user['role'] ?? '') === 'admin'): ?>
                                    <form 
                                        action="/admin/menus/delete" 
                                        method="POST" 
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')"
                                        class="inline"
                                    >
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button 
                                            type="submit" 
                                            class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs transition"
                                            title="Hapus Menu"
                                        >
                                            🗑️
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function menuStockManager() {
    return {
        stocks: {
            <?php foreach ($menus as $item): ?>
                <?= $item['id'] ?>: <?= (int)$item['is_available'] ?>,
            <?php endforeach; ?>
        },
        async toggleStock(id) {
            const current = this.stocks[id];
            const next = current === 1 ? 0 : 1;
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
                    this.stocks[id] = current;
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menghubungi server.');
                this.stocks[id] = current;
            }
        }
    };
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
