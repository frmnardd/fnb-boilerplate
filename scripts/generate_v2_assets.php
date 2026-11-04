<?php
declare(strict_types=1);

$dir = dirname(__DIR__) . '/public/assets/images';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$assets = [
    'hero-banner.png' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1000&q=80',
    'hero-signature.webp' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1000&q=80',
    'menu-kopi-aren.webp' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
    'menu-kopi-gayo.webp' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80',
    'menu-kopi-pandan.webp' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=800&q=80',
    'menu-nasgor-kampung.webp' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=800&q=80',
    'menu-ayam-taliwang.webp' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=800&q=80',
    'menu-mie-nyemek.webp' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=800&q=80',
    'menu-pisang-goreng.webp' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
    'menu-cireng-rujak.webp' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=800&q=80',
    'outlet-senopati.webp' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80',
    'outlet-braga.webp' => 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=800&q=80',
    'outlet-malioboro.webp' => 'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=800&q=80',
    'halal-badge.svg' => 'data:svg',
];

function fetch_image(string $url): ?string {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ]
    ]);
    return @file_get_contents($url, false, $ctx) ?: null;
}

echo "Generating and optimizing PRD v2.0 image assets...\n";

foreach ($assets as $filename => $url) {
    $targetPath = $dir . '/' . $filename;
    
    if ($filename === 'halal-badge.svg') {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" width="160" height="160">
  <circle cx="80" cy="80" r="76" fill="#047857" stroke="#10B981" stroke-width="4"/>
  <circle cx="80" cy="80" r="66" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-dasharray="4 2"/>
  <text x="80" y="52" font-family="Arial, sans-serif" font-weight="900" font-size="12" fill="#FFFFFF" text-anchor="middle" letter-spacing="2">INDONESIA</text>
  <text x="80" y="88" font-family="'Times New Roman', serif" font-weight="bold" font-size="34" fill="#FFFFFF" text-anchor="middle">حلال</text>
  <text x="80" y="108" font-family="Arial, sans-serif" font-weight="800" font-size="14" fill="#FCD34D" text-anchor="middle" letter-spacing="3">HALAL</text>
  <text x="80" y="126" font-family="Arial, sans-serif" font-weight="bold" font-size="8" fill="#E2E8F0" text-anchor="middle">ID31110001234560723</text>
</svg>
SVG;
        file_put_contents($targetPath, $svg);
        echo "  [OK] Created {$filename}\n";
        continue;
    }

    $raw = fetch_image($url);
    if ($raw && strlen($raw) > 500) {
        $src = @imagecreatefromstring($raw);
        if ($src) {
            $w = imagesx($src);
            $h = imagesy($src);
            
            // Target dimensions (4:3 ratio: 640x480)
            $tw = 640;
            $th = 480;
            if (str_contains($filename, 'hero')) {
                $tw = 800;
                $th = 500;
            }
            
            $dst = imagecreatetruecolor($tw, $th);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
            
            if (str_ends_with($filename, '.webp')) {
                imagewebp($dst, $targetPath, 82);
            } elseif (str_ends_with($filename, '.png')) {
                imagepng($dst, $targetPath, 6);
            }
            
            imagedestroy($dst);
            imagedestroy($src);
            echo "  [OK] Saved {$filename} (" . round(filesize($targetPath)/1024, 1) . " KB)\n";
            continue;
        }
    }
    
    // Fallback: create high-res gradient culinary placeholder card
    $dst = imagecreatetruecolor(640, 480);
    $bg = imagecolorallocate($dst, 245, 158, 11);
    imagefill($dst, 0, 0, $bg);
    if (str_ends_with($filename, '.webp')) {
        imagewebp($dst, $targetPath, 80);
    } else {
        imagepng($dst, $targetPath, 6);
    }
    imagedestroy($dst);
    echo "  [FALLBACK] Created gradient placeholder for {$filename}\n";
}

echo "All PRD v2.0 visual assets ready!\n";
