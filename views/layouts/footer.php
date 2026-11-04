<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Footer Layout (PRD v2.0)
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$settings = get_setting();
?>
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800/80">
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20">
                            <?= icon('coffee', 'w-5 h-5') ?>
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
                            <?= icon('whatsapp', 'w-4 h-4') ?>
                        </a>
                        <a href="#home" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition" title="Beranda">
                            <?= icon('globe', 'w-4 h-4') ?>
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
                        <li><a href="#faq" class="hover:text-primary transition">Tanya Jawab (FAQ)</a></li>
                    </ul>
                </div>

                <!-- Contact & WhatsApp Orders (OPS-08 Fix) -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Layanan Cabang & Kontak</h4>
                    <ul class="space-y-3 text-xs">
                        <li class="flex items-start gap-2.5">
                            <span class="text-emerald-400 shrink-0 mt-0.5"><?= icon('phone', 'w-4 h-4') ?></span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">WhatsApp Kasir Cabang:</span>
                                <a href="<?= format_whatsapp_url($settings['whatsapp_order_number'] ?? '6281211112222') ?>" target="_blank" class="text-white hover:text-emerald-400 font-medium">
                                    +<?= e($settings['whatsapp_order_number'] ?? '6281211112222') ?>
                                </a>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-amber-400 shrink-0 mt-0.5"><?= icon('trending-up', 'w-4 h-4') ?></span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">Kemitraan & Franchise:</span>
                                <a href="<?= format_whatsapp_url($settings['whatsapp_franchise_number'] ?? '6281298765432') ?>" target="_blank" class="text-white hover:text-amber-400 font-medium">
                                    +<?= e($settings['whatsapp_franchise_number'] ?? '6281298765432') ?>
                                </a>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="text-blue-400 shrink-0 mt-0.5"><?= icon('clock', 'w-4 h-4') ?></span>
                            <div>
                                <span class="block text-slate-500 font-semibold text-[11px]">Jam Operasional:</span>
                                <span class="text-slate-300">Setiap Hari 08.00 - 23.00 WIB</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Digital QR Menu & Halal Trust -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Jaminan Kualitas & QR</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Seluruh menu 100% Halal Indonesia dan higienis. Nikmati kemudahan memesan mandiri via scan QR meja atau takeaway tanpa antre.
                    </p>
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-800/80 border border-slate-700/60">
                        <img src="/assets/images/halal-badge.svg" alt="Halal Indonesia" class="w-10 h-10 object-contain">
                        <div class="text-[11px]">
                            <span class="font-bold text-white block">Tersertifikasi Halal</span>
                            <span class="text-slate-400">ID31110001234560723</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright & Legal Links (TR-10, OPS-08) -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© <?= date('Y') ?> <?= e($settings['site_name'] ?? 'Kopi & Rasa Nusantara') ?>. All rights reserved.</p>
                
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" @click="$dispatch('open-legal', { tab: 'privacy' })" class="hover:text-slate-300 transition underline underline-offset-2">
                        Kebijakan Privasi
                    </button>
                    <span>•</span>
                    <button type="button" @click="$dispatch('open-legal', { tab: 'terms' })" class="hover:text-slate-300 transition underline underline-offset-2">
                        Syarat & Ketentuan
                    </button>
                    <span>•</span>
                    <button type="button" @click="$dispatch('open-legal', { tab: 'refund' })" class="hover:text-slate-300 transition underline underline-offset-2">
                        Kebijakan Refund
                    </button>
                    <span>•</span>
                    <a href="/admin/login" class="text-slate-400 hover:text-primary transition font-bold underline underline-offset-2 flex items-center gap-1">
                        <?= icon('lock', 'w-3 h-3') ?>
                        <span>Portal Admin / Kasir</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- QRCode.js for Client-Side QR Generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Include Modals & Drawers -->
    <?php require dirname(__DIR__) . '/layouts/variant-modal.php'; ?>
    <?php require dirname(__DIR__) . '/layouts/product-detail-modal.php'; ?>
    <?php require dirname(__DIR__) . '/layouts/qris-modal.php'; ?>
    <?php require dirname(__DIR__) . '/layouts/legal-modal.php'; ?>
    <?php require dirname(__DIR__) . '/layouts/cart-drawer.php'; ?>
</body>
</html>
