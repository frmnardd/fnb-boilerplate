<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Web-to-WhatsApp Cart Drawer & Floating CTA Button
 * Zero-commission ordering system stored in localStorage with WhatsApp deep-link formatting.
 */

$settings = get_setting();
$waOrderNumber = $settings['whatsapp_order_number'] ?? '6281234567890';
$siteName = $settings['site_name'] ?? 'Kopi & Rasa Nusantara';
?>

<!-- 1. Floating WhatsApp Cart Button (Bottom Right) -->
<div 
    x-cloak 
    class="fixed bottom-6 right-6 z-40 transition-all duration-300"
    :class="$store.cart.getTotalCount() > 0 ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-12 opacity-0 scale-90 pointer-events-none'"
>
    <button 
        @click="$store.cart.openDrawer()"
        class="group flex items-center gap-3 px-5 py-3.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold shadow-2xl shadow-emerald-600/40 border-2 border-white/20 hover:scale-105 active:scale-95 transition-all"
        aria-label="Buka Keranjang Belanja"
    >
        <div class="relative">
            <!-- Shopping Cart Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span 
                x-text="$store.cart.getTotalCount()" 
                class="absolute -top-2.5 -right-3 bg-amber-400 text-slate-900 text-xs font-black px-1.5 py-0.5 rounded-full border-2 border-emerald-600 shadow"
            ></span>
        </div>
        <div class="text-left hidden sm:block">
            <span class="text-[11px] block leading-none font-medium text-emerald-100">Keranjang</span>
            <span class="text-sm block leading-tight font-black" x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
        </div>
    </button>
</div>

<!-- 2. Slide-out Cart Drawer -->
<div 
    x-cloak 
    x-show="$store.cart.isOpen" 
    class="fixed inset-0 z-50 overflow-hidden" 
    role="dialog" 
    aria-modal="true"
