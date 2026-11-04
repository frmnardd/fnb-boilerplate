<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Core Helper Functions
 * Sanitation (XSS), CSRF Protection, Currency Formatter, Auto-WebP Image Resizer & Auth Guards.
 */

// Start session if not already started
function start_session_safe(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            $configFile = __DIR__ . '/env.php';
            $env = file_exists($configFile) ? require $configFile : require __DIR__ . '/env.example.php';
            $sessionConfig = $env['session'] ?? [];

            session_name($sessionConfig['name'] ?? 'fnb_session');

            session_set_cookie_params([
                'lifetime' => $sessionConfig['lifetime'] ?? 86400,
                'path' => '/',
                'domain' => '',
                'secure' => (bool)($sessionConfig['secure'] ?? false),
                'httponly' => (bool)($sessionConfig['httponly'] ?? true),
                'samesite' => $sessionConfig['samesite'] ?? 'Lax',
            ]);

            session_start();
        } elseif (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }
}

/**
 * HTML Escaper to prevent XSS (0% vulnerability)
 */
function e(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format integer/float amount into Indonesian Rupiah format (e.g., "Rp 25.000")
 */
function format_rupiah(int|float|null $amount, bool $withSymbol = true): string
{
    if ($amount === null) {
        return $withSymbol ? 'Rp 0' : '0';
    }
    $formatted = number_format((float)$amount, 0, ',', '.');
    return $withSymbol ? 'Rp ' . $formatted : $formatted;
}

/**
 * Generate or get existing CSRF Token for the active session
 */
function csrf_token(): string
{
    start_session_safe();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output HTML hidden input with current CSRF Token
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validate CSRF token from POST request or direct argument
 */
function verify_csrf_token(?string $token = null): bool
{
    start_session_safe();
    $tokenToVerify = $token ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || empty($tokenToVerify)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $tokenToVerify);
}

/**
 * Auto-WebP Image Uploader & Resizer
 * Validates MIME type, max file size 5MB, resizes proportionally if width > 800px,
 * and saves as .webp (quality 80%) into storage/uploads.
 *
 * @param array $file $_FILES['input_name']
 * @param string $destinationSubdir Sub-directory inside uploads, e.g. 'menus', 'banners'
 * @param int $maxWidth Max width in pixels (default 800px)
 * @param int $quality WebP quality 1-100 (default 80)
 * @return array ['success' => bool, 'url' => string, 'file_name' => string, 'error' => string]
 */
function upload_image_webp(
    array $file,
    string $destinationSubdir = '',
    int $maxWidth = 800,
    int $quality = 80
): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Parameter upload file tidak valid.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas upload_max_filesize di php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas MAX_FILE_SIZE formulir.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary server tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
            UPLOAD_ERR_EXTENSION  => 'Ekstensi PHP menghentikan unggahan file.',
        ];
        return ['success' => false, 'error' => $uploadErrors[$file['error']] ?? 'Terjadi kesalahan saat unggah file.'];
    }

    // Validate file size (max 5MB)
    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Ukuran file maksimal adalah 5MB.'];
    }

    // Validate MIME Type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpeg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mimeType])) {
        return ['success' => false, 'error' => 'Format file tidak didukung. Harap unggah JPG, PNG, atau WebP.'];
    }

    // Create image resource with GD
    $sourceImage = match ($mimeType) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        default      => false,
    };

    if (!$sourceImage) {
        return ['success' => false, 'error' => 'Gagal memproses gambar. File gambar mungkin rusak.'];
    }

    $origWidth = imagesx($sourceImage);
    $origHeight = imagesy($sourceImage);

    // Calculate dimensions maintaining aspect ratio
    if ($origWidth > $maxWidth) {
        $targetWidth = $maxWidth;
        $targetHeight = (int)round(($origHeight / $origWidth) * $maxWidth);
    } else {
        $targetWidth = $origWidth;
        $targetHeight = $origHeight;
    }

    // Create target truecolor canvas
    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

    // Preserve transparency for PNG and WebP
    imagealphablending($targetImage, false);
    imagesavealpha($targetImage, true);
    $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
    imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

    // Resample original image onto target canvas
    imagecopyresampled(
        $targetImage,
        $sourceImage,
        0, 0, 0, 0,
        $targetWidth,
        $targetHeight,
        $origWidth,
        $origHeight
    );

    // Ensure storage upload directory exists
    $baseUploadDir = dirname(__DIR__) . '/storage/uploads';
    if (!empty($destinationSubdir)) {
        $baseUploadDir .= '/' . trim($destinationSubdir, '/');
    }
    if (!is_dir($baseUploadDir)) {
        mkdir($baseUploadDir, 0755, true);
    }

    // Generate unique filename
    $fileName = 'fnb_' . bin2hex(random_bytes(8)) . '_' . time() . '.webp';
    $targetFilePath = $baseUploadDir . '/' . $fileName;

    // Save as WebP with 80% quality
    $saved = imagewebp($targetImage, $targetFilePath, $quality);

    // Free GD memory
    imagedestroy($sourceImage);
    imagedestroy($targetImage);

    if (!$saved) {
        return ['success' => false, 'error' => 'Gagal menyimpan file WebP ke server storage.'];
    }

    // Also replicate or ensure file exists in public/uploads for direct web serving
    $publicUploadDir = dirname(__DIR__) . '/public/uploads';
    if (!empty($destinationSubdir)) {
        $publicUploadDir .= '/' . trim($destinationSubdir, '/');
    }
    if (!is_dir($publicUploadDir)) {
        @mkdir($publicUploadDir, 0755, true);
    }
    $publicFilePath = $publicUploadDir . '/' . $fileName;
    if (!file_exists($publicFilePath)) {
        @copy($targetFilePath, $publicFilePath);
    }

    $relativeUrl = '/uploads/' . ($destinationSubdir ? trim($destinationSubdir, '/') . '/' : '') . $fileName;

    return [
        'success'   => true,
        'url'       => $relativeUrl,
        'file_name' => $fileName,
        'width'     => $targetWidth,
        'height'    => $targetHeight,
    ];
}

