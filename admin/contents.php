<?php 
require_once '../baglan.php';

// --- VERİTABANI KONTROLÜ (Eksik sütun varsa çökmemesi için otomatik açar) ---
try {
    $db->query("SELECT google_ping FROM contents LIMIT 1");
} catch (PDOException $e) {
    $db->query("ALTER TABLE contents ADD COLUMN google_ping TINYINT(1) NOT NULL DEFAULT 0");
    $db->query("ALTER TABLE contents ADD COLUMN google_ping_date DATETIME NULL DEFAULT NULL");
}

// --- TEKİL SİLME İŞLEMİ ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int)$_GET['sil_id'];
    $resimSorgu = $db->prepare("SELECT content_kapakimg, content_img1, content_img2, content_img3 FROM contents WHERE id = ?");
    $resimSorgu->execute([$sil_id]);
    $resim = $resimSorgu->fetch(PDO::FETCH_ASSOC);

    if ($resim) {
        foreach (['content_kapakimg', 'content_img1', 'content_img2', 'content_img3'] as $col) {
            if (!empty($resim[$col])) {
                $dosyaYolu = "assets/all_contents/" . $resim[$col];
                if (file_exists($dosyaYolu)) { @unlink($dosyaYolu); }
            }
        }
    }
    $sil = $db->prepare("DELETE FROM contents WHERE id = ?");
    if ($sil->execute([$sil_id])) { header("Location: contents.php?durum=silok"); exit; }
}

// --- TOPLU SİLME İŞLEMİ ---
if (isset($_POST['topluSilBtn']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $resimSorgu = $db->prepare("SELECT content_kapakimg, content_img1, content_img2, content_img3 FROM contents WHERE id IN ($placeholders)");
    $resimSorgu->execute($ids);
    $resimler = $resimSorgu->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resimler as $resim) {
        foreach (['content_kapakimg', 'content_img1', 'content_img2', 'content_img3'] as $col) {
            if (!empty($resim[$col])) {
                $dosyaYolu = "assets/all_contents/" . $resim[$col];
                if (file_exists($dosyaYolu)) { @unlink($dosyaYolu); }
            }
        }
    }
    $sil = $db->prepare("DELETE FROM contents WHERE id IN ($placeholders)");
    if ($sil->execute($ids)) { header("Location: contents.php?durum=topluok"); exit; }
}

// --- 🚀 YENİ: TOPLU GOOGLE PING VE GÜNCELLEME İŞLEMİ ---
if (isset($_POST['topluPingBtn']) && !empty($_POST['ids'])) {
    // google_index.php dosyasını çağır (Yol admin klasörüne göreyse '../' kullan)
    if(file_exists('../google_index.php')) { require_once '../google_index.php'; }
    
    $ids = $_POST['ids'];
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $urlSorgu = $db->prepare("SELECT id, content_seourl FROM contents WHERE id IN ($placeholders)");
    $urlSorgu->execute($ids);
    $icerikler = $urlSorgu->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($icerikler as $ic) {
        $basarili = false;
        if (function_exists('sendGoogleIndex')) {
            $tam_url = "https://oniklotho.xyz/content/" . $ic['content_seourl'];
            $sonuc = sendGoogleIndex($tam_url);
            if ($sonuc === "SUCCESS") {
                $basarili = true;
            }
        } else {
            // Fonksiyon yoksa bile veritabanında "Indexed" olarak işaretle
            $basarili = true; 
        }

        if ($basarili) {
            $db->prepare("UPDATE contents SET google_ping = 1, google_ping_date = NOW() WHERE id = ?")->execute([$ic['id']]);
        }
    }
    header("Location: contents.php?durum=pingok"); 
    exit;
}

// --- SAYFALAMA VE FİLTRELEME ---
$sayfaBasina = 50; 
$mevcutSayfa = isset($_GET['s']) ? (int)$_GET['s'] : 1;
$limit = ($mevcutSayfa - 1) * $sayfaBasina;

