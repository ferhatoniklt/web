<?php
// 1. HATA RAPORLAMA VE GÜVENLİK
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'baglan.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['url']) || empty($_GET['url'])) {
    header("Location: index.php");
    exit;
}

$seourl = $_GET['url'];
$sorgu = $db->prepare("SELECT * FROM contents WHERE content_seourl = ? AND content_aktiflik = 1");
$sorgu->execute([$seourl]);
$detay = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$detay) {
    header("Location: index.php");
    exit;
}

require_once 'header.php';

// PHP 8+ Hatalarını önlemek için güvenli fonksiyon
function safe_html($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

$id = $detay['id'];
$type = (int) $detay['content_type'];
$content_name = safe_html($detay['content_name']);

// Kategori İsmi ve Akıllı SEO ALT Kelimeleri Belirleme
$catName = "Content";
$altKapak = "Download";
$altEkranG = "Screenshot";

if ($type == 1) { 
    $catName = "Games"; 
    $altKapak = "Free Game Download"; 
    $altEkranG = "Gameplay Screenshot HQ"; 
} elseif ($type == 2) { 
    $catName = "Movies & TV"; 
    $altKapak = "Watch Full Movie HD"; 
    $altEkranG = "Movie Scene Preview"; 
} elseif ($type == 4) { 
    $catName = "NSFW"; 
    $altKapak = "Adult Gallery"; 
    $altEkranG = "Exclusive Preview Image"; 
} elseif ($type == 5) { 
    $catName = "APK Mods"; 
    $altKapak = "Mod APK Download"; 
    $altEkranG = "App Interface"; 
}

// Sağ Sidebar İçin Önerilen İçerikler (Aynı kategoriden)
$onerilenSorgu = $db->prepare("SELECT * FROM contents WHERE content_type = ? AND id != ? AND content_aktiflik = 1 ORDER BY RAND() LIMIT 5");
$onerilenSorgu->execute([$type, $id]);
$onerilenIcerikler = $onerilenSorgu->fetchAll(PDO::FETCH_ASSOC);

// Yorumları Çek
$yorumSorgu = $db->prepare("SELECT comments.*, users.user_name FROM comments LEFT JOIN users ON comments.user_id = users.user_id WHERE comments.content_id = ? ORDER BY comments.comment_id DESC");
$yorumSorgu->execute([$id]);
$yorumlar = $yorumSorgu->fetchAll(PDO::FETCH_ASSOC);

// ÖRÜMCEK AĞI SORGUSU (Sadece Aynı Kategoriden 8 İçerik)
$rastgele_sorgu = $db->prepare("SELECT content_name, content_seourl, content_kapakimg, content_category FROM contents WHERE content_aktiflik = 1 AND content_type = ? AND id != ? ORDER BY RAND() LIMIT 8");
$rastgele_sorgu->execute([$type, $id]); 
$rastgeleler = $rastgele_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    h2.details__title-fix { color: #fff !important; font-size: 20px; font-weight: 600; margin-bottom: 20px; margin-top: 0; display: block; border-bottom: 1px solid rgba(255, 85, 165, 0.3); padding-bottom: 10px; }
    .seo-breadcrumb { font-size: 12px; color: #888; margin-bottom: 10px; }
    .seo-breadcrumb a { color: #ff55a5; text-decoration: none; }
    .seo-breadcrumb a:hover { text-decoration: underline; }

    .description-content { max-height: 250px; overflow: hidden; position: relative; transition: max-height 0.6s ease; color: #d2d2d2; line-height: 1.8; }
    .description-content.expanded { max-height: 10000px !important; }
    .description-fade { position: absolute; bottom: 0; left: 0; width: 100%; height: 80px; background: linear-gradient(to bottom, transparent, #151f30); pointer-events: none; }
    .description-content.expanded .description-fade { display: none !important; }
    .read-more-btn { display: none; margin-top: 15px; color: #ff55a5; cursor: pointer; font-weight: bold; border: 1px solid #ff55a5; padding: 6px 18px; border-radius: 8px; font-size: 12px; background: transparent; }

    .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 16px; background: #000; }
    .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
    
    .oniklotho-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
    .gallery-img-box { border-radius: 12px; overflow: hidden; border: 2px solid rgba(255, 255, 255, 0.05); cursor: pointer; }
    .gallery-img-box img { width: 100%; height: 130px; object-fit: cover; }

    #imgModal { display: none; position: fixed; z-index: 99999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); justify-content: center; align-items: center; }
    #modalImg { max-width: 90%; max-height: 90%; border: 2px solid #ff55a5; }
    .close-modal { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 40px; cursor: pointer; }

    .comment-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 15px; margin-bottom: 15px; }

    .spider-card { background: #16213e; border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; border: 1px solid rgba(255, 85, 165, 0.1); height: 100%; }
    .spider-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(255, 85, 165, 0.2); border-color: #ff55a5; }

    /* Sunucu Butonları Tasarımı */
    .server-tabs { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
    .server-btn { background: #151f30; color: #bbb; border: 1px solid rgba(255,85,165,0.3); padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold; transition: all 0.3s; }
    .server-btn:hover { background: rgba(255,85,165,0.1); color: #fff; }
    .server-btn.active { background: #ff55a5; color: #fff; border-color: #ff55a5; }
</style>

<div id="imgModal"><span class="close-modal" onclick="closeImage()">&times;</span><img id="modalImg" alt="<?php echo $content_name; ?> Full Screen Image"></div>

<article class="section section--head" style="padding-top: 120px; background: #0a101d;">
    <div class="container">
        
        <div class="row row--grid">
            <div class="col-12 col-xl-8">
                <div class="row row--grid">
                    
                    <div class="col-12 col-sm-4">
                        <img src="admin/assets/all_contents/<?php echo $detay['content_kapakimg'] ?: 'nophoto.jpg'; ?>" 
                             alt="<?php echo $content_name . ' - ' . $altKapak; ?>" 
                             style="width: 100%; border-radius: 16px; border: 1px solid #333;">
                        <a href="#download-section" class="sign__btn" style="width: 100%; margin-top: 20px; background: #ff55a5;">DOWNLOAD NOW</a>
                    </div>

                    <div class="col-12 col-sm-8">
                        <div class="seo-breadcrumb">
                            <a href="./">Home</a> &raquo; 
                            <a href="category_all.php?id=<?php echo $type; ?>"><?php echo $catName; ?></a> &raquo; 
                            <span style="color:#fff;"><?php echo mb_substr($content_name, 0, 30); ?>...</span>
                        </div>

                        <h1 style="color: #fff; font-size: 28px; font-weight: 700; margin-top: 0;"><?php echo $content_name; ?></h1>

                        <div class="article__description">
                            <div id="desc-box" class="description-content">
                                <?php echo nl2br(htmlspecialchars_decode($detay['content_description'] ?? '')); ?>
                                <div id="desc-fade" class="description-fade"></div>
                            </div>
                            <div id="read-more" class="read-more-btn">Read More ↓</div>
                        </div>
                    </div>

                    <?php if (!empty($detay['content_videourl'])): ?>
                    <div class="col-12" style="margin-top: 40px;">
                        <h2 class="details__title-fix"><?php echo ($type == 2) ? "🎬 Official Player & Servers" : "🎮 Official Trailer"; ?></h2>
                        
                        <?php 
                            // IMDB Kodu Ayrıştırma ve Sunucu URL'leri Hazırlama
                            $v = trim($detay['content_videourl']);
                            $is_movie_or_tv = false;
                            $imdb_id = "";
                            $vid_type = "movie";

                            // Sadece Film ve Diziler için IMDB kodunu yakala
                            if ($type == 2 && preg_match('/imdb=(tt\d+)/', $v, $matches)) {
                                $imdb_id = $matches[1];
                                $is_movie_or_tv = true;
                                $vid_type = (strpos($v, '/tv') !== false) ? "tv" : "movie";
                            }
                            
                            $v_id = str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v='], '', $v);
                            $default_src = (filter_var($v, FILTER_VALIDATE_URL)) ? $v : "https://www.youtube.com/embed/" . $v_id;
                        ?>

                        <?php if ($is_movie_or_tv): ?>
                        <div style="background: rgba(255, 85, 165, 0.05); border: 1px solid rgba(255, 85, 165, 0.2); border-left: 4px solid #ff55a5; padding: 15px; margin-bottom: 20px; border-radius: 8px; display: flex; gap: 15px; align-items: flex-start;">
                            <div style="font-size: 24px; line-height: 1;">🛡️</div>
                            <div>
                                <strong style="color: #ff55a5; font-size: 14px; display: block; margin-bottom: 5px; text-transform: uppercase;">Ad-Free Viewing Tip</strong>
                                <p style="color: #bbb; font-size: 13px; margin: 0; line-height: 1.5;">
                                    Video players are hosted on third-party servers. To avoid intrusive ads and pop-ups, we highly recommend using <b>Brave Browser</b> or installing the <b>uBlock Origin</b> extension. You can also switch servers below for alternative audio tracks or better speeds.
                                </p>
                            </div>
                        </div>

                        <?php 
                            // 4 FARKLI OYNATICI SUNUCUSU 
                            $server1 = $default_src; // Ana Vidsrc API
                            $server2 = "https://autoembed.co/" . $vid_type . "/imdb/" . $imdb_id;
                            $server3 = "https://multiembed.mov/?video_id=" . $imdb_id;
                            $server4 = "https://vidsrc.pro/embed/" . $vid_type . "/" . $imdb_id; // VIP Alternatif
                        ?>
                        
                        <div class="server-tabs">
                            <button class="server-btn active" onclick="changeServer('<?php echo $server1; ?>', this)">🎥 Server 1 (Fast)</button>
                            <button class="server-btn" onclick="changeServer('<?php echo $server2; ?>', this)">🎬 Server 2 (AutoEmbed)</button>
                            <button class="server-btn" onclick="changeServer('<?php echo $server3; ?>', this)">🍿 Server 3 (MultiEmbed)</button>
                            <button class="server-btn" onclick="changeServer('<?php echo $server4; ?>', this)">⚡ Server 4 (Alternative)</button>
                        </div>
                        <?php endif; ?>

                        <div class="video-container">
                            <iframe id="main-player" src="<?php echo $default_src; ?>" title="<?php echo $content_name; ?> Full Video Player" allowfullscreen></iframe>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-12" style="margin-top: 40px;">
                        <h2 class="details__title-fix">Screenshot Gallery</h2>
                        <div class="oniklotho-gallery">
                            <?php for ($i = 2; $i <= 4; $i++): $img = $detay["content_img$i"] ?? null; if ($img): ?>
                                <div class="gallery-img-box" onclick="openImage('admin/assets/all_contents/<?php echo $img; ?>', '<?php echo $content_name . ' ' . $altEkranG . ' ' . $i; ?>')">
                                    <img src="admin/assets/all_contents/<?php echo $img; ?>" 
                                         alt="<?php echo $content_name . ' - ' . $altEkranG . ' ' . $i; ?>" 
                                         loading="lazy">
                                </div>
                            <?php endif; endfor; ?>
                        </div>
                    </div>

                    <div id="download-section" class="col-12" style="margin-top: 40px;">
                        <h2 class="details__title-fix">Download Mirrors</h2>
                        <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 12px; border: 1px dashed #ff55a5; text-align: center;">
                            <a href="download.php?link=<?php echo base64_encode($detay['content_link1'] ?? ''); ?>" target="_blank" rel="nofollow noopener" class="sign__btn" style="display:inline-block; padding: 12px 40px; background: #ff55a5;">SERVER 1 (ULTRA FAST)</a>
                        </div>
                    </div>

                    <div class="col-12" style="margin-top: 60px; margin-bottom: 50px;">
                        <h2 class="details__title-fix">User Comments (<?php echo count($yorumlar); ?>)</h2>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="comment_op.php" method="POST" class="sign__form sign__form--comments" style="margin-bottom: 30px;">
                            <input type="hidden" name="content_id" value="<?php echo $id; ?>">
                            <textarea name="comment_text" class="sign__textarea" placeholder="Add a comment..." required style="background:#151f30; border-radius:12px; color:#fff; border:1px solid #333; padding:15px; width:100%; height:100px;"></textarea>
                            <button type="submit" class="sign__btn" style="margin-top:10px; width: 150px; background:#ff55a5;">Send</button>
                        </form>
                        <?php else: ?>
                        <div style="padding:15px; background:rgba(255,85,165,0.1); border-radius:10px; margin-bottom:20px; color:#ff55a5; font-size:13px;">
                            You must <a href="signin" style="font-weight:bold; color:#fff; text-decoration:underline;">sign in</a> to leave a comment.
                        </div>
                        <?php endif; ?>

                        <?php if(count($yorumlar) > 0): foreach($yorumlar as $y): ?>
                        <div class="comment-card">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <strong style="color:#ff55a5;"><?php echo safe_html($y['user_name']); ?></strong>
                                <small style="color:#555;"><?php echo $y['comment_date'] ?? ''; ?></small>
                            </div>
                            <p style="color:#eee; font-size:13px; margin:0;"><?php echo nl2br(safe_html($y['comment_text'])); ?></p>
                        </div>
                        <?php endforeach; else: ?>
                        <p style="color:#555; font-size:12px;">No comments yet. Be the first!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <aside class="col-12 col-xl-4">
                <div style="background: #151f30; padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); position: sticky; top: 100px;">
                    <h2 style="color: #ff55a5; font-size: 16px; font-weight: bold; margin-bottom: 20px; border-left: 3px solid #ff55a5; padding-left: 10px;">RELATED CONTENT</h2>
                    <?php foreach ($onerilenIcerikler as $onerilen): 
                        $relType = $onerilen['content_type'];
                        $relAlt = ($relType == 2) ? "Movie" : (($relType == 5) ? "APK" : "Game");
                    ?>
                        <a href="content/<?php echo $onerilen['content_seourl']; ?>" style="display: flex; gap: 12px; margin-bottom: 20px; text-decoration: none;">
                            <img src="admin/assets/all_contents/<?php echo $onerilen['content_kapakimg'] ?: 'nophoto.jpg'; ?>" 
                                 alt="<?php echo safe_html($onerilen['content_name']) . ' ' . $relAlt; ?>" 
                                 loading="lazy" 
                                 style="width: 65px; height: 65px; border-radius: 8px; object-fit: cover; border: 1px solid #222;">
                            <div style="display: flex; flex-direction: column; justify-content: center;">
                                <h3 style="color: #fff; font-size: 13px; margin: 0;"><?php echo safe_html($onerilen['content_name']); ?></h3>
                                <small style="color: #ff55a5; font-size: 10px;"><?php echo safe_html($onerilen['content_category']); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>

        <?php if (count($rastgeleler) > 0): ?>
        <div class="row mt-5 mb-5" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 40px;">
            <div class="col-12">
                <h2 style="color: #fff; font-size: 22px; font-weight: bold; margin-bottom: 25px; display:flex; align-items:center; gap:10px;">
                    <span style="background:#ff55a5; width:4px; height:22px; display:inline-block; border-radius:2px;"></span>
                    DISCOVER MORE <?php echo strtoupper($catName); ?>
                </h2>
            </div>
            <?php foreach ($rastgeleler as $r): ?>
                <div class="col-lg-3 col-md-4 col-6 mb-4">
                    <a href="content/<?php echo $r['content_seourl']; ?>" style="text-decoration: none; display:block; height: 100%;">
                        <div class="spider-card">
                            <img src="admin/assets/all_contents/<?php echo $r['content_kapakimg'] ?: 'nophoto.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($r['content_name']); ?>" 
                                 loading="lazy"
                                 style="width: 100%; height: 200px; object-fit: cover; border-bottom: 2px solid #ff55a5;">
                            <div style="padding: 12px;">
                                <h3 style="color: #fff; font-size: 14px; font-weight: bold; margin: 0 0 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo $r['content_name']; ?>
                               </h3>
                                <small style="color: #888; font-size: 11px;"><?php echo safe_html($r['content_category']); ?></small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</article>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Dinamik Sunucu Değiştirme Fonksiyonu
    function changeServer(url, btnElement) {
        // Player src güncelle
        document.getElementById('main-player').src = url;
        
        // Aktif buton stilini güncelle
        let btns = document.getElementsByClassName('server-btn');
        for (let i = 0; i < btns.length; i++) {
            btns[i].classList.remove('active');
        }
        btnElement.classList.add('active');
    }

    // Resim Modal
    function openImage(url, altText) {
        let modalImg = document.getElementById('modalImg');
        modalImg.src = url;
        modalImg.alt = altText;
        document.getElementById('imgModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeImage() {
        document.getElementById('imgModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // READ MORE VE YÜKSEKLİK KONTROLÜ
    $(document).ready(function () {
        const descBox = $('#desc-box');
        const readMoreBtn = $('#read-more');
        const descFade = $('#desc-fade');

        setTimeout(function() {
            if (descBox[0].scrollHeight > 250) {
                readMoreBtn.show();
            } else {
                descFade.hide();
                readMoreBtn.hide();
            }
        }, 100);

        readMoreBtn.on('click', function () {
            if (descBox.hasClass('expanded')) {
                descBox.removeClass('expanded');
                $(this).text('Read More ↓');
                $('html, body').animate({ scrollTop: descBox.offset().top - 150 }, 500);
            } else {
                descBox.addClass('expanded');
                $(this).text('Show Less ↑');
            }
        });
    });

    window.onclick = function (e) { if (e.target == document.getElementById('imgModal')) closeImage(); }
</script>

<?php require_once 'footer.php'; ?>