/**
 * Fetch singleton Settings from database with static in-memory caching
 */
function get_setting(?string $key = null, mixed $default = null): mixed
{
    static $settingsCache = null;

    if ($settingsCache === null) {
        try {
            $pdo = db();
            $stmt = $pdo->query('SELECT * FROM settings WHERE id = 1 LIMIT 1');
            $settingsCache = $stmt->fetch() ?: [];
        } catch (\Throwable $e) {
            $settingsCache = [];
        }
    }

    if ($key === null) {
        return $settingsCache;
    }

    return $settingsCache[$key] ?? $default;
}

/**
 * Fetch configuration value from config/env.php
 */
function get_config(?string $key = null, mixed $default = null): mixed
{
    static $configCache = null;

    if ($configCache === null) {
        $configFile = __DIR__ . '/env.php';
        $configCache = file_exists($configFile) ? require $configFile : require __DIR__ . '/env.example.php';
    }

    if ($key === null) {
        return $configCache;
    }

    $keys = explode('.', $key);
    $current = $configCache;
    foreach ($keys as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return $default;
        }
        $current = $current[$segment];
    }

    return $current;
}

/**
 * Format WhatsApp Phone Number (e.g. "0812345678" -> "62812345678")
 */
function clean_phone_number(string $phone): string
{
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    }
    return $clean;
}

/**
 * Build WhatsApp click-to-chat URL
 */
function format_whatsapp_url(string $phone, string $message = ''): string
{
    $cleanPhone = clean_phone_number($phone);
    $url = "https://wa.me/{$cleanPhone}";
    if (!empty($message)) {
        $url .= '?text=' . urlencode($message);
    }
    return $url;
}

/**
 * Convert string to URL-friendly slug
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

/**
 * JSON Response Helper for APIs
 */
function json_response(mixed $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * HTTP Redirect helper
 */
function redirect(string $url, int $statusCode = 302): void
{
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Flash Message System for Notifications
 */
function flash(string $type, string $message): void
{
    start_session_safe();
    $_SESSION['flash'][$type] = $message;
}

function get_flash(string $type): ?string
{
    start_session_safe();
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function has_flash(string $type): bool
{
    start_session_safe();
    return isset($_SESSION['flash'][$type]);
}

/**
 * Authentication Helpers
 */
function auth_user(): ?array
{
    start_session_safe();
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return auth_user() !== null;
}

function require_auth(?string $requiredRole = null): void
{
    start_session_safe();
    $user = auth_user();
    if (!$user) {
        flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect('/admin/login');
    }

    if ($requiredRole !== null && ($user['role'] ?? '') !== $requiredRole && ($user['role'] ?? '') !== 'admin') {
        flash('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        redirect('/admin');
    }
}
