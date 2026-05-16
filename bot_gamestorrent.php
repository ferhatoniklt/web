<?php
// 1. SİSTEM VE HATA AYARLARI
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; // Google API İletişimcisi
set_time_limit(0);

// Panelden gelen URL (Yoksa 1. sayfadan başlar)
$target_url = (isset($_GET['target']) && !empty($_GET['target'])) ? $_GET['target'] : "https://fitgirl-repacks.site/";

// --- FONKSİYONLAR ---

function getHtmlContent($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com/");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode != 200) {
        echo "<span style='color:red;'>[HATA]:</span> Siteye ulaşılamadı. HTTP Kodu: $httpCode <br>";
    }

    curl_close($ch);
    return ($httpCode == 200) ? $data : false;
}

function seourl($s)
{
    $tr = array('ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'Ç', 'ç', '+', '#');
    $en = array('s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', 'plus', 'sharp');
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
    return $s;
}

function getYoutubeTrailer($gameName)
{
    $cleanName = preg_replace('/ – v\d.*$/i', '', $gameName); 
    $q = urlencode($cleanName . " official gameplay trailer");
    $html = getHtmlContent("https://www.youtube.com/results?search_query=" . $q);
    if (preg_match('/watch\?v=([a-zA-Z0-9_-]{11})/', $html, $matches)) {
        return $matches[1];
    }
    return "";
}

function optimizeAndSaveImage($url, $savePath)
{
    if (substr($url, 0, 2) == "//") { $url = "https:" . $url; }
    $imageData = getHtmlContent($url);
    if (!$imageData) return false;
    $src = @imagecreatefromstring($imageData);
    if (!$src) return false;
    $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $savePath);
    imagewebp($src, $newPath, 75);
    imagedestroy($src);
    return basename($newPath);
}

// --- YENİ: BENZERSİZLEŞTİRME VE TEMİZLİK FONKSİYONU ---
function makeUnique($text, $gameName) {
    // 1. Yasaklı kelime ve reklamları temizle
    $yasakli = [
        'FitGirl', 'fitgirl-repacks.site', 'repack-site.com', 'Tapochek.net', 
        '1337x', 'Razor12911', 'Tenoke', 'CS.RIN.RU', 'Discussion', 'future updates'
    ];
    $text = str_ireplace($yasakli, "Oniklotho", $text);

    // 2. Teknik terimleri daha özgün hale getir
    $degistir = [
        'Lossless' => 'High Performance',
        'MD5 Perfect' => 'Fully Verified',
        'repack' => 'compressed version',
        'NOTHING re-encoded' => 'original quality preserved'
    ];
    $text = str_replace(array_keys($degistir), array_values($degistir), $text);
    
    return trim($text);
}

// --- ANA AKIŞ ---
echo "<h2>Oniklotho Pro Bot - Ultra SEO Mode Activated</h2><hr>";

$html = getHtmlContent($target_url);
if (!$html) die("Hedef siteye ulaşılamadı abi.");

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
$articles = $xpath->query("//article[contains(@class, 'hentry')]");

