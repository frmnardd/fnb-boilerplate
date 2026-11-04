<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Menu Create & Edit Form View
 * Includes Auto-WebP File Upload & Interactive Variant JSON Builder.
 */

$isEdit = !empty($menu['id']);
$title = $isEdit ? 'Edit Menu: ' . e($menu['name']) : 'Tambah Menu Baru';
$subtitle = 'Isi data hidangan, harga, foto produk (auto WebP), dan pilihan varian.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

$currentVariants = '{"options":[]}';
if (!empty($menu['variants_json'])) {
    $currentVariants = is_string($menu['variants_json']) ? $menu['variants_json'] : json_encode($menu['variants_json']);
}
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="/admin/menus" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
            <span>← Kembali ke Daftar Menu</span>
        </a>
    </div>

    <form 
        action="<?= $isEdit ? '/admin/menus/edit?id=' . $menu['id'] : '/admin/menus/create' ?>" 
        method="POST" 
        enctype="multipart/form-data" 
        class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-8"
        x-data="variantBuilder(<?= htmlspecialchars($currentVariants, ENT_QUOTES, 'UTF-8') ?>)"
    >
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
        <?php endif; ?>

        <!-- 1. General Product Information -->
        <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                1. Informasi Dasar Menu
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700">Nama Menu *</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        value="<?= e($menu['name'] ?? '') ?>"
                        placeholder="Contoh: Kopi Susu Gula Aren Senja"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Kategori Menu *</label>
                    <select 
                        name="category_id" 
                        required
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                    >
                        <option value="">Pilih Kategori...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($menu['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Urutan Tampil (Sort Order)</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        value="<?= e((string)($menu['sort_order'] ?? 0)) ?>"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Deskripsi Hidangan</label>
                <textarea 
                    name="description" 
                    rows="3" 
                    placeholder="Jelaskan aroma, cita rasa, dan bahan baku khas..."
                    class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                ><?= e($menu['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- 2. Pricing & Stock Status -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                2. Harga & Ketersediaan Stok
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Harga Jual Normal (Rupiah) *</label>
                    <input 
                        type="number" 
                        name="price" 
                        required 
                        min="0"
                        value="<?= e((string)($menu['price'] ?? 0)) ?>"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition font-bold"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Harga Coret / Sebelum Diskon (Opsional)</label>
                    <input 
                        type="number" 
                        name="strike_price" 
                        min="0"
                        value="<?= e((string)($menu['strike_price'] ?? '')) ?>"
                        placeholder="Kosongkan jika tidak ada promo diskon"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Stock Toggle -->
                <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 transition">
                    <input 
                        type="checkbox" 
                        name="is_available" 
                        value="1" 
                        <?= (!isset($menu['is_available']) || (int)$menu['is_available'] === 1) ? 'checked' : '' ?>
                        class="w-5 h-5 rounded text-primary focus:ring-primary"
                    >
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Stok Tersedia</span>
                        <span class="text-[11px] text-slate-500 block">Pelanggan dapat memesan menu ini</span>
                    </div>
                </label>

                <!-- Featured Toggle -->
                <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 transition">
                    <input 
                        type="checkbox" 
                        name="is_featured" 
                        value="1" 
                        <?= (!empty($menu['is_featured']) && (int)$menu['is_featured'] === 1) ? 'checked' : '' ?>
                        class="w-5 h-5 rounded text-primary focus:ring-primary"
                    >
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Menu Favorit / Rekomendasi</span>
                        <span class="text-[11px] text-slate-500 block">Tampilkan badge bintang di beranda</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 3. Photo Upload (Auto WebP 800px) -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                3. Foto Menu (Auto-WebP Uploader)
            </h3>

            <div class="flex flex-col sm:flex-row items-center gap-6">
                <?php if (!empty($menu['image_url'])): ?>
                    <div class="shrink-0">
                        <img 
                            src="<?= e($menu['image_url']) ?>" 
                            alt="<?= e($menu['name']) ?>"
                            class="w-24 h-24 rounded-2xl object-cover border border-slate-200 shadow-sm"
                        >
                        <span class="text-[10px] text-slate-400 block text-center mt-1">Foto Saat Ini</span>
                    </div>
                <?php endif; ?>

                <div class="flex-1 space-y-1.5 w-full">
                    <label class="block text-xs font-bold text-slate-700">
                        Unggah Foto Menu (JPG, PNG, atau WebP, Maks 5MB)
                    </label>
                    <input 
                        type="file" 
                        name="image" 
                        accept="image/jpeg,image/png,image/webp"
                        class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition cursor-pointer border border-slate-200 rounded-2xl p-2 bg-slate-50"
                    >
                    <p class="text-[11px] text-slate-500">
                        ⚡ <strong>Otomatisasi Server:</strong> Foto akan di-resize proporsional ke lebar maks 800px dan dikonversi ke format <code>.webp</code> (kualitas 80%) secara otomatis untuk menjaga kecepatan web.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Interactive Variant JSON Builder -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    4. Opsi Varian (Ukuran, Level Pedas, Topping)
                </h3>
                <button 
                    type="button" 
                    @click="addOptionGroup()"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1"
                >
                    <span>+ Tambah Grup Varian</span>
                </button>
            </div>

            <!-- Hidden Input to submit compiled JSON -->
            <input type="hidden" name="variants_json" :value="getJsonOutput()">

            <!-- Visual Groups Builder -->
            <div class="space-y-4">
                <template x-for="(group, gIdx) in options" :key="gIdx">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <input 
                                type="text" 
                                x-model="group.name" 
                                placeholder="Nama Grup (e.g. Ukuran, Level Pedas, Topping)"
                                class="text-xs font-bold px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary flex-1"
                            >
                            <button 
                                type="button" 
                                @click="removeOptionGroup(gIdx)"
                                class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50"
                            >
                                Hapus Grup
                            </button>
                        </div>

                        <!-- Choices List -->
                        <div class="space-y-2 pl-2 sm:pl-4">
                            <template x-for="(choice, cIdx) in group.choices" :key="cIdx">
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="text" 
                                        x-model="choice.name" 
                                        placeholder="Pilihan (e.g. Regular, Large, Pedas)"
                                        class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary flex-1"
                                    >
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-slate-400 font-semibold">+Rp</span>
                                        <input 
                                            type="number" 
                                            x-model.number="choice.extra_price" 
                                            placeholder="0"
                                            class="w-24 text-xs px-3 py-1.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary font-bold"
                                        >
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="removeChoice(gIdx, cIdx)"
                                        class="text-slate-400 hover:text-rose-500 p-1"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </template>
                            <button 
                                type="button" 
                                @click="addChoice(gIdx)"
                                class="text-[11px] font-bold text-primary hover:underline pt-1 block"
                            >
                                + Tambah Pilihan di Grup Ini
                            </button>
                        </div>
                    </div>
                </template>

                <p x-show="options.length === 0" class="text-xs text-slate-400 italic">
                    Belum ada varian. Menu ini akan langsung ditambahkan ke keranjang tanpa pilihan tambahan.
                </p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
            <a 
                href="/admin/menus" 
                class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-7 py-3 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-primary/25 transition active:scale-95"
            >
                Simpan Perubahan Menu
            </button>
        </div>
    </form>
</div>

<script>
function variantBuilder(initialData) {
    let initialOptions = [];
    if (initialData && initialData.options && Array.isArray(initialData.options)) {
        initialOptions = initialData.options;
    }

    return {
        options: initialOptions,
        addOptionGroup() {
            this.options.push({
                name: 'Varian Baru',
                choices: [
                    { name: 'Opsi 1', extra_price: 0 },
                    { name: 'Opsi 2', extra_price: 5000 }
                ]
            });
        },
        removeOptionGroup(idx) {
            this.options.splice(idx, 1);
        },
        addChoice(groupIdx) {
            this.options[groupIdx].choices.push({ name: '', extra_price: 0 });
        },
        removeChoice(groupIdx, choiceIdx) {
            this.options[groupIdx].choices.splice(choiceIdx, 1);
        },
        getJsonOutput() {
            if (this.options.length === 0) return '';
            return JSON.stringify({ options: this.options });
        }
    };
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