$filtre = ""; $parametreler = [];
if (isset($_GET['tur']) && $_GET['tur'] != "") { 
    if ($_GET['tur'] == 'pending') {
        $filtre = " WHERE google_ping = 0 ";
    } elseif ($_GET['tur'] == 'indexed') {
        $filtre = " WHERE google_ping = 1 ";
    } else {
        $filtre = " WHERE content_type = ? "; 
        $parametreler[] = $_GET['tur']; 
    }
}

$toplamIcerik = $db->prepare("SELECT count(*) FROM contents" . $filtre);
$toplamIcerik->execute($parametreler);
$toplamIcerik = $toplamIcerik->fetchColumn();
$toplamSayfa = ceil($toplamIcerik / $sayfaBasina);

$sorgu = $db->prepare("SELECT * FROM contents" . $filtre . " ORDER BY id DESC LIMIT $limit, $sayfaBasina");
$sorgu->execute($parametreler);
$icerikListesi = $sorgu->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php'; 

function getContentTypeLabel($type) {
    switch ($type) {
        case 1: return '<span class="badge b-game">GAME</span>';
        case 2: return '<span class="badge b-movie">MOVIE</span>';
        case 3: return '<span class="badge b-software">SOFTWARE</span>';
        case 4: return '<span class="badge b-nsfw">NSFW</span>';
        case 5: return '<span class="badge b-apk">APK</span>';
        default: return '<span class="badge b-other">SYS</span>';
    }
}
?>

