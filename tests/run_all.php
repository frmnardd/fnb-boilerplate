<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master CI/CD & Complete Test Runner
 * Executes all test suites across Phase 1 to Phase 6
 */

$testSuites = [
    'Phase 1: Scaffolding & Database Setup'                       => __DIR__ . '/test_phase1.php',
    'Phase 2: Frontend Foodie Layout, WA Cart Drawer & ROI Calc' => __DIR__ . '/test_phase2.php',
    'Phase 3: Admin CMS TailAdmin UI, Stock Toggle & Settings'   => __DIR__ . '/test_phase3.php',
    'Phase 4: Nginx Production Config & Multi-Client Cloner'      => __DIR__ . '/test_phase4.php',
    'Phase 5: Studio Meja, KDS, Thermal POS & Inventori HPP'     => __DIR__ . '/test_phase5.php',
    'Phase 6: Professional Open-Source Assets, Zero Emoji & API'  => __DIR__ . '/test_phase6.php',
];

echo "===================================================================\n";
echo "   FnB Boilerplate - Full Project Verification (Phase 1 to 6)      \n";
echo "===================================================================\n\n";

$allPassed = true;

foreach ($testSuites as $name => $path) {
    echo "▶ Menjalankan {$name}...\n";
    $output = [];
    $exitCode = 0;
    exec("php " . escapeshellarg($path), $output, $exitCode);
    
    if ($exitCode === 0) {
        // Grab summary line
        $lastLines = array_slice($output, -3);
        echo "  " . implode("\n  ", $lastLines) . "\n\n";
    } else {
        echo "  [ERROR] Suite gagal:\n";
        echo "  " . implode("\n  ", $output) . "\n\n";
        $allPassed = false;
    }
}

echo "===================================================================\n";
if ($allPassed) {
    echo "   HASIL AKHIR: SELURUH FASE 1 HINGGA FASE 6 LULUS 100%! ✓       \n";
    echo "===================================================================\n";
    exit(0);
} else {
    echo "   HASIL AKHIR: ADA PENGUJIAN YANG GAGAL. SILAKAN PERIKSA LOG.    \n";
    echo "===================================================================\n";
    exit(1);
}
