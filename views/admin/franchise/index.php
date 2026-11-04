<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Franchise Acquisition CRM Leads (PRD v2.0 - FR-10)
 * View, filter, and export prospective franchise partner leads.
 * 100% Emoji-Free. Powered by Lucide SVG Icons.
 */

$title = 'Prospek Kemitraan (Franchise CRM)';
$subtitle = 'Manajemen calon mitra waralaba yang mengunduh proposal & simulasi ROI dari website.';
require dirname(__DIR__) . '/layout/header.php';

$pdo = db();
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM franchise_leads WHERE 1=1";
$params = [];
if ($statusFilter !== '') {
    $sql .= " AND status = :status";
    $params['status'] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM franchise_leads")->fetchColumn();
$newLeads = (int)$pdo->query("SELECT COUNT(*) FROM franchise_leads WHERE status = 'NEW'")->fetchColumn();
$contactedLeads = (int)$pdo->query("SELECT COUNT(*) FROM franchise_leads WHERE status = 'CONTACTED'")->fetchColumn();
$dealLeads = (int)$pdo->query("SELECT COUNT(*) FROM franchise_leads WHERE status = 'DEAL'")->fetchColumn();
?>

<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 block uppercase">Total Prospek</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block"><?= $totalLeads ?></span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Semua kota</span>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-amber-500 block uppercase">Prospek Baru</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block"><?= $newLeads ?></span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Perlu follow-up</span>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-blue-500 block uppercase">Sedang Dihubungi</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block"><?= $contactedLeads ?></span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Diskusi lokasi</span>
        </div>

        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold text-emerald-500 block uppercase">Closing / Deal</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block"><?= $dealLeads ?></span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Mitra resmi</span>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a 
                    href="/admin/franchise-leads" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= empty($statusFilter) ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                >
                    Semua (<?= $totalLeads ?>)
                </a>
                <a 
                    href="/admin/franchise-leads?status=NEW" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'NEW' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                >
                    Baru (<?= $newLeads ?>)
                </a>
                <a 
                    href="/admin/franchise-leads?status=CONTACTED" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'CONTACTED' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                >
                    Dihubungi (<?= $contactedLeads ?>)
                </a>
            </div>

            <!-- Export CSV -->
            <a 
                href="/admin/franchise-leads/export" 
                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center gap-1.5"
            >
                <?= icon('download', 'w-3.5 h-3.5') ?>
                <span>Ekspor CSV Leads</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                    <tr>
                        <th class="p-4">Tanggal Masuk</th>
                        <th class="p-4">Nama Calon Mitra</th>
                        <th class="p-4">Kota Rencana Buka</th>
                        <th class="p-4">Paket Peminatan</th>
                        <th class="p-4">Kontak WhatsApp</th>
                        <th class="p-4">Status Prospek</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada data prospek kemitraan yang masuk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 text-slate-500 whitespace-nowrap">
                                    <?= date('d M Y, H:i', strtotime($lead['created_at'])) ?> WIB
                                </td>
                                <td class="p-4 font-bold text-slate-900">
                                    <?= e($lead['name']) ?>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-lg text-slate-800 font-bold">
                                        <?= icon('map-pin', 'w-3 h-3 text-primary') ?>
                                        <span><?= e($lead['city']) ?></span>
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-slate-800">
                                    <?= e($lead['package_chosen']) ?>
                                </td>
                                <td class="p-4">
                                    <a 
                                        href="<?= format_whatsapp_url($lead['whatsapp_number'], 'Halo Bapak/Ibu ' . $lead['name'] . ', terima kasih telah mengunduh proposal kemitraan Kopi & Rasa Nusantara.') ?>" 
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold hover:bg-emerald-100 transition"
                                    >
                                        <?= icon('whatsapp', 'w-3.5 h-3.5 text-emerald-600') ?>
                                        <span>+<?= e($lead['whatsapp_number']) ?></span>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $lead['status'] === 'DEAL' ? 'bg-emerald-100 text-emerald-800' : ($lead['status'] === 'CONTACTED' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                                        <?= e($lead['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
