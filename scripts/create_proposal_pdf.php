<?php
declare(strict_types=1);

$dir = dirname(__DIR__) . '/public/assets/docs';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$pdfPath = $dir . '/proposal-kemitraan.pdf';

// Construct valid minimal PDF 1.4 document
$content = "PROPOSAL KEMITRAAN KOPI & RASA NUSANTARA 2026\n\n"
         . "1. PROFIL BISNIS & BRAND\n"
         . "Kopi & Rasa Nusantara menyajikan perpaduan kopi lokal specialty dan kuliner legendaris Indonesia.\n"
         . "3 Cabang Aktif: Senopati (Jakarta Selatan), Braga (Bandung), Malioboro (Yogyakarta).\n\n"
         . "2. PILIHAN PAKET INVESTASI\n"
         . "- Paket Booth Express: Rp 35.000.000 (Cloud Kitchen / Minimarket)\n"
         . "- Paket Coffee Shop Standard: Rp 65.000.000 (Dine-in 15-20 Kursi)\n"
         . "- Paket Flagship Bistro: Rp 120.000.000 (Full Resto & Bar)\n\n"
         . "3. PROYEKSI KEUANGAN & ROI\n"
         . "- HPP Bahan Baku (COGS): ~35%\n"
         . "- Estimasi Laba Bersih: 30% - 35% dari Omzet\n"
         . "- Payback Period (BEP): 6 - 10 Bulan\n"
         . "- Royalti Fee: Rp 0 (100% Keuntungan Milik Mitra)\n\n"
         . "4. FASILITAS MITRA\n"
         . "- Mesin Espresso Komersial & Grinder Professional\n"
         . "- Tablet Kasir POS & Kitchen Display System (KDS)\n"
         . "- Pelatihan Barista & Manajemen Dapur (SOP Lengkap)\n"
         . "- Pasokan Bahan Baku Terstandarisasi & Bantuan Survei Lokasi\n\n"
         . "Kontak Business Development: +62 812-9876-5432\n"
         . "Website Resmi: https://fnbtech.biz.id\n";

// Minimal valid PDF structure
$stream = "BT /F1 11 Tf 50 720 Td 14 TL (" . str_replace(["\n", "(", ")"], [") ' (", "\\(", "\\)"], $content) . ") Tj ET";
$streamLen = strlen($stream);

$pdf = "%PDF-1.4\n"
     . "1 0 obj <</Type /Catalog /Pages 2 0 R>> endobj\n"
     . "2 0 obj <</Type /Pages /Kids [3 0 R] /Count 1>> endobj\n"
     . "3 0 obj <</Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
     . "4 0 obj <</Length {$streamLen}>> stream\n{$stream}\nendstream\nendobj\n"
     . "5 0 obj <</Type /Font /Subtype /Type1 /BaseFont /Helvetica>> endobj\n"
     . "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000244 00000 n \n"
     . sprintf("%010d 00000 n \n", 295 + $streamLen)
     . "trailer <</Size 6 /Root 1 0 R>>\nstartxref\n"
     . (360 + $streamLen)
     . "\n%%EOF\n";

file_put_contents($pdfPath, $pdf);
echo "Created proposal PDF at: {$pdfPath} (" . filesize($pdfPath) . " bytes)\n";
