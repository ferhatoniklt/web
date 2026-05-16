<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - SKIDROW INFILTRATOR V2.0
 * Kusursuz Magnet/Torrent Avcısı ve Derin Screenshot Çekici
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; 

set_time_limit(0); 

$target_url = (isset($_GET['target']) && !empty($_GET['target'])) ? $_GET['target'] : "https://www.skidrowreloaded.com/category/pc-games/";

// --- FONKSİYONLAR ---

function getHtmlContent($url) {
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

function seourl($s) {
    $tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç','+','#');
    $en = array('s','s','i','i','i','g','g','u','u','o','o','c','c','plus','sharp');
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
    return $s;
}

function getYoutubeTrailer($gameName) {
    $q = urlencode($gameName . " official gameplay trailer");
    $html = getHtmlContent("https://www.youtube.com/results?search_query=" . $q);
    if ($html && preg_match('/watch\?v=([a-zA-Z0-9_-]{11})/', $html, $matches)) {
        return $matches[1];
    }
    return "";
}

// 🚀 GÜÇLENDİRİLMİŞ RESİM İNDİRİCİ V4
function optimizeAndSaveImage($url, $savePath) {
    if (empty($url) || strpos($url, 'data:image') !== false) return false;
    if (substr($url, 0, 2) == "//") $url = "https:" . $url;

    $imageData = getHtmlContent($url);

    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) {
        $imageData = @file_get_contents("https://wsrv.nl/?url=" . urlencode($url) . "&output=webp&q=80");
    }

    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) {
        $url_no_http = str_replace(['http://', 'https://'], '', $url);
        $imageData = @file_get_contents("https://i0.wp.com/" . $url_no_http);
    }

    if (!$imageData || stripos(substr($imageData, 0, 50), '<html') !== false) return false;
    
    $src = @imagecreatefromstring($imageData);
    if (!$src) return false;
    
    imagepalettetotruecolor($src);
    imagealphablending($src, false);
    imagesavealpha($src, true);
    
    $newPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $savePath);
    imagewebp($src, $newPath, 80);
    imagedestroy($src);

    if (file_exists($newPath) && filesize($newPath) < 3000) {
        @unlink($newPath); 
        return false;
    }
    return basename($newPath);
}

// --- ANA AKIŞ ---
echo "<h2 style='color:#ff55a5;'>Oniklotho Skidrow Infiltrator V2.0 Aktif</h2><hr>";

$html = getHtmlContent($target_url);
if (!$html) die("Hedef siteye sızılamadı abi.");

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

$posts = $xpath->query("//div[h2/a]");

if ($posts->length == 0) die("Bu sayfada içerik bulunamadı.");

$islenen_sayisi = 0; 

