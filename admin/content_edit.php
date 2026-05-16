<?php
require_once '../baglan.php'; // Veritabanı bağlantısı

// 1. Gelen ID'ye göre mevcut veriyi çekelim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: contents.php");
    exit;
}

$id = $_GET['id'];
$sorgu = $db->prepare("SELECT * FROM contents WHERE id = ?");
$sorgu->execute([$id]);
$detay = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$detay) {
    header("Location: contents.php");
    exit;
}

// 2. Güncelleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = 'assets/all_contents/';

    // Resim Güncelleme Fonksiyonu
    function resimGuncelle($file, $dir, $eskiResim) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = uniqid('content_') . "." . $ext;
            if (move_uploaded_file($file['tmp_name'], $dir . $newName)) {
                if (!empty($eskiResim) && file_exists($dir . $eskiResim)) {
                    @unlink($dir . $eskiResim);
                }
                return $newName;
            }
        }
        return $eskiResim;
    }

    $baslik = $_POST['content_name'];
    $find = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#');
    $replace = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp');
    $seourl = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', str_replace($find, $replace, $baslik))));

    // Resimleri İşle (Kapak + 7 Galeri Resmi)
    $kapak = resimGuncelle($_FILES['content_kapakimg'], $uploadDir, $detay['content_kapakimg']);
    $imgs = [];
    for($i=1; $i<=7; $i++) {
        $imgs[$i] = resimGuncelle($_FILES['content_img'.$i], $uploadDir, $detay['content_img'.$i]);
    }

    // Veritabanı Güncelleme (UPDATE)
    $guncelle = $db->prepare("UPDATE contents SET 
        content_type = :type,
        content_name = :name,
        content_seourl = :seourl,
        content_description = :description,
        content_metadescription = :meta,
        content_kapakimg = :kapak,
        content_img1 = :img1, content_img2 = :img2, content_img3 = :img3, 
        content_img4 = :img4, content_img5 = :img5, content_img6 = :img6, content_img7 = :img7,
        content_videourl = :video,
        content_link1 = :link1,
        content_link2 = :link2,
        content_language = :lang,
        content_category = :cat,
        content_crack = :crack,
        content_platform = :plat,
        content_size = :size,
        content_aktiflik = :aktif 
        WHERE id = :id");

    $sonuc = $guncelle->execute([
        ':type'        => $_POST['content_type'],
        ':name'        => $baslik,
        ':seourl'      => $seourl,
        ':description' => $_POST['content_description'],
        ':meta'        => $_POST['content_metadescription'],
        ':kapak'       => $kapak,
        ':img1' => $imgs[1], ':img2' => $imgs[2], ':img3' => $imgs[3], 
        ':img4' => $imgs[4], ':img5' => $imgs[5], ':img6' => $imgs[6], ':img7' => $imgs[7],
        ':video'       => $_POST['content_videourl'],
        ':link1'       => $_POST['content_link1'],
        ':link2'       => $_POST['content_link2'],
        ':lang'        => $_POST['content_language'],
        ':cat'         => isset($_POST['content_category']) ? implode(',', $_POST['content_category']) : '',
        ':crack'       => $_POST['content_crack'],
        ':plat'        => $_POST['content_platform'],
        ':size'        => $_POST['content_size'],
        ':aktif'       => $_POST['content_aktiflik'],
        ':id'          => $id
    ]);

    if ($sonuc) {
        header("Location: contents.php?durum=guncellendi");
        exit;
    }
}

require_once 'header.php'; 
?>

