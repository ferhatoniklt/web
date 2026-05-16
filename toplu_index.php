<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - TOPLU GOOGLE INDEX RADARI
 * Geriye dönük taranmamış URL'leri tespit edip Google'a zorla yedirir.
 */
ignore_user_abort(true); // Tarayıcı kapansa bile bot çalışmaya devam eder
header("Connection: close"); // Tarayıcıya "benim işim bitti" der ama arka planda çalışır
set_time_limit(0);
ini_set('memory_limit', '512M'); // 12k veri için RAM'i biraz rahatlatalım

require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/google_index.php'; // sendGoogleIndex() anahtarımız

// --- KONFİGÜRASYON ---
define('SITE_URL', 'https://oniklotho.xyz');
define('BATCH_LIMIT', 20);          // Google API limitine (200) çarpmamak için güvenli sınır
define('REQUEST_DELAY_US', 1500000); // 1.000.000 mikrosaniye = 1 saniye
define('LOG_FILE', __DIR__ . '/logs/google_index.log');
define('IS_CLI', php_sapi_name() === 'cli'); 

if (!is_dir(dirname(LOG_FILE))) {
    mkdir(dirname(LOG_FILE), 0755, true);
}

// --- LOG FONKSİYONU ---
function logYaz(string $mesaj, string $tip = 'INFO'): void {
    $satir = '[' . date('Y-m-d H:i:s') . '] [' . $tip . '] ' . $mesaj . PHP_EOL;
    file_put_contents(LOG_FILE, $satir, FILE_APPEND | LOCK_EX);

    if (!IS_CLI) {
        $renkler = ['INFO' => '#00ff00', 'HATA' => '#ff2a2a', 'UYARI' => '#ffb300', 'BASLIK' => '#2f80ed'];
        $renk = $renkler[$tip] ?? '#ccc';
        echo "<span style='color:{$renk};font-family:monospace;'>[{$tip}]</span> {$mesaj}<br>\n";
        ob_flush(); flush(); 
    }
}

function sureFormat(float $saniye): string {
    return ($saniye < 60) ? round($saniye, 2) . ' sn' : round($saniye / 60, 1) . ' dk';
}

// --- VERİTABANI KONTROL (OTOMATİK SÜTUN AÇICI) ---
try {
    $db->query("SELECT google_ping, google_ping_date FROM contents LIMIT 1");
} catch (PDOException $e) {
    $db->query("ALTER TABLE contents ADD COLUMN google_ping TINYINT(1) NOT NULL DEFAULT 0");
    $db->query("ALTER TABLE contents ADD COLUMN google_ping_date DATETIME NULL DEFAULT NULL");
    logYaz("[SİSTEM GÜNCELLEMESİ]: google_ping sütunları başarıyla oluşturuldu.", 'INFO');
}

// --- EKRAN ÇIKTISI (Tarayıcı için Cyber-UI) ---
if (!IS_CLI) {
    echo "<!DOCTYPE html><html lang='tr'><head><meta charset='UTF-8'><title>Oniklotho Radar</title>
    <style>
        body { background: #050101; color: #ccc; font-family: 'Courier New', monospace; padding: 20px; line-height: 1.6; }
        h2 { color: #ff2a2a; border-bottom: 1px solid #330a0a; padding-bottom: 10px; }
        .kutu { background: #110202; border-left: 4px solid #ff2a2a; padding: 15px; margin: 10px 0; }
        .ozet { background: #1a0505; padding: 15px; border: 1px solid #330a0a; margin-top: 20px; color: #fff;}
    </style></head><body>
    <h2>📡 Oniklotho Otonom Index Radarı Başladı</h2><div class='kutu'>";
    ob_flush(); flush();
}

$baslangic = microtime(true);

// --- BEKLEYEN İÇERİKLERİ BUL ---
$sorgu = $db->prepare("SELECT id, content_seourl FROM contents WHERE content_aktiflik = 1 AND google_ping = 0 ORDER BY id ASC LIMIT :limit");
$sorgu->bindValue(':limit', BATCH_LIMIT, PDO::PARAM_INT);
$sorgu->execute();
$icerikler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
$toplam = count($icerikler);

if ($toplam === 0) {
    logYaz("Radar temiz. Bekleyen içerik yok.", 'UYARI');
    if (!IS_CLI) echo "</div><div class='ozet'>✅ Sistem Tamamen Güncel! Google'a bildirilecek içerik yok.</div></body></html>";
    exit;
}

logYaz("Radar {$toplam} adet hedefe kilitlendi. (Parti Limiti: " . BATCH_LIMIT . ")", 'BASLIK');

$basarili = 0; $basarisiz = 0; $basarisizlar = [];

foreach ($icerikler as $index => $icerik) {
    $url = SITE_URL . '/content/' . $icerik['content_seourl'];
    $siradaki = ($index + 1) . '/' . $toplam;

    // --- API İSTEĞİ (Oniklotho Güvencesiyle) ---
    $sonuc = null; $deneme = 0; $maxDeneme = 3;

    while ($deneme < $maxDeneme) {
        $deneme++;
        $sonuc = sendGoogleIndex($url);

        // KRİTİK DÜZELTME: Sadece "SUCCESS" dönerse başarılı say.
        if ($sonuc === "SUCCESS") { break; }

        if ($deneme < $maxDeneme) {
            logYaz("[{$siradaki}] Sinyal zayıf (Deneme {$deneme}), 2 sn bekleniyor... [HATA: $sonuc]", 'UYARI');
            sleep(2);
        }
    }

    if ($sonuc === "SUCCESS") {
        // BAŞARILI: Veritabanını mühürle
        $db->prepare("UPDATE contents SET google_ping = 1, google_ping_date = NOW() WHERE id = :id")->execute([':id' => $icerik['id']]);
        $basarili++;
        logYaz("[{$siradaki}] BİLDİRİLDİ ✓ {$url}", 'INFO');
    } else {
        // BAŞARISIZ: google_ping = 0 kalır, sonraki turda tekrar dener
        $basarisiz++;
        $basarisizlar[] = $url;
        logYaz("[{$siradaki}] BAŞARISIZ ✗ {$url} | Yanıt: {$sonuc}", 'HATA');
    }

    if ($index < $toplam - 1) usleep(REQUEST_DELAY_US);
}

// --- RAPORLAMA ---
$sure = sureFormat(microtime(true) - $baslangic);
logYaz("---", 'BASLIK');
logYaz("Operasyon Bitti! Süre: {$sure}", 'BASLIK');

if (!empty($basarisizlar)) {
    logYaz("Başarısız Olan Hedefler:", 'HATA');
    foreach ($basarisizlar as $failUrl) logYaz("  -> {$failUrl}", 'HATA');
}

if (!IS_CLI) {
    echo "</div><div class='ozet'>
    <strong>📊 Operasyon Özeti:</strong><br><br>
    <span style='color:#00ff00;'>✅ Başarılı: <strong>{$basarili}</strong></span><br>
    <span style='color:#ff2a2a;'>❌ Başarısız: <strong>{$basarisiz}</strong></span><br>
    ⏱️ Süre: <strong>{$sure}</strong>
    </div></body></html>";
}


?>