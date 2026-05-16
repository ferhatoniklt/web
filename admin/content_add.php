<?php
require_once 'header.php';
require_once '../baglan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = 'assets/all_contents/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function resimYukle($file, $dir) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = uniqid('content_') . "." . $ext;
            $targetPath = $dir . $newName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $newName;
            }
        }
        return null; 
    }

    // SEO URL Oluşturucu
    $baslik = $_POST['content_name'];
    $find = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#');
    $replace = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp');
    $seourl = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', str_replace($find, $replace, $baslik))));

    // Resimleri Yükle (7 Resim + Kapak)
    $kapak = resimYukle($_FILES['content_kapakimg'], $uploadDir);
    $imgs = [];
    for($i=1; $i<=7; $i++) {
        $imgs[$i] = resimYukle($_FILES['content_img'.$i], $uploadDir);
    }

    // Veritabanına Kayıt
    $sorgu = $db->prepare("INSERT INTO contents SET 
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
        content_aktiflik = :aktif");

    $ekle = $sorgu->execute([
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
        ':aktif'       => $_POST['content_aktiflik']
    ]);

    if ($ekle) {
        echo "<script>alert('İçerik başarıyla eklendi!'); window.location.href='content_add.php';</script>";
    } else {
        echo "<script>alert('Hata oluştu!');</script>";
    }
}
?>

<main class="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="main__title"><h2>Yeni İçerik Ekle (oniklotho.xyz)</h2></div>
            </div>

            <div class="col-12">
                <form action="" method="POST" enctype="multipart/form-data" class="form">
                    <div class="row">
                        <div class="col-12 col-md-5 form__cover">
                            <div class="form__group">
                                <label style="color: #ff55a5; font-weight: bold;">İÇERİK TÜRÜ</label>
                                <select name="content_type" id="content_type" class="form__input" onchange="toggleFields()">
                                    <option value="1">Oyun</option>
                                    <option value="2">Film / Dizi</option>
                                    <option value="3">Program</option>
                                    <option value="4">NSFW (+18)</option>
                                </select>
                            </div>
                            <div class="form__img">
                                <label for="form__img-upload">ANA KAPAK RESMİ</label>
                                <input id="form__img-upload" name="content_kapakimg" type="file" accept=".png, .jpg, .jpeg" onchange="previewImage(this, 'kapak-onizleme')">
                                <img id="kapak-onizleme" src="#" alt=" " style="width:100%; height:auto; display:none; border-radius:16px; margin-top:10px;">
                            </div>
                        </div>

                        <div class="col-12 col-md-7 form__content">
                            <div class="row">
                                <div class="col-12"><div class="form__group">
                                    <input type="text" name="content_name" class="form__input" placeholder="İÇERİK ADI" required>
                                </div></div>
                                <div class="col-12"><div class="form__group">
                                    <textarea name="content_description" class="form__textarea" placeholder="AÇIKLAMA / SİSTEM GEREKSİNİMLERİ"></textarea>
                                </div></div>
                                <div class="col-12"><div class="form__group">
                                    <input type="text" name="content_videourl" class="form__input" placeholder="YOUTUBE FRAGMAN (Sadece ID: dQw4w9WgXcQ)">
                                </div></div>
                                
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_link1" class="form__input" placeholder="İNDİRME LİNKİ 1">
                                </div></div>
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_link2" class="form__input" placeholder="İNDİRME LİNKİ 2 (Opsiyonel)">
                                </div></div>

                                <div class="col-12 col-sm-6 extra-fields"><div class="form__group">
                                    <input type="text" name="content_platform" class="form__input" placeholder="Platform (Örn: PC, Windows 11)">
                                </div></div>
                                <div class="col-12 col-sm-6 extra-fields"><div class="form__group">
                                    <input type="text" name="content_size" class="form__input" placeholder="Boyut (Örn: 50 GB)">
                                </div></div>
                                <div id="game-only" class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_crack" class="form__input" placeholder="Crack (Örn: RUNE, FitGirl)">
                                </div></div>
                                <div class="col-12 col-sm-6"><div class="form__group">
                                    <input type="text" name="content_language" class="form__input" placeholder="Dil (Örn: TR, EN)">
                                </div></div>

                                <div class="col-12">
                                    <div class="form__group">
                                        <select class="js-example-basic-multiple" name="content_category[]" id="genre" multiple="multiple">
                                            <option value="Action">Action</option>
                                            <option value="Horror">Horror</option>
                                            <option value="Thriller">Thriller</option>
                                            <option value="RPG">RPG</option>
                                            <option value="Software">Software</option>
                                            <option value="Adult">Adult Content</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12"><label style="color:white; margin:10px 0;">EKRAN GÖRÜNTÜLERİ / GALERİ</label></div>
                                <?php for($i=1; $i<=7; $i++): ?>
                                <div class="col-6 col-sm-3 <?php echo ($i > 3) ? 'nsfw-gallery' : ''; ?>">
                                    <div class="form__gallery" style="position:relative; overflow:hidden; min-height:80px; background:#151f30; border:1px dashed #ff55a5; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                                        <label for="gal_<?php echo $i; ?>" style="cursor:pointer; font-size:10px; z-index:2;">IMG <?php echo $i; ?></label>
                                        <input name="content_img<?php echo $i; ?>" id="gal_<?php echo $i; ?>" type="file" accept="image/*" style="position:absolute; width:100%; height:100%; opacity:0; z-index:3;" onchange="previewGallery(this, 'p_<?php echo $i; ?>')">
                                        <img id="p_<?php echo $i; ?>" src="#" alt="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; z-index:1;">
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <ul class="form__radio">
                                <li><span>Aktiflik:</span></li>
                                <li><input id="type1" type="radio" name="content_aktiflik" value="1" checked><label for="type1">Açık</label></li>
                                <li><input id="type2" type="radio" name="content_aktiflik" value="0"><label for="type2">Kapalı</label></li>
                            </ul>
                            <button type="submit" class="form__btn" style="width: 100%; background: #ff55a5; border:none;">İÇERİĞİ YAYINLA</button>
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

        // Oyun seçiliyse (1)
        if (type == "1") {
            gameOnly.style.display = "block";
            extras.forEach(e => e.style.display = "block");
            nsfwImgs.forEach(e => e.style.display = "none");
        } 
        // Film/Dizi seçiliyse (2)
        else if (type == "2") {
            gameOnly.style.display = "none";
            extras.forEach(e => e.style.display = "none");
            nsfwImgs.forEach(e => e.style.display = "none");
        }
        // NSFW seçiliyse (4) - Tüm resimleri göster
        else if (type == "4") {
            gameOnly.style.display = "none";
            extras.forEach(e => e.style.display = "none");
            nsfwImgs.forEach(e => e.style.display = "block");
        }
        else {
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

    // İlk açılışta alanları düzenle
    window.onload = toggleFields;
</script>

<?php require_once 'footer.php'; ?>