<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Kitchen Display System (KDS) & Bar Display (Professional Edition)
 * High-Contrast Dark Interface for Kitchen Tablets & Wall Monitors.
 * 100% Emoji-Free. Powered by Lucide SVG Icons & Web Audio API Synthesizer.
 */

start_session_safe();
require_auth();

require_once dirname(__DIR__, 3) . '/config/icons.php';

$settings = get_setting();
$pdo = db();

// Fetch active orders (PENDING, CONFIRMED, COOKING, READY)
$ordersQuery = $pdo->query("
    SELECT o.*, t.table_number, t.area 
    FROM orders o 
    LEFT JOIN tables t ON o.table_id = t.id 
    WHERE o.status IN ('PENDING', 'CONFIRMED', 'COOKING', 'READY')
    ORDER BY o.created_at ASC
")->fetchAll();

// Attach items to each order
$orders = [];
$orderIdsList = [];
foreach ($ordersQuery as $ord) {
    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC");
    $stmtItems->execute(['order_id' => $ord['id']]);
    $ord['items'] = $stmtItems->fetchAll();
    $ord['elapsed_seconds'] = time() - strtotime($ord['created_at']);
    $orders[] = $ord;
    $orderIdsList[] = (int)$ord['id'];
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Kitchen Display System (KDS) - <?= e($settings['site_name'] ?? 'FnB Resto') ?></title>
    
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
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col font-sans select-none antialiased" x-data="kdsSystem()">

    <!-- Top Navigation Header -->
    <header class="h-16 bg-slate-900 border-b border-slate-800 px-6 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white shadow-md shadow-primary/20 shrink-0">
                <?= icon('chef-hat', 'w-5 h-5') ?>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white tracking-wide">
                    <?= e($settings['site_name'] ?? 'FnB Resto') ?> — KDS Live Screen
                </h1>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sinkronisasi Realtime Aktif</span>
                    <span>•</span>
                    <span x-text="clockTime" class="tabular-nums font-mono font-bold text-slate-300"></span>
                    <span>•</span>
                    <span class="text-slate-400"><strong class="text-white"><?= count($orders) ?></strong> Antrean</span>
                </div>
            </div>
        </div>

        <!-- Filter Stasiun & Controls -->
        <div class="flex items-center gap-3">
            <div class="bg-slate-950 p-1 rounded-xl border border-slate-800 flex items-center gap-1">
                <button 
                    @click="activeStation = 'ALL'" 
                    :class="activeStation === 'ALL' ? 'bg-primary text-white font-bold' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                >
                    <?= icon('layout-dashboard', 'w-3.5 h-3.5') ?>
                    <span>Semua Stasiun</span>
                </button>
                <button 
                    @click="activeStation = 'KITCHEN'" 
                    :class="activeStation === 'KITCHEN' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                >
                    <?= icon('utensils', 'w-3.5 h-3.5') ?>
                    <span>Dapur (Kitchen)</span>
                </button>
                <button 
                    @click="activeStation = 'BAR'" 
                    :class="activeStation === 'BAR' ? 'bg-cyan-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                >
                    <?= icon('coffee', 'w-3.5 h-3.5') ?>
                    <span>Barista / Bar</span>
                </button>
            </div>

            <!-- Sound Alert Toggle -->
            <button 
                @click="toggleAudio()" 
                :class="audioEnabled ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' : 'bg-slate-800 text-slate-400 border-slate-700'"
                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5"
                title="Aktifkan/Nonaktifkan Notifikasi Suara"
            >
                <span x-show="audioEnabled" class="flex items-center gap-1">
                    <?= icon('bell', 'w-3.5 h-3.5') ?>
                    <span>Suara Aktif</span>
                </span>
                <span x-show="!audioEnabled" x-cloak class="flex items-center gap-1">
                    <?= icon('bell-off', 'w-3.5 h-3.5') ?>
                    <span>Suara Senyap</span>
                </span>
            </button>

            <!-- Manual Refresh Button -->
            <button 
                @click="manualRefresh()" 
                class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition"
                title="Muat Ulang Layar"
            >
                <?= icon('refresh-cw', 'w-4 h-4') ?>
            </button>

            <!-- Exit / Back to Admin -->
            <a 
                href="/admin" 
                class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition"
            >
                Kembali ke CMS
            </a>
        </div>
    </header>

    <!-- Main Tickets Horizontal Scroll Grid -->
    <main class="flex-1 p-6 overflow-x-auto overflow-y-hidden">
        <div class="flex items-start gap-6 h-full min-w-full">

            <?php if (empty($orders)): ?>
                <div class="m-auto text-center py-24">
                    <div class="w-16 h-16 rounded-3xl bg-slate-900 border border-slate-800 flex items-center justify-center text-emerald-500 mx-auto mb-4">
                        <?= icon('check-circle', 'w-8 h-8') ?>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-300">Semua Tiket Masak Selesai</h2>
                    <p class="text-sm text-slate-500 mt-1">Belum ada pesanan aktif yang menunggu di dapur atau bar.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($orders as $order): ?>
                <?php
                $aging = (int)$order['elapsed_seconds'];
                $agingColor = get_ticket_aging_color($aging);
                $cardBorderClass = ($agingColor === 'rose') 
                    ? 'border-rose-500/60 shadow-lg shadow-rose-950/50 ring-1 ring-rose-500/30' 
                    : (($agingColor === 'amber') ? 'border-amber-500/50 shadow-md shadow-amber-950/30' : 'border-slate-800');
                ?>
                <div 
                    x-show="filterOrder(<?= json_encode($order['items']) ?>)" 
                    class="w-80 lg:w-96 bg-slate-900 rounded-3xl border <?= $cardBorderClass ?> flex flex-col shrink-0 max-h-full shadow-xl overflow-hidden transition"
                >
                    <!-- Ticket Header -->
                    <div class="p-4 bg-slate-800/80 border-b border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-white">
                                    <?= !empty($order['table_number']) ? 'MEJA ' . e($order['table_number']) : e($order['order_type']) ?>
                                </span>
                                <?php if (!empty($order['area'])): ?>
                                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">
                                        <?= e($order['area']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs text-slate-400 font-mono block mt-0.5"><?= e($order['order_number']) ?></span>
                        </div>

                        <!-- Aging SLA Timer Badge -->
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black font-mono tabular-nums <?= $agingColor === 'rose' ? 'bg-rose-500 text-white animate-pulse' : ($agingColor === 'amber' ? 'bg-amber-500 text-slate-950' : 'bg-emerald-500 text-slate-950') ?>">
                                <?= icon('timer', 'w-3 h-3') ?>
                                <span><?= format_ticket_aging($aging) ?></span>
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5"><?= e($order['customer_name'] ?? 'Tamu') ?></span>
                        </div>
                    </div>

                    <!-- Order Items List -->
                    <div class="p-4 overflow-y-auto space-y-3 flex-1">
                        <?php foreach ($order['items'] as $item): ?>
                            <?php
                            $isDone = in_array($item['item_status'], ['READY', 'SERVED'], true);
                            $isCooking = $item['item_status'] === 'COOKING';
                            ?>
                            <div 
                                x-show="itemMatchStation('<?= $item['station'] ?>')" 
                                class="p-3.5 rounded-2xl border transition <?= $isDone ? 'bg-emerald-950/20 border-emerald-800/40 opacity-70' : ($isCooking ? 'bg-amber-950/20 border-amber-600/50' : 'bg-slate-950/60 border-slate-800/80') ?>"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-slate-800 text-white font-black text-xs flex items-center justify-center tabular-nums">
                                                <?= $item['quantity'] ?>x
                                            </span>
                                            <span class="text-sm font-bold <?= $isDone ? 'line-through text-slate-400' : 'text-white' ?>">
                                                <?= e($item['menu_name']) ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($item['variants_selected'])): ?>
                                            <div class="mt-1 text-xs text-amber-300 font-medium pl-8 flex items-center gap-1">
                                                <?= icon('tag', 'w-3 h-3') ?>
                                                <span><?= e($item['variants_selected']) ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($item['notes'])): ?>
                                            <div class="mt-1 text-xs text-rose-300 font-semibold italic pl-8 flex items-center gap-1">
                                                <?= icon('alert-triangle', 'w-3 h-3') ?>
                                                <span><?= e($item['notes']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Single Item Bump Button -->
                                    <button 
                                        @click="bumpItem(<?= $item['id'] ?>, '<?= $item['item_status'] ?>')" 
                                        class="shrink-0 px-2.5 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1 <?= $isDone ? 'bg-emerald-600 text-white' : ($isCooking ? 'bg-amber-500 text-slate-950 hover:bg-amber-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700') ?>"
                                    >
                                        <?php if ($isDone): ?>
                                            <?= icon('check', 'w-3.5 h-3.5') ?>
                                            <span>Siap</span>
                                        <?php elseif ($isCooking): ?>
                                            <span>Sedang Masak</span>
                                        <?php else: ?>
                                            <span>Mulai</span>
                                        <?php endif; ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Footer Action (Full Ticket Bump) -->
                    <div class="p-4 bg-slate-950/80 border-t border-slate-800 flex items-center justify-between gap-3">
                        <span class="text-xs text-slate-400">
                            Status: <strong class="text-white"><?= e($order['status']) ?></strong>
                        </span>

                        <button 
                            @click="bumpOrder(<?= $order['id'] ?>)" 
                            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition shadow-md shadow-emerald-500/20 flex items-center gap-1.5"
                        >
                            <?= icon('check', 'w-4 h-4') ?>
                            <span>Selesaikan Tiket</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <!-- KDS Alpine Logic & Polling -->
    <script>
        function kdsSystem() {
            return {
                activeStation: 'ALL',
                audioEnabled: true,
                clockTime: '',
                audioCtx: null,
                knownOrderIds: <?= json_encode($orderIdsList) ?>,

                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // Smart polling every 6 seconds to avoid jarring full page flash
                    setInterval(() => this.checkNewOrders(), 6000);
                },

                updateClock() {
                    const now = new Date();
                    this.clockTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                async checkNewOrders() {
                    try {
                        const res = await fetch('/admin/kds/api/orders');
                        if (!res.ok) return;
                        const data = await res.json();
                        const newIds = data.order_ids || [];

                        // Compare newIds with knownOrderIds
                        const hasDifference = newIds.length !== this.knownOrderIds.length || 
                            !newIds.every(id => this.knownOrderIds.includes(id));

                        if (hasDifference) {
                            if (newIds.length > this.knownOrderIds.length) {
                                this.playChime(440, 880); // Alert chime on new ticket
                            }
                            window.location.reload();
                        }
                    } catch (e) {
                        // ignore network glitches
                    }
                },

                manualRefresh() {
                    window.location.reload();
                },

                toggleAudio() {
                    this.audioEnabled = !this.audioEnabled;
                    if (this.audioEnabled) {
                        this.playChime(660, 880);
                    }
                },

                playChime(freq1 = 523.25, freq2 = 659.25) {
                    if (!this.audioEnabled) return;
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        if (!this.audioCtx) this.audioCtx = new AudioContext();
                        if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

                        const osc1 = this.audioCtx.createOscillator();
                        const osc2 = this.audioCtx.createOscillator();
                        const gain = this.audioCtx.createGain();

                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(freq1, this.audioCtx.currentTime);
                        osc2.type = 'triangle';
                        osc2.frequency.setValueAtTime(freq2, this.audioCtx.currentTime + 0.1);

                        gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.6);

                        osc1.connect(gain);
                        osc2.connect(gain);
                        gain.connect(this.audioCtx.destination);

                        osc1.start();
                        osc2.start(this.audioCtx.currentTime + 0.1);
                        osc1.stop(this.audioCtx.currentTime + 0.3);
                        osc2.stop(this.audioCtx.currentTime + 0.6);
                    } catch (e) {
                        console.error('Audio synthesizer not supported:', e);
                    }
                },

                filterOrder(items) {
                    if (this.activeStation === 'ALL') return true;
                    return items.some(item => item.station === this.activeStation);
                },

                itemMatchStation(station) {
                    if (this.activeStation === 'ALL') return true;
                    return station === this.activeStation;
                },

                async bumpItem(itemId, currentStatus) {
                    let nextStatus = 'COOKING';
                    if (currentStatus === 'COOKING') nextStatus = 'READY';
                    else if (currentStatus === 'READY') nextStatus = 'SERVED';

                    this.playChime(587.33, 880);

                    const form = new FormData();
                    form.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    form.append('item_id', itemId);
                    form.append('status', nextStatus);

                    await fetch('/admin/kds/api/bump', { method: 'POST', body: form });
                    window.location.reload();
                },

                async bumpOrder(orderId) {
                    this.playChime(523.25, 1046.50);

                    const form = new FormData();
                    form.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    form.append('order_id', orderId);
                    form.append('status', 'COMPLETED');

                    await fetch('/admin/orders/status', { method: 'POST', body: form });
                    window.location.reload();
                }
            }
        }
    </script>
</body>
</html>
