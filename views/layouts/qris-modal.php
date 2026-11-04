<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Instant QRIS Dynamic Payment Modal (PRD v2.0 - FR-07)
 * Interactive QRIS code, 15-minute countdown, and sandbox payment simulation.
 * 100% Emoji-Free. Powered by Lucide SVG Icons & QRCode.js.
 */
?>
<div 
    x-cloak 
    x-show="$store.cart.qrisModal.isOpen" 
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="qris-modal-title" 
    role="dialog" 
    aria-modal="true"
>
    <!-- Background Backdrop -->
    <div 
        x-show="$store.cart.qrisModal.isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/75 backdrop-blur-md transition-opacity"
        @click="$store.cart.qrisModal.isOpen = false"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div 
            x-show="$store.cart.qrisModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-md border border-slate-100 p-6 space-y-5"
        >
            <!-- Header Bar -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-600 text-white font-black text-xs flex items-center justify-center tracking-tighter">
                        QRIS
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Pembayaran Instan QRIS</h3>
                        <p class="text-[10px] text-slate-400" x-text="'No. Pesanan: #' + $store.cart.qrisModal.orderNumber"></p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="$store.cart.qrisModal.isOpen = false" 
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
                >
                    <?= icon('x', 'w-4 h-4') ?>
                </button>
            </div>

            <!-- Amount Due & Timer -->
            <div class="text-center p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan Pembayaran</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 block mt-0.5" x-text="$store.cart.formatRupiah($store.cart.qrisModal.grandTotal)"></span>

                <!-- Countdown Timer -->
                <div class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                    <?= icon('clock', 'w-3.5 h-3.5 text-rose-600') ?>
                    <span>Batas Pembayaran: </span>
                    <span x-text="Math.floor($store.cart.qrisModal.countdown / 60) + ':' + ($store.cart.qrisModal.countdown % 60 < 10 ? '0' : '') + ($store.cart.qrisModal.countdown % 60)"></span>
                </div>
            </div>

            <!-- Dynamic QR Code Container -->
            <div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <div id="qris-modal-qrcode" class="p-2 bg-white rounded-xl shadow-sm min-h-[200px] min-w-[200px] flex items-center justify-center">
                    <span class="text-xs text-slate-400">Memuat QR Code...</span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium text-center mt-3">
                    Scan menggunakan aplikasi <strong>BCA, Mandiri, GoPay, OVO, ShopeePay, atau DANA</strong>.
                </p>
            </div>

            <!-- Action Buttons: Sandbox Simulation for UAT -->
            <div class="space-y-2 pt-1">
                <button 
                    type="button" 
                    @click="$store.cart.simulateQrisPayment()"
                    :disabled="$store.cart.qrisModal.loading"
                    class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2"
                >
                    <?= icon('check-circle', 'w-4 h-4') ?>
                    <span x-text="$store.cart.qrisModal.loading ? 'Memproses...' : 'Simulasi Bayar Berhasil (UAT / Test Mode)'"></span>
                </button>

                <button 
                    type="button" 
                    @click="$store.cart.qrisModal.isOpen = false; $store.cart.openDrawer();"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition"
                >
                    Ubah Metode Pembayaran
                </button>
            </div>

            <!-- Security Footer -->
            <div class="text-[10px] text-center text-slate-400 flex items-center justify-center gap-1">
                <?= icon('shield-check', 'w-3 h-3 text-emerald-600') ?>
                <span>Standar Pembayaran Nasional QRIS Resmi Bank Indonesia.</span>
            </div>
        </div>
    </div>
</div>
