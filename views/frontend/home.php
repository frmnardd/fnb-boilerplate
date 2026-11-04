<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Frontend Home View (Foodie Adaptation)
 * Dynamic Theme Engine, Instant Alpine.js Menu Filter, Web-to-WhatsApp Ordering & Franchise ROI Calculator.
 */

$pdo = db();
$settings = get_setting();

// 1. Fetch Categories
$categoriesStmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
$categories = $categoriesStmt->fetchAll();

// 2. Fetch Menus with Category Names
$menusStmt = $pdo->query("
    SELECT m.*, c.name as category_name, c.slug as category_slug 
    FROM menus m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY m.sort_order ASC
");
$menus = $menusStmt->fetchAll();

// 3. Fetch Active Promos
$promosStmt = $pdo->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY id ASC");
$promos = $promosStmt->fetchAll();

// 4. Fetch Active Outlets
$outletsStmt = $pdo->query("SELECT * FROM outlets WHERE is_active = 1 ORDER BY id ASC");
$outlets = $outletsStmt->fetchAll();

// Prepare menu payload for Alpine.js client-side reactivity
$menusPayload = array_map(function($m) {
    return [
        'id'            => (int)$m['id'],
        'category_id'   => (int)$m['category_id'],
        'category_name' => $m['category_name'],
        'category_slug' => $m['category_slug'],
        'name'          => $m['name'],
        'slug'          => $m['slug'],
        'description'   => $m['description'] ?? '',
        'price'         => (int)$m['price'],
        'strike_price'  => $m['strike_price'] ? (int)$m['strike_price'] : null,
        'image_url'     => $m['image_url'] ?: '/assets/images/food-menu-1.png',
        'variants_json' => $m['variants_json'] ? json_decode($m['variants_json'], true) : null,
        'is_available'  => (int)$m['is_available'],
        'is_featured'   => (int)$m['is_featured'],
    ];
}, $menus);

// Active Dine-in Table Detection (from Table QR Code scan)
$activeTable = null;
$tableSlug = trim($_GET['table'] ?? ($_SESSION['active_table']['qr_slug'] ?? ''));
if (!empty($tableSlug)) {
    $stmtT = $pdo->prepare("SELECT * FROM tables WHERE qr_slug = :slug LIMIT 1");
    $stmtT->execute(['slug' => $tableSlug]);
    $foundTable = $stmtT->fetch();
    if ($foundTable) {
        $activeTable = $foundTable;
        $_SESSION['active_table'] = $foundTable;
    }
}

// Load Header Layout
require dirname(__DIR__) . '/layouts/header.php';
?>

<?php if ($activeTable): ?>
    <!-- Active Dine-in Table Banner -->
    <div class="bg-slate-900 text-white py-3 px-4 text-center text-xs font-bold flex flex-wrap items-center justify-center gap-2 border-b border-slate-800 shadow-sm sticky top-0 z-30">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-primary text-white text-[11px] font-black uppercase">
            🪑 Meja <?= e($activeTable['table_number']) ?>
        </span>
        <span class="text-slate-300">
            Area <?= e($activeTable['area']) ?> • Pesanan Anda terhubung langsung ke Dapur & Kasir
        </span>
    </div>
<?php endif; ?>

<main class="space-y-16 sm:space-y-24 pb-20">

    <!-- =================================================================== -->
    <!-- 1. HERO SECTION (Fast-Casual / Coffee Shop Foodie Style)            -->
    <!-- =================================================================== -->
    <section id="home" class="relative pt-6 sm:pt-12 pb-16 overflow-hidden">
        <!-- Background Ambient Shapes -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Hero Left Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary/10 text-primary text-xs sm:text-sm font-bold border border-primary/20">
                        <span>✨ Rasa Nusantara Asli</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        <span class="text-slate-600 font-semibold">Zero Commission Order</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight">
                        <?= e($settings['hero_title'] ?? 'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara') ?>
                    </h1>

                    <p class="text-slate-600 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                        <?= e($settings['hero_subtitle'] ?? 'Diseduh dari biji kopi pilihan petani lokal dan dipadukan dengan hidangan khas Nusantara bercita rasa legendaris.') ?>
                    </p>

                    <!-- CTAs & WhatsApp Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                        <a 
                            href="#food-menu"
                            class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-sm sm:text-base shadow-xl shadow-primary/30 transition-all hover:-translate-y-0.5 text-center flex items-center justify-center gap-2"
                        >
                            <span>🛒 Buka Menu & Pesan</span>
                        </a>

                        <a 
                            href="#franchise"
                            class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-white border border-slate-200 hover:border-slate-300 text-slate-800 font-bold text-sm sm:text-base shadow-sm hover:shadow transition-all text-center flex items-center justify-center gap-2"
                        >
                            <span>💼 Peluang Franchise & ROI</span>
                        </a>
                    </div>

                    <!-- Trust Signals & Badges -->
                    <div class="pt-6 border-t border-slate-200/60 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">100%</span>
                            <span class="text-xs text-slate-500 font-medium">Biji Kopi Lokal</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">Rp 0</span>
                            <span class="text-xs text-slate-500 font-medium">Biaya Tambahan</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">15 Menit</span>
                            <span class="text-xs text-slate-500 font-medium">Saji Segar & Hangat</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Right Banner Image -->
                <div class="lg:col-span-5 relative flex justify-center">
                    <div class="relative w-full max-w-md lg:max-w-none">
                        <!-- Decorative glow and frame -->
                        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-tr from-amber-500/20 to-primary/30 p-2 shadow-2xl">
                            <img 
                                src="/assets/images/hero-banner.png" 
                                onerror="this.src='/assets/images/about-banner.png'"
                                alt="<?= e($settings['site_name']) ?>" 
                                class="w-full h-auto object-cover rounded-2xl hover:scale-105 transition-transform duration-500"
                            >
                        </div>

                        <!-- Floating Promo Tag -->
                        <div class="absolute -bottom-6 -left-4 sm:left-4 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-100 shadow-xl flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-900 flex items-center justify-center font-black text-lg">
                                ☕
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Promo Spesial Hari Ini</span>
                                <span class="text-[11px] text-primary font-extrabold block">Diskon s/d 20% Menu Kopi</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. SPECIAL PROMOS SECTION                                           -->
    <!-- =================================================================== -->
    <?php if (!empty($promos)): ?>
    <section id="promos" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block mb-1">Penawaran Eksklusif</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kupon Promo & Diskon Spesial</h2>
                    </div>
                    <span class="text-xs text-slate-400">Gunakan kode voucher saat konfirmasi pesanan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($promos as $promo): ?>
                        <div class="bg-white/10 hover:bg-white/15 backdrop-blur-sm border border-white/10 p-5 rounded-2xl flex items-center justify-between gap-4 transition">
                            <div class="space-y-1">
                                <h3 class="font-bold text-base text-white"><?= e($promo['title']) ?></h3>
                                <p class="text-xs text-slate-300">
                                    Berlaku hingga: <?= $promo['valid_until'] ? date('d M Y', strtotime($promo['valid_until'])) : 'Sepanjang Tahun' ?>
                                </p>
                            </div>
                            <?php if (!empty($promo['promo_code'])): ?>
                                <div class="px-3.5 py-1.5 rounded-xl bg-amber-400 text-slate-950 font-black text-xs tracking-wider border border-amber-300 shadow-sm shrink-0">
                                    <?= e($promo['promo_code']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- 3. INTERACTIVE MENU & CATEGORY FILTER (Alpine.js)                   -->
    <!-- =================================================================== -->
    <section 
        id="food-menu" 
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        x-data="{
            activeCategory: 'all',
            searchQuery: '',
            allMenus: <?= htmlspecialchars(json_encode($menusPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>,
            get filteredMenus() {
                return this.allMenus.filter(item => {
                    const matchesCategory = this.activeCategory === 'all' || item.category_slug === this.activeCategory;
                    const matchesSearch = !this.searchQuery || 
                        item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        item.description.toLowerCase().includes(this.searchQuery.toLowerCase());
                    return matchesCategory && matchesSearch;
                });
            }
        }"
    >
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-10">
            <span class="text-xs font-extrabold text-primary uppercase tracking-wider block">Katalog Menu & Harga</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Pilihan Rasa Favorit Nusantara
            </h2>
            <p class="text-sm text-slate-500">
                Pilih menu favorit Anda, pilih varian rasa / topping, dan pesan langsung via WhatsApp tanpa biaya komisi.
            </p>
        </div>

        <!-- Filter Controls (Category Tabs & Instant Search) -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
            <!-- Category Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-none">
                <button 
                    type="button"
                    @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-primary text-white font-bold shadow-md shadow-primary/20' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition"
                >
                    Semua Menu (<?= count($menus) ?>)
                </button>

                <?php foreach ($categories as $cat): ?>
                    <button 
                        type="button"
                        @click="activeCategory = '<?= e($cat['slug']) ?>'"
                        :class="activeCategory === '<?= e($cat['slug']) ?>' ? 'bg-primary text-white font-bold shadow-md shadow-primary/20' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition"
                    >
                        <?= e($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Instant Search Bar -->
            <div class="w-full md:w-72 relative">
                <input 
                    type="search" 
                    x-model="searchQuery" 
                    placeholder="Cari kopi, nasi, mie..." 
                    class="w-full text-xs sm:text-sm pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-sm"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <template x-for="item in filteredMenus" :key="item.id">
                <div 
                    class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
                    :class="item.is_available === 0 ? 'opacity-70 grayscale-[35%]' : ''"
                >
                    <!-- Card Media / Photo -->
                    <div class="relative bg-slate-100 aspect-square overflow-hidden">
                        <img 
                            :src="item.image_url" 
                            :alt="item.name" 
                            onerror="this.src='/assets/images/food-menu-1.png'"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >

                        <!-- Sold Out Badge -->
                        <template x-if="item.is_available === 0">
                            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="bg-rose-600 text-white text-xs font-black px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-lg">
                                    Habis / Sold Out
                                </span>
                            </div>
                        </template>

                        <!-- Discount / Promo Badge -->
                        <template x-if="item.is_available === 1 && item.strike_price && item.strike_price > item.price">
                            <span 
                                class="absolute top-3 left-3 bg-rose-500 text-white text-[11px] font-black px-2.5 py-1 rounded-lg shadow-md"
                                x-text="'-' + Math.round(((item.strike_price - item.price) / item.strike_price) * 100) + '%'"
                            ></span>
                        </template>

                        <!-- Featured Badge -->
                        <template x-if="item.is_available === 1 && item.is_featured === 1">
                            <span class="absolute top-3 right-3 bg-amber-400 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded-md shadow-sm">
                                ★ Favorit
                            </span>
                        </template>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1" x-text="item.category_name"></span>
                            <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-primary transition-colors" x-text="item.name"></h3>
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed" x-text="item.description"></p>
                        </div>

                        <!-- Price & Action Button -->
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div>
                                <span class="text-base font-black text-slate-900 block" x-text="$store.cart.formatRupiah(item.price)"></span>
                                <template x-if="item.strike_price">
                                    <span class="text-xs text-slate-400 line-through block" x-text="$store.cart.formatRupiah(item.strike_price)"></span>
                                </template>
                            </div>

                            <!-- Pesan CTA Button -->
                            <button 
                                type="button"
                                :disabled="item.is_available === 0"
                                @click="$store.cart.openVariantModal(item)"
                                :class="item.is_available === 0 
                                    ? 'bg-slate-200 text-slate-400 cursor-not-allowed' 
                                    : 'bg-primary hover:bg-primary/90 text-white shadow-md shadow-primary/20 active:scale-95'"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                            >
                                <span x-text="item.is_available === 0 ? 'Habis' : (item.variants_json ? '+ Pilih Varian' : '+ Pesan')"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty Filter Results -->
        <div x-show="filteredMenus.length === 0" class="text-center py-16 space-y-3 bg-white rounded-3xl border border-slate-200/80">
            <span class="text-4xl block">🔍</span>
            <h3 class="text-base font-bold text-slate-800">Tidak ada menu yang sesuai</h3>
            <p class="text-xs text-slate-500">Silakan ubah kata kunci pencarian atau pilih kategori lain.</p>
            <button @click="activeCategory = 'all'; searchQuery = ''" class="text-xs font-bold text-primary hover:underline">
                Reset Filter
            </button>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. FRANCHISE & B2B PARTNERSHIP WITH INTERACTIVE ROI CALCULATOR      -->
    <!-- =================================================================== -->
    <section id="franchise" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-12 text-white shadow-2xl relative overflow-hidden" x-data="roiCalculator">
            
            <div class="relative z-10 max-w-4xl mx-auto text-center space-y-4 mb-10">
                <span class="inline-block px-3.5 py-1 rounded-full text-xs font-bold bg-amber-400 text-slate-950 uppercase tracking-wider">
                    Peluang Bisnis & Kemitraan (B2B)
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Miliki Outlet <?= e($settings['site_name']) ?> di Kota Anda
                </h2>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl mx-auto leading-relaxed">
                    Model bisnis FnB teruji dengan margin tinggi, sistem operasional terstandarisasi SOP lengkap, supply bahan baku terpusat, dan dukungan pemasaran digital berkala.
                </p>
            </div>

            <!-- The ROI Calculator Box -->
            <div class="bg-slate-800/80 backdrop-blur-md rounded-3xl border border-slate-700/80 p-6 sm:p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-amber-300 flex items-center gap-2">
                        <span>📊</span>
                        <span>Kalkulator Simulasi Balik Modal (ROI)</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Geser parameter di bawah ini untuk melihat estimasi potensi omset bulanan dan proyeksi waktu balik modal outlet Anda.
                    </p>
                </div>

                <!-- Package Selection Pills -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        1. Pilih Paket Investasi Kemitraan:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <template x-for="pkg in packages" :key="pkg.investment">
                            <button 
                                type="button"
                                @click="setPackage(pkg.investment)"
                                :class="investmentPackage === pkg.investment 
                                    ? 'border-amber-400 bg-amber-400/10 text-white ring-2 ring-amber-400/30' 
                                    : 'border-slate-700 bg-slate-900/40 text-slate-400 hover:border-slate-600'"
                                class="p-4 rounded-2xl border text-left transition"
                            >
                                <span class="font-bold text-sm block text-white" x-text="pkg.name"></span>
                                <span class="text-xs text-amber-300 font-extrabold block mt-0.5" x-text="formatRupiah(pkg.investment)"></span>
                                <span class="text-[11px] text-slate-400 block mt-1" x-text="pkg.desc"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Sliders Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-700">
                    <!-- Daily Cups Slider -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-300">Estimasi Penjualan Harian:</span>
                            <span class="font-extrabold text-amber-400 text-sm" x-text="dailyCups + ' Porsi / Hari'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="30" 
                            max="250" 
                            step="5" 
                            x-model.number="dailyCups"
                            class="w-full accent-amber-400 bg-slate-700 h-2 rounded-lg cursor-pointer"
                        >
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <span>Konservatif (30 porsi)</span>
                            <span>Moderat (100 porsi)</span>
                            <span>Ramai (250 porsi)</span>
                        </div>
                    </div>

                    <!-- Average Price per Item -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-300">Rata-rata Harga Jual:</span>
                            <span class="font-extrabold text-amber-400 text-sm" x-text="formatRupiah(avgPricePerItem) + ' / Item'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="15000" 
                            max="35000" 
                            step="1000" 
                            x-model.number="avgPricePerItem"
                            class="w-full accent-amber-400 bg-slate-700 h-2 rounded-lg cursor-pointer"
                        >
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <span>Rp 15.000</span>
                            <span>Rp 25.000</span>
                            <span>Rp 35.000</span>
                        </div>
                    </div>
                </div>

                <!-- Calculated Metric Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-slate-700">
                    <div class="bg-slate-900/70 p-4 rounded-2xl border border-slate-700/60">
                        <span class="text-[11px] text-slate-400 font-semibold block">Proyeksi Omset / Bln</span>
                        <span class="text-lg font-black text-white mt-1 block" x-text="formatRupiah(monthlyRevenue)"></span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">30 Hari Kerja</span>
                    </div>

                    <div class="bg-slate-900/70 p-4 rounded-2xl border border-slate-700/60">
                        <span class="text-[11px] text-slate-400 font-semibold block">Laba Bersih / Bln</span>
                        <span class="text-lg font-black text-emerald-400 mt-1 block" x-text="formatRupiah(monthlyNetProfit)"></span>
                        <span class="text-[10px] text-emerald-400/80 block mt-0.5">Setelah HPP & Opex</span>
                    </div>

                    <div class="bg-slate-900/70 p-4 rounded-2xl border border-slate-700/60">
                        <span class="text-[11px] text-slate-400 font-semibold block">Estimasi Balik Modal</span>
                        <span class="text-lg font-black text-amber-400 mt-1 block" x-text="'~' + paybackPeriodMonths + ' Bulan'"></span>
                        <span class="text-[10px] text-amber-400/80 block mt-0.5">Payback Period</span>
                    </div>

                    <div class="bg-slate-900/70 p-4 rounded-2xl border border-slate-700/60">
                        <span class="text-[11px] text-slate-400 font-semibold block">Annual ROI</span>
                        <span class="text-lg font-black text-cyan-400 mt-1 block" x-text="annualROI + '% / Tahun'"></span>
                        <span class="text-[10px] text-cyan-400/80 block mt-0.5">Return On Investment</span>
                    </div>
                </div>

                <!-- CTA Button to WhatsApp Business Development -->
                <div class="pt-4 text-center">
                    <a 
                        :href="generateFranchiseWhatsAppUrl('<?= e($settings['whatsapp_franchise_number'] ?? '6281298765432') ?>')"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-3 w-full sm:w-auto px-8 py-4 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-sm sm:text-base shadow-xl shadow-amber-400/20 transition-all hover:scale-105 active:scale-95"
                    >
                        <span>💬 Konsultasikan Slot Kemitraan via WhatsApp</span>
                    </a>
                    <p class="text-[11px] text-slate-400 mt-2">
                        Tim Business Development kami siap membantu survey lokasi potensial & proposal detail.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. OUTLET AGGREGATOR & DELIVERY DEEP LINKS                          -->
    <!-- =================================================================== -->
    <section id="outlets" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-12">
            <span class="text-xs font-extrabold text-primary uppercase tracking-wider block">Lokasi Cabang & Delivery</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Temukan Cabang Terdekat
            </h2>
            <p class="text-sm text-slate-500">
                Kunjungi outlet kami atau pesan melalui aplikasi pesan-antar favorit Anda dengan mudah.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($outlets as $outlet): ?>
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-xl font-bold">
                            📍
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-900"><?= e($outlet['name']) ?></h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed"><?= e($outlet['address']) ?></p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-700 font-semibold bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span>🕒</span>
                            <span>Jam Buka: <?= e($outlet['open_hours'] ?? '08:00 - 22:00 WIB') ?></span>
                        </div>
                    </div>

                    <!-- Direct Deep Links -->
                    <div class="space-y-2.5 pt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pesan Online / Arah:</span>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <?php if (!empty($outlet['gmaps_link'])): ?>
                                <a href="<?= e($outlet['gmaps_link']) ?>" target="_blank" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-center transition flex items-center justify-center gap-1.5">
                                    <span>📍</span>
                                    <span>Google Maps</span>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($outlet['gofood_url'])): ?>
                                <a href="<?= e($outlet['gofood_url']) ?>" target="_blank" class="px-3 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-center transition">
                                    GoFood
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($outlet['grabfood_url'])): ?>
                                <a href="<?= e($outlet['grabfood_url']) ?>" target="_blank" class="px-3 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-center transition">
                                    GrabFood
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($outlet['shopeefood_url'])): ?>
                                <a href="<?= e($outlet['shopeefood_url']) ?>" target="_blank" class="px-3 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold text-center transition">
                                    ShopeeFood
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<?php if ($activeTable): ?>
<!-- Floating Call Waiter Button & Modal (Bottom Left) -->
<div 
    x-data="{
        callOpen: false,
        calling: false,
        callSuccess: false,
        selectedType: 'WATER_TISSUE',
        async submitCall() {
            this.calling = true;
            try {
                const form = new FormData();
                form.append('table_id', '<?= $activeTable['id'] ?>');
                form.append('call_type', this.selectedType);
                const res = await fetch('/api/call-waiter', { method: 'POST', body: form });
                const data = await res.json();
                if (data.success) {
                    this.callSuccess = true;
                    setTimeout(() => { this.callOpen = false; this.callSuccess = false; }, 2500);
                } else {
                    alert(data.message || 'Gagal memanggil pelayan.');
                }
            } catch(e) {
                alert('Gagal memanggil pelayan. Silakan hubungi kasir.');
            } finally {
                this.calling = false;
            }
        }
    }"
