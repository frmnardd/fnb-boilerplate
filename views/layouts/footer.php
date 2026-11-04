<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Footer Layout
 */

$settings = get_setting();
?>
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800/80">
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-primary/20">
                            ☕
                        </div>
                        <span class="text-xl font-extrabold text-white tracking-tight">
                            <?= e($settings['site_name'] ?? 'Kopi & Rasa Nusantara') ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        <?= e($settings['tagline'] ?? 'Cita Rasa Otentik Kopi & Kuliner Warisan Nusantara.') ?> Komitmen menyajikan racikan kopi lokal premium dan hidangan khas dengan bahan baku segar pilihan.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="<?= format_whatsapp_url($settings['whatsapp_order_number'] ?? '', 'Halo, saya ingin bertanya') ?>" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-emerald-600 text-white flex items-center justify-center transition" title="WhatsApp Customer Service">
                            💬
                        </a>
                        <a href="https://instagram.com" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-pink-600 text-white flex items-center justify-center transition" title="Instagram">
                            📸
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition" title="TikTok">
                            🎵
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Navigasi Cepat</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#home" class="hover:text-primary transition">Beranda & Hero</a></li>
                        <li><a href="#promos" class="hover:text-primary transition">Promo & Diskon Hari Ini</a></li>
                        <li><a href="#food-menu" class="hover:text-primary transition">Daftar Menu & Harga</a></li>
                        <li><a href="#franchise" class="hover:text-primary transition">Peluang Franchise & ROI</a></li>
                        <li><a href="#outlets" class="hover:text-primary transition">Lokasi Cabang & Maps</a></li>
                    </ul>
                </div>

                <!-- Contact & WhatsApp Orders -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Layanan & Pemesanan</h4>
                    <ul class="space-y-3 text-xs">
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-400">📱</span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">WhatsApp Pesanan:</span>
                                <a href="<?= format_whatsapp_url($settings['whatsapp_order_number'] ?? '') ?>" target="_blank" class="text-white hover:text-emerald-400 font-medium">
                                    +<?= e($settings['whatsapp_order_number'] ?? '6281234567890') ?>
                                </a>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-amber-400">💼</span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">Kemitraan & Franchise:</span>
                                <a href="<?= format_whatsapp_url($settings['whatsapp_franchise_number'] ?? '') ?>" target="_blank" class="text-white hover:text-amber-400 font-medium">
                                    +<?= e($settings['whatsapp_franchise_number'] ?? '6281298765432') ?>
                                </a>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-blue-400">🕒</span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">Jam Operasional:</span>
                                <span class="text-slate-300">Setiap Hari 08.00 - 23.00 WIB</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Digital QR Menu & White-Label Note -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Digital QR Menu</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Tingkatkan kenyamanan pelanggan dine-in dengan memindai kode QR di meja untuk memesan langsung tanpa antre di kasir.
                    </p>
                    <div class="inline-flex items-center gap-3 p-3 rounded-2xl bg-slate-800/80 border border-slate-700/60">
                        <div class="w-12 h-12 bg-white rounded-xl p-1 flex items-center justify-center font-black text-slate-900 text-xs">
                            [ QR ]
                        </div>
                        <div class="text-[11px]">
                            <span class="font-bold text-white block">Self-Order Dine-In</span>
                            <span class="text-slate-400">Zero Commission Web App</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© <?= date('Y') ?> <?= e($settings['site_name'] ?? 'Kopi & Rasa Nusantara') ?>. All rights reserved.</p>
                <p class="flex items-center gap-2">
                    <span>Powered by FnB White-Label Boilerplate</span>
                    <span>•</span>
                    <a href="/admin/login" class="text-slate-400 hover:text-primary transition underline underline-offset-2">
                        Portal Admin / Kasir
                    </a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Include Variant Modal & WhatsApp Cart Drawer -->
    <?php require dirname(__DIR__) . '/layouts/variant-modal.php'; ?>
    <?php require dirname(__DIR__) . '/layouts/cart-drawer.php'; ?>
</body>
</html>
