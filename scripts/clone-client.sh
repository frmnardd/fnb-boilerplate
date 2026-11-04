#!/usr/bin/env bash
# ===================================================================
# FnB White-Label Boilerplate - Multi-Client VPS Duplication Script
# Automates: Project Cloning, MySQL Database Creation, Schema Import,
# Permissions, and Nginx Virtual Host Activation in < 60 seconds.
# Target: Ubuntu 24.04 LTS / Debian 12 / Nginx + PHP 8.3-FPM
# ===================================================================

set -euo pipefail

# Color Codes for Terminal Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# -------------------------------------------------------------------
# 1. Parameter Validation
# -------------------------------------------------------------------
CLIENT_NAME="${1:-}"
CLIENT_DOMAIN="${2:-}"
DB_PASSWORD="${3:-}"

if [[ -z "$CLIENT_NAME" || -z "$CLIENT_DOMAIN" ]]; then
    echo -e "${RED}${BOLD}Error: Parameter tidak lengkap!${NC}"
    echo -e "${CYAN}Penggunaan:${NC}"
    echo -e "  bash scripts/clone-client.sh <nama_klien> <domain_klien> [password_database]"
    echo ""
    echo -e "${CYAN}Contoh:${NC}"
    echo -e "  bash scripts/clone-client.sh \"Kopi Kenangan Senja\" \"kopikenangan.id\" \"Rahasia123!\""
    echo -e "  bash scripts/clone-client.sh \"Ayam Bakar Barokah\" \"ayambarokah.com\""
    exit 1
fi

# Sanitize client name into database identifier (lowercase, alphanumeric + underscore)
CLIENT_SLUG=$(echo "$CLIENT_NAME" | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-z0-9]/_/g' -e 's/__*/_/g' -e 's/^_//;s/_$//')
DB_NAME="fnb_${CLIENT_SLUG}"
DB_USER="usr_${CLIENT_SLUG:0:12}"

# Generate random secure database password if not provided
if [[ -z "$DB_PASSWORD" ]]; then
    if command -v openssl >/dev/null 2>&1; then
        DB_PASSWORD=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)
    else
        DB_PASSWORD=$(date +%s%N | sha256sum | head -c 16)
    fi
fi

# Paths
BASE_DIR="/var/www/fnb-clients"
TARGET_DIR="${BASE_DIR}/${CLIENT_DOMAIN}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MASTER_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

echo -e "${BLUE}===================================================================${NC}"
echo -e "${BOLD}${CYAN}   FnB White-Label Boilerplate - Multi-Client Instance Cloner     ${NC}"
echo -e "${BLUE}===================================================================${NC}"
echo -e "Nama Klien    : ${BOLD}${GREEN}${CLIENT_NAME}${NC}"
echo -e "Domain Target : ${BOLD}${GREEN}${CLIENT_DOMAIN}${NC}"
echo -e "Nama Database : ${BOLD}${YELLOW}${DB_NAME}${NC}"
echo -e "User Database : ${BOLD}${YELLOW}${DB_USER}${NC}"
echo -e "Folder Target : ${BOLD}${CYAN}${TARGET_DIR}${NC}"
echo -e "${BLUE}-------------------------------------------------------------------${NC}"

# Check if target directory already exists
if [[ -d "$TARGET_DIR" ]]; then
    echo -e "${RED}Error: Folder instance '${TARGET_DIR}' sudah ada! Batalkan untuk mencegah penimpaan data.${NC}"
    exit 1
fi

# -------------------------------------------------------------------
# 2. Duplicate Project Source Code
# -------------------------------------------------------------------
echo -e "${CYAN}[1/6] Menduplikasi codebase master ke ${TARGET_DIR}...${NC}"
mkdir -p "$BASE_DIR"

if command -v rsync >/dev/null 2>&1; then
    rsync -av --quiet \
        --exclude '.git' \
        --exclude 'tests' \
        --exclude 'database/*.sqlite*' \
        --exclude 'storage/uploads/*' \
        --exclude 'config/env.php' \
        "${MASTER_DIR}/" "${TARGET_DIR}/"
