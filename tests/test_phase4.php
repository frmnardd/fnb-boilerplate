<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Master Phase 4 Verification Test Suite
 * Tests Deployment Configurations, Nginx Virtual Host & Multi-Client Bash Automation Script
 */

echo "====================================================\n";
echo "   FnB Boilerplate - Phase 4 Verification Suite     \n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name}" . ($details ? ": {$details}" : '') . "\n";
        $failed++;
    }
}

$scriptsDir = dirname(__DIR__) . '/scripts';

// ----------------------------------------------------
// Test 1: Nginx Virtual Host Configuration Check
// ----------------------------------------------------
$vhostPath = $scriptsDir . '/nginx-vhost.conf';
assert_test("Nginx template (scripts/nginx-vhost.conf) exists", file_exists($vhostPath));

$vhostContent = file_get_contents($vhostPath);

assert_test("Nginx vhost has {{CLIENT_DOMAIN}} placeholder", str_contains($vhostContent, '{{CLIENT_DOMAIN}}'));
assert_test("Nginx vhost has {{CLIENT_ROOT}} placeholder", str_contains($vhostContent, '{{CLIENT_ROOT}}'));
assert_test("Document root strictly restricts to public/", str_contains($vhostContent, 'root {{CLIENT_ROOT}}/public;'));
assert_test("PHP 8.3-FPM FastCGI socket configured", str_contains($vhostContent, 'fastcgi_pass unix:/run/php/php8.3-fpm.sock;'));
assert_test("Gzip compression enabled with high-speed types", str_contains($vhostContent, 'gzip on;') && str_contains($vhostContent, 'gzip_comp_level 6;'));
assert_test("Static assets & WebP caching (expires 30d) configured", str_contains($vhostContent, 'webp') && str_contains($vhostContent, 'expires 30d;'));
assert_test("Security headers configured (X-Frame-Options, X-Content-Type-Options)", str_contains($vhostContent, 'X-Frame-Options') && str_contains($vhostContent, 'X-Content-Type-Options'));
assert_test("Direct access to internal directories (config, database, etc.) blocked", str_contains($vhostContent, 'location ~ ^/(config|database|storage|views|scripts|tests)/'));

// ----------------------------------------------------
// Test 2: Multi-Client Automation Script Check
// ----------------------------------------------------
$cloneScriptPath = $scriptsDir . '/clone-client.sh';
assert_test("Client cloning script (scripts/clone-client.sh) exists", file_exists($cloneScriptPath));

$cloneContent = file_get_contents($cloneScriptPath);

assert_test("Script contains bash shebang and strict error mode", str_starts_with($cloneContent, '#!/usr/bin/env bash') && str_contains($cloneContent, 'set -euo pipefail'));
assert_test("Script validates client name and domain parameters", str_contains($cloneContent, 'CLIENT_NAME="${1:-}"') && str_contains($cloneContent, 'CLIENT_DOMAIN="${2:-}"'));
assert_test("Script sanitizes client name into valid DB slug", str_contains($cloneContent, 'CLIENT_SLUG='));
assert_test("Script generates dedicated config/env.php with production settings", str_contains($cloneContent, 'config/env.php') && str_contains($cloneContent, "'env'      => 'production'"));
assert_test("Script automates MySQL database and user creation", str_contains($cloneContent, 'CREATE DATABASE IF NOT EXISTS') && str_contains($cloneContent, 'CREATE USER IF NOT EXISTS'));
assert_test("Script imports database/schema.sql and seed_demo.sql", str_contains($cloneContent, 'database/schema.sql') && str_contains($cloneContent, 'database/seed_demo.sql'));
assert_test("Script sets Linux www-data permissions and 775 storage", str_contains($cloneContent, 'chown -R www-data:www-data') && str_contains($cloneContent, 'chmod -R 775'));
assert_test("Script configures Nginx virtual host with sed replacement", str_contains($cloneContent, 'sites-available') && str_contains($cloneContent, 'sed -e "s|{{CLIENT_DOMAIN}}|'));
assert_test("Script provides Let's Encrypt Certbot SSL automation instructions", str_contains($cloneContent, 'certbot --nginx'));

// ----------------------------------------------------
// Test 3: Simulation Dry-Run of Client Configuration
// ----------------------------------------------------
$simulatedName = "Kopi Sahabat Nusantara";
$simulatedDomain = "kopisahabat.co.id";
$simulatedSlug = preg_replace('/[^a-z0-9]+/', '_', strtolower($simulatedName));
$simulatedDbName = "fnb_" . trim($simulatedSlug, '_');

assert_test("Client name slugification produces clean DB name ('{$simulatedDbName}')", $simulatedDbName === 'fnb_kopi_sahabat_nusantara');

// ----------------------------------------------------
// Summary
// ----------------------------------------------------
echo "\n====================================================\n";
echo "   Phase 4 Test Results: {$passed} Passed, {$failed} Failed\n";
echo "====================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
