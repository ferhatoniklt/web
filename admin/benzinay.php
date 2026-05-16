<?php
/** * Arasaka v24.9 - Zero Confusion Engine
 */
$sonuclar = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['fiyat_listesi']) && !empty($_POST['musteri_listesi'])) {
    $fiyat_raw = $_POST['fiyat_listesi'];
    $musteri_raw = $_POST['musteri_listesi'];
    
    $fiyatlar = [];
    $satirlar = explode("\n", trim($fiyat_raw));
    
    foreach ($satirlar as $satir) {
        $satir = trim($satir);
        if (empty($satir)) continue;

        // Satırı sekmelere (TAB) göre bölmeye çalış, olmazsa boşluklara bak
        $cols = preg_split('/\t+/', $satir);
        if (count($cols) < 2) {
            $cols = preg_split('/\s+/', $satir);
        }

        if (count($cols) > 1) {
            $tarih = $cols[0];
            // Sadece ilk fiyat sütununu temizleyip al (64.99 TL/LT gibi)
            $fiyatStr = str_replace(['TL/LT', 'TL/KG', '*', ' '], '', $cols[1]);
            $fiyatStr = str_replace(',', '.', $fiyatStr);
            $fiyat = floatval($fiyatStr);
            
            if ($fiyat > 0) {
                $fiyatlar[$tarih] = $fiyat;
            }
        }
    }

    $tarih_havuzu = array_keys($fiyatlar);
    $m_satirlar = explode("\n", trim($musteri_raw));

    foreach ($m_satirlar as $m_satir) {
        if (empty(trim($m_satir))) continue;
        
        $m_cols = explode(",", $m_satir);
        if (count($m_cols) == 2) {
            $id = trim($m_cols[0]);
            $input_val = strtoupper(trim($m_cols[1]));
            $displayId = (ctype_digit($id) && strlen($id) == 11) ? $id . " BİDON" : $id;

            if (strpos($input_val, 'TL') !== false) {
                $hedef_tutar = floatval(str_replace('TL', '', $input_val));
            } else {
                $ortalama = array_sum($fiyatlar) / count($fiyatlar);
                $hedef_tutar = floatval(str_replace('L', '', $input_val)) * $ortalama;
            }

            $kalan_tutar = $hedef_tutar;
            shuffle($tarih_havuzu);

            foreach ($tarih_havuzu as $t) {
                if ($kalan_tutar <= 1.0) break; // TUTAR BİTTİĞİ AN DUR!

                $fiyat = $fiyatlar[$t];
                
                // Eğer kalan tutar 9850'den küçükse (Örn: 1000 TL), tek fiş yap
                if ($kalan_tutar <= 9850) {
                    $mevcut_tutar = $kalan_tutar;
                } else {
                    // Büyük tutarları parçala (Karma limitler)
                    $sans = rand(1, 100);
                    $min = ($sans <= 30) ? 3500 : 7000;
                    $max = ($sans <= 30) ? 5500 : 9850;
                    $mevcut_tutar = rand($min, min($max, $kalan_tutar));
                    
                    if (($kalan_tutar - $mevcut_tutar) < 3000) {
                        $mevcut_tutar = $kalan_tutar;
                    }
                }

                $amt = $mevcut_tutar / $fiyat;
                $toplam = round($mevcut_tutar, 2);
                $matrah = round($toplam / 1.20, 2);
                $kdv = round($toplam - $matrah, 2);

                $sonuclar[] = [
                    'tarih' => $t,
                    'id' => $displayId,
                    'litre' => number_format($amt, 3, '.', ''),
                    'fiyat' => number_format($fiyat, 2, '.', ''),
                    'matrah' => number_format($matrah, 2, '.', ''),
                    'kdv' => number_format($kdv, 2, '.', ''),
                    'toplam' => number_format($toplam, 2, '.', '')
                ];

                $kalan_tutar -= $toplam;
            }
        }
    }
    usort($sonuclar, function($a, $b) { return strtotime($a['tarih']) - strtotime($b['tarih']); });
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arasaka v24.9 - Final Fix</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: #fff; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #1e293b; padding: 25px; border-radius: 10px; border: 1px solid #334155; }
        textarea { width: 100%; height: 120px; background: #000; color: #fcee0a; border: 1px solid #334155; padding: 10px; margin-bottom: 10px; font-family: monospace; }
        button { width: 100%; padding: 15px; background: #fcee0a; color: #000; font-weight: bold; border: none; cursor: pointer; border-radius: 5px; }
        .copy-box { background: #000; color: #00f3ff; padding: 15px; border: 1px dashed #fcee0a; margin-top: 20px; white-space: pre; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color:#fcee0a; text-align:center;">Arasaka v24.9 (Hata Giderildi)</h2>
        <form method="POST">
            <label>Fiyat Listesini Yapıştır:</label>
            <textarea name="fiyat_listesi"><?php echo $_POST['fiyat_listesi'] ?? ''; ?></textarea>
            <label>Plaka ve Tutar (Örn: 35ABC123,1000TL):</label>
            <textarea name="musteri_listesi"><?php echo $_POST['musteri_listesi'] ?? ''; ?></textarea>
            <button type="submit">HESAPLA</button>
        </form>

        <?php if (!empty($sonuclar)): ?>
        <div class="copy-box"><?php 
            foreach($sonuclar as $s) {
                echo "{$s['tarih']}\t{$s['id']}\t{$s['litre']}\t{$s['fiyat']}\t{$s['matrah']}\t{$s['kdv']}\t{$s['toplam']}\n";
            }
        ?></div>
        <p style="text-align:center; font-size:12px; color:#94a3b8; margin-top:10px;">Toplam <?php echo count($sonuclar); ?> fiş oluşturuldu.</p>
        <?php endif; ?>
    </div>
</body>
</html>
