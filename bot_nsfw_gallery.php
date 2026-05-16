<?php
// Eski hali: require_once 'baglan.php';
// Yeni hali (Tam yol gösteriyoruz):
require_once __DIR__ . '/baglan.php';
set_time_limit(0);

// --- PANEL BAĞLANTISI ---
// Eğer panelden bir target URL gelirse onu diziye al, gelmezse varsayılan listeyi kullan
if (isset($_GET['target']) && !empty($_GET['target'])) {
    $urls = [htmlspecialchars($_GET['target'])];
} else {
    // Varsayılan Çoklu URL Listesi
    $urls = [
        "https://www.pornpics.com/ai/",
        "https://www.pornpics.com/hentai/",
        "https://www.pornpics.com/selfie/",
        "https://www.pornpics.com/cosplay/",
        "https://www.pornpics.com/ahegao/",
        "https://www.pornpics.com/amateur/",
        "https://www.pornpics.com/homemade/",
        "https://www.pornpics.com/tags/amateur-teen/",



    ];
}

// SEO Fonksiyonu
function seourl($s)
{
    $tr = array('ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'Ç', 'ç', '+', '#');
    $en = array('s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', 'plus', 'sharp');
    $s = str_replace($tr, $en, $s);
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
    return $s;
}

// CURL İçerik Çekme
function getUrlContent($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.pornpics.com/');
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

// WebP Optimizasyon ve Kaydetme (GD Kütüphanesi Gerektirir)
function optimizeAndSaveImage($url, $savePath)
{
    $imageData = getUrlContent($url);
    if (!$imageData)
        return false;
    $src = @imagecreatefromstring($imageData);
    if (!$src)
        return false;

    $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $savePath);
    imagewebp($src, $newPath, 75);
    imagedestroy($src);
    return basename($newPath);
}

echo "<h2>oniklotho.xyz - Panel Entegrasyonlu Bot Çalışıyor...</h2>";

foreach ($urls as $target_url) {
    echo "<h3>Taranan Adres: $target_url</h3>";

    $html = getUrlContent($target_url);
    if (!$html) {
        echo "<span style='color:red;'>Kaynak çekilemedi: $target_url</span><br>";
        continue;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);

    // Galerileri Bul
    $articles = $xpath->query("//li[contains(@class, 'thumbwook')]//a[@class='rel-link']");

    $islenenLinkler = [];

    foreach ($articles as $article) {
        $detayLink = $article->getAttribute('href');

        // Aynı sayfada aynı linki iki kez işlememesi için
        if (in_array($detayLink, $islenenLinkler))
            continue;
        $islenenLinkler[] = $detayLink;

        $imgNode = $xpath->query(".//img", $article)->item(0);
        $title = $imgNode ? trim($imgNode->getAttribute('alt')) : "NSFW " . uniqid();
        $seo = seourl($title);

        // --- VERİTABANI KONTROLÜ ---
        $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ?");
        $kontrol->execute([$seo]);

        if ($kontrol->rowCount() == 0) {
            echo "<b>Yeni İçerik:</b> $title <br>";

            $detayHtml = getUrlContent($detayLink);
            // Resim linklerini regex ile çek
            preg_match_all('/https:\/\/cdni\.pornpics\.com\/[^\s"\'>]+\.jpg/', $detayHtml, $matches);
            $uniqueImages = array_unique($matches[0]);

            $collectedImages = [];
            $i = 1;
            foreach ($uniqueImages as $uzakResim) {
                // Logo gibi gereksiz resimleri ayıkla
                if (strpos($uzakResim, 'logo') !== false || strpos($uzakResim, 'icon') !== false)
                    continue;

                $resimIsmi = $seo . "-ok-" . $i . ".jpg";
                $tempPath = "admin/assets/all_contents/" . $resimIsmi;

                // Resim indirme ve WebP yapma
                $sonucDosya = optimizeAndSaveImage($uzakResim, $tempPath);

                if ($sonucDosya) {
                    $collectedImages[] = $sonucDosya;
                    $i++;
                }
                // Her galeri için 4 resim yeterli (1 kapak + 3 iç resim)
                if ($i > 4)
                    break;
            }

            if (!empty($collectedImages)) {
                $ekle = $db->prepare("INSERT INTO contents SET 
                    content_type = 4, 
                    content_name = :name,
                    content_seourl = :seo,
                    content_kapakimg = :kapak,
                    content_img1 = :i1,
                    content_img2 = :i2,
                    content_img3 = :i3,
                    content_aktiflik = 1,
                    content_category = 'NSFW'
                ");
                $ekle->execute([
                    ':name' => $title,
                    ':seo' => $seo,
                    ':kapak' => $collectedImages[0],
                    ':i1' => $collectedImages[1] ?? null,
                    ':i2' => $collectedImages[2] ?? null,
                    ':i3' => $collectedImages[3] ?? null
                ]);
                echo "<span style='color:green;'>--> Galeri başarıyla kaydedildi (Onay Bekliyor).</span><br><br>";
            }
        } else {
            echo "<span style='color:gray;'>--> Atlandı (Bu içerik zaten var)</span><br>";
        }
    }
}
echo "<h4>İşlem tamamlandı.</h4>";

function google_ping($url)
{
    $ping_url = "https://www.google.com/ping?sitemap=" . urlencode($url);
    file_get_contents($ping_url);
}
// Kullanımı:
google_ping("https://oniklotho.xyz/sitemap.php");
?>