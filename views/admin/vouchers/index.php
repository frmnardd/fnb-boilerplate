<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Promo Vouchers Management (PRD v2.0 - FR-10)
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$title = 'Manajemen Voucher Promo';
$subtitle = 'Kelola kode diskon promosi interaktif untuk meningkatkan konversi checkout pelanggan.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$vouchers = $pdo->query("SELECT * FROM vouchers ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-6">

    <!-- Header Stats & Action -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block uppercase">Total Voucher</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block"><?= count($vouchers) ?></span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <?= icon('tag', 'w-5 h-5') ?>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block uppercase">Voucher Aktif</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">
                    <?= count(array_filter($vouchers, fn($v) => (int)$v['is_active'] === 1)) ?>
                </span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <?= icon('check-circle', 'w-5 h-5') ?>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block uppercase">Aturan Diskon</span>
                <span class="text-xs font-bold text-slate-700 mt-1 block">Persentase & Potongan Tetap</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <?= icon('layers', 'w-5 h-5') ?>
            </div>
        </div>
    </div>

    <!-- Voucher Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($vouchers as $v): ?>
            <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="px-3 py-1 rounded-xl bg-amber-400 text-slate-950 font-black text-sm tracking-wider inline-block">
                                <?= e($v['code']) ?>
                            </span>
                            <h3 class="text-base font-extrabold text-slate-900 mt-2"><?= e($v['title']) ?></h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase <?= (int)$v['is_active'] === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                            <?= (int)$v['is_active'] === 1 ? 'Aktif' : 'Non-Aktif' ?>
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        <?= e($v['description'] ?? 'Voucher diskon otomatis saat checkout.') ?>
                    </p>

                    <!-- Details -->
                    <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-bold uppercase">Nilai Potongan</span>
                            <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                <?= $v['discount_type'] === 'PERCENT' ? e((string)$v['discount_value']) . '%' : format_rupiah((int)$v['discount_value']) ?>
                            </span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-bold uppercase">Min. Belanja</span>
                            <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                <?= (int)$v['min_subtotal'] > 0 ? format_rupiah((int)$v['min_subtotal']) : 'Tanpa Minimum' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span class="flex items-center gap-1">
                        <?= icon('clock', 'w-3.5 h-3.5') ?>
                        <span>Berlaku s/d: <?= date('d M Y', strtotime($v['valid_until'])) ?></span>
                    </span>
                    <form action="/admin/vouchers/toggle" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                        <button 
                            type="submit" 
                            class="px-3 py-1.5 rounded-lg border text-xs font-bold transition <?= (int)$v['is_active'] === 1 ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' ?>"
                        >
                            <?= (int)$v['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