>
    <!-- Dark Backdrop with Blur -->
    <div 
        x-show="$store.cart.isOpen"
        x-transition:enter="ease-in-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="$store.cart.closeDrawer()"
    ></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
        <div 
            x-show="$store.cart.isOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md bg-white shadow-2xl flex flex-col"
        >
            <!-- Drawer Header -->
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/20 text-primary flex items-center justify-center font-bold text-lg">
                        🛒
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight">Keranjang Pesanan</h2>
                        <p class="text-xs text-slate-400">
                            <span x-text="$store.cart.getTotalCount()"></span> item dipilih
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        x-show="$store.cart.items.length > 0"
                        @click="if(confirm('Kosongkan keranjang belanja?')) $store.cart.clearCart()"
                        class="text-xs text-rose-300 hover:text-rose-100 px-2 py-1 rounded-lg bg-rose-500/20 transition"
                        title="Kosongkan Keranjang"
                    >
                        Hapus Semua
                    </button>
                    <button 
                        @click="$store.cart.closeDrawer()"
                        class="p-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Empty State -->
                <div x-show="$store.cart.items.length === 0" class="text-center py-16 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center text-4xl shadow-inner">
                        🍲
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Keranjang Masih Kosong</h3>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">
                            Yuk pilih menu kopi atau makanan favorit Nusantara untuk memulai pesanan Anda!
                        </p>
                    </div>
                    <button 
                        @click="$store.cart.closeDrawer()"
                        class="inline-block text-xs font-bold px-4 py-2.5 rounded-xl bg-primary text-white shadow hover:opacity-90 transition"
                    >
                        Lihat Katalog Menu
                    </button>
                </div>

                <!-- Items List -->
                <div x-show="$store.cart.items.length > 0" class="space-y-4">
                    <template x-for="(item, idx) in $store.cart.items" :key="idx">
                        <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 space-y-3 relative group hover:border-slate-300 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-slate-900 leading-snug" x-text="item.name"></h4>
                                    
                                    <!-- Selected Variants Pill Tags -->
                                    <template x-if="item.variants && item.variants.length > 0">
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            <template x-for="(v, vIdx) in item.variants" :key="vIdx">
                                                <span class="inline-block text-[10px] font-semibold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded-md">
                                                    <span x-text="v.name"></span>
                                                    <span x-show="v.extra_price > 0" class="text-primary font-bold" x-text="'+' + $store.cart.formatRupiah(v.extra_price)"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Notes Preview -->
                                    <p x-show="item.notes" class="text-[11px] text-slate-500 italic mt-1 bg-white/80 px-2 py-1 rounded border border-slate-100" x-text="'Catatan: ' + item.notes"></p>
                                </div>

                                <!-- Remove Button -->
                                <button 
                                    @click="$store.cart.removeItem(idx)"
                                    class="text-slate-400 hover:text-red-500 p-1 transition"
                                    title="Hapus item"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Price & Qty Stepper -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                                <span class="text-sm font-extrabold text-slate-900" x-text="$store.cart.formatRupiah(item.unit_price * item.qty)"></span>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="$store.cart.updateQty(idx, -1)"
                                        class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100 transition text-xs shadow-sm"
                                    >-</button>
                                    <span class="font-bold text-xs w-6 text-center" x-text="item.qty"></span>
                                    <button 
                                        @click="$store.cart.updateQty(idx, 1)"
                                        class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100 transition text-xs shadow-sm"
                                    >+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Type & Customer Form -->
                <div x-show="$store.cart.items.length > 0" class="pt-4 border-t border-slate-200 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">
                            Pilih Tipe Pesanan:
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'dine_in'"
                                :class="$store.cart.orderType === 'dine_in' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <span class="text-base">🍽️</span>
                                <span>Dine-In</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'pickup'"
                                :class="$store.cart.orderType === 'pickup' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <span class="text-base">🛍️</span>
                                <span>Pickup</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'delivery'"
                                :class="$store.cart.orderType === 'delivery' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <span class="text-base">🛵</span>
                                <span>Delivery</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Input Fields According to Order Type -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <!-- Customer Name (Always Required) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemesan *</label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.name" 
                                placeholder="Nama Anda..."
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                            >
                        </div>

                        <!-- Dine-In Fields -->
                        <div x-show="$store.cart.orderType === 'dine_in'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Meja *</label>
                                <input 
                                    type="text" 
                                    x-model="$store.cart.customer.table" 
                                    placeholder="Contoh: Meja 05"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                        </div>

                        <!-- Pickup Fields -->
                        <div x-show="$store.cart.orderType === 'pickup'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Estimasi Jam Ambil</label>
                                <input 
                                    type="time" 
                                    x-model="$store.cart.customer.pickupTime" 
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">No WhatsApp Pemesan</label>
                                <input 
                                    type="tel" 
                                    x-model="$store.cart.customer.phone" 
                                    placeholder="0812xxxxxxxx"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                        </div>

                        <!-- Delivery Fields -->
                        <div x-show="$store.cart.orderType === 'delivery'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Pengantaran *</label>
                                <textarea 
                                    x-model="$store.cart.customer.address" 
                                    rows="2"
                                    placeholder="Jl. Raya No. ..., Patokan pagar putih..."
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">No WhatsApp Pemesan</label>
                                <input 
                                    type="tel" 
                                    x-model="$store.cart.customer.phone" 
                                    placeholder="0812xxxxxxxx"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kurir (Opsional)</label>
                                <input 
                                    type="text" 
                                    x-model="$store.cart.customer.notes" 
                                    placeholder="Titip di pos satpam / hubungi saat tiba"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer (Subtotal & WhatsApp CTA) -->
            <div x-show="$store.cart.items.length > 0" class="p-6 bg-slate-50 border-t border-slate-200 space-y-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Subtotal Produk</span>
                        <span x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Biaya Layanan / Komisi Aplikasi</span>
                        <span class="text-emerald-600 font-bold">Rp 0 (Hemat 100%)</span>
                    </div>
                    <div class="flex items-center justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Bayar</span>
                        <span class="text-primary text-xl" x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
                    </div>
                </div>

                <!-- Big High-Conversion WhatsApp Checkout Button -->
                <button 
                    type="button"
                    @click="$store.cart.sendWhatsAppOrder('<?= e($waOrderNumber) ?>', '<?= e($siteName) ?>')"
                    class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm sm:text-base shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-3 transition active:scale-98"
                >
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Pesan Sekarang via WhatsApp</span>
                </button>
                <p class="text-[11px] text-center text-slate-500">
                    🔒 Terhubung langsung ke WhatsApp Kasir / Cabang resmi tanpa perantara.
                </p>
            </div>
        </div>
    </div>
</div>