foreach ($articles as $article) {
    $linkNode = $xpath->query(".//h1[@class='entry-title']/a", $article)->item(0);
    if (!$linkNode) continue;

    $gameTitleRaw = trim($linkNode->textContent);
    $gameLink = $linkNode->getAttribute('href');
    $seo = seourl($gameTitleRaw);
    $cleanGameName = trim(preg_replace('/ – v\d.*$/i', '', $gameTitleRaw));

    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ?");
    $kontrol->execute([$seo]);

    if ($kontrol->rowCount() == 0) {
        echo "<b>İşleniyor:</b> $cleanGameName <br>";

        $detayHtml = getHtmlContent($gameLink);
        if (!$detayHtml) continue;

        preg_match('/href="(magnet:\?xt=urn:btih:[^"]+)"/', $detayHtml, $match);
        $magnet = $match[1] ?? null;

        $domDetay = new DOMDocument();
        @$domDetay->loadHTML('<?xml encoding="UTF-8">' . $detayHtml);
        $xpathDetay = new DOMXPath($domDetay);

        // --- TEKNİK DETAYLARI ÇEK ---
        $repackSize = "Check Below";
        $genres = "PC Action / Adventure";

        $strongNodes = $xpathDetay->query("//strong");
        foreach ($strongNodes as $strong) {
            $text = $strong->textContent;
            if (strpos($text, 'Repack Size:') !== false) {
                $repackSize = trim(str_replace('Repack Size:', '', $strong->parentNode->textContent));
                $repackSize = explode(' / ', $repackSize)[0];
            }
            if (strpos($text, 'Genres/Tags:') !== false) {
                $genres = trim(str_replace('Genres/Tags:', '', $strong->parentNode->textContent));
            }
        }

        // --- HAKKINDA YAZISI FİLTRELEME ---
        $descNodes = $xpathDetay->query("//div[@class='entry-content']/p | //div[@class='entry-content']/ul/li");
        $rawAboutText = "";
        
        foreach ($descNodes as $node) {
            $nodeText = trim($node->textContent);
            // Sadece anlamlı ve reklam içermeyen satırları al
            if (strlen($nodeText) > 15 && !preg_match('/(http|magnet|donate|discussion|repack uses|Razor12911|Based on)/i', $nodeText)) {
                $rawAboutText .= "<li>" . makeUnique($nodeText, $cleanGameName) . "</li>";
            }
        }

        // --- 🚀 MÜKEMMEL VE BENZERSİZ SEO AÇIKLAMASI ---
        $intros = [
            "Download <strong>$cleanGameName</strong> for PC with high-speed direct links.",
            "Get the latest highly compressed <strong>$cleanGameName</strong> repack on Oniklotho.",
            "Full version of <strong>$cleanGameName</strong> is now available for free download."
        ];
        $randomIntro = $intros[array_rand($intros)];

        $seoDescription = "<h2>$cleanGameName Free PC Download</h2>";
        $seoDescription .= "<p>$randomIntro Experience <strong>$cleanGameName</strong> with all updates and optimized performance. Our version ensures small file sizes with 100% original quality.</p>";

        $seoDescription .= "<h3>Game Overview & Info</h3>";
        $seoDescription .= "<ul>";
        $seoDescription .= "<li><strong>Name:</strong> $cleanGameName</li>";
        $seoDescription .= "<li><strong>Tags:</strong> $genres</li>";
        $seoDescription .= "<li><strong>Storage Required:</strong> $repackSize</li>";
        $seoDescription .= "<li><strong>System:</strong> Windows PC</li>";
        $seoDescription .= "</ul>";

        $seoDescription .= "<h3>Installation Features</h3>";
        $seoDescription .= "<ul>" . $rawAboutText . "</ul>";
        
        $seoDescription .= "<p><i>Note: This $cleanGameName package has been verified by the Oniklotho team for stability.</i></p>";

        // --- RESİM GALERİSİ ÇEKME (FİLTRELİ) ---
        $imgNodes = $xpathDetay->query("//div[@class='entry-content']//img");
        $kayitliResimler = [];
        foreach ($imgNodes as $img) {
            $uzakResim = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            
            // Reklam logolarını ve gifleri atla
            if (preg_match('/(logo|fitgirl|repack|donate|gif|header)/i', $uzakResim)) continue;

            $resimAdi = $seo . "-" . (count($kayitliResimler) + 1) . ".jpg";
            $kayitYolu = "admin/assets/all_contents/" . $resimAdi;

            $webpName = optimizeAndSaveImage($uzakResim, $kayitYolu);
            if ($webpName) { $kayitliResimler[] = $webpName; }
            if (count($kayitliResimler) >= 4) break;
        }

        // --- VERİTABANI KAYIT ---
        if ($magnet) {
            $trailerId = getYoutubeTrailer($gameTitleRaw);

            $ekle = $db->prepare("INSERT INTO contents SET 
                content_type = 1, content_name = :name, content_description = :descr,
                content_size = :size, content_seourl = :seo, content_videourl = :trailer,
                content_link1 = :magnet, content_kapakimg = :kapak,
                content_img1 = :i1, content_img2 = :i2, content_img3 = :i3,
                content_aktiflik = 1, content_category = 'Games'
            ");

            $ekle->execute([
                ':name' => $gameTitleRaw,
                ':descr' => $seoDescription,
                ':size' => $repackSize,
                ':seo' => $seo,
                ':trailer' => $trailerId,
                ':magnet' => $magnet,
                ':kapak' => $kayitliResimler[0] ?? null,
                ':i1' => $kayitliResimler[1] ?? null,
                ':i2' => $kayitliResimler[2] ?? null,
                ':i3' => $kayitliResimler[3] ?? null
            ]);
            echo "<span style='color:green;'>[DB OK]:</span> Benzersiz içerik kaydedildi.<br>";

            // Google Indexing API
            $yeni_url = "https://oniklotho.xyz/content/" . $seo;
            $index_sonuc = sendGoogleIndex($yeni_url);
            echo "<span style='color:cyan;'>[GOOGLE]:</span> $index_sonuc <br><br>";
        }
    } else {
        echo "<span style='color:gray;'>[VAR]:</span> $cleanGameName zaten listede.<br>";
    }
}

$db->prepare("INSERT INTO bot_history (bot_file, target_url, scan_date) VALUES (?, ?, ?)")->execute([basename(__FILE__), $target_url, date('Y-m-d H:i:s')]);
echo "<hr><h3>İşlem tamamlandı abi! Siten artık daha özgün.</h3>";
?>