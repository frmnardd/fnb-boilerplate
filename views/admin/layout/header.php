<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Admin CMS Header & Sidebar Layout (Professional Edition)
 * No emoji. Lucide SVG icons. Responsive dark sidebar.
 */

start_session_safe();
$user = auth_user();
$settings = get_setting();
$currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);

// Fetch quick stats for sidebar badges
try {
    $soldOutCount = (int)db()->query("SELECT COUNT(*) FROM menus WHERE is_available = 0")->fetchColumn();
    $activeOrdersCount = (int)db()->query("SELECT COUNT(*) FROM orders WHERE status IN ('PENDING', 'CONFIRMED', 'COOKING')")->fetchColumn();
} catch (\Throwable $e) {
    $soldOutCount = 0;
    $activeOrdersCount = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? 'Admin CMS') ?> - <?= e($settings['site_name'] ?? 'FnB Boilerplate') ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= e($settings['favicon_url'] ?? '/assets/images/favicon.svg') ?>" type="image/svg+xml">

    <!-- Fonts: Outfit + Inter for professional look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '<?= e($settings['primary_color'] ?? '#FF6B35') ?>',
                        secondary: '<?= e($settings['secondary_color'] ?? '#1E293B') ?>',
                        dark: '#1C2434',
                        sidebar: '#0F1724',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js (for analytics) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150; }
        .sidebar-link.active { @apply bg-primary/90 text-white shadow-lg; }
        .sidebar-link.inactive { @apply text-slate-400 hover:text-white hover:bg-slate-800/60; }
        .nav-section { @apply px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600 mt-4 mb-1; }
        /* Smooth scrollbar for sidebar */
        aside nav::-webkit-scrollbar { width: 4px; }
        aside nav::-webkit-scrollbar-thumb { background: rgba(100,116,139,.3); border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- ============================================================== -->
        <!-- SIDEBAR                                                         -->
        <!-- ============================================================== -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col justify-between shadow-2xl border-r border-slate-800/60"
        >
            <div class="flex flex-col h-full">
                <!-- Sidebar Brand -->
                <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/50 shrink-0">
                    <a href="/admin" class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white shrink-0 shadow shadow-primary/30">
                            <?= icon('utensils', 'w-4 h-4') ?>
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-extrabold text-white tracking-tight block leading-tight truncate">
                                <?= e($settings['site_name'] ?? 'FnB CMS') ?>
                            </span>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 block">
                                <?= ($user['role'] ?? '') === 'admin' ? 'Super Administrator' : 'Staff Kasir' ?>
                            </span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-500 hover:text-white p-1 shrink-0">
                        <?= icon('x', 'w-5 h-5') ?>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-3 overflow-y-auto space-y-0.5">

                    <div class="nav-section">Menu Utama</div>

                    <!-- Dashboard -->
                    <a href="/admin" class="sidebar-link <?= $currentRoute === '/admin' ? 'active' : 'inactive' ?>">
                        <?= icon('layout-dashboard', 'w-4 h-4 shrink-0') ?>
                        <span>Dashboard</span>
                    </a>

                    <!-- Analytics -->
                    <a href="/admin/analytics" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/analytics') ? 'active' : 'inactive' ?>">
                        <?= icon('bar-chart-2', 'w-4 h-4 shrink-0') ?>
                        <span>Analitik Bisnis</span>
                    </a>

                    <div class="nav-section">Operasional Resto</div>

                    <!-- Pesanan & Kasir POS -->
                    <a href="/admin/orders" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/orders') ? 'active' : 'inactive' ?> justify-between">
                        <div class="flex items-center gap-3">
                            <?= icon('receipt', 'w-4 h-4 shrink-0') ?>
                            <span>Pesanan & Kasir</span>
                        </div>
                        <?php if ($activeOrdersCount > 0): ?>
                            <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full bg-amber-500 text-slate-900 leading-none shrink-0" title="Pesanan Aktif">
                                <?= $activeOrdersCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Layar Dapur (KDS) -->
                    <a href="/admin/kds" target="_blank" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/kds') ? 'active' : 'inactive' ?> justify-between">
                        <div class="flex items-center gap-3">
                            <?= icon('monitor', 'w-4 h-4 shrink-0') ?>
                            <span>Layar Dapur (KDS)</span>
                        </div>
                        <span class="text-[9px] uppercase font-black px-1.5 py-0.5 bg-emerald-500/20 text-emerald-400 rounded leading-none shrink-0">Live</span>
                    </a>

                    <!-- Studio Meja & QR -->
                    <a href="/admin/tables" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/tables') ? 'active' : 'inactive' ?>">
                        <?= icon('table-2', 'w-4 h-4 shrink-0') ?>
                        <span>Studio Meja & QR</span>
                    </a>

                    <!-- Inventori Bahan & HPP -->
                    <a href="/admin/inventory" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/inventory') ? 'active' : 'inactive' ?>">
                        <?= icon('package', 'w-4 h-4 shrink-0') ?>
                        <span>Inventori & HPP</span>
                    </a>

                    <div class="nav-section">Katalog & Outlet</div>

                    <!-- Kelola Menu & Stok -->
                    <a href="/admin/menus" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/menus') ? 'active' : 'inactive' ?> justify-between">
                        <div class="flex items-center gap-3">
                            <?= icon('book-open', 'w-4 h-4 shrink-0') ?>
                            <span>Menu & Toggle Stok</span>
                        </div>
                        <?php if ($soldOutCount > 0): ?>
                            <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full bg-rose-500 text-white leading-none shrink-0" title="Menu Habis">
                                <?= $soldOutCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Kategori -->
                    <a href="/admin/categories" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/categories') ? 'active' : 'inactive' ?>">
                        <?= icon('tag', 'w-4 h-4 shrink-0') ?>
                        <span>Kategori Menu</span>
                    </a>

                    <!-- Cabang Outlet -->
                    <a href="/admin/outlets" class="sidebar-link <?= str_starts_with($currentRoute, '/admin/outlets') ? 'active' : 'inactive' ?>">
                        <?= icon('map-pin', 'w-4 h-4 shrink-0') ?>
                        <span>Cabang Outlet</span>
                    </a>

                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <div class="nav-section">White-Label Brand</div>

                    <!-- Brand Settings -->
                    <a href="/admin/settings" class="sidebar-link <?= $currentRoute === '/admin/settings' ? 'active' : 'inactive' ?>">
                        <?= icon('settings', 'w-4 h-4 shrink-0') ?>
                        <span>Pengaturan Brand</span>
                    </a>
                    <?php endif; ?>

                </nav>

                <!-- Sidebar Footer -->
                <div class="p-3 border-t border-slate-800/60 bg-slate-950/40 shrink-0 space-y-2">
                    <a 
                        href="/" 
                        target="_blank" 
                        class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 text-slate-300 text-xs font-semibold transition"
                    >
                        <?= icon('globe', 'w-3.5 h-3.5') ?>
                        <span>Buka Halaman Publik</span>
                        <?= icon('external-link', 'w-3 h-3 text-slate-500') ?>
                    </a>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center text-xs font-black text-white uppercase shrink-0 border border-slate-700">
                                <?= substr($user['username'] ?? 'U', 0, 2) ?>
                            </div>
                            <div class="text-xs min-w-0">
                                <span class="font-bold text-white block leading-tight truncate"><?= e($user['username'] ?? 'User') ?></span>
                                <span class="text-[10px] text-slate-500 block"><?= e($user['role'] ?? 'staff') ?></span>
                            </div>
                        </div>
                        <a 
                            href="/admin/logout" 
                            class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition shrink-0"
                            title="Keluar"
                        >
                            <?= icon('log-out', 'w-4 h-4') ?>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Backdrop (Mobile) -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        ></div>

        <!-- ============================================================== -->
        <!-- MAIN CONTENT AREA                                              -->
        <!-- ============================================================== -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            
            <!-- Top Navbar -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <!-- Hamburger Toggle -->
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                        aria-label="Toggle Sidebar"
                    >
                        <?= icon('menu', 'w-5 h-5') ?>
                    </button>

                    <!-- Page Title & Subtitle -->
                    <div>
                        <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-tight">
                            <?= e($title ?? 'Dashboard') ?>
                        </h1>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">
                            <?= e($subtitle ?? 'Kelola operasional dan identitas brand bisnis FnB Anda.') ?>
                        </p>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                        Sistem Aktif
                    </span>
                    <a 
                        href="/admin/logout" 
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition flex items-center gap-1.5"
                    >
                        <?= icon('log-out', 'w-3.5 h-3.5') ?>
                        <span class="hidden sm:inline">Keluar</span>
                    </a>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50/50">
                <div class="max-w-screen-2xl mx-auto space-y-5">

                    <!-- Flash Messages -->
                    <?php if (has_flash('success')): ?>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm">
                            <?= icon('check-circle', 'w-5 h-5 text-emerald-600 shrink-0') ?>
                            <span><?= e(get_flash('success')) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash('error')): ?>
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm">
                            <?= icon('alert-triangle', 'w-5 h-5 text-rose-500 shrink-0') ?>
                            <span><?= e(get_flash('error')) ?></span>
                        </div>
                    <?php endif; ?>