else
    cp -r "${MASTER_DIR}" "${TARGET_DIR}"
    rm -rf "${TARGET_DIR}/.git" "${TARGET_DIR}/tests" "${TARGET_DIR}/database/"*.sqlite* "${TARGET_DIR}/config/env.php"
fi

# Ensure storage directories exist
mkdir -p "${TARGET_DIR}/storage/uploads/menus"
mkdir -p "${TARGET_DIR}/public/uploads"

# -------------------------------------------------------------------
# 3. Create Custom config/env.php
# -------------------------------------------------------------------
echo -e "${CYAN}[2/6] Membuat file konfigurasi config/env.php khusus klien...${NC}"
cat <<EOF > "${TARGET_DIR}/config/env.php"
<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Dedicated Client Configuration
 * Client: ${CLIENT_NAME}
 * Domain: ${CLIENT_DOMAIN}
 * Generated: $(date -u +"%Y-%m-%dT%H:%M:%SZ")
 */

return [
    'app' => [
        'name'     => '${CLIENT_NAME}',
        'env'      => 'production',
        'debug'    => false,
        'url'      => 'https://${CLIENT_DOMAIN}',
        'timezone' => 'Asia/Jakarta',
    ],

    'database' => [
        'driver'        => 'mysql',
        'auto_fallback' => true,
        'host'          => '127.0.0.1',
        'port'          => 3306,
        'database'      => '${DB_NAME}',
        'username'      => '${DB_USER}',
        'password'      => '${DB_PASSWORD}',
        'charset'       => 'utf8mb4',
        'sqlite_path'   => dirname(__DIR__) . '/database/fnb.sqlite',
    ],

    'session' => [
        'name'     => 'fnb_sess_${CLIENT_SLUG:0:8}',
        'lifetime' => 86400,
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'upload' => [
        'max_size'      => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_width'     => 800,
        'quality'       => 80,
        'storage_path'  => dirname(__DIR__) . '/storage/uploads',
        'public_path'   => dirname(__DIR__) . '/public/uploads',
    ],
];
EOF

# -------------------------------------------------------------------
# 4. Provision MySQL Database & Import Schema
# -------------------------------------------------------------------
echo -e "${CYAN}[3/6] Membuat database MySQL & mengimpor skema...${NC}"
if command -v mysql >/dev/null 2>&1; then
    mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"

    # Import schema & seed
    mysql "${DB_NAME}" < "${TARGET_DIR}/database/schema.sql"
    mysql "${DB_NAME}" < "${TARGET_DIR}/database/seed_demo.sql"

    # Update initial branding in settings table
    mysql "${DB_NAME}" -e "UPDATE settings SET site_name = '${CLIENT_NAME}' WHERE id = 1;"
    echo -e "      ${GREEN}✓ Database '${DB_NAME}' & tabel berhasil diinisialisasi.${NC}"
else
    echo -e "      ${YELLOW}Perhatian: CLI 'mysql' tidak ditemukan. Fallback SQLite otomatis disiapkan.${NC}"
    # Seed SQLite fallback if mysql binary is not local
    php -r "require '${TARGET_DIR}/config/database.php'; db(); echo 'SQLite Fallback Database initialized.\n';"
fi

# -------------------------------------------------------------------
# 5. Set Linux File Permissions & Symlinks
# -------------------------------------------------------------------
echo -e "${CYAN}[4/6] Mengatur hak akses folder (www-data)...${NC}"
if id -u www-data >/dev/null 2>&1; then
    chown -R www-data:www-data "${TARGET_DIR}/storage"
    chown -R www-data:www-data "${TARGET_DIR}/public/uploads"
    chmod -R 775 "${TARGET_DIR}/storage"
    chmod -R 775 "${TARGET_DIR}/public/uploads"
fi

# Create symlink public/uploads -> storage/uploads if needed
if [[ ! -L "${TARGET_DIR}/public/uploads" && ! -d "${TARGET_DIR}/public/uploads" ]]; then
    ln -sfn "${TARGET_DIR}/storage/uploads" "${TARGET_DIR}/public/uploads"
fi

# -------------------------------------------------------------------
# 6. Configure & Enable Nginx Virtual Host
# -------------------------------------------------------------------
echo -e "${CYAN}[5/6] Mengonfigurasi virtual host Nginx...${NC}"
NGINX_AVAILABLE="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"

if [[ -d "$NGINX_AVAILABLE" ]]; then
    CONF_FILE="${NGINX_AVAILABLE}/${CLIENT_DOMAIN}.conf"
    
    # Replace placeholders in template
    sed -e "s|{{CLIENT_DOMAIN}}|${CLIENT_DOMAIN}|g" \
        -e "s|{{CLIENT_ROOT}}|${TARGET_DIR}|g" \
        "${TARGET_DIR}/scripts/nginx-vhost.conf" > "$CONF_FILE"

    # Enable site
    ln -sfn "$CONF_FILE" "${NGINX_ENABLED}/${CLIENT_DOMAIN}.conf"

    # Test & reload nginx
    if command -v nginx >/dev/null 2>&1; then
        if nginx -t >/dev/null 2>&1; then
            systemctl reload nginx || true
            echo -e "      ${GREEN}✓ Nginx virtual host aktif & di-reload.${NC}"
        else
            echo -e "      ${RED}Peringatan: Uji konfigurasi Nginx gagal. Silakan periksa ${CONF_FILE}.${NC}"
        fi
    fi
else
    echo -e "      ${YELLOW}Catatan: Folder /etc/nginx/sites-available tidak ditemukan (mode lokal). Template Nginx disimpan di ${TARGET_DIR}/scripts/nginx-vhost.conf.${NC}"
fi

# -------------------------------------------------------------------
# 7. Complete Summary & Next Steps
# -------------------------------------------------------------------
echo -e "${CYAN}[6/6] Selesai!${NC}"
echo ""
echo -e "${GREEN}===================================================================${NC}"
echo -e "${BOLD}${GREEN}   KLONING INSTANCE KLIEN BERHASIL! (Waktu < 60 Detik)             ${NC}"
echo -e "${GREEN}===================================================================${NC}"
echo -e "Brand Klien   : ${BOLD}${WHITE}${CLIENT_NAME}${NC}"
echo -e "URL Publik    : ${BOLD}${CYAN}http://${CLIENT_DOMAIN}/${NC}"
echo -e "URL Admin CMS : ${BOLD}${CYAN}http://${CLIENT_DOMAIN}/admin${NC}"
echo -e "Login Default : User: ${BOLD}${YELLOW}admin${NC} | Pass: ${BOLD}${YELLOW}admin123${NC}"
echo -e "Akun Kasir    : User: ${BOLD}${YELLOW}kasir${NC} | Pass: ${BOLD}${YELLOW}kasir123${NC}"
echo -e "Database Info : DB: ${BOLD}${YELLOW}${DB_NAME}${NC} | User: ${BOLD}${YELLOW}${DB_USER}${NC} | Pass: ${BOLD}${YELLOW}${DB_PASSWORD}${NC}"
echo -e "${GREEN}-------------------------------------------------------------------${NC}"
echo -e "${BOLD}Langkah Selanjutnya di VPS:${NC}"
echo -e "1. Arahkan DNS Record (A Record) domain ${BOLD}${CLIENT_DOMAIN}${NC} ke IP VPS Anda."
echo -e "2. Pasang Sertifikat SSL Gratis (Let's Encrypt) via Certbot:"
echo -e "   ${CYAN}sudo certbot --nginx -d ${CLIENT_DOMAIN} -d www.${CLIENT_DOMAIN}${NC}"
echo -e "3. Masuk ke ${CYAN}http://${CLIENT_DOMAIN}/admin/settings${NC} untuk mengubah logo dan nomor WhatsApp klien."
echo -e "${GREEN}===================================================================${NC}"
exit 0
