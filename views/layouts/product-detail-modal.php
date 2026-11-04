<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Product Detail & Nutrition/Halal Modal (PRD v2.0 - FR-06)
 * Displays flavor notes, ingredients, Halal certification, calories & reviews.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */
?>
<div 
    x-cloak 
    x-show="$store.cart.productDetailModal.isOpen" 
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="product-detail-title" 
    role="dialog" 
    aria-modal="true"
>
    <!-- Dark Backdrop -->
    <div 
        x-show="$store.cart.productDetailModal.isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="$store.cart.closeProductDetail()"
    ></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div 
            x-show="$store.cart.productDetailModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-t-3xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-slate-100"
        >
            <template x-if="$store.cart.productDetailModal.menu">
                <div>
                    <!-- Product Image & Close Button -->
                    <div class="relative h-64 sm:h-72 w-full bg-slate-100 overflow-hidden">
                        <img 
                            :src="$store.cart.productDetailModal.menu.image_url || '/assets/images/menu-kopi-aren.webp'" 
                            :alt="$store.cart.productDetailModal.menu.name"
                            class="w-full h-full object-cover"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20"></div>

                        <button 
                            @click="$store.cart.closeProductDetail()"
                            class="absolute top-4 right-4 p-2 rounded-full bg-black/40 hover:bg-black/60 text-white transition backdrop-blur-sm"
                        >
                            <?= icon('x', 'w-5 h-5') ?>
                        </button>

                        <!-- Floating Badges -->
                        <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                            <div>
                                <span class="bg-amber-400 text-slate-900 text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider block w-fit mb-1" x-text="$store.cart.productDetailModal.menu.category_name || 'Katalog Pilihan'"></span>
                                <h3 class="text-xl sm:text-2xl font-black text-white leading-tight" x-text="$store.cart.productDetailModal.menu.name"></h3>
                            </div>
                            <span class="text-lg sm:text-xl font-black text-amber-300 shrink-0 ml-2" x-text="$store.cart.formatRupiah($store.cart.productDetailModal.menu.price)"></span>
                        </div>
                    </div>

                    <!-- Modal Body: Nutrition, Halal & Ingredients Info -->
                    <div class="p-6 space-y-5 max-h-[50vh] overflow-y-auto">
                        <!-- Rating & Halal Trust Bar -->
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <!-- Star Rating -->
                            <div class="flex items-center gap-2">
                                <div class="flex items-center text-amber-500">
                                    <?= icon('star', 'w-4 h-4 fill-amber-400 text-amber-400') ?>
                                    <span class="ml-1 text-xs font-black text-slate-900" x-text="($store.cart.productDetailModal.menu.rating || 4.9).toFixed(1)"></span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium" x-text="'(' + ($store.cart.productDetailModal.menu.review_count || 248) + ' ulasan)'"></span>
                            </div>

                            <!-- Halal Badge -->
                            <div class="flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200 text-emerald-800 text-[11px] font-bold">
                                <?= icon('shield-check', 'w-4 h-4 text-emerald-600') ?>
                                <span>100% Halal Indonesia</span>
                            </div>
                        </div>

                        <!-- Taste & Recipe Description -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Deskripsi & Cita Rasa</h4>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" x-text="$store.cart.productDetailModal.menu.description"></p>
                        </div>

                        <!-- Ingredients / Komposisi -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                <?= icon('layers', 'w-3.5 h-3.5 text-primary') ?>
                                <span>Bahan Baku Utama</span>
                            </h4>
                            <p class="text-xs text-slate-700 bg-amber-500/5 p-3 rounded-xl border border-amber-500/20 leading-relaxed font-medium" x-text="$store.cart.productDetailModal.menu.ingredients_text || 'Bahan baku alami berkualitas tinggi tanpa pengawet buatan.'"></p>
                        </div>

                        <!-- Nutrition Information -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50">
                                <span class="text-[10px] text-slate-400 block font-bold uppercase">Estimasi Energi</span>
                                <span class="text-sm font-black text-slate-800 flex items-center gap-1 mt-0.5">
                                    <?= icon('activity', 'w-3.5 h-3.5 text-rose-500') ?>
                                    <span x-text="($store.cart.productDetailModal.menu.calories || 185) + ' kkal'"></span>
                                </span>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50">
                                <span class="text-[10px] text-slate-400 block font-bold uppercase">Nutrisi Ringkas</span>
                                <span class="text-xs font-bold text-slate-700 block mt-0.5 truncate" x-text="$store.cart.productDetailModal.menu.nutrition_info || 'Protein & Energi Alami'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action: Transition to Variant Modal -->
                    <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                        <button 
                            type="button" 
                            @click="$store.cart.closeProductDetail()"
                            class="px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 transition"
                        >
                            Tutup
                        </button>
                        <button 
                            type="button"
                            @click="const m = $store.cart.productDetailModal.menu; $store.cart.closeProductDetail(); $store.cart.openVariantModal(m);"
                            class="flex-1 py-3 px-5 rounded-2xl bg-primary hover:bg-primary/90 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-primary/25 transition active:scale-95 flex items-center justify-center gap-2"
                        >
                            <?= icon('plus', 'w-4 h-4') ?>
                            <span>Kustomisasi & Pesan Menu Ini</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
