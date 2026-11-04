<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Header Layout
 * Dynamic Theme Engine (CSS Variables), Pixel Scripts & Responsive Navigation.
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$settings = get_setting();
$primaryColor = $settings['primary_color'] ?? '#FF6B35';
$secondaryColor = $settings['secondary_color'] ?? '#1E293B';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($settings['site_name'] ?? 'FnB Boilerplate') ?> - <?= e($settings['tagline'] ?? '') ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= e($settings['favicon_url'] ?? '/assets/images/favicon.svg') ?>" type="image/svg+xml">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN with dynamic theme config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: 'var(--color-primary)',
                        secondary: 'var(--color-secondary)',
                    }
                }
            }
        }
    </script>

    <!-- Dynamic Theme Engine (CSS Variables) -->
    <style>
        :root {
            --color-primary: <?= e($primaryColor) ?>;
            --color-secondary: <?= e($secondaryColor) ?>;
        }
        [x-cloak] { display: none !important; }
    </style>

    <!-- Alpine.js Cart & App Logic -->
    <script src="/assets/js/cart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Meta Pixel Code (Conditional) -->
    <?php if (!empty($settings['meta_pixel_id'])): ?>
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= e($settings['meta_pixel_id']) ?>');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?= e($settings['meta_pixel_id']) ?>&ev=PageView&noscript=1"
    /></noscript>
    <?php endif; ?>

    <!-- TikTok Pixel Code (Conditional) -->
    <?php if (!empty($settings['tiktok_pixel_id'])): ?>
    <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject=t;var tt=w[t]=w[t]||[];tt.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],tt.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<tt.methods.length;i++)tt.setAndDefer(tt,tt.methods[i]);tt.instance=function(t){for(var e=tt._i[t]||[],n=0;n<tt.methods.length;n++)tt.setAndDefer(e,tt.methods[n]);return e},tt.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";tt._i=tt._i||{},tt._i[e]=[],tt._i[e]._u=i,s=d.createElement("script");s.type="text/javascript",s.async=!0,s.src=i+"?sdkid="+e+"&lib="+t;var c=d.getElementsByTagName("script")[0];c.parentNode.insertBefore(s,c)};
            ttq.load('<?= e($settings['tiktok_pixel_id']) ?>');
            ttq.page();
        }(window, document, 'ttq');
    </script>
    <?php endif; ?>

    <!-- Google Analytics (Conditional) -->
    <?php if (!empty($settings['google_analytics_id'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($settings['google_analytics_id']) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e($settings['google_analytics_id']) ?>');
    </script>
    <?php endif; ?>
</head>
<body class="bg-amber-50/20 text-slate-800 antialiased font-sans selection:bg-primary selection:text-white" x-data="{ mobileMenu: false }">

    <!-- 1. Top Announcement Bar -->
    <?php if (!empty($settings['is_announcement_active']) && !empty($settings['announcement_text'])): ?>
    <aside class="bg-primary text-white text-xs md:text-sm font-semibold py-2 px-4 shadow-sm relative z-40 transition" aria-label="Pengumuman Penting">
        <div class="max-w-7xl mx-auto flex items-center justify-center text-center gap-2">
            <?= icon('bell', 'w-4 h-4 text-white shrink-0') ?>
            <span><?= e($settings['announcement_text']) ?></span>
        </div>
    </aside>
    <?php endif; ?>

    <!-- 2. Sticky Navbar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-100 sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo & Title -->
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white shadow-md shadow-primary/20 group-hover:scale-105 transition-transform">
                        <?= icon('utensils', 'w-6 h-6 text-white') ?>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-primary transition-colors block leading-tight">
                            <?= e($settings['site_name'] ?? 'Kopi & Rasa') ?>
                        </span>
                        <span class="text-[11px] font-medium text-slate-500 tracking-wider uppercase block">
                            Warisan Rasa Otentik
                        </span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="#home" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-primary hover:bg-slate-50 transition">
                        Beranda
                    </a>
                    <a href="#promos" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-primary hover:bg-slate-50 transition">
                        Promo Spesial
                    </a>
                    <a href="#food-menu" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-primary hover:bg-slate-50 transition">
                        Katalog Menu
                    </a>
                    <a href="#franchise" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-primary hover:bg-slate-50 transition">
                        Kemitraan (ROI)
                    </a>
                    <a href="#outlets" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-primary hover:bg-slate-50 transition">
                        Cabang Kami
                    </a>
                </nav>

                <!-- Action Button & WhatsApp Cart Trigger -->
                <div class="flex items-center gap-3">
                    <!-- Cart Navbar Button -->
                    <button 
                        @click="$store.cart.toggleDrawer()"
                        class="relative inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-primary text-white text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95"
                        aria-label="Keranjang Belanja"
                    >
                        <?= icon('shopping-cart', 'w-5 h-5') ?>
                        <span class="hidden sm:inline">Keranjang</span>
                        <span 
                            x-show="$store.cart.getTotalCount() > 0" 
                            x-text="$store.cart.getTotalCount()" 
                            class="bg-primary text-white text-xs font-black px-2 py-0.5 rounded-full border-2 border-white shadow-sm"
                        ></span>
                    </button>

                    <!-- Mobile Hamburger Button -->
                    <button 
                        @click="mobileMenu = !mobileMenu" 
                        class="md:hidden p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:text-primary hover:bg-slate-200 transition"
                        aria-label="Toggle Mobile Menu"
                    >
                        <span x-show="!mobileMenu"><?= icon('menu', 'w-6 h-6') ?></span>
                        <span x-show="mobileMenu" x-cloak><?= icon('x', 'w-6 h-6') ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div 
            x-show="mobileMenu" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.away="mobileMenu = false"
            class="md:hidden border-t border-slate-100 bg-white/95 px-4 pt-3 pb-6 space-y-2 shadow-xl"
        >
            <a @click="mobileMenu = false" href="#home" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-base font-semibold text-slate-800 hover:bg-primary/10 hover:text-primary transition">
                <?= icon('layout-dashboard', 'w-4 h-4 text-slate-500') ?>
                <span>Beranda</span>
            </a>
            <a @click="mobileMenu = false" href="#promos" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-base font-semibold text-slate-800 hover:bg-primary/10 hover:text-primary transition">
                <?= icon('tag', 'w-4 h-4 text-slate-500') ?>
                <span>Promo Spesial</span>
            </a>
            <a @click="mobileMenu = false" href="#food-menu" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-base font-semibold text-slate-800 hover:bg-primary/10 hover:text-primary transition">
                <?= icon('book-open', 'w-4 h-4 text-slate-500') ?>
                <span>Katalog Menu</span>
            </a>
            <a @click="mobileMenu = false" href="#franchise" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-base font-semibold text-slate-800 hover:bg-primary/10 hover:text-primary transition">
                <?= icon('trending-up', 'w-4 h-4 text-slate-500') ?>
                <span>Kemitraan & ROI</span>
            </a>
            <a @click="mobileMenu = false" href="#outlets" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-base font-semibold text-slate-800 hover:bg-primary/10 hover:text-primary transition">
                <?= icon('map-pin', 'w-4 h-4 text-slate-500') ?>
                <span>Lokasi Cabang</span>
            </a>
            <div class="pt-2">
                <a href="<?= format_whatsapp_url($settings['whatsapp_order_number'] ?? '', 'Halo, saya ingin bertanya tentang menu') ?>" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-sm shadow hover:bg-emerald-700 transition">
                    <?= icon('whatsapp', 'w-4 h-4') ?>
                    <span>Hubungi WhatsApp CS</span>
                </a>
            </div>
        </div>
    </header>
