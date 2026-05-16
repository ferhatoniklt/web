<?php
// Hata raporlamayı açalım ki hatanın ne olduğunu ekranda görebilelim
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../baglan.php';

// Temizlenecek kelimeler
$degisimler = [
    'FitGirl' => 'Oniklotho',
    'fitgirl-repacks.site' => 'oniklotho.xyz',
    'repack-site.com' => 'oniklotho.xyz',
    'Tapochek.net' => 'Oniklotho',
    'repack' => 'compressed version'
];

echo "<h2>Oniklotho Veritabanı Temizliği</h2><hr>";

try {
    foreach ($degisimler as $eski => $yeni) {
        // En basit SQL güncelleme komutu
        $sorgu = $db->prepare("UPDATE contents SET content_description = REPLACE(content_description, :eski, :yeni) WHERE content_description LIKE :benzer");
        $sorgu->execute([
            ':eski' => $eski,
            ':yeni' => $yeni,
            ':benzer' => '%' . $eski . '%'
        ]);
        
        echo "<b>'$eski'</b> kelimesi elendi. Güncellenen satır: " . $sorgu->rowCount() . "<br>";
    }
    
    echo "<br><b>İşlem başarıyla tamamlandı abi!</b>";

} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?>