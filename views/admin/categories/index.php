<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Category Management View
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$title = 'Kategori Menu';
$subtitle = 'Kelola kategori hidangan (Kopi, Makanan Utama, Camilan, dsb.)';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$categories = $pdo->query("
    SELECT c.*, COUNT(m.id) as total_menus 
    FROM categories c 
    LEFT JOIN menus m ON c.id = m.category_id 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC
")->fetchAll();
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" x-data="{ editMode: false, currentCategory: { id: '', name: '', sort_order: 0, is_active: 1 } }">
    
    <!-- Table of Categories (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <?= icon('tag', 'w-5 h-5 text-primary') ?>
                <span>Daftar Kategori</span>
            </h2>
            <span class="text-xs text-slate-400 font-semibold"><?= count($categories) ?> Total Kategori</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Nama & Slug</th>
                        <th class="py-4 px-6">Jumlah Menu</th>
                        <th class="py-4 px-6">Urutan</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900 block"><?= e($cat['name']) ?></span>
                                <span class="text-[11px] text-slate-400 block font-mono"><?= e($cat['slug']) ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                    <?= $cat['total_menus'] ?> Menu
                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-600">
                                <?= $cat['sort_order'] ?>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button"
                                        @click="editMode = true; currentCategory = { id: '<?= $cat['id'] ?>', name: '<?= addslashes($cat['name']) ?>', sort_order: <?= (int)$cat['sort_order'] ?>, is_active: <?= (int)$cat['is_active'] ?> }"
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition inline-flex items-center gap-1"
                                        title="Edit Kategori"
                                    >
                                        <?= icon('edit', 'w-3.5 h-3.5') ?>
                                        <span>Edit</span>
                                    </button>

                                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                                        <form 
                                            action="/admin/categories/delete" 
                                            method="POST" 
                                            onsubmit="return confirm('Hapus kategori ini? Seluruh menu di bawah kategori ini juga akan terhapus.')"
                                            class="inline"
                                        >
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button 
                                                type="submit" 
                                                class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold transition"
                                                title="Hapus Kategori"
                                            >
                                                <?= icon('trash-2', 'w-3.5 h-3.5') ?>
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

    <!-- Category Form Card (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-6">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 tracking-tight" x-text="editMode ? 'Edit Kategori Menu' : 'Tambah Kategori Baru'"></h3>
            <p class="text-xs text-slate-500 mt-1">Kategori digunakan untuk memfilter pilihan produk di beranda publik.</p>
        </div>

        <form action="/admin/categories/save" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id" x-model="currentCategory.id">

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Nama Kategori *</label>
                <input 
                    type="text" 
                    name="name" 
                    required 
                    x-model="currentCategory.name"
                    placeholder="Contoh: Kopi Nusantara"
                    class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                >
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Urutan Tampil (Sort Order)</label>
                <input 
                    type="number" 
                    name="sort_order" 
                    x-model.number="currentCategory.sort_order"
                    class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                >
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button 
                    type="submit" 
                    class="flex-1 py-3 px-4 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-primary/20 transition active:scale-95"
                    x-text="editMode ? 'Perbarui Kategori' : 'Simpan Kategori Baru'"
                ></button>

                <button 
                    type="button" 
                    x-show="editMode"
                    @click="editMode = false; currentCategory = { id: '', name: '', sort_order: 0, is_active: 1 }"
                    class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
                >
                    Batal
                </button>
            </div>
        </form>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
