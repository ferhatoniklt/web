<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - TV SERIES ENGINE V2.2 (STABILIZED)
 */

require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; 

error_reporting(E_ALL);
ini_set('display_errors', '1'); 
set_time_limit(0); 

function seo($text) {
    $find = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#', ':');
    $replace = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp', '');
    $text = str_ireplace($find, $replace, $text);
    $text = preg_replace("/[^a-zA-Z0-9\s]/", "", $text);
    $text = strtolower(trim(preg_replace("/\s+/", "-", $text)));
    $text = preg_replace("/-+/", "-", $text);
    return $text;
}

// GÜÇLENDİRİLMİŞ CURL MOTORU
function getRemoteData($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 40, // Süreyi uzattık
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return $res ? $res : false;
}

echo "<h3>🎬 Oniklotho - TV Series Engine V2.2 Aktif</h3><hr>";

$zar = rand(1, 100); 
$rastgele_sayfa = ($zar <= 70) ? rand(1, 10) : rand(11, 250);
$skip_degeri = ($rastgele_sayfa - 1) * 20;

echo "<b>[HEDEF]:</b> Sayfa $rastgele_sayfa | Skip: $skip_degeri <br>";

$api_url = "https://v3-cinemeta.strem.io/catalog/series/top/skip=" . $skip_degeri . ".json";

// İLK DENEME
$response = getRemoteData($api_url);

// EĞER BAŞARISIZSA 2. DENEME (Farklı Link Yapısıyla)
if (!$response) {
    echo "<span style='color:orange;'>[UYARI]: Birinci deneme başarısız, alternatif hattan sızılıyor...</span><br>";
    $api_url = "http://v3-cinemeta.strem.io/catalog/series/top/skip=" . $skip_degeri . ".json"; // HTTP denemesi
    $response = getRemoteData($api_url);
}

if (!$response) {
    die("<b style='color:red;'>[KRİTİK HATA]: API şu an cevap vermiyor abi. Stremio sunucuları bakımda olabilir.</b>");
}

$data = json_decode($response, true);
if (empty($data['metas'])) die("<b style='color:orange;'>Sayfa boş döndü (Meta yok).</b>");

$eklenen = 0;
foreach ($data['metas'] as $series) {
    $imdb_id = $series['id']; 
    $full_title = trim($series['name']);
    $year = isset($series['releaseInfo']) ? substr($series['releaseInfo'], 0, 4) : date('Y');
    $img_url = $series['poster'] ?? '';
    
    if (empty($imdb_id) || empty($full_title) || empty($img_url)) continue;

    $seo_name = seo($full_title);
    $kontrol = $db->prepare("SELECT id FROM contents WHERE content_seourl = ? OR content_videourl LIKE ?");
    $kontrol->execute([$seo_name, "%$imdb_id%"]);

    if ($kontrol->rowCount() == 0) {
        // Resim Kaydetme
        $kapak_adi = $seo_name . '-' . $imdb_id . '.webp';
        $save_path = 'admin/assets/all_contents/' . $kapak_adi;
        
        $img_data = getRemoteData("https://wsrv.nl/?url=" . urlencode($img_url) . "&output=webp&q=80");
        if ($img_data) { 
            file_put_contents($save_path, $img_data); 
        } else { $kapak_adi = 'nophoto.jpg'; }

        $final_player_url = "https://vidsrc.me/embed/tv?imdb=" . $imdb_id;
        $seoDescription = "<h2>Watch $full_title Online</h2><p>Stream <strong>$full_title</strong> online for free in FHD.</p>";

        $kaydet = $db->prepare("INSERT INTO contents SET content_name = :name, content_seourl = :seourl, content_description = :desc, content_category = :cat, content_kapakimg = :img, content_videourl = :video, content_type = 2, content_size = 'FHD', content_aktiflik = 1");
        
        if ($kaydet->execute([':name' => $full_title, ':seourl' => $seo_name, ':desc' => $seoDescription, ':cat' => "TV Series, $year", ':img' => $kapak_adi, ':video' => $final_player_url])) { 
            $eklenen++; 
            echo "<span style='color:green;'>[EKLENDİ]:</span> $full_title <br>";
            
            if (function_exists('sendGoogleIndex')) {
                sendGoogleIndex("https://oniklotho.xyz/content/" . $seo_name);
            }
        }
        usleep(300000); 
    } else {
        echo "<span style='color:gray;'>[VAR]:</span> $full_title <br>";
    }
}
echo "<hr><b>Operasyon Tamamlandı. $eklenen dizi eklendi.</b>";
?>