foreach ($posts as $post) {
    $titleNode = $xpath->query(".//h2/a", $post)->item(0);
    if (!$titleNode) continue;

    $gameTitleRaw = trim($titleNode->textContent);
    $gameLink = $titleNode->getAttribute('href');

    $cleanGameName = trim(preg_replace('/(Early Access|Free Download|Update|v\d+.*).*$/i', '', $gameTitleRaw));
    if (empty($cleanGameName)) $cleanGameName = $gameTitleRaw;

    $seo = seourl($cleanGameName);

    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ?");
    $kontrol->execute([$seo]);

    if ($kontrol->rowCount() == 0) {
        echo "<b>Hedef Kilitlendi:</b> $cleanGameName <br>";

        // 1. ANA KAPAK RESMİ
        $kapakResmiUzak = "";
        $imgNode = $xpath->query(".//div[contains(@class, 'post')]//img", $post)->item(0);
        if ($imgNode) {
            $kapakResmiUzak = $imgNode->getAttribute('src');
        }

        // ==========================================
        // DETAY SAYFASINA SIZMA
        // ==========================================
        $detayHtml = getHtmlContent($gameLink);
        if (!$detayHtml) continue;

        $domDetay = new DOMDocument();
        @$domDetay->loadHTML('<?xml encoding="UTF-8">' . $detayHtml);
        $xpathDetay = new DOMXPath($domDetay);

        // ==========================================
        // 🚀 ZIRH DELİCİ: KUSURSUZ TORRENT / MAGNET YAKALAMA
        // ==========================================
        $magnet = null;
        $fallbackLink = null;
        $allLinks = $xpathDetay->query("//div[contains(@class, 'post')]//a");

        foreach ($allLinks as $aNode) {
            $href = trim($aNode->getAttribute('href'));
            $linkMetni = strtoupper(trim($aNode->textContent));

            // Menü ve sekme linklerini (çöpleri) atla
            if (empty($href) || strpos($href, '#tabs') !== false || strpos($href, 'category') !== false || strpos($href, 'author') !== false) continue;

            // Öncelik 1: Saf Magnet Linki
            if (strpos($href, 'magnet:?xt=') === 0) {
                $magnet = $href;
                break; 
            }
            
            // Öncelik 2: Torrent Dosyası
            if (preg_match('/\.torrent$/i', $href) || strpos($href, 'download.php') !== false) {
                if (!$magnet) $magnet = $href; 
            }

            // Öncelik 3: Alternatif Dosya Siteleri veya Metninde TORRENT yazanlar
            if (preg_match('/(katfile|megaup|1fichier|rapidgator|clicknupload|pixeldrain|zippyshare)/i', $href) || strpos($linkMetni, 'TORRENT') !== false) {
                if (!$fallbackLink) $fallbackLink = $href;
            }
        }
        
        // Eğer hala boşsa sırayla düşür
        if (empty($magnet)) $magnet = $fallbackLink;
        if (empty($magnet)) $magnet = $gameLink; // Son çare olarak Skidrow konu linkini kaydet

        // ==========================================
        // 🚀 AÇIKLAMA VE BOYUT
        // ==========================================
        $repackSize = "Unknown";
        if (preg_match('/Size:\s*([0-9\.]+\s*(GB|MB))/i', $detayHtml, $sizeMatch)) {
            $repackSize = $sizeMatch[1];
        }

        $descNodes = $xpathDetay->query("//div[contains(@class, 'post')]//p");
        $rawAboutText = "";
        foreach ($descNodes as $node) {
            $nodeText = trim($node->textContent);
            // Sadece gerçek paragrafları al
            if (strlen($nodeText) > 40 && strpos(strtolower($nodeText), 'skidrow') === false && strpos($nodeText, 'http') === false) {
                $rawAboutText .= "<p>" . $nodeText . "</p>";
            }
        }
        if (empty($rawAboutText)) $rawAboutText = "<p>Download $cleanGameName for PC. Experience optimized gameplay and secure downloads.</p>";

        $seoDescription = "<h2>Download $cleanGameName Free PC Game</h2>";
        $seoDescription .= "<p>Get <strong>$cleanGameName</strong> for free. Fast download links, completely secure and verified.</p>";
        $seoDescription .= "<h3>Game Details</h3><ul><li><strong>Size:</strong> $repackSize</li><li><strong>Platform:</strong> PC / Windows</li></ul>";
        $seoDescription .= "<h3>About the Game</h3>" . $rawAboutText;

        // ==========================================
        // 🚀 KUSURSUZ RESİM ÇEKİCİ (KAPAK + EKRAN GÖRÜNTÜLERİ)
        // ==========================================
        $kayitliResimler = [];
        
        // 1. Kapak Resmini Kaydet
        if (!empty($kapakResmiUzak)) {
            $resimPath = "admin/assets/all_contents/" . $seo . "-cover.jpg";
            $webpName = optimizeAndSaveImage($kapakResmiUzak, $resimPath);
            if ($webpName) $kayitliResimler[] = $webpName;
        }

        // 2. Ekran Görüntülerini (Screenshots) Derinlemesine Tara
        $detayImgNodes = $xpathDetay->query("//div[contains(@class, 'post')]//img");
        foreach ($detayImgNodes as $img) {
            // Lazy load etiketlerini sırayla kontrol et
            $uzakResim = $img->getAttribute('data-lazy-src') ?: $img->getAttribute('data-src') ?: $img->getAttribute('src');
            
            // Kapağı, logoları, ikonları ve çok küçük çözünürlüklü linkleri atla
            if ($uzakResim == $kapakResmiUzak || empty($uzakResim) || strpos($uzakResim, 'logo') !== false || strpos($uzakResim, 'gif') !== false || strpos($uzakResim, 'avatar') !== false) continue;

            $resimPath = "admin/assets/all_contents/" . $seo . "-ss" . (count($kayitliResimler) + 1) . ".jpg";
            $webpName = optimizeAndSaveImage($uzakResim, $resimPath);
            
            if ($webpName) {
                $kayitliResimler[] = $webpName;
            }
            
            if (count($kayitliResimler) >= 4) break; 
        }

        $trailerId = getYoutubeTrailer($cleanGameName);

        // KAYIT
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

        echo "<span style='color:green;'>[DB OK]:</span> Oyun kaydedildi. Link: <span style='font-size:11px;color:#aaa;'>$magnet</span><br>";

        // GOOGLE INDEX TETİKLEYİCİ
        if (function_exists('sendGoogleIndex')) {
            $yeni_url = "https://oniklotho.xyz/content/" . $seo;
            $index_sonuc = sendGoogleIndex($yeni_url);
            
            if ($index_sonuc === "SUCCESS") {
                echo "<span style='color:cyan;'>[GOOGLE INDEX]: Sinyal başarıyla gönderildi.</span><br>";
            } else {
                if (strpos($index_sonuc, 'Quota') !== false || strpos($index_sonuc, '429') !== false) {
                    echo "<span style='color:#f39c12;'>[GOOGLE KOTA UYARISI]: Günlük bildirim limiti doldu.</span><br>";
                }
            }
        }

        $islenen_sayisi++;
        if ($islenen_sayisi >= 2) {
            echo "<br><b style='color:orange;'>[FREN]: Sistem sağlığı için durduruldu. Kalanlar diğer turda!</b><br>";
            break;
        }
        echo "<br>";

    } else {
        echo "<span style='color:gray;'>[VAR]:</span> $cleanGameName zaten mevcut.<br>";
    }
}
echo "<h3>Skidrow Operasyonu Tamamlandı. 🏴‍☠️</h3>";
?>
