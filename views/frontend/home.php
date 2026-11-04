<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Frontend Home View (PRD v2.0 Omnichannel Edition)
 * Multi-Outlet, Dynamic Category Modifiers, Dual Checkout, Voucher Engine & Franchise CRM.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
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

// Prepare menu payload for Alpine.js client-side reactivity (FR-01, FR-06)
$menusPayload = array_map(function($m) {
    return [
        'id'               => (int)$m['id'],
        'category_id'      => (int)$m['category_id'],
        'category_name'    => $m['category_name'],
        'category_slug'    => $m['category_slug'],
        'name'             => $m['name'],
        'slug'             => $m['slug'],
        'description'      => $m['description'] ?? '',
        'price'            => (int)$m['price'],
        'strike_price'     => $m['strike_price'] ? (int)$m['strike_price'] : null,
        'image_url'        => $m['image_url'] ?: '/assets/images/menu-kopi-aren.webp',
        'variants_json'    => $m['variants_json'] ? json_decode($m['variants_json'], true) : null,
        'station'          => $m['station'] ?? 'BAR',
        'is_available'     => (int)$m['is_available'],
        'is_featured'      => (int)$m['is_featured'],
        'calories'         => (int)($m['calories'] ?? 185),
        'nutrition_info'   => $m['nutrition_info'] ?? 'Karbohidrat 22g, Protein 3g, Lemak 4g',
        'ingredients_text' => $m['ingredients_text'] ?? 'Bahan baku alami pilihan Nusantara.',
        'rating'           => (float)($m['rating'] ?? 4.9),
        'review_count'     => (int)($m['review_count'] ?? 248),
        'halal_certified'  => (int)($m['halal_certified'] ?? 1),
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
            <?= icon('table-2', 'w-3.5 h-3.5') ?> Meja <?= e($activeTable['table_number']) ?>
        </span>
        <span class="text-slate-300">
            Area <?= e($activeTable['area']) ?> • Pesanan Anda otomatis terhubung ke Dapur & Kasir
        </span>
    </div>
<?php endif; ?>

<main class="space-y-16 sm:space-y-24 pb-20">

    <!-- =================================================================== -->
    <!-- 1. HERO SECTION (FR-01: Authentic Indonesian Culinary Signature)    -->
    <!-- =================================================================== -->
    <section id="home" class="relative pt-6 sm:pt-12 pb-16 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Hero Left Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary/10 text-primary text-xs sm:text-sm font-bold border border-primary/20">
                        <span class="inline-flex items-center gap-1"><?= icon('star', 'w-3.5 h-3.5') ?> Warisan Rasa Otentik</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        <span class="text-slate-600 font-semibold">Zero Commission Order</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight">
                        <?= e($settings['hero_title'] ?? 'Nikmati Harmoni Kopi & Cita Rasa Otentik Nusantara') ?>
                    </h1>

                    <p class="text-slate-600 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                        <?= e($settings['hero_subtitle'] ?? 'Perpaduan biji kopi pilihan nusantara dengan kuliner legendaris Indonesia. Pesan mandiri tanpa antre, nikmati langsung di meja atau pesan antar ke rumah.') ?>
                    </p>

                    <!-- CTAs & Ordering Options -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                        <a 
                            href="#food-menu"
                            class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-sm sm:text-base shadow-xl shadow-primary/30 transition-all hover:-translate-y-0.5 text-center flex items-center justify-center gap-2"
                        >
                            <?= icon('shopping-bag', 'w-4 h-4') ?>
                            <span>Buka Menu & Pesan Mandiri</span>
                        </a>

                        <a 
                            href="#franchise"
                            class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-white border border-slate-200 hover:border-slate-300 text-slate-800 font-bold text-sm sm:text-base shadow-sm hover:shadow transition-all text-center flex items-center justify-center gap-2"
                        >
                            <?= icon('trending-up', 'w-4 h-4 text-emerald-600') ?>
                            <span>Peluang Franchise & ROI 2.0</span>
                        </a>
                    </div>

                    <!-- Trust Signals & Badges (Halal Certified) -->
                    <div class="pt-6 border-t border-slate-200/60 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">100% Halal</span>
                            <span class="text-[11px] text-slate-500 font-medium">Sertifikasi Resmi BPJPH</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">3 Cabang</span>
                            <span class="text-[11px] text-slate-500 font-medium">Senopati, Braga, Malioboro</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-slate-900 block">0% Komisi</span>
                            <span class="text-[11px] text-slate-500 font-medium">Harga Asli Tanpa Mark-up</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Right Banner: Authentic Coffee & Indonesian Spread (FR-01, BUG-03) -->
                <div class="lg:col-span-5 relative flex justify-center">
                    <div class="relative w-full max-w-md lg:max-w-none">
                        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-tr from-amber-500/20 to-primary/30 p-2 shadow-2xl">
                            <img 
                                src="/assets/images/hero-banner.png" 
                                onerror="this.src='/assets/images/hero-signature.webp'"
                                alt="Kopi Susu Gula Aren & Kuliner Nusantara Otentik" 
                                loading="lazy"
                                class="w-full h-auto object-cover rounded-2xl hover:scale-105 transition-transform duration-500"
                            >
                        </div>

                        <!-- Floating Promo Tag -->
                        <div class="absolute -bottom-6 -left-4 sm:left-4 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-100 shadow-xl flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-900 flex items-center justify-center font-black text-lg">
                                <?= icon('coffee', 'w-6 h-6 text-slate-900') ?>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Promo Spesial Hari Ini</span>
                                <span class="text-[11px] text-primary font-extrabold block">Diskon 20% Voucher KOPIASIK</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. SPECIAL PROMOS SECTION (FR-04: Click-to-Apply Voucher Codes)     -->
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
                    <span class="text-xs text-slate-400">Klik "Salin & Pakai" untuk menerapkan otomatis di keranjang</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($promos as $promo): ?>
                        <div class="bg-white/10 hover:bg-white/15 backdrop-blur-sm border border-white/10 p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition">
                            <div class="space-y-1">
                                <h3 class="font-bold text-base text-white"><?= e($promo['title']) ?></h3>
                                <p class="text-xs text-slate-300">
                                    Berlaku hingga: <?= $promo['valid_until'] ? date('d M Y', strtotime($promo['valid_until'])) : '31 Des 2026' ?>
                                </p>
                            </div>
                            <?php if (!empty($promo['promo_code'])): ?>
                                <button 
                                    type="button"
                                    @click="$store.cart.copyAndApplyVoucher('<?= e($promo['promo_code']) ?>')"
                                    class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs tracking-wider border border-amber-300 shadow-md shrink-0 transition flex items-center justify-center gap-1.5"
                                    title="Salin dan gunakan voucher langsung di keranjang"
                                >
                                    <?= icon('tag', 'w-3.5 h-3.5') ?>
                                    <span>Salin & Pakai (<?= e($promo['promo_code']) ?>)</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- 3. INTERACTIVE MENU & CATEGORY FILTER (FR-01, FR-06)                -->
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
                Pilih menu favorit Anda, lihat detail komposisi & sertifikasi Halal, pilih varian rasa / topping, dan bayar instan via QRIS atau pesan via WhatsApp.
            </p>
        </div>

        <!-- Filter Controls -->
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
                    placeholder="Cari kopi, nasi, mie, camilan..." 
                    class="w-full text-xs sm:text-sm pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-sm"
                >
                <div class="absolute left-3.5 top-3 text-slate-400">
                    <?= icon('search', 'w-4 h-4') ?>
                </div>
            </div>
        </div>

        <!-- Menu Grid with Real WebP Assets (FR-01, FR-06, BUG-02) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <template x-for="item in filteredMenus" :key="item.id">
                <div 
                    class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group cursor-pointer"
                    :class="item.is_available === 0 ? 'opacity-70 grayscale-[35%]' : ''"
                >
                    <!-- Card Media / Photo: Click opens FR-06 Product Detail Modal -->
                    <div 
                        class="relative bg-slate-100 aspect-square overflow-hidden"
                        @click="$store.cart.openProductDetail(item)"
                    >
                        <img 
                            :src="item.image_url" 
                            :alt="item.name" 
                            loading="lazy"
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
                                <span class="inline-flex items-center gap-1"><?= icon('star', 'w-3 h-3 fill-slate-900') ?> Favorit</span>
                            </span>
                        </template>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div @click="$store.cart.openProductDetail(item)">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.category_name"></span>
                                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full">Halal</span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-primary transition-colors" x-text="item.name"></h3>
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed" x-text="item.description"></p>
                        </div>

                        <!-- Price & Action Button -->
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-base sm:text-lg font-black text-slate-900" x-text="$store.cart.formatRupiah(item.price)"></span>
                                    <template x-if="item.strike_price">
                                        <span class="text-xs text-slate-400 line-through font-medium" x-text="$store.cart.formatRupiah(item.strike_price)"></span>
                                    </template>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                :disabled="item.is_available === 0"
                                @click.stop="$store.cart.openVariantModal(item)"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                                :class="item.is_available === 1 ? 'bg-primary hover:bg-primary/90 text-white shadow-primary/20 active:scale-95' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                            >
                                <?= icon('plus', 'w-3.5 h-3.5') ?>
                                <span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 4. FRANCHISE ACQUISITION & ROI 2.0 (FR-09, TR-09)                   -->
    <!-- =================================================================== -->
    <section id="franchise" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-900 rounded-3xl p-6 sm:p-12 text-white shadow-2xl relative overflow-hidden" x-data="roiCalculator">
            <div class="max-w-3xl mx-auto text-center space-y-3 mb-10">
                <span class="text-xs font-extrabold text-amber-400 uppercase tracking-wider block">Peluang Investasi F&B 2026</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Simulasi Kemitraan & Kalkulator ROI 2.0
                </h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Hitung proyeksi pendapatan gerai kopi & kuliner Nusantara Anda secara transparan. Sistem kemitraan otonom tanpa royalti fee berkelanjutan.
                </p>
            </div>

            <!-- Investment Packages Selector -->
            <div class="space-y-6 max-w-4xl mx-auto">
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

                <!-- Detailed Operational Cost Breakdown (FR-09) -->
                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                            <?= icon('bar-chart', 'w-4 h-4 text-amber-400') ?>
                            <span>Rincian Komponen Biaya Operasional Bulanan:</span>
                        </span>
                        <span class="text-xs font-bold text-amber-300" x-text="'Total Opex: ' + formatRupiah(monthlyOpex)"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-700">
                            <span class="text-[10px] text-slate-400 block">HPP Bahan Baku (~35%)</span>
                            <span class="font-bold text-white block mt-0.5" x-text="formatRupiah(monthlyCogs)"></span>
                        </div>
                        <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-700">
                            <span class="text-[10px] text-slate-400 block">Estimasi Sewa Tempat</span>
                            <span class="font-bold text-white block mt-0.5" x-text="formatRupiah(monthlyRent)"></span>
                        </div>
                        <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-700">
                            <span class="text-[10px] text-slate-400 block">Gaji 2 Barista Staf</span>
                            <span class="font-bold text-white block mt-0.5" x-text="formatRupiah(monthlyLabor)"></span>
                        </div>
                        <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-700">
                            <span class="text-[10px] text-slate-400 block">Utilitas Listrik & Wifi</span>
                            <span class="font-bold text-white block mt-0.5" x-text="formatRupiah(monthlyUtility)"></span>
                        </div>
                    </div>
                </div>

                <!-- Lead Capture Form: Download Proposal PDF (FR-09, TR-09) -->
                <div 
                    class="p-6 rounded-3xl bg-slate-800/90 border border-amber-400/30 space-y-4"
                    x-data="{
                        leadName: '',
                        leadCity: '',
                        leadPackage: 'Coffee Shop Standard (Rp 65 Juta)',
                        leadPhone: '',
                        leadSubmitted: false,
                        leadLoading: false,
                        async submitLead() {
                            if (!this.leadName || !this.leadCity || !this.leadPhone) {
                                alert('Silakan lengkapi data formulir proposal.');
                                return;
                            }
                            this.leadLoading = true;
                            try {
                                const res = await fetch('/api/franchise-leads/create', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        name: this.leadName,
                                        city: this.leadCity,
                                        package_chosen: this.leadPackage,
                                        whatsapp_number: this.leadPhone
                                    })
                                });
                                const data = await res.json();
                                if (data.success) {
                                    this.leadSubmitted = true;
                                    // Trigger proposal download
                                    window.open('/download/proposal-kemitraan', '_blank');
                                } else {
                                    alert(data.message || 'Gagal mengirim data.');
                                }
                            } catch (e) {
                                alert('Koneksi terputus.');
                            } finally {
                                this.leadLoading = false;
                            }
                        }
                    }"
                >
                    <div class="text-left">
                        <span class="text-[10px] font-black text-amber-400 uppercase tracking-wider block">Official Franchise Deck</span>
                        <h3 class="text-lg font-bold text-white">Unduh Proposal & Katalog Kemitraan Resmi (PDF)</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Dapatkan detail SOP operasional, daftar peralatan, panduan survei lokasi, dan proyeksi keuangan lengkap.</p>
                    </div>

                    <div x-show="!leadSubmitted" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2">
                        <input 
                            type="text" 
                            x-model="leadName" 
                            placeholder="Nama Lengkap *" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-400"
                        >
                        <input 
                            type="text" 
                            x-model="leadCity" 
                            placeholder="Kota Rencana Buka *" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-400"
                        >
                        <select 
                            x-model="leadPackage"
                            class="px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-400"
                        >
                            <option value="Booth Express (Rp 35 Juta)">Booth Express (Rp 35 Jt)</option>
                            <option value="Coffee Shop Standard (Rp 65 Juta)">Coffee Shop Standard (Rp 65 Jt)</option>
                            <option value="Flagship Bistro (Rp 120 Juta)">Flagship Bistro (Rp 120 Jt)</option>
                        </select>
                        <input 
                            type="tel" 
                            x-model="leadPhone" 
                            placeholder="No. WhatsApp *" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-400"
                        >
                    </div>

                    <div x-show="!leadSubmitted" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <span class="text-[11px] text-slate-400">PDF langsung diunduh otomatis setelah formulir dikirim.</span>
                        <button 
                            type="button" 
                            @click="submitLead()" 
                            :disabled="leadLoading"
                            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs transition flex items-center justify-center gap-2"
                        >
                            <?= icon('download', 'w-4 h-4') ?>
                            <span x-text="leadLoading ? 'Menyimpan...' : 'Kirim & Unduh Proposal PDF'"></span>
                        </button>
                    </div>

                    <!-- Success State -->
                    <div x-show="leadSubmitted" class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 text-xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <?= icon('check-circle', 'w-5 h-5 text-emerald-400 shrink-0') ?>
                            <div>
                                <span class="font-bold text-white block">Terima kasih! Proposal kemitraan Anda telah diterbitkan.</span>
                                <span>Tim Business Development kami akan menghubungi Anda via WhatsApp untuk diskusi lebih lanjut.</span>
                            </div>
                        </div>
                        <a href="/download/proposal-kemitraan" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-500 text-white font-bold shrink-0 hover:bg-emerald-400 transition">
                            Unduh Ulang PDF
                        </a>
                    </div>
                </div>

                <!-- CTA Button to WhatsApp Business Development -->
                <div class="pt-4 text-center">
                    <a 
                        :href="generateFranchiseWhatsAppUrl('<?= e($settings['whatsapp_franchise_number'] ?? '6281298765432') ?>')"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-3 w-full sm:w-auto px-8 py-4 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-sm sm:text-base shadow-xl shadow-amber-400/20 transition-all hover:scale-105 active:scale-95"
                    >
                        <?= icon('whatsapp', 'w-5 h-5') ?>
                        <span>Konsultasikan Slot Kemitraan via WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 5. SOCIAL PROOF & PARTNER TESTIMONIALS (TR-09)                      -->
    <!-- =================================================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-10">
            <span class="text-xs font-extrabold text-primary uppercase tracking-wider block">Kredibilitas & Rekam Jejak</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Dipercaya Mitra di Berbagai Kota
            </h2>
            <p class="text-sm text-slate-500">
                Simak pengalaman nyata para pengusaha dan mitra franchise yang telah bertumbuh bersama Kopi & Rasa Nusantara.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-500">
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed italic">
                    "Sistem self-order dan KDS-nya sangat praktis. Di cabang Senopati kami bisa melayani 150+ transaksi per hari hanya dengan 3 staf barista tanpa antrean kasir menumpuk. BEP tercapai dalam 7 bulan!"
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Hendra Gunawan</h4>
                        <span class="text-[11px] text-slate-400 block">Mitra Cabang Senopati, Jakarta</span>
                    </div>
                    <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Flagship Bistro</span>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-500">
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed italic">
                    "Rasa kopi susu gula aren dan mie nyemeknya konsisten karena SOP bahan bakunya jelas. Pelanggan di Braga suka sekali pesan langsung dari meja pakai QR. Omzet stabil 85 juta per bulan."
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Rina Wijaya</h4>
                        <span class="text-[11px] text-slate-400 block">Mitra Cabang Braga, Bandung</span>
                    </div>
                    <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">Standard Shop</span>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-500">
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                    <?= icon('star', 'w-4 h-4 fill-amber-400') ?>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed italic">
                    "Bantuan survei lokasi dari tim pusat sangat akurat. Di Malioboro banyak wisatawan mampir untuk take-away dan dine-in. Margin bersih kami sekitar 32% tanpa terpotong komisi aplikasi."
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Dimas Pratama</h4>
                        <span class="text-[11px] text-slate-400 block">Mitra Cabang Malioboro, Jogja</span>
                    </div>
                    <span class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">Booth Express</span>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 6. OUTLET DIRECTORY & GALLERY (FR-05, TR-09)                        -->
    <!-- =================================================================== -->
    <section id="outlets" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-12">
            <span class="text-xs font-extrabold text-primary uppercase tracking-wider block">Lokasi Cabang & Delivery</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Temukan Cabang Terdekat
            </h2>
            <p class="text-sm text-slate-500">
                Kunjungi gerai fisik kami untuk suasana santap hangat atau pesan online dengan rute langsung ke dapur cabang terdekat.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($outlets as $outlet): ?>
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <!-- Outlet Real Photo -->
                    <div class="h-48 w-full bg-slate-100 overflow-hidden relative">
                        <img 
                            src="<?= e($outlet['image_url'] ?? '/assets/images/outlet-senopati.webp') ?>" 
                            alt="<?= e($outlet['name']) ?>"
                            loading="lazy"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                        >
                        <span class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-sm text-white text-[11px] font-bold px-2.5 py-1 rounded-lg">
                            <?= e($outlet['city'] ?? 'Indonesia') ?>
                        </span>
                    </div>

                    <div class="p-6 space-y-4 flex-1 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 leading-tight"><?= e($outlet['name']) ?></h3>
                                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed"><?= e($outlet['address']) ?></p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-700 font-semibold bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span><?= icon('clock', 'w-4 h-4 text-slate-400') ?></span>
                                <span>Jam Buka: <?= e($outlet['open_hours'] ?? '08:00 - 22:00 WIB') ?></span>
                            </div>
                        </div>

                        <!-- Action: Switch active outlet in cart -->
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <button 
                                type="button"
                                @click="$store.cart.setActiveOutlet('<?= e($outlet['slug'] ?? 'senopati') ?>'); $store.cart.openDrawer();"
                                class="w-full py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-primary hover:text-white text-slate-800 text-xs font-bold transition flex items-center justify-center gap-1.5"
                            >
                                <?= icon('shopping-bag', 'w-3.5 h-3.5') ?>
                                <span>Pesan di Cabang Ini</span>
                            </button>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <?php if (!empty($outlet['gmaps_link'])): ?>
                                    <a href="<?= e($outlet['gmaps_link']) ?>" target="_blank" class="px-2.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-center transition flex items-center justify-center gap-1">
                                        <?= icon('map-pin', 'w-3 h-3 text-primary') ?>
                                        <span>Maps</span>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($outlet['gofood_url'])): ?>
                                    <a href="<?= e($outlet['gofood_url']) ?>" target="_blank" class="px-2.5 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-center transition">
                                        GoFood
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($outlet['grabfood_url'])): ?>
                                    <a href="<?= e($outlet['grabfood_url']) ?>" target="_blank" class="px-2.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-center transition">
                                        GrabFood
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($outlet['shopeefood_url'])): ?>
                                    <a href="<?= e($outlet['shopeefood_url']) ?>" target="_blank" class="px-2.5 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold text-center transition">
                                        ShopeeFood
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 7. FAQ ACCORDION SECTION (FR-11, TR-10)                             -->
    <!-- =================================================================== -->
    <section id="faq" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-3 mb-10">
            <span class="text-xs font-extrabold text-primary uppercase tracking-wider block">Bantuan & FAQ</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Pertanyaan yang Sering Diajukan
            </h2>
            <p class="text-sm text-slate-500">
                Informasi seputar sistem pemesanan online, voucher diskon, jaminan Halal, dan peluang kemitraan.
            </p>
        </div>

        <div class="space-y-3" x-data="{ activeFaq: 1 }">
            <!-- FAQ 1 -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <button 
                    type="button" 
                    @click="activeFaq = (activeFaq === 1 ? null : 1)"
                    class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between hover:bg-slate-50 transition"
                >
                    <span>Bagaimana cara memesan mandiri tanpa komisi aplikasi?</span>
                    <span :class="activeFaq === 1 ? 'rotate-180' : ''" class="transition-transform duration-200 text-slate-400">
                        <?= icon('chevron-down', 'w-4 h-4') ?>
                    </span>
                </button>
                <div x-show="activeFaq === 1" class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Pilih menu favorit Anda di website, sesuaikan varian dan catatan rasa, lalu buka keranjang. Anda dapat memilih bayar cepat instan via QRIS Dinamis atau konfirmasi pemesanan ke nomor WhatsApp kasir cabang yang dipilih. Bebas mark-up harga dan 100% tanpa biaya layanan pihak ketiga.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <button 
                    type="button" 
                    @click="activeFaq = (activeFaq === 2 ? null : 2)"
                    class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between hover:bg-slate-50 transition"
                >
                    <span>Bagaimana cara menggunakan voucher promo KOPIASIK & KENYANG2?</span>
                    <span :class="activeFaq === 2 ? 'rotate-180' : ''" class="transition-transform duration-200 text-slate-400">
                        <?= icon('chevron-down', 'w-4 h-4') ?>
                    </span>
                </button>
                <div x-show="activeFaq === 2" class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Cukup klik tombol <strong>"Salin & Pakai"</strong> pada kartu promo di halaman beranda atau ketikkan kode voucher di dalam kolom promo Cart Drawer. Kode <strong>KOPIASIK</strong> memberikan potongan 20% khusus menu kopi, sedangkan <strong>KENYANG2</strong> memotong Rp 15.000 dengan minimum transaksi Rp 60.000.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <button 
                    type="button" 
                    @click="activeFaq = (activeFaq === 3 ? null : 3)"
                    class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between hover:bg-slate-50 transition"
                >
                    <span>Apakah seluruh menu Kopi & Rasa Nusantara terverifikasi Halal?</span>
                    <span :class="activeFaq === 3 ? 'rotate-180' : ''" class="transition-transform duration-200 text-slate-400">
                        <?= icon('chevron-down', 'w-4 h-4') ?>
                    </span>
                </button>
                <div x-show="activeFaq === 3" class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Ya, 100% Halal. Kami telah mengantongi Sertifikasi Halal Resmi dari BPJPH dan LPPOM MUI dengan nomor registrasi <strong>ID31110001234560723</strong>. Seluruh rantai pasok bahan baku mulai dari biji kopi, daging ayam, beras, hingga saus rempah dipastikan higienis dan halal.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <button 
                    type="button" 
                    @click="activeFaq = (activeFaq === 4 ? null : 4)"
                    class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between hover:bg-slate-50 transition"
                >
                    <span>Apa saja paket kemitraan franchise dan berapa perkiraan balik modal (BEP)?</span>
                    <span :class="activeFaq === 4 ? 'rotate-180' : ''" class="transition-transform duration-200 text-slate-400">
                        <?= icon('chevron-down', 'w-4 h-4') ?>
                    </span>
                </button>
                <div x-show="activeFaq === 4" class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Kami menyediakan 3 pilihan paket: Booth Express (Rp 35 Juta), Coffee Shop Standard (Rp 65 Juta), dan Flagship Bistro (Rp 120 Juta). Berdasarkan data operasional 3 cabang aktif kami, rata-rata perkiraan balik modal (BEP) berkisar antara 6 hingga 10 bulan dengan margin laba bersih rata-rata 30-35%.
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <button 
                    type="button" 
                    @click="activeFaq = (activeFaq === 5 ? null : 5)"
                    class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between hover:bg-slate-50 transition"
                >
                    <span>Bagaimana alur pelacakan pesanan (Live Order Tracking)?</span>
                    <span :class="activeFaq === 5 ? 'rotate-180' : ''" class="transition-transform duration-200 text-slate-400">
                        <?= icon('chevron-down', 'w-4 h-4') ?>
                    </span>
                </button>
                <div x-show="activeFaq === 5" class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Setelah pembayaran QRIS terkonfirmasi atau pemesanan WhatsApp dikonfirmasi, Anda akan diarahkan ke halaman Live Order Tracker kami. Anda dapat melihat secara real-time status pesanan dari tahap diterima kasir, sedang diracik barista/dapur, hingga siap disajikan atau diantar kurir.
                </div>
            </div>
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
            <?= icon('bell', 'w-4 h-4 text-white') ?>
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
                    <?= icon('bell', 'w-6 h-6 text-primary') ?>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Panggil Pelayan</h3>
                        <p class="text-[11px] text-slate-400">Meja <?= e($activeTable['table_number']) ?> (<?= e($activeTable['area']) ?>)</p>
                    </div>
                </div>
                <button @click="callOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg"><?= icon('x', 'w-5 h-5') ?></button>
            </div>

            <!-- Success State -->
            <div x-show="callSuccess" class="py-8 text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 text-2xl flex items-center justify-center mx-auto">
                    <?= icon('check', 'w-6 h-6') ?>
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
                        <span class="text-cyan-500"><?= icon('coffee', 'w-5 h-5') ?></span>
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
                        <span class="text-blue-500"><?= icon('user', 'w-5 h-5') ?></span>
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
                        <span class="text-amber-500"><?= icon('refresh-cw', 'w-5 h-5') ?></span>
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
                        <span class="text-emerald-500"><?= icon('receipt', 'w-5 h-5') ?></span>
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
