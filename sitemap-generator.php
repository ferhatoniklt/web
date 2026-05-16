<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'baglan.php';

define('SITE_URL', 'https://oniklotho.xyz');
define('IMG_BASE_URL', SITE_URL . '/admin/assets/all_contents/');

$type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
$cache_file = __DIR__ . "/cache/sitemap_type_{$type}.xml";
$cache_time = 3600; // 1 saatlik önbellek

// --- CACHE KONTROLÜ ---
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    readfile($cache_file);
    exit;
}

if (!is_dir(__DIR__ . '/cache')) { mkdir(__DIR__ . '/cache', 0755, true); }

ob_start();

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

try {
    // Kategorilere göre öncelik puanı
    $priorityMap = [1 => '0.9', 2 => '0.7', 3 => '0.8', 4 => '0.5', 5 => '0.8'];
    $priority = $priorityMap[$type] ?? '0.6';

    $sorgu = $db->prepare("
        SELECT content_seourl, content_kapakimg, COALESCE(updated_at, created_at) AS son_guncelleme 
        FROM contents 
        WHERE content_type = :type AND content_aktiflik = 1 
        ORDER BY id DESC
    ");
    $sorgu->execute([':type' => $type]);
    $icerikler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    foreach ($icerikler as $row) {
        $lastmod = !empty($row['son_guncelleme']) ? date('Y-m-d', strtotime($row['son_guncelleme'])) : date('Y-m-d');
        
        echo '<url>';
        echo '<loc>' . SITE_URL . '/content/' . htmlspecialchars($row['content_seourl'], ENT_XML1, 'UTF-8') . '</loc>';
        echo '<lastmod>' . $lastmod . '</lastmod>';
        echo '<changefreq>weekly</changefreq>';
        echo '<priority>' . $priority . '</priority>';

        // Resim desteği (Search Console'da resimlerin de indexlenmesini sağlar)
        if (!empty($row['content_kapakimg'])) {
            echo '<image:image>';
            echo '<image:loc>' . IMG_BASE_URL . htmlspecialchars($row['content_kapakimg'], ENT_XML1, 'UTF-8') . '</image:loc>';
            echo '</image:image>';
        }
        echo '</url>';
    }
} catch (Exception $e) {
    error_log("Sitemap Gen Error: " . $e->getMessage());
}

echo '</urlset>';

$output = ob_get_clean();
file_put_contents($cache_file, $output);
echo $output;