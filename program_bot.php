<?php
require_once __DIR__ . '/baglan.php';

// Hataları görmen için (Sunucuda yayına alırken kapatabilirsin)
ini_set('display_errors', 1);
error_reporting(E_ALL);

function seo($text) {
    $find = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#');
    $replace = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp');
    $text = str_ireplace($find, $replace, $text);
    $text = preg_replace("/[^a-zA-Z0-9\s]/", "", $text);
    $text = strtolower(trim(preg_replace("/\s+/", "-", $text)));
    $text = preg_replace("/-+/", "-", $text);
    return $text;
}

// CURL fonksiyonu (Detay sayfalarına girmek için tekrar tekrar kullanacağız)
function baglan($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36');
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

header('Content-Type: text/html; charset=utf-8');

$domain = "https://1337x.to"; // Hedef sitenin ana domaini
$target = $domain . "/cat/Apps/1/"; // Uygulama listesi sayfası

$html = baglan($target);
if (!$html) { die("Ana listeye ulaşılamadı."); }

$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($doc);

// Tablodaki satırları seçiyoruz
$rows = $xpath->query("//table[contains(@class, 'table-list')]/tbody/tr");

echo "<h3>Oniklotho Software & Apps Bot</h3><hr>";

foreach ($rows as $row) {
    // 1. Program Adı ve Detay Linki
    // coll-1 içindeki ikinci <a> etiketi başlığı tutar
    $link_node = $xpath->query(".//td[contains(@class, 'coll-1')]/a[2]", $row)->item(0);
    $soft_name = $link_node ? $link_node->nodeValue : '';
    $soft_href = $link_node ? $link_node->getAttribute('href') : '';
    
    if (empty($soft_name) || empty($soft_href)) continue;

    // 2. Mükerrer Kontrolü (content_type 4 = Program olsun)
    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_name = ? AND content_type = 4");
    $kontrol->execute([$soft_name]);
    if ($kontrol->rowCount() > 0) {
        echo "<span style='color:orange;'>[ATLANDI]:</span> $soft_name (Zaten ekli)<br>";
        continue;
    }

    // 3. DETAY SAYFASINA GİRİŞ
    $detay_html = baglan($domain . $soft_href);
    $detay_doc = new DOMDocument();
    @$detay_doc->loadHTML('<?xml encoding="UTF-8">' . $detay_html);
    $detay_xpath = new DOMXPath($detay_doc);

    // 4. Magnet Linki Çekme
    // Genellikle href'i "magnet:?xt=" ile başlayan linktir
    $magnet_node = $detay_xpath->query("//a[starts-with(@href, 'magnet:?')]", $detay_doc)->item(0);
    $magnet_link = $magnet_node ? $magnet_node->getAttribute('href') : '';

    // 5. Görsel Çekme (Screenshot)
    $img_node = $detay_xpath->query("//div[contains(@class, 'screenshot')]//img", $detay_doc)->item(0);
    if (!$img_node) {
        // Eğer screenshot yoksa açıklama içindeki ilk resmi dene
        $img_node = $detay_xpath->query("//div[@id='description']//img", $detay_doc)->item(0);
    }
    $img_url = $img_node ? $img_node->getAttribute('src') : '';

    // 6. Açıklama Çekme
    $desc_node = $detay_xpath->query("//div[@id='description']", $detay_doc)->item(0);
    $description = $desc_node ? $detay_doc->saveHTML($desc_node) : 'Açıklama bulunamadı.';

    // 7. Boyut Bilgisi (Listeden alıyoruz)
    $size_node = $xpath->query(".//td[contains(@class, 'coll-4')]", $row)->item(0);
    $soft_size = $size_node ? $size_node->childNodes->item(0)->nodeValue : 'Bilinmiyor';

    // 8. Resmi İndir ve WebP Yap
    $kapak_adi = 'app-' . time() . '-' . rand(100,999) . '.webp';
    $save_path = 'admin/assets/all_contents/' . $kapak_adi;

    if ($img_url) {
        $img_data = @file_get_contents($img_url);
        if ($img_data) {
            $temp_img = @imagecreatefromstring($img_data);
            if ($temp_img) {
                imagewebp($temp_img, $save_path, 80);
                imagedestroy($temp_img);
            }
        }
    }

    // 9. Veritabanına Kaydet
    $kaydet = $db->prepare("INSERT INTO contents SET
        content_name = :name,
        content_seourl = :seourl,
        content_description = :desc,
        content_category = :cat,
        content_kapakimg = :img,
        content_videourl = :magnet, -- Video URL alanına Magnet Linki basıyoruz
        content_type = 4,
        content_size = :size,
        content_aktiflik = 1
    ");

    $insert = $kaydet->execute([
        'name' => $soft_name,
        'seourl' => seo($soft_name),
        'desc' => trim($description),
        'cat' => "Software, Windows",
        'img' => $kapak_adi,
        'magnet' => $magnet_link,
        'size' => $soft_size
    ]);

    if ($insert) {
        echo "<span style='color:green;'>[OK]:</span> $soft_name (Magnet ve Detaylar Alındı)<br>";
    }
    
    // Sunucuyu yormamak için kısa bir bekleme (Saniyede 1 detay sayfası)
    usleep(500000); 
}
?>