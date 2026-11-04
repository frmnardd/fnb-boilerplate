<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Web Live Order Tracker View (PRD v2.0 - FR-08)
 * Real-time 4-stage order progress tracking, smart polling, & digital receipt preview.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$settings = get_setting();
$primaryColor = $settings['primary_color'] ?? '#FF6B35';
$secondaryColor = $settings['secondary_color'] ?? '#1E293B';

// Determine stage index (1..4)
$statusMap = [
    'PENDING'   => 1, // Menunggu Pembayaran / Verifikasi
    'CONFIRMED' => 2, // Diterima Kasir
    'COOKING'   => 3, // Sedang Disiapkan Barista/Dapur
    'READY'     => 4, // Siap Disajikan / Diantar
    'SERVED'    => 4,
    'COMPLETED' => 4,
    'CANCELLED' => 0
];
$currentStage = $statusMap[$order['status'] ?? 'PENDING'] ?? 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Status Pesanan #<?= e($order['order_number']) ?> - <?= e($settings['site_name']) ?></title>
    <link rel="shortcut icon" href="<?= e($settings['favicon_url'] ?? '/assets/images/favicon.svg') ?>" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --color-primary: <?= e($primaryColor) ?>;
            --color-secondary: <?= e($secondaryColor) ?>;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen py-8 px-4 sm:px-6 lg:px-8">

    <div class="max-w-2xl mx-auto space-y-6" x-data="orderTracker(<?= (int)$order['id'] ?>, '<?= e($order['status']) ?>')">
        
        <!-- Header Brand Bar -->
        <div class="flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow">
                    <?= icon('coffee', 'w-5 h-5') ?>
                </div>
                <span class="font-extrabold text-base text-slate-900 group-hover:text-primary transition-colors">
                    <?= e($settings['site_name']) ?>
                </span>
            </a>

            <a href="/" class="text-xs font-bold text-slate-500 hover:text-primary transition flex items-center gap-1">
                <?= icon('arrow-left', 'w-3.5 h-3.5') ?>
                <span>Ke Beranda</span>
            </a>
        </div>

        <!-- Main Live Tracking Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden">
            <!-- Top Status Banner -->
            <div class="p-6 bg-slate-900 text-white relative overflow-hidden">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live Tracking Otomatis</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight" x-text="'Pesanan #' + orderNumber"></h1>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Cabang: <strong><?= e($order['outlet_name'] ?? 'Cabang Senopati') ?></strong>
                        </p>
                    </div>

                    <div class="text-left sm:text-right shrink-0">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan</span>
                        <span class="text-xl sm:text-2xl font-black text-amber-400">
                            <?= format_rupiah((int)$order['grand_total']) ?>
                        </span>
                        <span class="text-[11px] block mt-0.5 font-bold" :class="paymentStatus === 'PAID' ? 'text-emerald-400' : 'text-amber-300'">
                            <span x-text="paymentStatus === 'PAID' ? 'Lunas (' + paymentMethod + ')' : 'Menunggu Bayar Kasir'"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stepper Progress Bar (FR-08: 4 Real-time Stages) -->
            <div class="p-6 bg-slate-50 border-b border-slate-100">
                <div class="grid grid-cols-4 gap-2 text-center relative">
                    <!-- Step 1: Menunggu -->
                    <div class="space-y-2">
                        <div 
                            class="w-10 h-10 mx-auto rounded-full flex items-center justify-center font-bold text-xs transition shadow-sm"
                            :class="stage >= 1 ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500'"
                        >
                            <?= icon('check', 'w-4 h-4') ?>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold block leading-tight" :class="stage >= 1 ? 'text-slate-900' : 'text-slate-400'">
                            Pesanan Dibuat
                        </span>
                    </div>

                    <!-- Step 2: Diterima Kasir -->
                    <div class="space-y-2">
                        <div 
                            class="w-10 h-10 mx-auto rounded-full flex items-center justify-center font-bold text-xs transition shadow-sm"
                            :class="stage >= 2 ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : (stage === 1 ? 'bg-amber-400 text-slate-900 animate-pulse' : 'bg-slate-200 text-slate-500')"
                        >
                            <?= icon('receipt', 'w-4 h-4') ?>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold block leading-tight" :class="stage >= 2 ? 'text-slate-900' : 'text-slate-400'">
                            Diterima Kasir
                        </span>
                    </div>

                    <!-- Step 3: Sedang Dirujuk Dapur -->
                    <div class="space-y-2">
                        <div 
                            class="w-10 h-10 mx-auto rounded-full flex items-center justify-center font-bold text-xs transition shadow-sm"
                            :class="stage >= 3 ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : (stage === 2 ? 'bg-amber-400 text-slate-900 animate-pulse' : 'bg-slate-200 text-slate-500')"
                        >
                            <?= icon('utensils', 'w-4 h-4') ?>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold block leading-tight" :class="stage >= 3 ? 'text-slate-900' : 'text-slate-400'">
                            Diracik Dapur
                        </span>
                    </div>

                    <!-- Step 4: Siap Disajikan / Diantar -->
                    <div class="space-y-2">
                        <div 
                            class="w-10 h-10 mx-auto rounded-full flex items-center justify-center font-bold text-xs transition shadow-sm"
                            :class="stage >= 4 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 animate-bounce' : 'bg-slate-200 text-slate-500'"
                        >
                            <?= icon('check-circle', 'w-4 h-4') ?>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold block leading-tight" :class="stage >= 4 ? 'text-emerald-700 font-black' : 'text-slate-400'">
                            Siap Disajikan
                        </span>
                    </div>
                </div>

                <!-- Live Status Narrative -->
                <div class="mt-6 p-4 rounded-2xl bg-white border border-slate-200 text-center">
                    <p class="text-xs font-bold text-slate-900" x-text="statusNarrative"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Halaman ini otomatis menyinkronkan status dengan dapur & kasir setiap 3 detik.</p>
                </div>
            </div>

            <!-- Order Details Section -->
            <div class="p-6 space-y-6">
                <!-- Customer Details -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Pemesan</span>
                        <span class="font-bold text-slate-800"><?= e($order['customer_name']) ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Tipe Pesanan</span>
                        <span class="font-bold text-slate-800 uppercase"><?= e($order['order_type'] ?? 'Dine-In') ?></span>
                    </div>
                    <?php if (!empty($order['table_number'])): ?>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Nomor Meja</span>
                        <span class="font-bold text-primary">Meja <?= e($order['table_number']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['delivery_address'])): ?>
                    <div class="col-span-2 sm:col-span-3 pt-2 border-t border-slate-200">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Alamat Antar</span>
                        <span class="text-slate-700 font-medium"><?= e($order['delivery_address']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rincian Hidangan</h3>
                    <div class="divide-y divide-slate-100 border-y border-slate-100">
                        <?php foreach ($items as $item): ?>
                            <div class="py-3 flex items-start justify-between gap-3 text-xs">
                                <div>
                                    <span class="font-bold text-slate-900"><?= e($item['menu_name']) ?></span>
                                    <span class="text-slate-500"> × <?= (int)$item['quantity'] ?></span>
                                    <?php if (!empty($item['variants_selected'])): ?>
                                        <p class="text-[11px] text-slate-400 mt-0.5"><?= e($item['variants_selected']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="font-bold text-slate-900 shrink-0">
                                    <?= format_rupiah((int)$item['line_total']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cost Breakdown -->
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk:</span>
                        <span><?= format_rupiah((int)$order['subtotal']) ?></span>
                    </div>
                    <?php if (!empty($order['voucher_discount']) && $order['voucher_discount'] > 0): ?>
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Diskon Voucher (<?= e($order['voucher_code'] ?? 'PROMO') ?>):</span>
                            <span>- <?= format_rupiah((int)$order['voucher_discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($order['delivery_fee']) && $order['delivery_fee'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Ongkos Kirim:</span>
                            <span><?= format_rupiah((int)$order['delivery_fee']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between font-black text-sm text-slate-900 pt-2 border-t border-slate-200">
                        <span>Grand Total:</span>
                        <span class="text-primary text-base"><?= format_rupiah((int)$order['grand_total']) ?></span>
                    </div>
                </div>

                <!-- Action Buttons: Print Digital Receipt (FR-08) -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    <a 
                        href="/admin/orders/receipt?id=<?= (int)$order['id'] ?>" 
                        target="_blank"
                        class="w-full sm:flex-1 py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-2 transition"
                    >
                        <?= icon('printer', 'w-4 h-4') ?>
                        <span>Cetak Struk Digital (ESC/POS)</span>
                    </a>

                    <a 
                        href="/" 
                        class="w-full sm:w-auto py-3 px-5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs text-center transition"
                    >
                        Pesan Menu Lain
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400">
            Ada pertanyaan tentang pesanan Anda? Hubungi kasir cabang via WhatsApp: 
            <a href="https://wa.me/<?= e($order['outlet_phone'] ?? '6281211112222') ?>" target="_blank" class="text-primary font-bold underline">
                Chat Kasir
            </a>
        </p>
    </div>

    <!-- Live Polling Script -->
    <script>
    function orderTracker(orderId, initialStatus) {
        return {
            orderId: orderId,
            orderNumber: '<?= e($order['order_number']) ?>',
            status: initialStatus,
            paymentStatus: '<?= e($order['payment_status']) ?>',
            paymentMethod: '<?= e($order['payment_method']) ?>',
            stage: <?= $currentStage ?>,
            pollInterval: null,

            get statusNarrative() {
                if (this.status === 'PENDING') return 'Pesanan Anda telah diterima sistem dan menunggu konfirmasi pembayaran.';
                if (this.status === 'CONFIRMED') return 'Pesanan telah dikonfirmasi kasir dan masuk antrean dapur.';
                if (this.status === 'COOKING') return 'Barista & Dapur sedang meracik hidangan segar Anda.';
                if (this.status === 'READY') return 'Pesanan telah siap! Silakan ambil atau tunggu kurir/staf mengantarkan.';
                if (this.status === 'SERVED' || this.status === 'COMPLETED') return 'Pesanan telah selesai disajikan. Selamat menikmati!';
                return 'Pesanan sedang diproses.';
            },

            init() {
                this.pollInterval = setInterval(async () => {
                    try {
                        const res = await fetch(`/api/orders/${this.orderId}/status`);
                        const data = await res.json();
                        if (data.success) {
                            this.status = data.status;
                            this.paymentStatus = data.payment_status;
                            
                            const map = { 'PENDING': 1, 'CONFIRMED': 2, 'COOKING': 3, 'READY': 4, 'SERVED': 4, 'COMPLETED': 4 };
                            this.stage = map[data.status] || 1;
                        }
                    } catch (e) {}
                }, 3000);
            }
        };
    }
    </script>
</body>
</html>
