<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - White-Label Brand Settings Panel View
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$title = 'Pengaturan Brand (White-Label)';
$subtitle = 'Ubah identitas visual, nomor WhatsApp pemesanan, warna tema, dan tracking pixel iklan.';
require dirname(__DIR__) . '/layout/header.php';

$settings = get_setting();
?>

<div class="max-w-4xl mx-auto space-y-8">

    <form 
        action="/admin/settings" 
        method="POST" 
        class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-8"
        x-data="{
            primaryColor: '<?= e($settings['primary_color'] ?? '#FF6B35') ?>',
            secondaryColor: '<?= e($settings['secondary_color'] ?? '#1E293B') ?>'
        }"
    >
        <?= csrf_field() ?>

        <!-- 1. Brand Identity -->
        <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                <?= icon('settings', 'w-4 h-4 text-primary') ?>
                <span>1. Identitas & Nama Brand (White-Label)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Nama Bisnis / Brand *</label>
                    <input 
                        type="text" 
                        name="site_name" 
                        required 
                        value="<?= e($settings['site_name'] ?? '') ?>"
                        placeholder="Contoh: Kopi & Rasa Nusantara"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary font-bold"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Tagline / Slogan</label>
                    <input 
                        type="text" 
                        name="tagline" 
                        value="<?= e($settings['tagline'] ?? '') ?>"
                        placeholder="Contoh: Cita Rasa Otentik Kopi & Kuliner Warisan Nusantara"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                    >
                </div>
            </div>
        </div>

        <!-- 2. Dynamic Theme Engine (Theme Colors) -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <?= icon('layers', 'w-4 h-4 text-primary') ?>
                    <span>2. Warna Tema Visual (CSS Variables Engine)</span>
                </h3>
                <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                    <?= icon('check', 'w-3 h-3') ?>
                    <span>Tanpa Rebuild CSS</span>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Primary Color -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Warna Utama (Primary Color)</label>
                    <div class="flex items-center gap-3">
                        <input 
                            type="color" 
                            x-model="primaryColor"
                            class="w-12 h-12 rounded-xl border border-slate-300 cursor-pointer p-0.5 bg-white"
                        >
                        <input 
                            type="text" 
                            name="primary_color" 
                            x-model="primaryColor"
                            class="w-full text-xs font-mono font-bold px-3 py-2 rounded-xl border border-slate-200 bg-white uppercase"
                        >
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Warna tombol pesan, aksen harga, dan announcement bar di seluruh website.
                    </p>
                </div>

                <!-- Secondary Color -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Warna Sekunder (Secondary Color)</label>
                    <div class="flex items-center gap-3">
                        <input 
                            type="color" 
                            x-model="secondaryColor"
                            class="w-12 h-12 rounded-xl border border-slate-300 cursor-pointer p-0.5 bg-white"
                        >
                        <input 
                            type="text" 
                            name="secondary_color" 
                            x-model="secondaryColor"
                            class="w-full text-xs font-mono font-bold px-3 py-2 rounded-xl border border-slate-200 bg-white uppercase"
                        >
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Warna header latar belakang sekunder, footer, dan kartu analitik.
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. WhatsApp Integration -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                <?= icon('whatsapp', 'w-4 h-4 text-emerald-600') ?>
                <span>3. Integrasi WhatsApp Pemesanan & Kemitraan</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Nomor WhatsApp Kasir / Order *</label>
                    <input 
                        type="text" 
                        name="whatsapp_order_number" 
                        required 
                        value="<?= e($settings['whatsapp_order_number'] ?? '') ?>"
                        placeholder="Contoh: 6281234567890"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary font-mono font-bold"
                    >
                    <span class="text-[11px] text-slate-400 block">Gunakan kode negara (awalan 628...)</span>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Nomor WhatsApp Franchise / Kemitraan</label>
                    <input 
                        type="text" 
                        name="whatsapp_franchise_number" 
                        value="<?= e($settings['whatsapp_franchise_number'] ?? '') ?>"
                        placeholder="Contoh: 6281298765432"
                        class="w-full text-xs sm:text-sm px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary font-mono font-bold"
                    >
                    <span class="text-[11px] text-slate-400 block">Tujuan chat dari Kalkulator ROI Franchise</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Template Pesan Pembuka Chat WhatsApp</label>
                <textarea 
                    name="whatsapp_greeting_template" 
                    rows="3" 
                    class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                ><?= e($settings['whatsapp_greeting_template'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- 4. Announcement Bar -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                <?= icon('bell', 'w-4 h-4 text-primary') ?>
                <span>4. Announcement Bar (Pengumuman Berjalan)</span>
            </h3>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_announcement_active" 
                        value="1" 
                        <?= (!empty($settings['is_announcement_active']) && (int)$settings['is_announcement_active'] === 1) ? 'checked' : '' ?>
                        class="w-5 h-5 rounded text-primary focus:ring-primary"
                    >
                    <span class="text-xs font-bold text-slate-900">Aktifkan Announcement Bar di Puncak Website</span>
                </label>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Teks Pengumuman Promo</label>
                    <input 
                        type="text" 
                        name="announcement_text" 
                        value="<?= e($settings['announcement_text'] ?? '') ?>"
                        placeholder="Contoh: PROMO SPESIAL: Diskon 20% hari ini!"
                        class="w-full text-xs sm:text-sm px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                    >
                </div>
            </div>
        </div>

        <!-- 5. Advertising Pixels & Analytics -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                <?= icon('activity', 'w-4 h-4 text-primary') ?>
                <span>5. Tracking Pixel Iklan & Analitik</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Meta Pixel ID</label>
                    <input 
                        type="text" 
                        name="meta_pixel_id" 
                        value="<?= e($settings['meta_pixel_id'] ?? '') ?>"
                        placeholder="e.g. 123456789012345"
                        class="w-full text-xs font-mono px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">TikTok Pixel ID</label>
                    <input 
                        type="text" 
                        name="tiktok_pixel_id" 
                        value="<?= e($settings['tiktok_pixel_id'] ?? '') ?>"
                        placeholder="e.g. C9876543210ABCDEF"
                        class="w-full text-xs font-mono px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Google Analytics ID</label>
                    <input 
                        type="text" 
                        name="google_analytics_id" 
                        value="<?= e($settings['google_analytics_id'] ?? '') ?>"
                        placeholder="e.g. G-XXXXXXXXXX"
                        class="w-full text-xs font-mono px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary"
                    >
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
            <button 
                type="submit" 
                class="px-8 py-3.5 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-primary/25 transition active:scale-95"
            >
                Simpan Konfigurasi Brand
            </button>
        </div>
    </form>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
