<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - TailAdmin Inspired CMS Header & Sidebar Layout
 */

start_session_safe();
$user = auth_user();
$settings = get_setting();
$currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);

// Fetch quick stats for sidebar badges
try {
    $soldOutCount = (int)db()->query("SELECT COUNT(*) FROM menus WHERE is_available = 0")->fetchColumn();
} catch (\Throwable $e) {
    $soldOutCount = 0;
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN with dynamic theme config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Rubik', 'sans-serif'],
                    },
                    colors: {
                        primary: '<?= e($settings['primary_color'] ?? '#FF6B35') ?>',
                        secondary: '<?= e($settings['secondary_color'] ?? '#1E293B') ?>',
                        dark: '#1C2434',
                        sidebar: '#1C2434',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- ============================================================== -->
        <!-- SIDEBAR (TailAdmin Dark Theme Aesthetic)                       -->
        <!-- ============================================================== -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col justify-between shadow-2xl border-r border-slate-800"
        >
            <div>
                <!-- Sidebar Brand Header -->
                <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
                    <a href="/admin" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white text-xl font-bold shadow-md shadow-primary/20">
                            ☕
                        </div>
                        <div>
                            <span class="text-base font-extrabold text-white tracking-tight block leading-tight">
                                <?= e($settings['site_name'] ?? 'FnB CMS') ?>
                            </span>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mt-0.5">
                                <?= ($user['role'] ?? '') === 'admin' ? 'Super Administrator' : 'Staff Kasir' ?>
                            </span>
                        </div>
                    </a>

                    <!-- Close Button for Mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-160px)]">
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        Menu Utama
                    </div>

                    <!-- Dashboard -->
                    <a 
                        href="/admin" 
                        class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition <?= $currentRoute === '/admin' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>"
                    >
                        <span class="text-lg">📊</span>
                        <span>Dashboard</span>
                    </a>

                    <!-- Kelola Menu & Stok -->
                    <a 
                        href="/admin/menus" 
                        class="flex items-center justify-between px-3.5 py-3 rounded-xl text-sm font-semibold transition <?= str_starts_with($currentRoute, '/admin/menus') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-lg">🍲</span>
                            <span>Menu & Toggle Stok</span>
                        </div>
                        <?php if ($soldOutCount > 0): ?>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-500 text-white shadow-sm" title="Menu Habis">
                                <?= $soldOutCount ?> Habis
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Kategori -->
                    <a 
                        href="/admin/categories" 
                        class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition <?= str_starts_with($currentRoute, '/admin/categories') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>"
                    >
                        <span class="text-lg">📁</span>
                        <span>Kategori Menu</span>
                    </a>

                    <!-- Cabang Outlet -->
                    <a 
                        href="/admin/outlets" 
                        class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition <?= str_starts_with($currentRoute, '/admin/outlets') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>"
                    >
                        <span class="text-lg">📍</span>
                        <span>Cabang Outlet</span>
                    </a>

                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        White-Label Brand
                    </div>

                    <!-- White-Label Brand Settings -->
                    <a 
                        href="/admin/settings" 
                        class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition <?= $currentRoute === '/admin/settings' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>"
                    >
                        <span class="text-lg">⚙️</span>
                        <span>Pengaturan Brand</span>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Sidebar Footer (Quick Links & User Session) -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 space-y-3">
                <a 
                    href="/" 
                    target="_blank" 
                    class="flex items-center justify-center gap-2 w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition"
                >
                    <span>🌐 Buka Halaman Publik</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-xs font-bold text-white uppercase">
                            <?= substr($user['username'] ?? 'U', 0, 2) ?>
                        </div>
                        <div class="text-xs">
                            <span class="font-bold text-white block leading-tight"><?= e($user['username'] ?? 'User') ?></span>
                            <span class="text-[10px] text-slate-400 block"><?= e($user['role'] ?? 'staff') ?></span>
                        </div>
                    </div>
                    <a 
                        href="/admin/logout" 
                        class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition"
                        title="Keluar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Backdrop for mobile drawer -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
        ></div>

        <!-- ============================================================== -->
        <!-- MAIN CONTENT AREA                                              -->
        <!-- ============================================================== -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Navbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <!-- Hamburger Toggle Button -->
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                        aria-label="Toggle Sidebar"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Breadcrumbs & Title -->
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                            <?= e($title ?? 'Dashboard') ?>
                        </h1>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">
                            <?= e($subtitle ?? 'Kelola operasional dan identitas brand bisnis FnB Anda.') ?>
                        </p>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        ● Sistem Aktif
                    </span>

                    <a 
                        href="/admin/logout" 
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                    >
                        Keluar
                    </a>
                </div>
            </header>

            <!-- Main Page Content Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8 bg-slate-50/50">
                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Flash Message Alerts -->
                    <?php if (has_flash('success')): ?>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg">✅</span>
                                <span><?= e(get_flash('success')) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash('error')): ?>
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg">⚠️</span>
                                <span><?= e(get_flash('error')) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
