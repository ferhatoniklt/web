<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/baglan.php';

$cookieFile = __DIR__ . '/cookies_new.txt';

echo "<h2>Oniklotho Ultimate Scraper Engine</h2><hr>";

// PANELDEN GELEN URL VARSA ONU KULLAN
if (isset($_GET['target']) && !empty($_GET['target'])) {
    $target_url = $_GET['target'];
} else {
    $target_url = "https://www.xvideos.com/new/1";
}

// SEO
function seourl($s)
{
    $tr = ['ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
    $en = ['s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
    $s = str_replace($tr, $en, $s);
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
}

// CURL
function getUrlContent($url)
{
    global $cookieFile;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile
    ]);

    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

// 🚀 IFRAME ÇÖZÜCÜ (EN KRİTİK KISIM)
function extractIframe($html)
{

    // 1. Gizli input içinden çöz
    if (preg_match('/<input[^>]*id=["\']copy-video-embed["\'][^>]*value=["\'](.*?)["\']/i', $html, $kutu)) {
        $decoded = htmlspecialchars_decode($kutu[1]);

        if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $decoded, $iframe)) {
            return $iframe[1];
        }
    }

    // 2. Direkt iframe varsa
    if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $html, $match)) {
        return $match[1];
    }

    return "";
}

// Görsel indir
function saveImage($url, $path)
{
    $data = @file_get_contents($url);
    if (!$data)
        return false;

    file_put_contents($path, $data);
    return basename($path);
}

echo "<h3>[TARANIYOR]: $target_url</h3>";

$html = getUrlContent($target_url);

if (!$html) {
    die("<span style='color:red;'>Liste çekilemedi</span>");
}

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

// frame-block = liste item
$videos = $xpath->query("//div[contains(@class, 'frame-block')]");

$parsed = parse_url($target_url);
$baseUrl = $parsed['scheme'] . "://" . $parsed['host'];

foreach ($videos as $video) {

    $linkNode = $xpath->query(".//p[@class='title']/a", $video)->item(0);
    if (!$linkNode)
        continue;

    $link = $baseUrl . $linkNode->getAttribute('href');
    $title = trim($linkNode->textContent);
    $seo = seourl($title);

    // DB kontrol
    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl=?");
    $kontrol->execute([$seo]);

    if ($kontrol->rowCount() > 0)
        continue;

    echo "<span style='color:yellow;'>Yeni:</span> $title <br>";

    // DETAY SAYFASI
    $detayHtml = getUrlContent($link);

    if (!$detayHtml) {
        echo "Detay çekilemedi<br>";
        continue;
    }

    // 🚀 iframe çöz
    $iframe = extractIframe($detayHtml);

    if (empty($iframe)) {
        echo "<span style='color:red;'>Iframe bulunamadı</span><br><br>";
        continue;
    }

    // protocol düzelt
    if (substr($iframe, 0, 2) == "//") {
        $iframe = "https:" . $iframe;
    }

    // RESİM
    $imgNode = $xpath->query(".//img", $video)->item(0);

    $imgUrl = "";
    $previewVideo = "";

    if ($imgNode) {

        // XVideos özel
        $imgUrl = $imgNode->getAttribute('data-src');

        if (!$imgUrl)
            $imgUrl = $imgNode->getAttribute('src');

        // PREVIEW VIDEO (EN ÖNEMLİ)
        $previewVideo = $imgNode->getAttribute('data-preview');
    }

    // link düzelt
    if (!empty($imgUrl) && strpos($imgUrl, 'http') === false) {
        $imgUrl = "https://www.xvideos.com" . $imgUrl;
    }

    // KAPAK DEFAULT
    $kapak = "nophoto.jpg";

    // RESİM KAYDET
    if (!empty($imgUrl)) {
        $path = "admin/assets/all_contents/" . $seo . ".jpg";
        $saved = saveImage($imgUrl, $path);
        if ($saved)
            $kapak = $saved;
    }
    // relative → absolute
    if (!empty($imgUrl) && strpos($imgUrl, 'http') === false) {
        $imgUrl = $baseUrl . $imgUrl;
    }

    // DB KAYIT
    $ekle = $db->prepare("
        INSERT INTO contents SET
        content_type=4,
        content_name=:name,
        content_seourl=:seo,
        content_kapakimg=:kapak,
        content_videourl=:video,
        content_aktiflik=1,
        content_category='NSFW Video'
    ");

    $ekle->execute([
        ':name' => $title,
        ':seo' => $seo,
        ':kapak' => $kapak,
        ':video' => $iframe
    ]);

    echo "<span style='color:green;'>Kaydedildi ✔</span><br><br>";
}

echo "<hr><h4 style='color:green;'>TAMAMLANDI</h4>";
?>