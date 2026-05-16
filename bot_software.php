<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - SOFTWARE INFILTRATOR V2.0
 * Anti-Banner Koruması ve V4 Resim İndirici Entegreli
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
    "https://1377x.to/apps/1/", 
    "https://1377x.to/user/CracksHash/1/", 
    "https://1377x.to/user/ThumperDC/1/"   
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

// 🚀 GÜÇLENDİRİLMİŞ RESİM İNDİRİCİ V4 (Sahte Resim Avcısı ve Proxy)
function optimizeAndSaveImage($url, $savePath)
{
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
echo "<h2 style='color:#00b894;'>Oniklotho Software Infiltrator V2.0 Aktif</h2><hr>";

foreach ($target_urls as $current_url) {
    echo "<h3 style='color:#2f80ed;'>Taranan Kaynak: <a href='$current_url' target='_blank' style='color:#fff;'>$current_url</a></h3>";
    
    $html = getHtmlContent($current_url);
    if (!$html) {
        echo "<span style='color:red;'>Bu adrese ulaşılamadı, bir sonraki hedefe geçiliyor...</span><hr>";
        continue;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);

    $rows = $xpath->query("//table[contains(@class, 'table-list')]/tbody/tr");

    if ($rows->length == 0) continue;

    $islenen_sayisi = 0;

    foreach ($rows as $row) {
        $titleNode = $xpath->query(".//td[contains(@class, 'name')]/a[2]", $row)->item(0);
        if (!$titleNode) continue;

        $softwareTitleRaw = trim($titleNode->textContent);
        $softwareLink = $baseUrl . $titleNode->getAttribute('href');

        $sizeNode = $xpath->query(".//td[contains(@class, 'size')]", $row)->item(0);
        $softwareSize = $sizeNode ? trim(explode('B', $sizeNode->textContent)[0] . 'B') : "Unknown";

        $cleanSoftwareName = trim(preg_replace('/(\(|\[|-).*$/', '', $softwareTitleRaw));
        if (empty($cleanSoftwareName)) $cleanSoftwareName = $softwareTitleRaw;

        $seo = seourl($cleanSoftwareName);

        $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ? OR content_name LIKE ?");
        $kontrol->execute([$seo, "%$cleanSoftwareName%"]);

        if ($kontrol->rowCount() == 0) {
            echo "<b>İşleniyor:</b> $cleanSoftwareName <br>";

            $detayHtml = getHtmlContent($softwareLink);
            if (!$detayHtml) continue;

            $domDetay = new DOMDocument();
            @$domDetay->loadHTML('<?xml encoding="UTF-8">' . $detayHtml);
            $xpathDetay = new DOMXPath($domDetay);

            // Magnet Link
            $magnetNode = $xpathDetay->query("//a[starts-with(@href, 'magnet:')]")->item(0);
            $magnet = $magnetNode ? $magnetNode->getAttribute('href') : null;
            if (!$magnet) continue;

            // Açıklama
            $descNodes = $xpathDetay->query("//div[@id='description']//p | //div[@id='description']//span | //div[@id='description']//li");
            $rawAboutText = "";
            if ($descNodes->length > 0) {
                foreach ($descNodes as $node) {
                    $nodeText = trim($node->textContent);
                    if (strlen($nodeText) > 20 && strpos(strtolower($nodeText), 'crackshash') === false && strpos($nodeText, 'http') === false) {
                        $rawAboutText .= "<p>" . $nodeText . "</p>";
                    }
                }
            }
            if (empty($rawAboutText)) $rawAboutText = "<p>Download the latest full version of $cleanSoftwareName for PC. Pre-activated and ready to use. Enjoy premium features unlocked.</p>";

            $seoDescription = "<h2>Download $cleanSoftwareName For Free (Pre-Activated)</h2>";
            $seoDescription .= "<p>Get the full version of <strong>$cleanSoftwareName</strong> for free. Enhance your productivity and creativity with this premium software. This release is completely safe, pre-activated, and requires no complicated installation steps.</p>";
            $seoDescription .= "<h3>Software Details</h3><ul><li><strong>File Size:</strong> $softwareSize</li><li><strong>Platform:</strong> PC / Windows</li><li><strong>License:</strong> Full Version / Pre-Activated</li></ul>";
            $seoDescription .= "<h3>Overview of $cleanSoftwareName</h3>" . $rawAboutText;

            // 🚀 ANTİ-BANNER RESİM FİLTRESİ
            $imgNodes = $xpathDetay->query("//div[@id='description']//img");
            $kayitliResimler = [];
            
            // Kara Liste: Bu kelimeleri içeren resim linklerini çöpe atar.
            $bannerBlacklist = ['crackshash', 'thumperdc', 'discord', 'telegram', 'donate', 'button', 'banner', 'logo', 'gif', 'icon'];

            foreach ($imgNodes as $img) {
                $uzakResim = $img->getAttribute('src');
                if (empty($uzakResim) || strpos($uzakResim, 'data:image') !== false) {
                    $uzakResim = $img->getAttribute('data-src') ?: $img->getAttribute('data-original');
                }

                if (empty($uzakResim)) continue;

                // Kara liste kontrolü
                $isGarbage = false;
                foreach ($bannerBlacklist as $badWord) {
                    if (stripos($uzakResim, $badWord) !== false) {
                        $isGarbage = true;
                        break;
                    }
                }
                if ($isGarbage) continue; // Çöp resimse atla, sıradakine geç

                // Temiz resmi kaydet
                $resimPath = "admin/assets/all_contents/" . $seo . "-software-" . (count($kayitliResimler) + 1) . ".jpg";
                $webpName = optimizeAndSaveImage($uzakResim, $resimPath);
                
                if ($webpName) {
                    $kayitliResimler[] = $webpName;
                }
                
                if (count($kayitliResimler) >= 4) break;
            }

            // KAYIT
            $ekle = $db->prepare("INSERT INTO contents SET 
                content_type = 3, 
                content_name = :name,
                content_description = :descr,
                content_size = :size,
                content_seourl = :seo,
                content_videourl = '', 
                content_link1 = :magnet,
                content_kapakimg = :kapak,
                content_img1 = :i1,
                content_img2 = :i2,
                content_img3 = :i3,
                content_aktiflik = 1,
                content_category = 'Software'
            ");

            $ekle->execute([
                ':name' => $cleanSoftwareName,
                ':descr' => $seoDescription,
                ':size' => $softwareSize,
                ':seo' => $seo,
                ':magnet' => $magnet,
                ':kapak' => $kayitliResimler[0] ?? 'nophoto.jpg',
                ':i1' => $kayitliResimler[1] ?? null,
                ':i2' => $kayitliResimler[2] ?? null,
                ':i3' => $kayitliResimler[3] ?? null
            ]);

            echo "<span style='color:green;'>[DB OK]:</span> Yazılım kaydedildi.<br>";

            if (function_exists('sendGoogleIndex')) {
                $yeni_url = "https://oniklotho.xyz/content/" . $seo;
                $index_sonuc = sendGoogleIndex($yeni_url);
                if ($index_sonuc === "SUCCESS") {
                    echo "<span style='color:cyan;'>[GOOGLE INDEX]: Sinyal uçuruldu.</span><br>";
                } else {
                    if (strpos($index_sonuc, 'Quota') !== false || strpos($index_sonuc, '429') !== false) {
                        echo "<span style='color:#f39c12;'>[GOOGLE KOTA UYARISI]: Günlük limit doldu.</span><br>";
                    }
                }
            }
            
            $db->prepare("INSERT INTO bot_history (bot_file, target_url, scan_date) VALUES (?, ?, ?)")->execute([basename(__FILE__), $current_url, date('Y-m-d H:i:s')]);
            
            $islenen_sayisi++;
            if ($islenen_sayisi >= 2) {
                echo "<br><b style='color:orange;'>[FREN]: Sistem sağlığı için durduruldu. Kalanlar diğer turda!</b><br>";
                break 2;
            }
            echo "<br>";

        } else {
            echo "<span style='color:gray;'>[VAR]:</span> $cleanSoftwareName zaten mevcut.<br>";
        }
    }
    echo "<hr>"; 
}
echo "<h3>Yazılım (Software) Taraması Tamamlandı! 🏴‍☠️</h3>";
?>