<main class="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="main__title"><h2>İçerik Düzenle: <?php echo $detay['content_name']; ?></h2></div>
            </div>

            <div class="col-12">
                <form action="" method="POST" enctype="multipart/form-data" class="form">
                    <div class="row">
                        <div class="col-12 col-md-5 form__cover">
                            <div class="form__group">
                                <label style="color:#ff55a5;">İÇERİK TÜRÜ</label>
                                <select name="content_type" id="content_type" class="form__input" onchange="toggleFields()">
                                    <option value="1" <?php echo $detay['content_type'] == 1 ? 'selected' : ''; ?>>Oyun</option>
                                    <option value="2" <?php echo $detay['content_type'] == 2 ? 'selected' : ''; ?>>Film / Dizi</option>
                                    <option value="3" <?php echo $detay['content_type'] == 3 ? 'selected' : ''; ?>>Program</option>
                                    <option value="4" <?php echo $detay['content_type'] == 4 ? 'selected' : ''; ?>>NSFW (+18)</option>
                                </select>
                            </div>
                            <div class="form__img">
                                <label for="form__img-upload">ANA KAPAK (Değiştirmek için tıkla)</label>
                                <input id="form__img-upload" name="content_kapakimg" type="file" accept=".png, .jpg, .jpeg" onchange="previewImage(this, 'kapak-onizleme')">
                                <img id="kapak-onizleme" src="assets/all_contents/<?php echo $detay['content_kapakimg']; ?>" alt=" " style="width:100%; height:auto; display:block; border-radius:16px; margin-top:10px;">
                            </div>
                        </div>

                        <div class="col-12 col-md-7 form__content">
                            <div class="row">
                                <div class="col-12"><div class="form__group">
                                    <input type="text" name="content_name" class="form__input" value="<?php echo $detay['content_name']; ?>" required>
                                </div></div>
                                <div class="col-12"><div class="form__group">
                                    <textarea name="content_description" class="form__textarea"><?php echo $detay['content_description']; ?></textarea>
                                </div></div>
                                <div class="col-12"><div class="form__group">
                                    <input type="text" name="content_videourl" class="form__input" value="<?php echo $detay['content_videourl']; ?>" placeholder="Youtube Video ID">
                                </div></div>
                                
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_link1" class="form__input" value="<?php echo $detay['content_link1']; ?>" placeholder="Link 1">
                                </div></div>
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_link2" class="form__input" value="<?php echo $detay['content_link2']; ?>" placeholder="Link 2">
                                </div></div>

                                <div class="col-12 col-sm-6 extra-fields"><div class="form__group">
                                    <input type="text" name="content_platform" class="form__input" value="<?php echo $detay['content_platform']; ?>" placeholder="Platform">
                                </div></div>
                                <div class="col-12 col-sm-6 extra-fields"><div class="form__group">
                                    <input type="text" name="content_size" class="form__input" value="<?php echo $detay['content_size']; ?>" placeholder="Boyut">
                                </div></div>
                                <div id="game-only" class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_crack" class="form__input" value="<?php echo $detay['content_crack']; ?>" placeholder="Crack Grubu">
                                </div></div>
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_language" class="form__input" value="<?php echo $detay['content_language']; ?>" placeholder="Dil">
                                </div></div>

                                <div class="col-12">
                                    <div class="form__group">
                                        <?php $mevcutKategoriler = explode(',', $detay['content_category']); ?>
                                        <select class="js-example-basic-multiple" name="content_category[]" id="genre" multiple="multiple">
                                            <option value="Action" <?php echo in_array('Action', $mevcutKategoriler) ? 'selected' : ''; ?>>Action</option>
                                            <option value="Horror" <?php echo in_array('Horror', $mevcutKategoriler) ? 'selected' : ''; ?>>Horror</option>
                                            <option value="RPG" <?php echo in_array('RPG', $mevcutKategoriler) ? 'selected' : ''; ?>>RPG</option>
                                            <option value="Software" <?php echo in_array('Software', $mevcutKategoriler) ? 'selected' : ''; ?>>Software</option>
                                        </select>
                                    </div>
                                </div>

                                <?php for($i=1; $i<=7; $i++): $imgKey = "content_img$i"; ?>
                                <div class="col-6 col-sm-3 <?php echo ($i > 3) ? 'nsfw-gallery' : ''; ?>">
                                    <div class="form__gallery" style="position:relative; overflow:hidden; min-height:80px; background:#151f30; border:1px dashed #ff55a5; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                                        <label for="gal_<?php echo $i; ?>" style="cursor:pointer; font-size:10px; z-index:2; opacity:0.5;">IMG <?php echo $i; ?></label>
                                        <input name="content_img<?php echo $i; ?>" id="gal_<?php echo $i; ?>" type="file" accept="image/*" style="position:absolute; width:100%; height:100%; opacity:0; z-index:3;" onchange="previewGallery(this, 'p_<?php echo $i; ?>')">
                                        <img id="p_<?php echo $i; ?>" src="<?php echo !empty($detay[$imgKey]) ? 'assets/all_contents/'.$detay[$imgKey] : '#'; ?>" alt="" style="display:<?php echo !empty($detay[$imgKey]) ? 'block' : 'none'; ?>; width:100%; height:100%; object-fit:cover; position:absolute; z-index:1;">
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <ul class="form__radio">
                                <li><span>Aktiflik:</span></li>
                                <li><input id="type1" type="radio" name="content_aktiflik" value="1" <?php echo ($detay['content_aktiflik'] == 1) ? 'checked' : ''; ?>><label for="type1">Açık</label></li>
                                <li><input id="type2" type="radio" name="content_aktiflik" value="0" <?php echo ($detay['content_aktiflik'] == 0) ? 'checked' : ''; ?>><label for="type2">Kapalı</label></li>
                            </ul>
                            <button type="submit" class="form__btn" style="width: 100%; background:#2f80ed; border:none;">DEĞİŞİKLİKLERİ KAYDET</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    function toggleFields() {
        var type = document.getElementById("content_type").value;
        var gameOnly = document.getElementById("game-only");
        var extras = document.querySelectorAll(".extra-fields");
        var nsfwImgs = document.querySelectorAll(".nsfw-gallery");

        if (type == "1") {
            gameOnly.style.display = "block";
            extras.forEach(e => e.style.display = "block");
            nsfwImgs.forEach(e => e.style.display = "none");
        } else if (type == "4") {
            gameOnly.style.display = "none";
            extras.forEach(e => e.style.display = "none");
            nsfwImgs.forEach(e => e.style.display = "block");
        } else {
            gameOnly.style.display = "none";
            extras.forEach(e => e.style.display = "none");
            nsfwImgs.forEach(e => e.style.display = "none");
        }
    }

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGallery(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { 
                preview.src = e.target.result; 
                preview.style.display = 'block'; 
                input.previousElementSibling.style.opacity = '0.1';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    window.onload = toggleFields;
</script>

<?php require_once 'footer.php'; ?>