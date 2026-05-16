<?php
// 1. HATA RAPORLAMA VE GÜVENLİK
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'header.php';
require_once 'baglan.php';

if (!isset($db)) {
    die("<div style='margin-top:100px; color:white; background:red; padding:20px;'>Hata: Veritabanı bağlantısı kurulamadı!</div>");
}

// 2. KATEGORİ VE SAYFALAMA AYARLARI
$type = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
if ($sayfa < 1)
    $sayfa = 1;

$limit = 30; // Bir sayfada kaç tane gözüksün (6'nın katı iyidir abi)
$offset = ($sayfa - 1) * $limit;

// Başlık Belirleme
$titles = [1 => "PC GAMES", 2 => "MOVIES", 3 => "SOFTWARE", 4 => "NSFW (+18)"];
$current_title = isset($titles[$type]) ? $titles[$type] : "ARCHIVE";

// 3. VERİLERİ ÇEKME
try {
    // Toplam Sayı
    $toplam_sorgu = $db->prepare("SELECT COUNT(*) FROM contents WHERE content_type = ? AND content_aktiflik = 1");
    $toplam_sorgu->execute([$type]);
    $toplam_icerik = $toplam_sorgu->fetchColumn();
    $toplam_sayfa = ceil($toplam_icerik / $limit);

    // İçerikler
    $sorgu = $db->prepare("SELECT * FROM contents WHERE content_type = ? AND content_aktiflik = 1 ORDER BY id DESC LIMIT $limit OFFSET $offset");
    $sorgu->execute([$type]);
    $icerikler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
?>

<style>
    /* index.php'deki Stilini Koruyoruz */
    .oniklotho-main {
        padding: 110px 30px 50px 30px;
        background: #0a101d;
        min-height: 100vh;
    }

    .side-title {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        border-left: 4px solid #ff55a5;
        padding-left: 15px;
        text-transform: uppercase;
        margin-bottom: 30px;
    }

    /* Kartların Taşmasını Önleyen Ayar */
    .card-item {
        background: #151f30;
        border-radius: 10px;
        margin-bottom: 25px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .card-item:hover {
        border-color: #ff55a5;
        transform: translateY(-5px);
    }

    .card-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .card-body {
        padding: 12px;
        flex-grow: 1;
        text-align: center;
    }

    .card-name {
        font-size: 13px;
        color: #fff;
        font-weight: 500;
        height: 36px;
        overflow: hidden;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .card-meta {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #555;
        font-weight: bold;
    }

    /* NSFW Blur Efekti (id=4 ise çalışır) */
    <?php if ($type == 4): ?>
        .card-img {
            filter: blur(25px);
            transition: 0.4s;
        }

        .card-item:hover .card-img {
            filter: blur(0);
        }

    <?php endif; ?>

    /* Sayfalama */
    .pagin-wrap {
        display: flex;
        justify-content: center;
        list-style: none;
        gap: 8px;
        padding-top: 30px;
    }

    .pagin-wrap a {
        background: #151f30;
        color: #fff;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        border: 1px solid #333;
        font-size: 12px;
    }

    .pagin-wrap .active a {
        background: #ff55a5;
        border-color: #ff55a5;
    }
</style>

<div class="oniklotho-main">
    <div class="container">

        <div class="row">
            <div class="col-12">
                <h2 class="side-title"><?php echo $current_title; ?></h2>
            </div>
        </div>

        <div class="row">
            <?php if (empty($icerikler)): ?>
                <div class="col-12 text-center" style="color:#444; padding:100px 0;">Bu kategoride henüz içerik yok abi.
                </div>
            <?php else: ?>
                <?php foreach ($icerikler as $item): ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="card-item"
                            style="background: #151f30; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05); overflow:hidden;">

                            <?php
                            // Link Yapısını index.php ile BİREBİR AYNI yaptık abi
                            $slug = isset($item['content_seourl']) ? $item['content_seourl'] : $item['id'];

                            // Tarih Hatası (1970) Fix: Eğer sütun yoksa hata vermez
                            $tarih = "";
                            if (isset($item['content_date']) && $item['content_date'] != null) {
                                $tarih = date('Y', strtotime($item['content_date']));
                            }
                            ?>

                            <a href="content/<?php echo $slug; ?>">
                                <img src="admin/assets/all_contents/<?php echo $item['content_kapakimg']; ?>"
                                    style="width:100%; height:180px; object-fit:cover;">
                            </a>

                            <div style="padding:12px; text-align:center;">
                                <h3
                                    style="font-size: 13px; color: #fff; font-weight: 500; height: 36px; overflow: hidden; margin-bottom: 8px;">
                                    <a href="content/<?php echo $slug; ?>" style="color:inherit; text-decoration:none;">
                                        <?php echo mb_substr($item['content_name'], 0, 35); ?>
                                    </a>
                                </h3>
                                <div style="display:flex; justify-content:space-between; font-size:10px;">
                                    <span
                                        style="color:#ff55a5; font-weight:bold;"><?php echo (isset($item['content_size']) ? $item['content_size'] : 'FREE'); ?></span>
                                    <span style="color:#555;"><?php echo $tarih; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($toplam_sayfa > 1): ?>
            <ul class="pagin-wrap">
                <?php
                $aralik = 2; // Aktif sayfanın sağında ve solunda kaç sayı görünsün?
            
                // 1. ÖNCEKİ SAYFA (Geri butonu)
                if ($sayfa > 1): ?>
                    <li><a href="category_all.php?id=<?php echo $type; ?>&sayfa=<?php echo $sayfa - 1; ?>">&laquo;</a></li>
                <?php endif;

                // 2. SAYFA NUMARALARI DÖNGÜSÜ
                for ($i = 1; $i <= $toplam_sayfa; $i++):
                    // İlk sayfayı, son sayfayı ve aktif sayfanın çevresini gösteriyoruz
                    if ($i == 1 || $i == $toplam_sayfa || ($i >= $sayfa - $aralik && $i <= $sayfa + $aralik)):
                        ?>
                        <li class="<?php echo ($i == $sayfa) ? 'active' : ''; ?>">
                            <a href="category_all.php?id=<?php echo $type; ?>&sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php
                        // Sayılar arasında boşluk varsa "..." koyuyoruz
                    elseif ($i == $sayfa - $aralik - 1 || $i == $sayfa + $aralik + 1):
                        echo "<li style='color:#444; padding:6px 10px;'>...</li>";
                    endif;
                endfor;

                // 3. SONRAKİ SAYFA (İleri butonu)
                if ($sayfa < $toplam_sayfa): ?>
                    <li><a href="category_all.php?id=<?php echo $type; ?>&sayfa=<?php echo $sayfa + 1; ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'footer.php'; ?>