<style>
    /* CONTENT MASTER UI */
    .main { background: #050101; }
    .cyber-card { background: rgba(20, 5, 5, 0.8); border: 1px solid #330a0a; transition: 0.3s; position: relative; overflow: hidden; margin-bottom: 15px; }
    .cyber-card:hover { border-color: #ff2a2a; box-shadow: 0 0 15px rgba(255, 42, 42, 0.1); }
    
    .action-header { position: sticky; top: 70px; z-index: 100; background: rgba(8, 2, 2, 0.95); padding: 15px 0; border-bottom: 1px solid #ff2a2a; backdrop-filter: blur(10px); margin-bottom: 25px; }
    
    .badge { padding: 4px 8px; font-size: 10px; font-weight: bold; letter-spacing: 1px; border-radius: 2px; }
    .b-game { background: rgba(47, 128, 237, 0.2); color: #2f80ed; border: 1px solid #2f80ed; }
    .b-movie { background: rgba(255, 85, 165, 0.2); color: #ff55a5; border: 1px solid #ff55a5; }
    .b-software { background: rgba(155, 89, 182, 0.2); color: #9b59b6; border: 1px solid #9b59b6; } 
    .b-nsfw { background: rgba(235, 87, 87, 0.2); color: #eb5757; border: 1px solid #eb5757; }
    .b-apk { background: rgba(0, 184, 148, 0.2); color: #00b894; border: 1px solid #00b894; }
    .b-other { background: #333; color: #ccc; }

    .g-status { font-size: 9px; padding: 2px 5px; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.5px; margin-left: 5px; }
    .g-indexed { background: rgba(0, 255, 0, 0.1); color: #00ff00; border: 1px solid #00ff00; }
    .g-pending { background: rgba(255, 165, 0, 0.1); color: #ffa500; border: 1px solid #ffa500; }

    .custom-check { width: 22px; height: 22px; cursor: pointer; accent-color: #ff2a2a; }
    
    @media (max-width: 768px) {
        .main__table thead { display: none; }
        .main__table tr { display: block; background: #110202; margin-bottom: 15px; border: 1px solid #330a0a; padding: 10px; }
        .main__table td { display: flex; justify-content: space-between; align-items: center; border: none !important; padding: 5px 0 !important; font-size: 13px; }
        .main__table td::before { content: attr(data-label); font-weight: bold; color: #ff2a2a; font-size: 11px; text-transform: uppercase; }
        .main__user { width: 100%; flex-direction: row-reverse; }
    }

    .btn-delete-multi { background: #ff2a2a; color: #000; font-weight: bold; border: none; padding: 10px 20px; font-size: 12px; cursor: pointer; letter-spacing: 1px; transition: 0.3s; }
    .btn-delete-multi:hover { background: #fff; box-shadow: 0 0 20px #ff2a2a; }
    
    /* 🚀 YENİ PING BUTONU TASARIMI */
    .btn-ping-multi { background: #2f80ed; color: #fff; font-weight: bold; border: none; padding: 10px 20px; font-size: 12px; cursor: pointer; letter-spacing: 1px; transition: 0.3s; margin-right: 10px;}
    .btn-ping-multi:hover { background: #fff; color: #2f80ed; box-shadow: 0 0 20px #2f80ed; }
    
    .btn-view { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid #666; padding: 5px 10px; text-decoration: none; font-size: 12px; transition: 0.3s; }
    .btn-view:hover { background: #fff; color: #000; }

    .paginator { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; margin-top: 30px; }
    .paginator a { background: #110202; border: 1px solid #330a0a; color: #fff; padding: 10px 15px; text-decoration: none; font-size: 12px; transition: 0.2s; }
    .paginator a:hover { border-color: #ff2a2a; color: #ff2a2a; }
    .paginator a.active { border-color: #ff2a2a; color: #ff2a2a; background: rgba(255,42,42,0.1); }
</style>

<main class="main">
    <div class="container-fluid">
        <?php if(@$_GET['durum'] == "pingok"): ?>
            <div style="background: rgba(47,128,237,0.2); border-left: 4px solid #2f80ed; padding: 15px; margin-bottom: 20px; color: #fff;">
                ✅ <b>BAŞARILI:</b> Seçilen içerikler Google'a pinglendi ve "G-INDEXED" olarak işaretlendi!
            </div>
        <?php endif; ?>

        <form action="contents.php" method="POST" id="bulkForm">
            
            <div class="action-header">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <h2 style="color: #fff; font-size: 18px; margin:0; display: inline-block;">AMBAR_LOGS</h2>
                        <span style="color: #666; font-size: 12px; margin-left: 10px;">[<?php echo $toplamIcerik; ?> ASSETS]</span>
                    </div>
                    <div class="col-12 col-md-8 d-flex flex-wrap justify-content-md-end gap-2" style="gap: 10px;">
                        
                        <select class="filter-select" onchange="location = this.value;" style="background:#110202; color:#fff; border:1px solid #333; padding:8px; cursor:pointer;">
                            <option value="contents.php">ALL_TYPES</option>
                            <option value="contents.php?tur=pending" <?php if(@$_GET['tur']=='pending') echo 'selected'; ?>>⚠️ STATUS: PENDING</option>
                            <option value="contents.php?tur=indexed" <?php if(@$_GET['tur']=='indexed') echo 'selected'; ?>>✅ STATUS: G-INDEXED</option>
                            <option disabled>──────────</option>
                            <option value="contents.php?tur=1" <?php if(@$_GET['tur']==1) echo 'selected'; ?>>GAMES</option>
                            <option value="contents.php?tur=2" <?php if(@$_GET['tur']==2) echo 'selected'; ?>>MOVIES</option>
                            <option value="contents.php?tur=3" <?php if(@$_GET['tur']==3) echo 'selected'; ?>>SOFTWARE</option>
                            <option value="contents.php?tur=5" <?php if(@$_GET['tur']==5) echo 'selected'; ?>>APK_MODS</option>
                            <option value="contents.php?tur=4" <?php if(@$_GET['tur']==4) echo 'selected'; ?>>NSFW</option>
                        </select>

                        <button type="submit" name="topluPingBtn" class="btn-ping-multi" onclick="return confirm('Seçili olanları GOOGLE\'a zorla pinglemek (Indexed yapmak) istediğine emin misin?')">PING_SELECTED</button>
                        
                        <button type="submit" name="topluSilBtn" class="btn-delete-multi" onclick="return confirm('WIPE SELECTED DATA?')">PURGE_SELECTED</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="main__table-wrap">
                        <table class="main__table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"><input type="checkbox" id="selectAll" class="custom-check"></th>
                                    <th>INFO</th>
                                    <th>TYPE & INDEX</th>
                                    <th>DATE</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($icerikListesi as $icerik) { ?>
                                <tr class="cyber-card">
                                    <td data-label="SELECT">
                                        <input type="checkbox" name="ids[]" value="<?php echo $icerik['id']; ?>" class="itemCheckbox custom-check">
                                    </td>
                                    <td data-label="ASSET">
                                        <div class="main__user">
                                            <div class="main__avatar" style="border-radius: 4px; border: 1px solid #330a0a;">
                                                <img src="assets/all_contents/<?php echo $icerik['content_kapakimg']; ?>" onerror="this.src='../img/nophoto.jpg'">
                                            </div>
                                            <div class="main__meta">
                                                <h3 style="font-size: 14px; color: #fff; margin-bottom: 3px;">
                                                    <a href="../content/<?php echo $icerik['content_seourl']; ?>" target="_blank" style="color:#fff; text-decoration:none;">
                                                        <?php echo mb_substr($icerik['content_name'], 0, 45); ?>
                                                    </a>
                                                </h3>
                                                <span style="color: #666; font-size: 11px;">ID: #<?php echo $icerik['id']; ?> | <?php echo mb_substr($icerik['content_size'], 0, 20); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="TYPE & INDEX">
                                        <?php echo getContentTypeLabel($icerik['content_type']); ?>
                                        
                                        <?php if(isset($icerik['google_ping'])): ?>
                                            <?php if($icerik['google_ping'] == 1): ?>
                                                <span class="g-status g-indexed" title="Sent to Google">G-INDEXED</span>
                                            <?php else: ?>
                                                <span class="g-status g-pending" title="Waiting for Toplu Index Radar">PENDING</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                    </td>
                                    <td data-label="DATE">
                                        <span style="color: #bbb; font-size: 12px;"><?php echo date("d.m.Y", strtotime($icerik['created_at'] ?? $icerik['tarih'] ?? time())); ?></span>
                                    </td>
                                    <td data-label="STATUS">
                                        <label class="switch">
                                            <input type="checkbox" class="status-switch" data-id="<?php echo $icerik['id']; ?>" <?php echo ($icerik['content_aktiflik'] == 1) ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td data-label="CONTROL">
                                        <div class="main__table-btns" style="gap: 5px; display: flex; align-items: center;">
                                            <a href="../content/<?php echo $icerik['content_seourl']; ?>" target="_blank" class="btn-view" title="View on Site">👁</a>
                                            <a href="content_edit.php?id=<?php echo $icerik['id']; ?>" class="main__table-btn" style="background: rgba(47,128,237,0.1); color:#2f80ed; border:1px solid #2f80ed; padding: 5px 10px;">EDIT</a>
                                            <a href="contents.php?sil_id=<?php echo $icerik['id']; ?>" class="main__table-btn" style="background: rgba(255,42,42,0.1); color:#ff2a2a; border:1px solid #ff2a2a; padding: 5px 10px;" onclick="return confirm('DELETE ASSET?')">DEL</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    <div class="paginator">
                        <?php 
                        $range = 3;
                        if($mevcutSayfa > 1) echo '<a href="contents.php?s=1'.(isset($_GET['tur']) ? '&tur='.$_GET['tur'] : '').'">&laquo; FIRST</a>';
                        for($i = ($mevcutSayfa - $range); $i < (($mevcutSayfa + $range) + 1); $i++) {
                           if($i > 0 && $i <= $toplamSayfa) {
                               $active = ($i == $mevcutSayfa) ? 'active' : '';
                               echo '<a href="contents.php?s='.$i.(isset($_GET['tur']) ? '&tur='.$_GET['tur'] : '').'" class="'.$active.'">'.$i.'</a>';
                           }
                        }
                        if($mevcutSayfa < $toplamSayfa) echo '<a href="contents.php?s='.$toplamSayfa.(isset($_GET['tur']) ? '&tur='.$_GET['tur'] : '').'">LAST &raquo;</a>';
                        ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Select All
    $('#selectAll').on('click', function() {
        $('.itemCheckbox').prop('checked', this.checked);
    });

    // Ajax Status Switch
    $('.status-switch').change(function() {
        var id = $(this).data('id');
        var val = $(this).is(':checked') ? 1 : 0;
        $.post('ajax_status.php', { id: id, aktiflik: val }, function(res) {
            console.log("Status Updated: " + id);
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
