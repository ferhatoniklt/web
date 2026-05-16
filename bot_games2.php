<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - 1377x INFILTRATOR V4.0
 * Ghost Image Proxy, Anti-Fake Koruması ve Google Indexing Entegreli
 */

// 1. SİSTEM VE HATA AYARLARI
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Dosya yollarını garantiye alıyoruz
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; 

set_time_limit(0); 

$baseUrl = "https://1377x.to"; 

// --- ÇOKLU HEDEF URL LİSTESİ ---
$target_urls = [
    "https://1377x.to/user/DODI/1/",
    "https://1377x.to/user/KaOsKrew/1/"
    
];

if (isset($_GET['target']) && !empty($_GET['target'])) {
    $target_urls = [$_GET['target']];
}

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
    $q = urlencode($gameName . " official gameplay trailer");
    $html = getHtmlContent("https://www.youtube.com/results?search_query=" . $q);
    if ($html && preg_match('/watch\?v=([a-zA-Z0-9_-]{11})/', $html, $matches)) {
        return $matches[1];
    }
    return "";
}

// 🚀 GÜÇLENDİRİLMİŞ RESİM İNDİRİCİ V4 (Sahte Resim Avcısı ve 3 Katmanlı Proxy)
function optimizeAndSaveImage($url, $savePath)
{
    if (empty($url) || strpos($url, 'data:image') !== false) return false;
    if (substr($url, 0, 2) == "//") $url = "https:" . $url;

    // 1. Katman: Normal İndirme
    $imageData = getHtmlContent($url);

    // 2. Katman: WSRV Proxy (Normal indirme html/hata dönerse)
    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) {
        $imageData = @file_get_contents("https://wsrv.nl/?url=" . urlencode($url) . "&output=webp&q=80");
    }

    // 3. Katman: Jetpack Proxy (En zorlu kalkanları delmek için)
    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) {
        $url_no_http = str_replace(['http://', 'https://'], '', $url);
        $imageData = @file_get_contents("https://i0.wp.com/" . $url_no_http);
    }

    // Proxy de patlarsa işlemi iptal et (nophoto.jpg'ye düşsün)
    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) return false;
    
    $src = @imagecreatefromstring($imageData);
    if (!$src) return false;
    
    // Şeffaflık (PNG) sorunlarını çözmek için
    imagepalettetotruecolor($src);
    imagealphablending($src, false);
    imagesavealpha($src, true);
    
    $newPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $savePath);
    imagewebp($src, $newPath, 80);
    imagedestroy($src);

    // 🚨 ANTİ-FAKE KORUMASI: Dosya inmiş ama boyutu 3KB altındaysa sahtedir!
    if (file_exists($newPath) && filesize($newPath) < 3000) {
        @unlink($newPath); // Sahte/kırık dosyayı sil
        return false;
    }

    return basename($newPath);
}

// --- ANA AKIŞ ---
echo "<h2 style='color:#ff55a5;'>Oniklotho 1377x Infiltrator V4.0 Aktif</h2><hr>";

