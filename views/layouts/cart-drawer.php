<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Customer Cart Drawer Component (Alpine.js Powered)
 * Supports Dine-In (Table Auto-Detection), Pickup, and Delivery to WhatsApp.
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$settings = get_setting();
$waOrderNumber = $settings['whatsapp_order_number'] ?? '';
$siteName = $settings['site_name'] ?? 'FnB Resto';
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
            <?= icon('shopping-cart', 'w-6 h-6') ?>
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

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
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
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/20 text-primary flex items-center justify-center font-bold text-lg">
                        <?= icon('shopping-cart', 'w-5 h-5') ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight" id="cart-drawer-title">Keranjang Pesanan</h2>
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
                        <?= icon('x', 'w-5 h-5') ?>
                    </button>
                </div>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Empty State -->
                <div x-show="$store.cart.items.length === 0" class="text-center py-16 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-inner">
                        <?= icon('utensils', 'w-10 h-10') ?>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Keranjang Masih Kosong</h3>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">
                            Yuk pilih menu kopi atau makanan favorit Nusantara untuk memulai pesanan Anda!
                        </p>
                    </div>
                    <button 
                        @click="$store.cart.closeDrawer()" 
                        class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-90 transition"
                    >
                        Jelajahi Menu
                    </button>
                </div>

                <!-- Items List -->
                <div x-show="$store.cart.items.length > 0" class="space-y-4">
                    <template x-for="item in $store.cart.items" :key="item.cart_item_id">
                        <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 flex gap-4 transition hover:bg-slate-50">
                            <!-- Thumbnail -->
                            <img 
                                :src="item.image_url || '/assets/images/food-menu-1.png'" 
                                :alt="item.name"
                                class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0"
                            >

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 truncate" x-text="item.name"></h4>
                                    <button 
                                        @click="$store.cart.removeItem(item.cart_item_id)"
                                        class="text-slate-400 hover:text-rose-500 p-0.5" 
                                        title="Hapus Item"
                                    >
                                        <?= icon('x', 'w-4 h-4') ?>
                                    </button>
                                </div>

                                <!-- Variants List (If Any) -->
                                <template x-if="item.selected_variants && Object.keys(item.selected_variants).length > 0">
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        <template x-for="(choice, group) in item.selected_variants" :key="group">
                                            <span class="inline-block mr-1">
                                                <strong x-text="group + ':'"></strong> <span x-text="choice"></span>;
                                            </span>
                                        </template>
                                    </div>
                                </template>

                                <!-- Unit Price & Qty Controls -->
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-xs sm:text-sm font-black text-slate-900" x-text="$store.cart.formatRupiah(item.calculated_price)"></span>

                                    <!-- Qty Control Pill -->
                                    <div class="flex items-center gap-2 border border-slate-200 bg-white rounded-xl px-2 py-1">
                                        <button 
                                            @click="$store.cart.decrementQuantity(item.cart_item_id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 font-bold"
                                        >-</button>
                                        <span class="text-xs font-bold text-slate-900 min-w-[1rem] text-center" x-text="item.quantity"></span>
                                        <button 
                                            @click="$store.cart.incrementQuantity(item.cart_item_id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 font-bold"
                                        >+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Type & Customer Details Form (Shown if cart has items) -->
                <div x-show="$store.cart.items.length > 0" class="space-y-4 pt-4 border-t border-slate-100">
                    <!-- Order Type Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Pemesanan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'dine_in'"
                                :class="$store.cart.orderType === 'dine_in' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('utensils', 'w-4 h-4') ?>
                                <span>Dine-In</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'pickup'"
                                :class="$store.cart.orderType === 'pickup' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('package', 'w-4 h-4') ?>
                                <span>Pickup</span>
                            </button>
                            <button 
                                type="button"
                                @click="$store.cart.orderType = 'delivery'"
                                :class="$store.cart.orderType === 'delivery' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                            >
                                <?= icon('map-pin', 'w-4 h-4') ?>
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

                        <!-- Table Number (For Dine-In) -->
                        <div x-show="$store.cart.orderType === 'dine_in'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Nomor Meja (Auto-Detect QR)
                            </label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.table_number" 
                                placeholder="Contoh: 04 / VIP-1"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary font-bold"
                            >
                        </div>

                        <!-- Pickup Notes (For Pickup) -->
                        <div x-show="$store.cart.orderType === 'pickup'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Jam Ambil</label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.pickup_time" 
                                placeholder="Contoh: Sekitar jam 14.30 WIB"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                            >
                        </div>

                        <!-- Delivery Address & Phone (For Delivery) -->
                        <div x-show="$store.cart.orderType === 'delivery'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp Aktif *</label>
                                <input 
                                    type="tel" 
                                    x-model="$store.cart.customer.phone" 
                                    placeholder="Contoh: 081234567890"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Pengiriman *</label>
                                <textarea 
                                    rows="2" 
                                    x-model="$store.cart.customer.address" 
                                    placeholder="Jalan, Nomor Rumah, Patokan..."
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                                ></textarea>
                            </div>
                        </div>

                        <!-- General Order Notes -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pesanan (Opsional)</label>
                            <input 
                                type="text" 
                                x-model="$store.cart.customer.notes" 
                                placeholder="Contoh: Jangan terlalu manis, es dipisah"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer (Subtotal & WhatsApp Direct Checkout) -->
            <div x-show="$store.cart.items.length > 0" class="p-6 bg-slate-50 border-t border-slate-200 space-y-4">
                <!-- Cost Breakdown -->
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk:</span>
                        <span class="font-bold text-slate-900" x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Ongkos Kirim:</span>
                        <span class="italic" x-text="$store.cart.orderType === 'delivery' ? 'Dihitung kurir kasir' : 'Gratis'"></span>
                    </div>
                    <div class="flex justify-between text-sm sm:text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Sementara:</span>
                        <span class="text-primary" x-text="$store.cart.formatRupiah($store.cart.getSubtotal())"></span>
                    </div>
                </div>

                <!-- Send to WhatsApp Button -->
                <button 
                    type="button"
                    @click="$store.cart.sendWhatsAppOrder('<?= e($waOrderNumber) ?>', '<?= e($siteName) ?>')"
                    class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm sm:text-base shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-3 transition active:scale-98"
                >
                    <?= icon('whatsapp', 'w-6 h-6') ?>
                    <span>Pesan Sekarang via WhatsApp</span>
                </button>
                <p class="text-[11px] text-center text-slate-500 flex items-center justify-center gap-1">
                    <?= icon('shield-check', 'w-3.5 h-3.5 text-emerald-600 shrink-0') ?>
                    <span>Terhubung langsung ke WhatsApp Kasir / Cabang resmi tanpa perantara.</span>
                </p>
            </div>
        </div>
    </div>
</div>
