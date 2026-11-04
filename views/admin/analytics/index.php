<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Business Analytics & Financial Insights View
 * Powered by Chart.js & Lucide SVG Icons. 100% Emoji-Free.
 */

$title = 'Analitik Bisnis & Keuangan';
$subtitle = 'Laporan komprehensif omzet penjualan, menu terlaris, jam sibuk operasional, dan metode pembayaran.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();

// 1. Overall Order Summary KPIs
try {
    $kpi = $pdo->query("
        SELECT 
            COALESCE(SUM(grand_total), 0) as total_revenue,
            COUNT(*) as total_orders,
            COALESCE(AVG(grand_total), 0) as aov,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(service_charge), 0) as total_service
        FROM orders 
        WHERE payment_status = 'PAID'
    ")->fetch() ?: [];

    $totalRevenue = (int)($kpi['total_revenue'] ?? 0);
    $totalOrders = (int)($kpi['total_orders'] ?? 0);
    $avgOrderValue = (int)($kpi['aov'] ?? 0);
    $totalTax = (int)($kpi['total_tax'] ?? 0);
    $totalService = (int)($kpi['total_service'] ?? 0);
} catch (\Throwable $e) {
    $totalRevenue = 0;
    $totalOrders = 0;
    $avgOrderValue = 0;
    $totalTax = 0;
    $totalService = 0;
}