foreach ($target_urls as $current_url) {
    echo "<h3 style='color:#2f80ed;'>Kaynak Analiz Ediliyor: $current_url</h3>";
    
    $html = getHtmlContent($current_url);
    if (!$html) continue;

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    $rows = $xpath->query("//table[contains(@class, 'table-list')]/tbody/tr");

    if ($rows->length == 0) continue;

    $islenen_sayisi = 0; 

    foreach ($rows as $row) {
        $titleNode = $xpath->query(".//td[contains(@class, 'name')]/a[2]", $row)->item(0);
        if (!$titleNode) continue;

        $gameTitleRaw = trim($titleNode->textContent);
        $gameLink = $baseUrl . $titleNode->getAttribute('href');

        $sizeNode = $xpath->query(".//td[contains(@class, 'size')]", $row)->item(0);
        $repackSize = $sizeNode ? trim(explode('B', $sizeNode->textContent)[0] . 'B') : "Unknown";

        // Temiz isim çıkarma
        $cleanGameName = trim(preg_replace('/(\(|\[|-).*$/', '', $gameTitleRaw));
        if (empty($cleanGameName)) $cleanGameName = $gameTitleRaw;

        $seo = seourl($cleanGameName);

        $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ? OR content_name LIKE ?");
        $kontrol->execute([$seo, "%$cleanGameName%"]);

        if ($kontrol->rowCount() == 0) {
            echo "<b>Hedef Kilitlendi:</b> $cleanGameName <br>";

            $detayHtml = getHtmlContent($gameLink);
            if (!$detayHtml) continue;

            $domDetay = new DOMDocument();
            @$domDetay->loadHTML('<?xml encoding="UTF-8">' . $detayHtml);
            $xpathDetay = new DOMXPath($domDetay);

            // Magnet
            $magnetNode = $xpathDetay->query("//a[starts-with(@href, 'magnet:')]")->item(0);
            $magnet = $magnetNode ? $magnetNode->getAttribute('href') : null;
            if (!$magnet) continue;

            // Açıklama
            $descNodes = $xpathDetay->query("//div[@id='description']//p | //div[@id='description']//span");
            $rawAboutText = "";
            foreach ($descNodes as $node) {
                $nodeText = trim($node->textContent);
                if (strlen($nodeText) > 20 && strpos(strtolower($nodeText), 'dodi') === false && strpos($nodeText, 'http') === false) {
                    $rawAboutText .= "<p>" . $nodeText . "</p>";
                }
            }
            if (empty($rawAboutText)) $rawAboutText = "<p>Download $cleanGameName for PC. Highly compressed and optimized version.</p>";

            // SEO Yazısı
            $seoDescription = "<h2>Download $cleanGameName Repack For Free</h2>";
            $seoDescription .= "<p>Get <strong>$cleanGameName</strong> highly compressed repack for free. Experience optimized gameplay with fast install times.</p>";
            $seoDescription .= "<h3>Technical Details</h3><ul><li><strong>Size:</strong> $repackSize</li><li><strong>Platform:</strong> PC / Windows</li></ul>";
            $seoDescription .= "<h3>About the Game</h3>" . $rawAboutText;

            // 🚀 RESİMLERİ YAKALAMA (Lazy Load Uyumu ile)
            $imgNodes = $xpathDetay->query("//div[@id='description']//img");
            $kayitliResimler = [];
            
            foreach ($imgNodes as $img) {
                $uzakResim = $img->getAttribute('src');
                
                // Lazy load etiketlerini kontrol et
                if (empty($uzakResim) || strpos($uzakResim, 'data:image') !== false) {
                    $uzakResim = $img->getAttribute('data-src');
                    if (empty($uzakResim)) $uzakResim = $img->getAttribute('data-original');
                }

                if (empty($uzakResim) || strpos($uzakResim, 'logo') !== false || strpos($uzakResim, 'gif') !== false) continue;

                $resimPath = "admin/assets/all_contents/" . $seo . "-" . (count($kayitliResimler) + 1) . ".jpg";
                $webpName = optimizeAndSaveImage($uzakResim, $resimPath);
                
                if ($webpName) {
                    $kayitliResimler[] = $webpName;
                }
                
                if (count($kayitliResimler) >= 4) break;
            }

            $trailerId = getYoutubeTrailer($cleanGameName);

            // Kaydet
            $ekle = $db->prepare("INSERT INTO contents SET content_type = 1, content_name = :name, content_description = :descr, content_size = :size, content_seourl = :seo, content_videourl = :trailer, content_link1 = :magnet, content_kapakimg = :kapak, content_img1 = :i1, content_img2 = :i2, content_img3 = :i3, content_aktiflik = 1, content_category = 'Games'");
            $ekle->execute([
                ':name' => $cleanGameName,
                ':descr' => $seoDescription,
                ':size' => $repackSize,
                ':seo' => $seo,
                ':trailer' => $trailerId,
                ':magnet' => $magnet,
                ':kapak' => $kayitliResimler[0] ?? 'nophoto.jpg',
                ':i1' => $kayitliResimler[1] ?? null,
                ':i2' => $kayitliResimler[2] ?? null,
                ':i3' => $kayitliResimler[3] ?? null
            ]);

            echo "<span style='color:green;'>[DB OK]:</span> Veritabanına kaydedildi.<br>";

            // GOOGLE INDEX TETİKLEYİCİ
            if (function_exists('sendGoogleIndex')) {
                $yeni_url = "https://oniklotho.xyz/content/" . $seo;
                $index_sonuc = sendGoogleIndex($yeni_url);
                
                if ($index_sonuc === "SUCCESS") {
                    echo "<span style='color:cyan;'>[GOOGLE INDEX]:</span> Sinyal başarıyla gönderildi: $seo <br>";
                } else {
                    // Kota uyarısını daha temiz bas
                    if (strpos($index_sonuc, 'Quota exceeded') !== false || strpos($index_sonuc, '429') !== false) {
                        echo "<span style='color:#f39c12;'>[GOOGLE KOTA UYARISI]: Günlük bildirim limiti doldu. (Toplu Index dosyası gece halledecek)</span><br>";
                    } else {
                        echo "<span style='color:orange;'>[GOOGLE INDEX WARN]:</span> Hata kodu alındı. <br>";
                    }
                }
            }

            $islenen_sayisi++;
            if ($islenen_sayisi >= 2) {
                echo "<br><b style='color:orange;'>[FREN]: Sistem sağlığı için durduruldu. Kalanlar diğer turda!</b><br>";
                break 2;
            }
            echo "<br>";

        } else {
            echo "<span style='color:gray;'>[ATLANDI]:</span> $cleanGameName zaten mevcut.<br>";
        }
    }
    echo "<hr>";
}
echo "<h3>Operasyon Tamamlandı. 🏴‍☠️</h3>";
?>
