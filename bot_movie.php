<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - BOT_MOVIE V2.1 MOONSHOT EDITION
 * Google Indexing API & Ultra-SEO Optimized
 */

// ============================================================
// 0. TEMEL KURULUM
// ============================================================
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; // Google Indexing Tetikleyicisi

error_reporting(E_ALL);
ini_set('display_errors', '1'); // Hata ayıklama için açık, her şey bitince 0 yaparsın abi
set_time_limit(0); // Cronjob takılmasın diye limitsiz yapıldı

// ============================================================
// 1. MERKEZ KONFİGÜRASYON
// ============================================================
$CONFIG = [
    'api_base' => 'https://v3-cinemeta.strem.io/catalog/movie/top/skip=',
    'api_timeout' => 30,
    'api_retry' => 3,
    'api_retry_delay' => 2,

    'trend_probability' => 70,
    'trend_page_min' => 1,
    'trend_page_max' => 10,
    'archive_page_min' => 11,
    'archive_page_max' => 250,
    'page_size' => 20,

    'img_save_dir' => __DIR__ . '/admin/assets/all_contents/',
    'img_proxy' => 'https://wsrv.nl/?url=',
    'img_quality' => 82,
    'img_max_width' => 600,
    'img_max_height' => 900,
    'img_fallback' => 'nophoto.jpg',

    'player_sources' => [
        'https://vidsrc.me/embed/movie?imdb={imdb_id}',
    ],

    'content_type' => 2, // 2 = Film
    'content_size' => 'FHD',
    'content_active' => 1,
    'sleep_between' => 500000,
];

// ============================================================
// 2. YARDIMCI FONKSİYONLAR
// ============================================================

