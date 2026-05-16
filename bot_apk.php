<?php
// 1. SİSTEM VE HATA AYARLARI
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Dosya yollarını Oniklotho standartlarına göre sabitliyoruz
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; 

set_time_limit(0); 

$target_url = (isset($_GET['target']) && !empty($_GET['target'])) ? $_GET['target'] : "https://apps2app.com/page/1/";

// --- FONKSİYONLAR ---

function getHtmlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com/");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode == 200) ? $data : false;
}

function seourl($s) {
    $tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç','+','#');
    $en = array('s','s','i','i','i','g','g','u','u','o','o','c','c','plus','sharp');
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
    return $s;
}

function downloadGhostImage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_REFERER, "https://apps2app.com/"); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $data && stripos(substr($data, 0, 50), '<html') === false) {
        return $data;
    }

    // Proxy Fallback
    $proxyUrl = "https://wsrv.nl/?url=" . urlencode($url);
    $proxyData = @file_get_contents($proxyUrl);
    return ($proxyData && stripos(substr($proxyData, 0, 50), '<html') === false) ? $proxyData : false;
}

function optimizeAndSaveImage($url, $savePath) {
    if (empty($url)) return false;
    if (substr($url, 0, 2) == "//") $url = "https:" . $url;
    elseif (substr($url, 0, 1) == "/") $url = "https://apps2app.com" . $url;

    $imageData = downloadGhostImage($url);
    if (!$imageData) return false;
    
    $src = @imagecreatefromstring($imageData);
    if (!$src) return false;

    imagepalettetotruecolor($src);
    imagealphablending($src, false);
    imagesavealpha($src, true);

    $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $savePath);
    imagewebp($src, $newPath, 75);
    imagedestroy($src);
    return basename($newPath);
}

// --- ANA AKIŞ ---
echo "<h2 style='color:#00ff00;'>Oniklotho APK Infiltrator V1.2 Aktif</h2><hr>";

$html = getHtmlContent($target_url);
if (!$html) die("Hedef siteye sızılamadı abi.");

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
$apps = $xpath->query("//div[contains(@class, 'bav1')]");

foreach ($apps as $app) {
    $linkNode = $xpath->query(".//a", $app)->item(0);
    if (!$linkNode) continue;

    $appLink = $linkNode->getAttribute('href');
    $appTitleFull = trim($linkNode->getAttribute('title')); 
    $cleanTitle = trim(preg_replace('/ (Mod |)APK.*$/i', '', $appTitleFull));
    $seo = seourl($cleanTitle);

    $versionNode = $xpath->query(".//span[@class='version']/span[2]", $app)->item(0);
    $version = $versionNode ? trim($versionNode->textContent) : 'Latest';

    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ?");
    $kontrol->execute([$seo]);

    if ($kontrol->rowCount() == 0) {
        echo "<b>Sızılıyor:</b> $cleanTitle <br>";

        $detayHtml = getHtmlContent($appLink);
        if (!$detayHtml) continue;

        $domDetay = new DOMDocument();
        @$domDetay->loadHTML('<?xml encoding="UTF-8">' . $detayHtml);
        $xpathDetay = new DOMXPath($domDetay);

        // APK LİNKİ
        $apkLink = "";
        $dlNodes = $xpathDetay->query("//ul[@id='list-downloadlinks']//a");
        if ($dlNodes->length > 0) $apkLink = $dlNodes->item(0)->getAttribute('href');

        // AÇIKLAMA (SEO Optimize)
        $descNodes = $xpathDetay->query("//div[contains(@class, 'entry-content')]//p");
        $rawText = "";
        foreach ($descNodes as $node) {
            $t = trim($node->textContent);
            if (strlen($t) > 30 && strpos($t, 'apps2app') === false) $rawText .= "<p>$t</p>";
        }

        $seoDesc = "<h2>Download $cleanTitle MOD APK (Premium Unlocked)</h2>";
        $seoDesc .= "<p>Get <strong>$cleanTitle</strong> for Android with all premium features unlocked. Safe download link for $version version.</p>";
        $seoDesc .= $rawText;

        // KAPAK RESMİ
        $imgNode = $xpath->query(".//div[contains(@class, 'bloque-imagen')]//img", $app)->item(0);
        $webpName = 'nophoto.jpg';
        if ($imgNode) {
            $rawSrc = $imgNode->getAttribute('src');
            $uzakResim = preg_replace('/-\d+x\d+(\.[a-zA-Z0-9]+)$/', '$1', $rawSrc);
            $resimPath = "admin/assets/all_contents/" . $seo . "-apk.jpg";
            $resVal = optimizeAndSaveImage($uzakResim, $resimPath);
            if ($resVal) $webpName = $resVal;
        }

        // VERİTABANI
        $ekle = $db->prepare("INSERT INTO contents SET content_type = 5, content_name = :name, content_description = :descr, content_size = :version, content_seourl = :seo, content_link1 = :apklink, content_kapakimg = :kapak, content_aktiflik = 1, content_category = 'APK Mods'");
        
        $insert = $ekle->execute([
            ':name' => $cleanTitle,
            ':descr' => $seoDesc,
            ':version' => "v" . $version,
            ':seo' => $seo,
            ':apklink' => $apkLink,
            ':kapak' => $webpName
        ]);

        if ($insert) {
            echo "<span style='color:green;'>[DB SUCCESS]:</span> $cleanTitle kaydedildi.<br>";
            
            // --- GOOGLE INDEX TRIGGER ---
            if (function_exists('sendGoogleIndex')) {
                $yeni_url = "https://oniklotho.xyz/content/" . $seo;
                $res = sendGoogleIndex($yeni_url);
                if ($res === "SUCCESS") echo "<span style='color:cyan;'>[GOOGLE PING]: Sinyal Uçuruldu!</span><br>";
            }
            echo "<br>";
        }
        
    } else {
        echo "<span style='color:gray;'>[VAR]:</span> $cleanTitle zaten ambarımızda.<br>";
    }
}
echo "<h3>Operasyon Tamamlandı Kankam! 🏴‍☠️</h3>";
?>