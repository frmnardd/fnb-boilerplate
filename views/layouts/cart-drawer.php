<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Customer Cart Drawer Component (PRD v2.0)
 * Multi-Outlet Routing, Instant QRIS Dual Checkout, Voucher Engine, & Dynamic Modifiers.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$settings = get_setting();
$waOrderNumber = $settings['whatsapp_order_number'] ?? '6281211112222';
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
            <?= icon('shopping-cart', 'w-6 h-6') ?>
            <span 
                x-text="$store.cart.getTotalCount()" 
                class="absolute -top-2.5 -right-3 bg-amber-400 text-slate-900 text-xs font-black px-1.5 py-0.5 rounded-full border-2 border-emerald-600 shadow"
            ></span>
        </div>
        <div class="text-left hidden sm:block">
            <span class="text-[11px] block leading-none font-medium text-emerald-100">Keranjang</span>
            <span class="text-sm block leading-tight font-black" x-text="$store.cart.formatRupiah($store.cart.getGrandTotal())"></span>
        </div>
    </button>
</div>

<!-- 2. Slide-out Cart Drawer -->
<div 
    x-cloak 
    x-show="$store.cart.isOpen" 
    class="fixed inset-0 z-50 overflow-hidden" 
    aria-labelledby="cart-drawer-title"
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

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
        <div 
            x-show="$store.cart.isOpen"
            x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md bg-white shadow-2xl flex flex-col"
        >
            <!-- Drawer Header -->
            <div class="p-5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/20 text-primary flex items-center justify-center font-bold text-lg">
                        <?= icon('shopping-bag', 'w-5 h-5') ?>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight" id="cart-drawer-title">Keranjang Pesanan</h2>
                        <p class="text-xs text-slate-400">
                            <span x-text="$store.cart.getTotalCount()"></span> item dipilih
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        x-show="$store.cart.items.length > 0"
                        @click="if(confirm('Kosongkan keranjang belanja?')) $store.cart.clearCart()"
                        class="text-xs text-rose-300 hover:text-rose-100 px-2.5 py-1 rounded-lg bg-rose-500/20 transition"
                        title="Kosongkan Keranjang"
                    >
                        Hapus Semua
                    </button>
                    <button 
                        @click="$store.cart.closeDrawer()"
                        class="p-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                    >
                        <?= icon('x', 'w-5 h-5') ?>
                    </button>
                </div>
            </div>

            <!-- Active Outlet Selector Pill (FR-05) -->
            <div class="bg-amber-500/10 border-b border-amber-500/20 px-5 py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <?= icon('map-pin', 'w-4 h-4 text-amber-600 shrink-0') ?>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold text-slate-500 block uppercase">Cabang Tujuan:</span>
                        <span class="text-xs font-black text-slate-900 truncate block" x-text="$store.cart.activeOutlet?.name"></span>
                    </div>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        class="text-xs font-bold text-primary hover:underline bg-white px-2.5 py-1 rounded-lg border border-amber-300 shadow-sm"
                    >
                        Ganti Cabang
                    </button>
                    <div 
                        x-show="open" 
                        @click.away="open = false" 
                        x-cloak 
                        class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl z-30 p-1 space-y-1"
                    >
                        <template x-for="outl in $store.cart.outlets" :key="outl.id">
                            <button 
                                @click="$store.cart.setActiveOutlet(outl.slug); open = false;"
                                class="w-full text-left p-2.5 rounded-xl hover:bg-slate-50 transition flex items-start gap-2 text-xs"
                                :class="$store.cart.activeOutlet?.id === outl.id ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700'"
                            >
                                <?= icon('coffee', 'w-4 h-4 shrink-0 mt-0.5') ?>
                                <div>
                                    <span class="block font-bold leading-tight" x-text="outl.name"></span>
                                    <span class="text-[10px] text-slate-400 block" x-text="outl.city"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                <!-- Empty State -->
                <div x-show="$store.cart.items.length === 0" class="text-center py-16 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-inner">
                        <?= icon('utensils', 'w-10 h-10') ?>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Keranjang Masih Kosong</h3>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">
                            Pilih menu kopi atau hidangan favorit Nusantara untuk memulai pesanan Anda!
                        </p>
                    </div>
                    <button 
                        @click="$store.cart.closeDrawer()" 
                        class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-90 transition"
                    >
                        Jelajahi Menu
                    </button>
                </div>

                <!-- Items List with BUG-01 Fix (No more Rp 0!) -->
                <div x-show="$store.cart.items.length > 0" class="space-y-3">
                    <template x-for="item in $store.cart.items" :key="item.cart_item_id || item.id">
                        <div class="p-3.5 rounded-2xl border border-slate-200/80 bg-slate-50/50 flex gap-3.5 transition hover:bg-slate-50">
                            <!-- Thumbnail -->
                            <img 
                                :src="item.image_url || '/assets/images/menu-kopi-aren.webp'" 
                                :alt="item.name"
                                class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0"
                            >

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 truncate" x-text="item.name"></h4>
                                    <button 
                                        @click="$store.cart.removeItem(item.cart_item_id || item.id)"
                                        class="text-slate-400 hover:text-rose-500 p-0.5" 
                                        title="Hapus Item"
                                    >
                                        <?= icon('x', 'w-4 h-4') ?>
                                    </button>
                                </div>

                                <!-- Dynamic Modifier Chips (FR-03) -->
                                <template x-if="item.variants && item.variants.length > 0">
                                    <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap gap-1">
                                        <template x-for="(v, vIdx) in item.variants" :key="vIdx">
                                            <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">
                                                <strong x-text="v.group + ':'"></strong> <span x-text="v.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </template>

                                <!-- Note -->
                                <template x-if="item.notes">
                                    <p class="text-[10px] text-slate-400 italic mt-0.5" x-text="'Catatan: ' + item.notes"></p>
                                </template>

                                <!-- Unit Price & Qty Controls (BUG-01 Fix: accurate line price) -->
                                <div class="flex items-center justify-between mt-2.5">
                                    <div>
                                        <span class="text-xs sm:text-sm font-black text-slate-900 block" x-text="$store.cart.formatRupiah((item.unit_price || item.base_price) * (item.qty || 1))"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'@ ' + $store.cart.formatRupiah(item.unit_price || item.base_price)"></span>
                                    </div>

                                    <!-- Qty Control Pill -->
                                    <div class="flex items-center gap-2 border border-slate-200 bg-white rounded-xl px-2 py-1 shadow-sm">
                                        <button 
                                            @click="$store.cart.decrementQuantity(item.cart_item_id || item.id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 font-bold"
                                        >-</button>
                                        <span class="text-xs font-bold text-slate-900 min-w-[1rem] text-center" x-text="item.qty || item.quantity"></span>
                                        <button 
                                            @click="$store.cart.incrementQuantity(item.cart_item_id || item.id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 font-bold"
                                        >+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Interactive Voucher Input Box (FR-04) -->
                <div x-show="$store.cart.items.length > 0" class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <?= icon('tag', 'w-4 h-4 text-amber-600') ?>
                            <span>Kode Promo / Voucher</span>
                        </span>
                        <!-- Quick Click-to-Apply Hints -->
                        <div class="flex items-center gap-1.5">
                            <button 
                                type="button" 
                                @click="$store.cart.copyAndApplyVoucher('KOPIASIK')" 
                                class="text-[10px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-800 px-2 py-0.5 rounded transition"
                            >
                                KOPIASIK
                            </button>
                            <button 
                                type="button" 
                                @click="$store.cart.copyAndApplyVoucher('KENYANG2')" 
                                class="text-[10px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-800 px-2 py-0.5 rounded transition"
                            >
                                KENYANG2
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            x-model="$store.cart.voucherCodeInput" 
                            @keydown.enter.prevent="$store.cart.applyVoucher()"
                            placeholder="Masukkan kode promo (misal: KOPIASIK)" 
                            class="flex-1 text-xs px-3 py-2 rounded-xl border border-slate-200 uppercase tracking-wider font-bold bg-white focus:outline-none focus:border-primary"
                        >
                        <button 
                            type="button" 
                            @click="$store.cart.applyVoucher()"
                            class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition"
                        >
                            Terapkan
                        </button>
                    </div>

                    <!-- Voucher Applied Badge -->
                    <template x-if="$store.cart.appliedVoucher">
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <?= icon('check-circle', 'w-4 h-4 text-emerald-600 shrink-0') ?>
                                <div>
                                    <strong x-text="$store.cart.appliedVoucher.code"></strong>
                                    <span class="text-[11px] text-emerald-700 block" x-text="$store.cart.appliedVoucher.title"></span>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="$store.cart.removeVoucher()" 
                                class="text-rose-500 hover:text-rose-700 text-xs font-bold"
                            >
                                Batal
                            </button>
                        </div>
                    </template>

                    <!-- Error & Success Messages -->
                    <p x-show="$store.cart.voucherError" x-text="$store.cart.voucherError" class="text-[11px] text-rose-600 font-semibold"></p>
                    <p x-show="$store.cart.voucherSuccess && !$store.cart.appliedVoucher" x-text="$store.cart.voucherSuccess" class="text-[11px] text-emerald-600 font-semibold"></p>
                </div>

                <!-- Order Type & Customer Details Form (FR-05) -->
                <div x-show="$store.cart.items.length > 0" class="space-y-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Pemesanan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'dine_in'"
                                :class="$store.cart.orderType === 'dine_in' ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('utensils', 'w-4 h-4') ?>
                                <span>Dine-In</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'pickup'"
                                :class="$store.cart.orderType === 'pickup' ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('package', 'w-4 h-4') ?>
                                <span>Pickup</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'delivery'"
                                :class="$store.cart.orderType === 'delivery' ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('map-pin', 'w-4 h-4') ?>
                                <span>Delivery</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Form Inputs -->
                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <!-- Customer Name -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemesan *</label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.name" 
                                placeholder="Contoh: Budi Santoso"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                            >
                        </div>

                        <!-- Table Number (Dine-In) -->
                        <div x-show="$store.cart.orderType === 'dine_in'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Nomor Meja * <span class="text-slate-400 font-normal">(Auto-Detect via QR)</span>
                            </label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.table_number" 
                                placeholder="Contoh: 01 / VIP-2"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary font-bold"
                            >
                        </div>

                        <!-- Pickup Notes -->
                        <div x-show="$store.cart.orderType === 'pickup'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Jam Ambil</label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.pickupTime" 
                                placeholder="Contoh: Jam 14.30 WIB"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                            >
                        </div>

                        <!-- Delivery Address, Map Note & Phone (Delivery) -->
                        <div x-show="$store.cart.orderType === 'delivery'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Pengantaran *</label>
                                <textarea 
                                    rows="2" 
                                    x-model="$store.cart.customer.address" 
                                    placeholder="Nama Jalan, Blok/Nomor Rumah, Kelurahan..."
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                ></textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp Aktif *</label>
                                    <input 
                                        type="tel" 
                                        x-model="$store.cart.customer.phone" 
                                        placeholder="0812xxxxxxxx"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Patokan / Catatan Kurir</label>
                                    <input 
                                        type="text" 
                                        x-model="$store.cart.customer.notes" 
                                        placeholder="Pagar putih / titip pos"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer (Subtotal, Vouchers & Dual Checkout) -->
            <div x-show="$store.cart.items.length > 0" class="p-5 bg-slate-50 border-t border-slate-200 space-y-4">
                <!-- Cost Breakdown Table -->
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk:</span>
                        <span class="font-bold text-slate-900" x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
                    </div>

                    <!-- Voucher Discount Row (Green) -->
                    <template x-if="$store.cart.getDiscount() > 0">
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span class="flex items-center gap-1">
                                <?= icon('tag', 'w-3.5 h-3.5') ?>
                                <span x-text="'Diskon Voucher (' + $store.cart.appliedVoucher.code + '):'"></span>
                            </span>
                            <span x-text="'- ' + $store.cart.formatRupiah($store.cart.getDiscount())"></span>
                        </div>
                    </template>

                    <!-- Delivery Fee Row -->
                    <template x-if="$store.cart.orderType === 'delivery'">
                        <div class="flex justify-between text-slate-600">
                            <span>Ongkos Kirim (Flat Cabang):</span>
                            <span class="font-bold" x-text="$store.cart.formatRupiah($store.cart.getDeliveryFee())"></span>
                        </div>
                    </template>

                    <!-- Total Amount -->
                    <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Pembayaran:</span>
                        <span class="text-primary text-xl" x-text="$store.cart.formatRupiah($store.cart.getGrandTotal())"></span>
                    </div>
                </div>

                <!-- Dual Checkout Buttons (FR-07) -->
                <div class="space-y-2 pt-1">
                    <!-- 1. Instant QRIS Payment -->
                    <button 
                        type="button"
                        @click="$store.cart.initiateQrisCheckout()"
                        :disabled="$store.cart.qrisModal.loading"
                        class="w-full py-3.5 px-5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs sm:text-sm shadow-xl flex items-center justify-center gap-2.5 transition active:scale-98"
                    >
                        <?= icon('qr-code', 'w-5 h-5 text-amber-400') ?>
                        <span>Bayar Cepat via QRIS (Instan & Otomatis)</span>
                    </button>

                    <!-- 2. WhatsApp Checkout -->
                    <button 
                        type="button"
                        @click="$store.cart.sendWhatsAppOrder('<?= e($waOrderNumber) ?>', '<?= e($siteName) ?>')"
                        class="w-full py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2.5 transition active:scale-98"
                    >
                        <?= icon('whatsapp', 'w-5 h-5') ?>
                        <span>Pesan via WhatsApp Kasir Cabang</span>
                    </button>
                </div>

                <p class="text-[10px] text-center text-slate-400 flex items-center justify-center gap-1">
                    <?= icon('shield-check', 'w-3.5 h-3.5 text-emerald-600 shrink-0') ?>
                    <span>Zero Commission Direct Ordering. Terhubung langsung ke kasir cabang resmi.</span>
                </p>
            </div>
        </div>
    </div>
</div>