// 2. Best Sellers (Top 5 Menu Items)
try {
    $topMenus = $pdo->query("
        SELECT 
            oi.menu_name,
            SUM(oi.quantity) as total_qty,
            SUM(oi.line_total) as total_sales
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.payment_status = 'PAID'
        GROUP BY oi.menu_name
        ORDER BY total_qty DESC
        LIMIT 5
    ")->fetchAll();
} catch (\Throwable $e) {
    $topMenus = [];
}

// 3. Payment Method Distribution
try {
    $paymentStats = $pdo->query("
        SELECT 
            COALESCE(payment_method, 'CASH') as method,
            COUNT(*) as count,
            SUM(grand_total) as amount
        FROM orders
        WHERE payment_status = 'PAID'
        GROUP BY payment_method
    ")->fetchAll();
} catch (\Throwable $e) {
    $paymentStats = [];
}

// 4. Peak Hours Heatmap Data (00 - 23)
try {
    $driver = Database::getInstance()->getActiveDriver();
    $hourExpr = ($driver === 'sqlite') ? "strftime('%H', created_at)" : "HOUR(created_at)";
    $hourlyStats = $pdo->query("
        SELECT 
            {$hourExpr} as hour,
            COUNT(*) as count
        FROM orders
        GROUP BY {$hourExpr}
        ORDER BY hour ASC
    ")->fetchAll();
} catch (\Throwable $e) {
    $hourlyStats = [];
}

// 5. 30-Day Revenue Trend Data for Chart
$daysMap = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $daysMap[$d] = 0;
}

try {
    $minDate30 = date('Y-m-d 00:00:00', strtotime('-30 days'));
    $trendStmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as order_date,
            SUM(grand_total) as daily_revenue
        FROM orders
        WHERE payment_status = 'PAID'
          AND created_at >= :min_date
        GROUP BY DATE(created_at)
        ORDER BY order_date ASC
    ");
    $trendStmt->execute(['min_date' => $minDate30]);
    $trendRows = $trendStmt->fetchAll();

    foreach ($trendRows as $row) {
        if (isset($daysMap[$row['order_date']])) {
            $daysMap[$row['order_date']] = (int)$row['daily_revenue'];
        }
    }
} catch (\Throwable $e) {
    // fallback
}

$chartLabels = array_map(fn($d) => date('d M', strtotime($d)), array_keys($daysMap));
$chartValues = array_values($daysMap);

// Payment chart data prep
$payLabels = array_column($paymentStats, 'method');
$payCounts = array_map('intval', array_column($paymentStats, 'count'));

// Hourly chart data prep (00 to 23)
$hoursFull = [];
for ($h = 0; $h < 24; $h++) {
    $hh = sprintf('%02d', $h);
    $hoursFull[$hh] = 0;
}
foreach ($hourlyStats as $hs) {
    $hourStr = (string)$hs['hour'];
    if (isset($hoursFull[$hourStr])) {
        $hoursFull[$hourStr] = (int)$hs['count'];
    }
}
$hourlyLabels = array_map(fn($h) => $h . ':00', array_keys($hoursFull));
$hourlyValues = array_values($hoursFull);
?>

<div class="space-y-6">

    <!-- 1. KPI Financial Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Omzet -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Omzet Bersih</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= format_rupiah($totalRevenue) ?></span>
                <span class="text-xs font-semibold text-emerald-600 mt-1 flex items-center gap-1">
                    <?= icon('shield-check', 'w-3.5 h-3.5') ?>
                    <span>Transaksi Terverifikasi</span>
                </span>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <?= icon('dollar-sign', 'w-7 h-7') ?>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Transaksi</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= $totalOrders ?> Nota</span>
                <span class="text-xs font-semibold text-slate-500 mt-1 flex items-center gap-1">
                    <?= icon('check-circle', 'w-3.5 h-3.5 text-blue-500') ?>
                    <span>Pesanan Sukses Selesai</span>
                </span>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <?= icon('receipt', 'w-7 h-7') ?>
            </div>
        </div>

        <!-- Rata-rata Nilai Transaksi (AOV) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Rata-rata Nota (AOV)</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= format_rupiah($avgOrderValue) ?></span>
                <span class="text-xs font-semibold text-slate-500 mt-1 block">
                    Per struk transaksi
                </span>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <?= icon('trending-up', 'w-7 h-7') ?>
            </div>
        </div>

        <!-- Pajak PB1 & Service -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">PB1 Resto & Service</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 block"><?= format_rupiah($totalTax + $totalService) ?></span>
                <span class="text-xs font-semibold text-slate-500 mt-1 block">
                    PB1: <?= format_rupiah($totalTax) ?>
                </span>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <?= icon('pie-chart', 'w-7 h-7') ?>
            </div>
        </div>
    </div>

    <!-- 2. Main 30-Day Revenue Trend Chart -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <?= icon('bar-chart-2', 'w-5 h-5 text-primary') ?>
                    <span>Tren Omzet Penjualan (30 Hari Terakhir)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Dinamika pendapatan harian bisnis FnB Anda.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl">
                Periode 30 Hari Terakhir
            </span>
        </div>
        <div class="relative h-80 w-full">
            <canvas id="analyticsRevenueChart"></canvas>
        </div>
    </div>

    <!-- 3. Dual Charts: Payment Methods & Hourly Traffic -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart: Metode Pembayaran -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2 mb-1">
                    <?= icon('pie-chart', 'w-5 h-5 text-primary') ?>
                    <span>Distribusi Metode Pembayaran</span>
                </h2>
                <p class="text-xs text-slate-500 mb-4">Preferensi pembayaran pelanggan (Cash, QRIS, Transfer).</p>
            </div>
            <div class="relative h-64 w-full flex items-center justify-center">
                <?php if (!empty($payLabels)): ?>
                    <canvas id="analyticsPaymentChart"></canvas>
                <?php else: ?>
                    <p class="text-xs text-slate-400">Belum ada data pembayaran terverifikasi.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chart: Jam Sibuk Operasional (Peak Hours) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2 mb-1">
                    <?= icon('clock', 'w-5 h-5 text-primary') ?>
                    <span>Analisis Jam Sibuk Pemesanan (00:00 - 23:00)</span>
                </h2>
                <p class="text-xs text-slate-500 mb-4">Distribusi jam masuknya order untuk optimasi jadwal kerja staf.</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="analyticsHourlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. Top 5 Best Seller Items Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <?= icon('star', 'w-5 h-5 text-amber-500') ?>
                    <span>5 Menu Terlaris (Top Best Sellers)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Peringkat menu dengan volume penjualan dan omzet tertinggi.</p>
            </div>
            <a href="/admin/menus" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                <span>Kelola Menu</span>
                <?= icon('arrow-right', 'w-3.5 h-3.5') ?>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6 text-center w-16">Peringkat</th>
                        <th class="py-3.5 px-6">Nama Menu</th>
                        <th class="py-3.5 px-6 text-center">Porsi Terjual</th>
                        <th class="py-3.5 px-6 text-right">Total Penjualan</th>
                        <th class="py-3.5 px-6 text-center">Kontribusi Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($topMenus)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-400">Belum ada transaksi menu tercatat.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $rank = 1;
                        foreach ($topMenus as $menu): 
                            $share = $totalRevenue > 0 ? round(((int)$menu['total_sales'] / $totalRevenue) * 100, 1) : 0;
                        ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 text-center">
                                    <span class="w-7 h-7 rounded-full inline-flex items-center justify-center text-xs font-black <?= $rank === 1 ? 'bg-amber-100 text-amber-800' : ($rank === 2 ? 'bg-slate-200 text-slate-800' : ($rank === 3 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600')) ?>">
                                        <?= $rank ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    <?= e($menu['menu_name']) ?>
                                </td>
                                <td class="py-4 px-6 text-center font-extrabold text-slate-900">
                                    <?= number_format((int)$menu['total_qty'], 0, ',', '.') ?> porsi
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900">
                                    <?= format_rupiah($menu['total_sales']) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-20 bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div class="bg-primary h-full rounded-full" style="width: <?= min(100, $share) ?>%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-600"><?= $share ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Render Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Revenue 30-Day Trend
    const revCtx = document.getElementById('analyticsRevenueChart')?.getContext('2d');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Omzet Harian (Rp)',
                    data: <?= json_encode($chartValues) ?>,
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#FF6B35'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Omzet: Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#64748B' }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            font: { family: 'Inter', size: 10 },
                            color: '#64748B',
                            callback: function(val) {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Payment Distribution Doughnut Chart
    const payCtx = document.getElementById('analyticsPaymentChart')?.getContext('2d');
    if (payCtx) {
        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($payLabels) ?>,
                datasets: [{
                    data: <?= json_encode($payCounts) ?>,
                    backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 11 }, padding: 16 }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // 3. Hourly Order Traffic Bar Chart
    const hourCtx = document.getElementById('analyticsHourlyChart')?.getContext('2d');
    if (hourCtx) {
        new Chart(hourCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($hourlyLabels) ?>,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: <?= json_encode($hourlyValues) ?>,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 9 }, color: '#64748B' }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#64748B', stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
