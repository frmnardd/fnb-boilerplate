<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Legal Documents Modal (PRD v2.0 - FR-11)
 * Privacy Policy, Terms of Service, and Refund Policy.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */
?>
<div 
    x-cloak 
    x-data="{ legalOpen: false, legalTab: 'privacy' }"
    @open-legal.window="legalTab = $event.detail.tab || 'privacy'; legalOpen = true;"
    x-show="legalOpen" 
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog" 
    aria-modal="true"
>
    <!-- Dark Backdrop -->
    <div 
        x-show="legalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="legalOpen = false"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div 
            x-show="legalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-100 p-6 space-y-4"
        >
            <!-- Header Tabs -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <button 
                        type="button"
                        @click="legalTab = 'privacy'"
                        :class="legalTab === 'privacy' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs transition"
                    >
                        Kebijakan Privasi
                    </button>
                    <button 
                        type="button"
                        @click="legalTab = 'terms'"
                        :class="legalTab === 'terms' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs transition"
                    >
                        Syarat & Ketentuan
                    </button>
                    <button 
                        type="button"
                        @click="legalTab = 'refund'"
                        :class="legalTab === 'refund' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs transition"
                    >
                        Kebijakan Pengembalian Dana
                    </button>
                </div>
                <button 
                    type="button" 
                    @click="legalOpen = false" 
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
                >
                    <?= icon('x', 'w-4 h-4') ?>
                </button>
            </div>

            <!-- Content Area -->
            <div class="max-h-[60vh] overflow-y-auto pr-2 text-xs text-slate-600 space-y-3 leading-relaxed">
                <!-- 1. Privacy Policy -->
                <div x-show="legalTab === 'privacy'" class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-900">Kebijakan Privasi (Privacy Policy)</h3>
                    <p>Kopi & Rasa Nusantara menghargai dan melindungi privasi data setiap pelanggan dan mitra. Kami mengumpulkan informasi kontak (nama, nomor telepon WhatsApp, alamat pengiriman) semata-mata untuk tujuan pemrosesan pesanan, verifikasi pembayaran, serta pengiriman hidangan ke alamat tujuan.</p>
                    <p>Data pribadi Anda tidak akan pernah diperjualbelikan kepada pihak ketiga. Kami menggunakan enkripsi standar industri untuk menjaga keamanan setiap transaksi digital Anda.</p>
                </div>

                <!-- 2. Terms of Service -->
                <div x-show="legalTab === 'terms'" class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-900">Syarat & Ketentuan Pemesanan (Terms of Service)</h3>
                    <p>1. <strong>Pemesanan Mandiri:</strong> Seluruh pesanan yang dibuat melalui sistem web self-order diproses secara real-time ke sistem antrean dapur dan kasir cabang yang dipilih.</p>
                    <p>2. <strong>Ketersediaan Menu:</strong> Stok menu dan bahan baku disinkronkan secara langsung. Jika terjadi kekosongan mendadak, kasir cabang berhak mengonfirmasi penggantian menu atau pengembalian saldo.</p>
                    <p>3. <strong>Voucher Promo:</strong> Penggunaan kode voucher resmi (seperti KOPIASIK dan KENYANG2) mematuhi batas ketentuan minimum belanja dan masa berlaku yang ditentukan.</p>
                </div>

                <!-- 3. Refund Policy -->
                <div x-show="legalTab === 'refund'" class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-900">Kebijakan Pengembalian Dana (Refund Policy)</h3>
                    <p>1. Pembatalan pesanan dan refund penuh berlaku apabila pesanan belum diproses oleh dapur/barista atau jika stok menu habis setelah pembayaran terkonfirmasi.</p>
                    <p>2. Pengembalian dana transaksi QRIS diproses dalam 1x24 jam kerja ke rekening atau e-wallet asal pelanggan setelah diverifikasi oleh staf kasir cabang.</p>
                    <p>3. Keluhan terkait kualitas makanan atau pengantaran dapat disampaikan langsung melalui kontak WhatsApp Customer Service cabang kami.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button 
                    type="button" 
                    @click="legalOpen = false" 
                    class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition"
                >
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
