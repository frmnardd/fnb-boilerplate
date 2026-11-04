<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Inventory Management & Recipe HPP (Bill of Materials)
 */

$title = 'Inventori & HPP (Resep)';
$subtitle = 'Manajemen stok bahan baku, batas minimum stok (menipis), dan kalkulasi HPP otomatis.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();

// Fetch ingredients
$ingredients = $pdo->query("SELECT * FROM ingredients ORDER BY name ASC")->fetchAll();

// Stats
$totalIngredients = count($ingredients);
$lowStockCount = 0;
$outOfStockCount = 0;
foreach ($ingredients as $ing) {
    $stock = (float)$ing['current_stock'];
    $min = (float)$ing['min_threshold'];
    if ($stock <= 0) {
        $outOfStockCount++;
    } elseif ($stock <= $min) {
        $lowStockCount++;
    }
}

// Fetch recipes grouped by menu
$menusWithRecipes = $pdo->query("
    SELECT m.id, m.name, m.price, m.cost_price, c.name as category_name
    FROM menus m
    JOIN categories c ON m.category_id = c.id
    ORDER BY c.sort_order ASC, m.sort_order ASC
")->fetchAll();

// Attach recipe lines to each menu
foreach ($menusWithRecipes as &$m) {
    $stmtR = $pdo->prepare("
        SELECT mr.quantity, i.name as ingredient_name, i.unit, i.cost_per_unit
        FROM menu_recipes mr
        JOIN ingredients i ON mr.ingredient_id = i.id
        WHERE mr.menu_id = :menu_id
    ");
    $stmtR->execute(['menu_id' => $m['id']]);
    $lines = $stmtR->fetchAll();
    $computedHpp = 0;
    foreach ($lines as $line) {
        $computedHpp += ((float)$line['quantity'] * (int)$line['cost_per_unit']);
    }
    $m['recipes'] = $lines;
    $m['computed_hpp'] = (int)round($computedHpp);
    $margin = (int)$m['price'] - (int)$m['computed_hpp'];
    $m['margin_amount'] = $margin;
    $m['margin_percent'] = ($m['price'] > 0) ? (int)round(($margin / $m['price']) * 100) : 0;
}
unset($m);
?>

<div class="space-y-6" x-data="{
    ingModalOpen: false,
    editMode: false,
    form: { id: 0, sku: '', name: '', unit: 'GRAM', current_stock: 0, min_threshold: 10, cost_per_unit: 0 },
    openCreate() {
        this.editMode = false;
        this.form = { id: 0, sku: 'ING-' + Math.floor(Math.random() * 8999 + 1000), name: '', unit: 'GRAM', current_stock: 1000, min_threshold: 200, cost_per_unit: 50 };
        this.ingModalOpen = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.form = { 
            id: item.id, 
            sku: item.sku, 
            name: item.name, 
            unit: item.unit, 
            current_stock: item.current_stock, 
            min_threshold: item.min_threshold, 
            cost_per_unit: item.cost_per_unit 
        };
        this.ingModalOpen = true;
    }
}">

    <!-- Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Bahan Baku</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block"><?= $totalIngredients ?> Item</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-xl">
                📦
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Stok Menipis (Warning)</span>
                <span class="text-2xl font-black text-amber-600 mt-1 block"><?= $lowStockCount ?> Bahan</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-xl text-amber-500">
                ⚠️
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Stok Habis</span>
                <span class="text-2xl font-black text-rose-600 mt-1 block"><?= $outOfStockCount ?> Bahan</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-xl text-rose-500">
                🚨
            </div>
        </div>
    </div>

    <!-- Section 1: Ingredients Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Stok Bahan Baku</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pantau kuantitas stok bahan dasar dapur dan harga pokok per satuan unit.</p>
            </div>
            <button 
                @click="openCreate()" 
                class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-95 transition flex items-center gap-1.5"
            >
                <span>➕</span>
                <span>Tambah Bahan Baku</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-6">SKU & Nama Bahan</th>
                        <th class="py-3.5 px-6">Satuan</th>
                        <th class="py-3.5 px-6">Stok Saat Ini</th>
                        <th class="py-3.5 px-6">Batas Minimum</th>
                        <th class="py-3.5 px-6">Harga Beli / Satuan</th>
                        <th class="py-3.5 px-6">Status Stok</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada bahan baku terdaftar.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($ingredients as $ing): ?>
                        <?php
                        $stock = (float)$ing['current_stock'];
                        $min = (float)$ing['min_threshold'];
                        $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        $statusText = 'Aman';
                        if ($stock <= 0) {
                            $statusClass = 'bg-rose-50 text-rose-700 border-rose-200';
                            $statusText = 'Habis';
                        } elseif ($stock <= $min) {
                            $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                            $statusText = 'Menipis';
                        }
                        ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6">
                                <span class="font-mono text-[10px] text-slate-400 uppercase block"><?= e($ing['sku']) ?></span>
                                <strong class="font-bold text-slate-900 block"><?= e($ing['name']) ?></strong>
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-600">
                                <?= e($ing['unit']) ?>
                            </td>
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-900 text-sm">
                                <?= number_format($stock, 1, ',', '.') ?>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-500">
                                <?= number_format($min, 1, ',', '.') ?>
                            </td>
                            <td class="py-3.5 px-6 font-bold text-slate-800">
                                <?= format_rupiah($ing['cost_per_unit']) ?> / <?= strtolower(e($ing['unit'])) ?>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full border <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <button 
                                    @click='openEdit(<?= json_encode($ing) ?>)' 
                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                                    title="Edit Bahan"
                                >
                                    ✏️
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Recipe & HPP Margin Overview -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Analitik HPP Resep & Margin Laba (BOM)</h3>
            <p class="text-xs text-slate-500 mt-0.5">Perhitungan otomatis HPP menu berdasarkan takaran bahan baku vs harga jual.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-6">Menu & Kategori</th>
                        <th class="py-3.5 px-6">Komposisi Resep (Bahan Baku)</th>
                        <th class="py-3.5 px-6">HPP Terhitung</th>
                        <th class="py-3.5 px-6">Harga Jual</th>
                        <th class="py-3.5 px-6">Gross Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($menusWithRecipes as $menu): ?>
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6">
                                <strong class="font-bold text-slate-900 block"><?= e($menu['name']) ?></strong>
                                <span class="text-[11px] text-slate-400"><?= e($menu['category_name']) ?></span>
                            </td>

                            <td class="py-3.5 px-6 max-w-sm">
                                <?php if (!empty($menu['recipes'])): ?>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($menu['recipes'] as $rec): ?>
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px]">
                                                <?= (float)$rec['quantity'] ?> <?= strtolower(e($rec['unit'])) ?> <?= e($rec['ingredient_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">Belum ada takaran resep</span>
                                <?php endif; ?>
                            </td>

                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-800 block">
                                    <?= format_rupiah($menu['computed_hpp'] > 0 ? $menu['computed_hpp'] : $menu['cost_price']) ?>
                                </span>
                            </td>

                            <td class="py-3.5 px-6">
                                <span class="font-black text-slate-900 block">
                                    <?= format_rupiah($menu['price']) ?>
                                </span>
                            </td>

                            <td class="py-3.5 px-6">
                                <span class="inline-block px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    +<?= format_rupiah($menu['margin_amount']) ?> (<?= $menu['margin_percent'] ?>%)
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Ingredient Modal -->
    <div 
        x-show="ingModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="ingModalOpen = false" 
            class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Ubah Bahan Baku' : 'Tambah Bahan Baku Baru'"></h3>
                <button @click="ingModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl">✕</button>
            </div>

            <form action="/admin/inventory/save" method="POST" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="id" :value="form.id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">SKU Kode Bahan</label>
                    <input 
                        type="text" 
                        name="sku" 
                        x-model="form.sku" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary font-mono"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Bahan Baku</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="form.name" 
                        placeholder="Contoh: Biji Kopi Gayo, Susu UHT" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Satuan Unit</label>
                        <select 
                            name="unit" 
                            x-model="form.unit" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                            <option value="GRAM">GRAM (gr)</option>
                            <option value="ML">ML (mililiter)</option>
                            <option value="PIECE">PIECE (butir/buah)</option>
                            <option value="PACK">PACK (bungkus)</option>
                            <option value="SLICE">SLICE (lembar)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Beli / Unit (Rp)</label>
                        <input 
                            type="number" 
                            name="cost_per_unit" 
                            x-model="form.cost_per_unit" 
                            min="0" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary font-mono"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok Saat Ini</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="current_stock" 
                            x-model="form.current_stock" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary font-mono"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Minimum (Warning)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="min_threshold" 
                            x-model="form.min_threshold" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary font-mono"
                        >
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="ingModalOpen = false" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-95 transition"
                    >
                        Simpan Bahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
