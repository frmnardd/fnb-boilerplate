<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Menu Variant Customization Modal
 * Allows customers to customize sizes, toppings, sweetness levels & notes before adding to cart.
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
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-900 uppercase tracking-wider mb-2">
                                    Kustomisasi Pilihan
                                </span>
                                <h3 class="text-xl font-extrabold tracking-tight" x-text="$store.cart.variantModal.menu.name"></h3>
                                <p class="text-xs text-slate-300 mt-1 line-clamp-2" x-text="$store.cart.variantModal.menu.description"></p>
                            </div>
                            <button 
                                @click="$store.cart.closeVariantModal()" 
                                class="text-slate-400 hover:text-white p-1 rounded-lg bg-white/10 hover:bg-white/20 transition"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Variant Groups List -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
                        <template x-if="$store.cart.variantModal.menu.parsedVariants && $store.cart.variantModal.menu.parsedVariants.options">
                            <template x-for="(group, gIdx) in $store.cart.variantModal.menu.parsedVariants.options" :key="gIdx">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-slate-900" x-text="group.name"></h4>
                                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pilih 1</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <template x-for="(choice, cIdx) in group.choices" :key="cIdx">
                                            <button 
                                                type="button"
                                                @click="$store.cart.selectVariantChoice(group.name, choice)"
                                                :class="$store.cart.variantModal.selectedChoices[group.name]?.name === choice.name 
                                                    ? 'border-primary bg-primary/5 text-primary font-bold shadow-sm' 
                                                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                                class="flex items-center justify-between p-3 rounded-2xl border text-xs transition text-left"
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
                                placeholder="Contoh: Es sedikit, jangan terlalu manis, sambal dipisah..."
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Masukkan ke Keranjang</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
