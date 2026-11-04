<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Store Branches & Outlets Management View
 */

$title = 'Cabang Outlet & Delivery Aggregator';
$subtitle = 'Kelola alamat cabang fisik serta deep-links ke GoFood, GrabFood, ShopeeFood, dan Google Maps.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$outlets = $pdo->query("SELECT * FROM outlets ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-8" x-data="{ 
    editModal: false, 
    modalOutlet: { 
        id: '', 
        name: '', 
        address: '', 
        open_hours: '08:00 - 22:00 WIB', 
        gmaps_link: '', 
        gofood_url: '', 
        grabfood_url: '', 
        shopeefood_url: '' 
    },
    openCreate() {
        this.modalOutlet = { 
            id: '', 
            name: '', 
            address: '', 
            open_hours: '08:00 - 22:00 WIB', 
            gmaps_link: '', 
            gofood_url: '', 
            grabfood_url: '', 
            shopeefood_url: '' 
        };
        this.editModal = true;
    },
    openEdit(item) {
        this.modalOutlet = Object.assign({}, item);
        this.editModal = true;
    }
}">
    <!-- Action Header -->
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Outlet</span>
            <span class="text-lg font-black text-slate-900"><?= count($outlets) ?> Cabang Terdaftar</span>
        </div>

        <?php if (($user['role'] ?? '') === 'admin'): ?>
            <button 
                type="button" 
                @click="openCreate()"
                class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/95 text-white font-bold text-xs shadow-md shadow-primary/20 transition flex items-center gap-2"
            >
                <span>+ Tambah Cabang Baru</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Outlets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($outlets as $item): ?>
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-base font-extrabold text-slate-900 leading-snug"><?= e($item['name']) ?></h3>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0 mt-1" title="Aktif"></span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed"><?= e($item['address']) ?></p>
                    <div class="text-xs font-semibold text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                        <span>🕒</span>
                        <span><?= e($item['open_hours']) ?></span>
                    </div>
                </div>

                <!-- Deep Links Status Badges -->
                <div class="space-y-3 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        <?php if (!empty($item['gmaps_link'])): ?>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">📍 Maps</span>
                        <?php endif; ?>
                        <?php if (!empty($item['gofood_url'])): ?>
                            <span class="px-2 py-0.5 rounded bg-red-50 text-red-700 font-semibold">GoFood</span>
                        <?php endif; ?>
                        <?php if (!empty($item['grabfood_url'])): ?>
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold">GrabFood</span>
                        <?php endif; ?>
                        <?php if (!empty($item['shopeefood_url'])): ?>
                            <span class="px-2 py-0.5 rounded bg-orange-50 text-orange-700 font-semibold">ShopeeFood</span>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-2">
                        <button 
                            type="button" 
                            @click='openEdit(<?= json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>)'
                            class="text-xs font-bold text-primary hover:underline"
                        >
                            ✏️ Edit Data Cabang
                        </button>

                        <?php if (($user['role'] ?? '') === 'admin'): ?>
                            <form 
                                action="/admin/outlets/delete" 
                                method="POST" 
                                onsubmit="return confirm('Hapus cabang outlet ini?')" 
                                class="inline"
                            >
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="text-xs font-bold text-rose-500 hover:text-rose-700">
                                    🗑️ Hapus
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Edit / Create Outlet Modal -->
    <div 
        x-cloak 
        x-show="editModal" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900" x-text="modalOutlet.id ? 'Edit Cabang Outlet' : 'Tambah Cabang Baru'"></h3>
                    <p class="text-xs text-slate-500 mt-1">Lengkapi informasi lokasi dan tautan pemesanan online.</p>
                </div>

                <form action="/admin/outlets/save" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" x-model="modalOutlet.id">

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Nama Cabang Outlet *</label>
                        <input 
                            type="text" 
                            name="name" 
                            required 
                            x-model="modalOutlet.name" 
                            placeholder="Contoh: Cabang Senopati Flagship"
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Alamat Lengkap *</label>
                        <textarea 
                            name="address" 
                            required 
                            rows="2" 
                            x-model="modalOutlet.address" 
                            placeholder="Jl. Senopati No. 45, Jakarta Selatan"
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                        ></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Jam Operasional *</label>
                        <input 
                            type="text" 
                            name="open_hours" 
                            required 
                            x-model="modalOutlet.open_hours" 
                            placeholder="08:00 - 23:00 WIB"
                            class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700">Tautan Google Maps</label>
                        <input 
                            type="url" 
                            name="gmaps_link" 
                            x-model="modalOutlet.gmaps_link" 
                            placeholder="https://maps.google.com/?q=..."
                            class="w-full text-xs px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700">GoFood Link</label>
                            <input 
                                type="url" 
                                name="gofood_url" 
                                x-model="modalOutlet.gofood_url" 
                                placeholder="https://..."
                                class="w-full text-[11px] px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700">GrabFood Link</label>
                            <input 
                                type="url" 
                                name="grabfood_url" 
                                x-model="modalOutlet.grabfood_url" 
                                placeholder="https://..."
                                class="w-full text-[11px] px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700">ShopeeFood Link</label>
                            <input 
                                type="url" 
                                name="shopeefood_url" 
                                x-model="modalOutlet.shopeefood_url" 
                                placeholder="https://..."
                                class="w-full text-[11px] px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                            >
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            @click="editModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/95 text-white font-extrabold text-xs shadow-md shadow-primary/20 transition active:scale-95"
                        >
                            Simpan Cabang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
