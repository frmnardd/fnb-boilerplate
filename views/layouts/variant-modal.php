<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Dynamic Menu Variant Customization Modal (PRD v2.0 - FR-03)
 * Dynamic category modifiers (Coffee vs Food vs Snacks) with instant price recalculation.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */
?>
<div 
    x-cloak 
    x-show="$store.cart.variantModal.isOpen" 
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
>
    <!-- Background Backdrop -->
    <div 
        x-show="$store.cart.variantModal.isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="$store.cart.closeVariantModal()"
    ></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div 
            x-show="$store.cart.variantModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-t-3xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-slate-100"
        >
            <template x-if="$store.cart.variantModal.menu">
                <div>
                    <!-- Header with Image & Title -->
                    <div class="relative bg-slate-900 text-white p-6 pb-5 overflow-hidden">
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 uppercase tracking-wider mb-2">
                                    Kustomisasi Varian
                                </span>
                                <h3 class="text-xl font-extrabold tracking-tight" x-text="$store.cart.variantModal.menu.name"></h3>
                                <p class="text-xs text-slate-300 mt-1 line-clamp-2" x-text="$store.cart.variantModal.menu.description"></p>
                            </div>
                            <button 
                                @click="$store.cart.closeVariantModal()" 
                                class="text-slate-400 hover:text-white p-1 rounded-lg bg-white/10 hover:bg-white/20 transition"
                            >
                                <?= icon('x', 'w-5 h-5') ?>
                            </button>
                        </div>
                    </div>

                    <!-- Product Metadata Bar: Halal, Rating, Calories, Main Ingredients (FR-206, UAT-204) -->
                    <div class="px-6 py-3.5 bg-slate-50 border-b border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                        <div class="flex items-center gap-1.5">
                            <img src="/assets/images/halal-badge.svg" class="w-4 h-4 shrink-0" alt="Halal">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Sertifikasi</span>
                                <span class="text-[10px] font-bold text-emerald-700 block leading-tight" title="ID31110001234560723">ID31110001234560723</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-500 shrink-0"><?= icon('star', 'w-4 h-4') ?></span>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Rating</span>
                                <span class="text-[11px] font-bold text-slate-800 block leading-tight" x-text="($store.cart.variantModal.menu?.rating || '4.9') + '/5.0'"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-rose-500 shrink-0"><?= icon('flame', 'w-4 h-4') ?></span>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Estimasi Kalori</span>
                                <span class="text-[11px] font-bold text-slate-800 block leading-tight" x-text="($store.cart.variantModal.menu?.calories ? $store.cart.variantModal.menu.calories + ' kkal' : '~180 kkal')"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="text-primary shrink-0"><?= icon('utensils', 'w-4 h-4') ?></span>
                            <div class="truncate">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Bahan Utama</span>
                                <span class="text-[11px] font-semibold text-slate-700 block leading-tight truncate" :title="$store.cart.variantModal.menu?.ingredients_text || 'Bahan Pilihan Segar'" x-text="$store.cart.variantModal.menu?.ingredients_text || 'Bahan Pilihan Alami'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Modifier Groups List -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
                        <template x-if="$store.cart.variantModal.menu.parsedVariants && $store.cart.variantModal.menu.parsedVariants.options">
                            <template x-for="(group, gIdx) in $store.cart.variantModal.menu.parsedVariants.options" :key="gIdx">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                                            <?= icon('check', 'w-3.5 h-3.5 text-primary') ?>
                                            <span x-text="group.name"></span>
                                        </h4>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-100 px-2 py-0.5 rounded">Pilih 1</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="(choice, cIdx) in group.choices" :key="cIdx">
                                            <button 
                                                type="button"
                                                @click="$store.cart.selectVariantChoice(group.name, choice)"
                                                :class="$store.cart.variantModal.selectedChoices[group.name]?.name === choice.name 
                                                    ? 'border-primary bg-primary/5 text-primary font-bold shadow-sm' 
                                                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                                class="flex items-center justify-between p-3 rounded-xl border text-xs transition text-left"
                                            >
                                                <span x-text="choice.name"></span>
                                                <span 
                                                    class="text-xs font-semibold"
                                                    :class="$store.cart.variantModal.selectedChoices[group.name]?.name === choice.name ? 'text-primary' : 'text-slate-500'"
                                                    x-text="choice.extra_price > 0 ? '+ ' + $store.cart.formatRupiah(choice.extra_price) : 'Gratis'"
                                                ></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </template>

                        <!-- Item Notes Input -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700">
                                Catatan Khusus untuk Dapur / Barista:
                            </label>
                            <input 
                                type="text" 
                                x-model="$store.cart.variantModal.itemNotes" 
                                placeholder="Contoh: Takaran es sedikit, sambal dipisah..."
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                        </div>

                        <!-- Quantity Selector -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                            <span class="text-xs font-bold text-slate-800">Jumlah Pesanan:</span>
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button"
                                    @click="if($store.cart.variantModal.qty > 1) $store.cart.variantModal.qty--"
                                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-200 transition"
                                >-</button>
                                <span class="font-bold text-sm w-6 text-center" x-text="$store.cart.variantModal.qty"></span>
                                <button 
                                    type="button"
                                    @click="$store.cart.variantModal.qty++"
                                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-200 transition"
                                >+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 block">Total Item:</span>
                            <span class="text-lg font-black text-slate-900" x-text="$store.cart.formatRupiah($store.cart.getModalTotalPrice())"></span>
                        </div>
                        <button 
                            type="button"
                            @click="$store.cart.confirmVariantSelection()"
                            class="flex-1 py-3 px-5 rounded-2xl bg-primary hover:bg-primary/90 text-white font-bold text-sm shadow-lg shadow-primary/25 transition active:scale-95 flex items-center justify-center gap-2"
                        >
                            <?= icon('plus', 'w-4 h-4') ?>
                            <span>Masukkan ke Keranjang</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