function seo($s)
{
    $tr = array('ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'Ç', 'ç', '+', '#');
    $en = array('s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', 'plus', 'sharp');
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
    return $s;
}

function downloadAndConvertImage($imgUrl, $seoName, $imdbId, $config)
{
    $fileName = $seoName . '-' . $imdbId . '.webp';
    $savePath = $config['img_save_dir'] . $fileName;

    if (file_exists($savePath) && filesize($savePath) > 1024) {
        return $fileName;
    }

    $proxyUrl = $config['img_proxy'] . urlencode($imgUrl)
        . '&w=' . $config['img_max_width']
        . '&h=' . $config['img_max_height']
        . '&fit=cover&output=webp&q=' . $config['img_quality'];

    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $imgData = curl_exec($ch);
    curl_close($ch);

    if (!$imgData || strlen($imgData) < 1024) {
        return $config['img_fallback'];
    }

    file_put_contents($savePath, $imgData);
    return (file_exists($savePath)) ? $fileName : $config['img_fallback'];
}

function curlGetWithRetry($url, $config)
{
    $attempt = 0;
    while ($attempt < $config['api_retry']) {
        $attempt++;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => $config['api_timeout'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0.0.0 Safari/537.36',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response)
            return [$response, $httpCode, ''];
        sleep($config['api_retry_delay'] * $attempt);
    }
    return ['', 0, 'API Timeout'];
}

function resolvePlayerUrl($imdbId, $sources)
{
    return str_replace('{imdb_id}', $imdbId, $sources[0]);
}

function buildDescription($title, $year, $genres)
{
    return "<h2>Watch $title ($year) Free Online</h2>"
        . "<p>Stream and watch <strong>$title</strong> online for free in high definition (FHD).</p>"
        . "<h3>Movie Details</h3><ul>"
        . "<li><strong>Movie Title:</strong> $title</li>"
        . "<li><strong>Release Year:</strong> $year</li>"
        . "<li><strong>Genres:</strong> $genres</li>"
        . "<li><strong>Quality:</strong> FHD / 1080p</li>"
        . "</ul>";
}

function botLog($db, $level, $message)
{
    $db->prepare("INSERT INTO bot_log (log_level, message) VALUES (?, ?)")
        ->execute([$level, $message]);
}

function contentExists($db, $imdbId, $seoUrl)
{
    $stmt = $db->prepare("SELECT id FROM contents WHERE content_videourl LIKE ? OR content_seourl = ? LIMIT 1");
    $stmt->execute(['%' . $imdbId . '%', $seoUrl]);
    return $stmt->rowCount() > 0;
}

function insertContent($db, $data)
{
    try {
        $stmt = $db->prepare("
            INSERT INTO contents
                (content_name, content_seourl, content_description, content_category,
                 content_kapakimg, content_videourl, content_type, content_size, content_aktiflik)
            VALUES
                (:name, :seourl, :desc, :cat, :img, :video, :type, :size, :active)
        ");
        return $stmt->execute([
            ':name' => $data['title'],
            ':seourl' => $data['seo_url'],
            ':desc' => $data['description'],
            ':cat' => $data['category'],
            ':img' => $data['cover_image'],
            ':video' => $data['player_url'],
            ':type' => $data['content_type'],
            ':size' => $data['content_size'],
            ':active' => $data['content_active'],
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================
// 6. ANA CRONJOB: HİBRİT MOTOR
// ============================================================
echo "<h3>🎬 Oniklotho Movie Engine V2.1 Başladı</h3><hr>";

$zar = rand(1, 100);
if ($zar <= $CONFIG['trend_probability']) {
    $page = rand($CONFIG['trend_page_min'], $CONFIG['trend_page_max']);
    $mode = "TREND";
} else {
    $page = rand($CONFIG['archive_page_min'], $CONFIG['archive_page_max']);
    $mode = "ARCHIVE";
}

$skip = ($page - 1) * $CONFIG['page_size'];
$apiUrl = $CONFIG['api_base'] . $skip . '.json';
[$response, $httpCode, $apiError] = curlGetWithRetry($apiUrl, $CONFIG);

if (!$response)
    die("API Error.");

$data = json_decode($response, true);
if (empty($data['metas']))
    die("No data on page $page.");

foreach ($data['metas'] as $film) {
    $imdbId = trim($film['id'] ?? '');
    $title = trim($film['name'] ?? '');
    if (empty($imdbId) || empty($title))
        continue;

    $year = isset($film['releaseInfo']) ? substr($film['releaseInfo'], 0, 4) : date('Y');
    $genres = isset($film['genres']) ? implode(', ', $film['genres']) : 'Movie';
    $imgUrl = $film['poster'] ?? '';
    $seoUrl = seo($title);

    if (contentExists($db, $imdbId, $seoUrl)) {
        echo "<span style='color:gray;'>[VAR]:</span> $title<br>";
        continue;
    }

    $coverImage = !empty($imgUrl) ? downloadAndConvertImage($imgUrl, $seoUrl, $imdbId, $CONFIG) : $CONFIG['img_fallback'];
    $playerUrl = resolvePlayerUrl($imdbId, $CONFIG['player_sources']);
    $description = buildDescription($title, $year, $genres);

    $inserted = insertContent($db, [
        'title' => $title,
        'seo_url' => $seoUrl,
        'description' => $description,
        'category' => "Movie, $year",
        'cover_image' => $coverImage,
        'player_url' => $playerUrl,
        'content_type' => $CONFIG['content_type'],
        'content_size' => $CONFIG['content_size'],
        'content_active' => $CONFIG['content_active'],
    ]);

    if ($inserted) {
        echo "<span style='color:#2ecc71;'>[EKLENDİ]:</span> <b>$title</b><br>";

        // --- 🚀 GOOGLE INDEXING API TRIGGER ---
        if (function_exists('sendGoogleIndex')) {
            $fullUrl = "https://oniklotho.xyz/content/" . $seoUrl;
            $indexResult = sendGoogleIndex($fullUrl);
            if ($indexResult === "SUCCESS") {
                echo "<small style='color:cyan;'> -> Google Index Notified!</small><br>";
            }
        }
    }

    usleep($CONFIG['sleep_between']);
}
echo "<hr><b>Operasyon Tamamlandı.</b>";