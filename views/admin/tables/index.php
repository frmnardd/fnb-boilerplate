<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Table Management & QR Studio
 */

$title = 'Studio Meja & QR Code';
$subtitle = 'Kelola denah meja, nomor meja, status okupansi, dan cetak QR Code meja siap pakai.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();

// Fetch tables
$areaFilter = trim($_GET['area'] ?? '');
if (!empty($areaFilter)) {
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE area = :area ORDER BY table_number ASC");
    $stmt->execute(['area' => $areaFilter]);
    $tables = $stmt->fetchAll();
} else {
    $tables = $pdo->query("SELECT * FROM tables ORDER BY area ASC, table_number ASC")->fetchAll();
}

// Fetch distinct areas for filter
$areas = $pdo->query("SELECT DISTINCT area FROM tables ORDER BY area ASC")->fetchAll(PDO::FETCH_COLUMN);

// Table stats
$totalTables = count($tables);
$availableCount = 0;
$occupiedCount = 0;
$cleaningCount = 0;
foreach ($tables as $t) {
    if ($t['status'] === 'AVAILABLE') $availableCount++;
    elseif ($t['status'] === 'OCCUPIED') $occupiedCount++;
    elseif ($t['status'] === 'NEEDS_CLEANING') $cleaningCount++;
}
?>

<div class="space-y-6" x-data="{
    tableModalOpen: false,
    qrModalOpen: false,
    editMode: false,
    form: { id: 0, table_number: '', area: 'Indoor', pin: '1234' },
    activeQr: { number: '', area: '', slug: '', url: '' },
    openCreate() {
        this.editMode = false;
        this.form = { id: 0, table_number: '', area: 'Indoor', pin: '1234' };
        this.tableModalOpen = true;
    },
    openEdit(table) {
        this.editMode = true;
        this.form = { id: table.id, table_number: table.table_number, area: table.area, pin: table.pin || '1234' };
        this.tableModalOpen = true;
    },
    openQr(table) {
        this.activeQr = {
            number: table.table_number,
            area: table.area,
            slug: table.qr_slug,
            url: window.location.origin + '/t/' + table.qr_slug
        };
        this.qrModalOpen = true;
    }
}">

    <!-- Top Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Meja</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block"><?= $totalTables ?> Meja</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tersedia</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block"><?= $availableCount ?> Meja</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Terisi</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block"><?= $occupiedCount ?> Meja</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Perlu Dibersihkan</span>
            <span class="text-2xl font-black text-rose-600 mt-1 block"><?= $cleaningCount ?> Meja</span>
        </div>
    </div>

    <!-- Header Actions & Filters -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Area Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
            <a 
                href="/admin/tables" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= empty($areaFilter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
            >
                Semua Area (<?= $totalTables ?>)
            </a>
            <?php foreach ($areas as $area): ?>
                <a 
                    href="/admin/tables?area=<?= urlencode($area) ?>" 
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?= $areaFilter === $area ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
                >
                    <?= e($area) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Add Button -->
        <button 
            @click="openCreate()" 
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-95 transition flex items-center justify-center gap-2"
        >
            <span>➕</span>
            <span>Tambah Meja Baru</span>
        </button>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($tables as $table): ?>
            <?php
            $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $statusLabel = 'Tersedia';
            if ($table['status'] === 'OCCUPIED') {
                $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                $statusLabel = 'Terisi Pelanggan';
            } elseif ($table['status'] === 'NEEDS_CLEANING') {
                $statusClass = 'bg-rose-50 text-rose-700 border-rose-200';
                $statusLabel = 'Perlu Bersih';
            }
            ?>
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 block"><?= e($table['area']) ?></span>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Meja <?= e($table['table_number']) ?></h3>
                        </div>
                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full border <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>

                    <div class="mt-4 p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">QR Slug:</span>
                        <code class="font-mono font-bold text-slate-700">/t/<?= e($table['qr_slug']) ?></code>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-2">
                    <!-- Status quick toggle form -->
                    <form action="/admin/tables/status" method="POST" class="flex-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $table['id'] ?>">
                        <select 
                            name="status" 
                            onchange="this.form.submit()" 
                            class="w-full text-xs font-semibold rounded-xl border-slate-200 py-1.5 px-2 bg-slate-50 focus:bg-white"
                        >
                            <option value="AVAILABLE" <?= $table['status'] === 'AVAILABLE' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="OCCUPIED" <?= $table['status'] === 'OCCUPIED' ? 'selected' : '' ?>>Terisi</option>
                            <option value="NEEDS_CLEANING" <?= $table['status'] === 'NEEDS_CLEANING' ? 'selected' : '' ?>>Perlu Dibersihkan</option>
                        </select>
                    </form>

                    <!-- QR Button -->
                    <button 
                        @click='openQr(<?= json_encode($table) ?>)' 
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                        title="Cetak Kartu QR"
                    >
                        📲
                    </button>

                    <!-- Edit Button -->
                    <button 
                        @click='openEdit(<?= json_encode($table) ?>)' 
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                        title="Ubah Data"
                    >
                        ✏️
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Create/Edit Modal -->
    <div 
        x-show="tableModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="tableModalOpen = false" 
            class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Ubah Data Meja' : 'Tambah Meja Baru'"></h3>
                <button @click="tableModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl">✕</button>
            </div>

            <form action="/admin/tables/save" method="POST" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="id" :value="form.id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Meja</label>
                    <input 
                        type="text" 
                        name="table_number" 
                        x-model="form.table_number" 
                        placeholder="Contoh: 06, VIP-1, T-12" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Area Meja</label>
                    <input 
                        type="text" 
                        name="area" 
                        x-model="form.area" 
                        placeholder="Contoh: Indoor, Outdoor, VIP Lantai 2" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">PIN Meja (Opsional / Anti-Prank)</label>
                    <input 
                        type="text" 
                        name="pin" 
                        x-model="form.pin" 
                        placeholder="1234" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="tableModalOpen = false" 
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-md shadow-primary/20 hover:opacity-95 transition"
                    >
                        Simpan Meja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Studio Modal (Tent Card Ready) -->
    <div 
        x-show="qrModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="qrModalOpen = false" 
            class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-slate-100 text-center"
        >
            <div id="printableQrTentCard" class="p-6 rounded-2xl border-2 border-slate-900 bg-white shadow-inner">
                <span class="text-xs font-black uppercase tracking-widest text-primary block">
                    <?= e($settings['site_name'] ?? 'Kopi & Rasa Nusantara') ?>
                </span>
                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block mt-0.5" x-text="activeQr.area"></span>
                
                <div class="my-3 py-1">
                    <span class="text-xs font-bold text-slate-500 block">NOMOR MEJA</span>
                    <h2 class="text-4xl font-black text-slate-900" x-text="activeQr.number"></h2>
                </div>

                <!-- Live Generated QR Image -->
                <div class="p-3 bg-white border border-slate-200 rounded-xl inline-block shadow-sm">
                    <img 
                        :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(activeQr.url)" 
                        alt="QR Code Meja" 
                        class="w-40 h-40 object-contain mx-auto"
                    >
                </div>

                <div class="mt-3">
                    <span class="text-xs font-extrabold text-slate-800 block">Scan QR untuk Pesan & Bayar</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Tanpa unduh aplikasi • Langsung dari HP</span>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-center gap-3">
                <button 
                    @click="qrModalOpen = false" 
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                >
                    Tutup
                </button>
                <button 
                    @click="window.print()" 
                    class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow hover:bg-slate-800 transition flex items-center gap-2"
                >
                    <span>🖨️</span>
                    <span>Cetak Tent Card</span>
                </button>
            </div>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
