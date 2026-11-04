<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Admin & Cashier Authentication View
 * Professional Edition: 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

require_once dirname(__DIR__, 2) . '/config/icons.php';

$settings = get_setting();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - <?= e($settings['site_name'] ?? 'FnB Boilerplate') ?></title>
    <link rel="shortcut icon" href="<?= e($settings['favicon_url'] ?? '/assets/images/favicon.svg') ?>" type="image/svg+xml">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 antialiased font-sans flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md space-y-6">
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-14 h-14 rounded-3xl bg-primary text-white items-center justify-center shadow-xl shadow-primary/25">
                <?= icon('utensils', 'w-7 h-7') ?>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                <?= e($settings['site_name'] ?? 'Kopi & Rasa Nusantara') ?>
            </h1>
            <p class="text-xs text-slate-500 font-medium">Portal Masuk Administrator & Kasir Outlet</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xl space-y-6">
            
            <!-- Flash Message Alerts -->
            <?php if (has_flash('error')): ?>
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                    <?= icon('alert-triangle', 'w-4 h-4 text-rose-600 shrink-0') ?>
                    <span><?= e(get_flash('error')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (has_flash('success')): ?>
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                    <?= icon('check-circle', 'w-4 h-4 text-emerald-600 shrink-0') ?>
                    <span><?= e(get_flash('success')) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="/admin/login" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-bold text-slate-700">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <?= icon('user', 'w-4 h-4') ?>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            autofocus
                            placeholder="admin atau kasir"
                            class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                        >
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <?= icon('shield-check', 'w-4 h-4') ?>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="••••••••"
                            class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition font-medium"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 rounded-2xl bg-primary hover:bg-primary/95 text-white font-extrabold text-sm shadow-lg shadow-primary/25 transition active:scale-98 flex items-center justify-center gap-2 mt-2"
                >
                    <span>Masuk ke Dashboard</span>
                    <?= icon('arrow-right', 'w-4 h-4') ?>
                </button>
            </form>

            <!-- Demo Pitching Credentials Helper Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1.5">
                <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                    <?= icon('info', 'w-3.5 h-3.5 text-primary') ?>
                    <span>Akun Demo Siap Pakai:</span>
                </span>
                <div class="flex items-center justify-between text-[11px] pt-1">
                    <span>Admin: <strong class="font-mono text-slate-900">admin</strong> / <strong class="font-mono text-slate-900">admin123</strong></span>
                    <span class="text-slate-500 font-semibold">(Akses Penuh)</span>
                </div>
                <div class="flex items-center justify-between text-[11px]">
                    <span>Kasir: <strong class="font-mono text-slate-900">kasir</strong> / <strong class="font-mono text-slate-900">kasir123</strong></span>
                    <span class="text-slate-500 font-semibold">(Operasional Menu & Stok)</span>
                </div>
            </div>
        </div>

        <!-- Back to Website -->
        <div class="text-center">
            <a href="/" class="text-xs font-semibold text-slate-500 hover:text-primary transition inline-flex items-center gap-1.5">
                <?= icon('arrow-left', 'w-3.5 h-3.5') ?>
                <span>Kembali ke Halaman Utama</span>
            </a>
        </div>
    </div>

</body>
</html>