>
    <!-- Floating Trigger -->
    <div class="fixed bottom-6 left-6 z-40">
        <button 
            @click="callOpen = true"
            class="flex items-center gap-2.5 px-4 py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-2xl shadow-slate-900/40 border-2 border-white/20 transition-all active:scale-95 hover:scale-105"
        >
            <span class="text-base animate-bounce">🛎️</span>
            <span>Panggil Pelayan (Meja <?= e($activeTable['table_number']) ?>)</span>
        </button>
    </div>

    <!-- Modal Panggil Pelayan -->
    <div 
        x-show="callOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="callOpen = false" 
            class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-slate-100"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🛎️</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Panggil Pelayan</h3>
                        <p class="text-[11px] text-slate-400">Meja <?= e($activeTable['table_number']) ?> (<?= e($activeTable['area']) ?>)</p>
                    </div>
                </div>
                <button @click="callOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
            </div>

            <!-- Success State -->
            <div x-show="callSuccess" class="py-8 text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 text-2xl flex items-center justify-center mx-auto">
                    ✓
                </div>
                <h4 class="text-base font-bold text-slate-900">Panggilan Diterima!</h4>
                <p class="text-xs text-slate-500">Pelayan segera menuju ke Meja <?= e($activeTable['table_number']) ?>.</p>
            </div>

            <!-- Selection Options Form -->
            <div x-show="!callSuccess" class="mt-5 space-y-3">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Kebutuhan Layanan:</label>

                <div class="grid grid-cols-1 gap-2.5">
                    <label 
                        @click="selectedType = 'WATER_TISSUE'"
                        :class="selectedType === 'WATER_TISSUE' ? 'border-primary bg-primary/5 text-slate-900 font-bold' : 'border-slate-200 text-slate-700'"
                        class="p-3 rounded-2xl border flex items-center gap-3 cursor-pointer transition text-xs"
                    >
                        <span class="text-lg">💧</span>
                        <div class="flex-1">
                            <span class="block font-bold">Minta Air Mineral & Tissue</span>
                            <span class="text-[10px] text-slate-400 block">Antar air minum atau tisu tambahan</span>
                        </div>
                    </label>

                    <label 
                        @click="selectedType = 'ASK_MENU'"
                        :class="selectedType === 'ASK_MENU' ? 'border-primary bg-primary/5 text-slate-900 font-bold' : 'border-slate-200 text-slate-700'"
                        class="p-3 rounded-2xl border flex items-center gap-3 cursor-pointer transition text-xs"
                    >
                        <span class="text-lg">🙋</span>
                        <div class="flex-1">
                            <span class="block font-bold">Panggil Pelayan ke Meja</span>
                            <span class="text-[10px] text-slate-400 block">Bantuan informasi atau rekomendasi menu</span>
                        </div>
                    </label>

                    <label 
                        @click="selectedType = 'CLEAN_TABLE'"
                        :class="selectedType === 'CLEAN_TABLE' ? 'border-primary bg-primary/5 text-slate-900 font-bold' : 'border-slate-200 text-slate-700'"
                        class="p-3 rounded-2xl border flex items-center gap-3 cursor-pointer transition text-xs"
                    >
                        <span class="text-lg">🧹</span>
                        <div class="flex-1">
                            <span class="block font-bold">Bersihkan Meja</span>
                            <span class="text-[10px] text-slate-400 block">Angkat piring kotor atau lap meja</span>
                        </div>
                    </label>

                    <label 
                        @click="selectedType = 'REQUEST_BILL'"
                        :class="selectedType === 'REQUEST_BILL' ? 'border-primary bg-primary/5 text-slate-900 font-bold' : 'border-slate-200 text-slate-700'"
                        class="p-3 rounded-2xl border flex items-center gap-3 cursor-pointer transition text-xs"
                    >
                        <span class="text-lg">🧾</span>
                        <div class="flex-1">
                            <span class="block font-bold">Minta Tagihan / Bill</span>
                            <span class="text-[10px] text-slate-400 block">Siapkan struk dan metode bayar</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="callOpen = false" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        @click="submitCall()" 
                        :disabled="calling"
                        class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-95 transition flex items-center gap-1.5"
                    >
                        <span x-text="calling ? 'Memanggil...' : 'Kirim Panggilan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Load Footer Layout
require dirname(__DIR__) . '/layouts/footer.php';
?>
