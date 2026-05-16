<?php
// ÖNEMLİ: PHP tagından önce boşluk bırakma.
header("Content-Type: application/xml; charset=utf-8");
header("X-Robots-Tag: noindex"); 

define('SITE_URL', 'https://oniklotho.xyz');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Kategorilerin (Veritabanındaki ID'lerinle eşleşmeli)
$categories = [
    'games'    => 1,
    'movies'   => 2,
    'software' => 3, 
    'nsfw'     => 4,
    'apk'      => 5
];

foreach ($categories as $name => $id) {
    echo '<sitemap>';
    // Burada generator dosyasına yönlendiriyoruz
    echo '<loc>' . SITE_URL . '/sitemap-generator.php?type=' . $id . '</loc>';
    echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
    echo '</sitemap>';
}

echo '</sitemapindex>';