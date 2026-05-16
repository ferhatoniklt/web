<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'header.php';
require_once 'baglan.php'; 

// Sidebar Chat için varsayılan kategori
$side_cat = 'GENERAL';

// --- SAYFALAMA VE VERİ ÇEKME (Burası aynı kalıyor) ---
$limit = 12;
$g_p = isset($_GET['g_p']) ? (int)$_GET['g_p'] : 1;
$m_p = isset($_GET['m_p']) ? (int)$_GET['m_p'] : 1;
$n_p = isset($_GET['n_p']) ? (int)$_GET['n_p'] : 1;
if($g_p < 1) $g_p = 1; if($m_p < 1) $m_p = 1; if($n_p < 1) $n_p = 1;
$g_off = ($g_p - 1) * $limit;
$m_off = ($m_p - 1) * $limit;
$n_off = ($n_p - 1) * $limit;

try {
    $slider = $db->query("SELECT * FROM contents WHERE content_type=1 AND content_aktiflik=1 ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $listGames = $db->query("SELECT * FROM contents WHERE content_type=1 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $g_off")->fetchAll(PDO::FETCH_ASSOC);
    $listMovies = $db->query("SELECT * FROM contents WHERE content_type=2 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $m_off")->fetchAll(PDO::FETCH_ASSOC);
    $listNsfw = $db->query("SELECT * FROM contents WHERE content_type=4 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $n_off")->fetchAll(PDO::FETCH_ASSOC);
    $trending = $db->query("SELECT * FROM contents WHERE content_aktiflik=1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    $g_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=1 AND content_aktiflik=1")->fetchColumn();
    $m_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=2 AND content_aktiflik=1")->fetchColumn();
    $n_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=4 AND content_aktiflik=1")->fetchColumn();
    $g_pages = ceil($g_total / $limit); $m_pages = ceil($m_total / $limit); $n_pages = ceil($n_total / $limit);
} catch (PDOException $e) { $slider = $listGames = $listMovies = $listNsfw = $trending = []; }
?>

<style>
    /* Portal ve NSFW Düzenlemeleri */
    .oniklotho-portal { padding: 100px 30px 50px 30px; }
    .sidebar-block { background: #151f30; border-radius: 12px; padding: 15px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.05); }
    .sidebar-title { color: #ff55a5; font-size: 14px; font-weight: 700; margin-bottom: 12px; border-left: 3px solid #ff55a5; padding-left: 10px; text-transform: uppercase; }
    
    /* Canlı Chat Sidebar Tasarımı */
    #side-chat-box { height: 350px; overflow-y: auto; background: #0a101d; border-radius: 8px; padding: 10px; font-size: 12px; color: #bbb; margin-bottom: 10px; border: 1px solid #222; }
    .chat-input-area { display: flex; gap: 5px; }
    .chat-input-area input { flex-grow: 1; background: #050a14; border: 1px solid #333; color: #fff; padding: 8px; border-radius: 6px; font-size: 12px; }
    .chat-input-area button { background: #ff55a5; border: none; color: #fff; padding: 5px 12px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px; }

    /* Glitch Efekti */
    .nsfw-card { position: relative; overflow: hidden; border-radius: 8px; background: #000; }
    .nsfw-card img { width: 100%; height: 160px; object-fit: cover; filter: blur(25px); transition: 0.4s; }
    .nsfw-card:hover img { filter: blur(0); }
    .nsfw-card:hover { animation: glitch-side 0.2s infinite; outline: 1px solid #ff55a5; }
    @keyframes glitch-side { 0% { transform: translate(0); } 20% { transform: translate(-2px, 1px); } 80% { transform: translate(2px, -1px); } 100% { transform: translate(0); } }

    /* Forum-Style ve Paginator (Burası aynı) */
    .forum-row { display: flex; align-items: center; background: rgba(255,255,255,0.03); padding: 8px; margin-bottom: 5px; border-radius: 6px; text-decoration: none !important; }
    .forum-row:hover { background: rgba(255,85,165,0.1); transform: translateX(5px); }
    .paginator { display: flex; justify-content: center; list-style: none; gap: 5px; margin-top: 20px; }
    .paginator a { background: #151f30; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; text-decoration: none; border: 1px solid #333; }
    .paginator .active a { background: #ff55a5; border-color: #ff55a5; }
</style>

<div class="oniklotho-portal">
    <div class="row">
        
        <div class="col-xl-2 d-none d-xl-block">
            <div class="sidebar-block">
                <h3 class="sidebar-title">NAVIGATION</h3>
                <a href="index.php" style="color:#eee; display:block; padding:8px 0; font-size:13px; text-decoration:none;">🏠 Anasayfa</a>
                <a href="category/games" style="color:#bbb; display:block; padding:8px 0; font-size:13px; text-decoration:none;">🎮 PC Games</a>
                <a href="category/movies" style="color:#bbb; display:block; padding:8px 0; font-size:13px; text-decoration:none;">🎬 Movies Hub</a>
                <a href="category/nsfw" style="color:#bbb; display:block; padding:8px 0; font-size:13px; text-decoration:none;">🔞 NSFW (+18)</a>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="home" style="margin-bottom: 40px;">
                <div class="home__carousel owl-carousel">
                    <?php foreach ($slider as $s): ?>
                        <div class="home__card">
                            <a href="content/<?php echo $s['content_seourl']; ?>">
                                <img src="admin/assets/all_contents/<?php echo $s['content_kapakimg']; ?>" style="width:100%; height:320px; object-fit:cover; border-radius:16px;">
                            </a>
                            <h2 style="font-size: 18px; margin-top:10px; color:#fff;"><?php echo $s['content_name']; ?></h2>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-block" style="background:none; border:none; padding:0;">
                <h4 class="sidebar-title" style="font-size:18px;">🎬 Movie Archive</h4>
                <div class="row row--grid">
                    <?php foreach ($listMovies as $m): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="content/<?php echo $m['content_seourl']; ?>" style="text-decoration:none;">
                                <img src="admin/assets/all_contents/<?php echo $m['content_kapakimg']; ?>" style="width:100%; height:160px; object-fit:cover; border-radius:8px;">
                                <p style="font-size:11px; color:#aaa; text-align:center; margin-top:5px;"><?php echo mb_substr($m['content_name'], 0, 15); ?>...</p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <ul class="paginator">
                    <?php for($i=1; $i<=$m_pages; $i++): ?>
                        <li class="<?php echo ($i==$m_p)?'active':''; ?>"><a href="?m_p=<?php echo $i; ?>&g_p=<?php echo $g_p; ?>&n_p=<?php echo $n_p; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </div>

            <div class="sidebar-block" style="background:none; border:none; padding:0; margin-top:40px;">
                <h4 class="sidebar-title" style="font-size:18px;">🎮 Game Archive</h4>
                <div class="row row--grid">
                    <?php foreach ($listGames as $g): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="content/<?php echo $g['content_seourl']; ?>" style="text-decoration:none;">
                                <img src="admin/assets/all_contents/<?php echo $g['content_kapakimg']; ?>" style="width:100%; height:160px; object-fit:cover; border-radius:8px;">
                                <p style="font-size:11px; color:#aaa; text-align:center; margin-top:5px;"><?php echo mb_substr($g['content_name'], 0, 15); ?>...</p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <ul class="paginator">
                    <?php for($i=1; $i<=$g_pages; $i++): ?>
                        <li class="<?php echo ($i==$g_p)?'active':''; ?>"><a href="?g_p=<?php echo $i; ?>&m_p=<?php echo $m_p; ?>&n_p=<?php echo $n_p; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </div>

            <div class="sidebar-block" style="background:none; border:none; padding:0; margin-top:40px;">
                <h4 class="sidebar-title" style="font-size:18px; color:#f2c94c;">🔞 NSFW (+18)</h4>
                <div class="row row--grid">
                    <?php foreach ($listNsfw as $n): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="nsfw-card">
                                <a href="content/<?php echo $n['content_seourl']; ?>">
                                    <img src="admin/assets/all_contents/<?php echo $n['content_kapakimg']; ?>">
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <ul class="paginator">
                    <?php for($i=1; $i<=$n_pages; $i++): ?>
                        <li class="<?php echo ($i==$n_p)?'active':''; ?>"><a href="?n_p=<?php echo $i; ?>&g_p=<?php echo $g_p; ?>&m_p=<?php echo $m_p; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>

        <div class="col-12 col-xl-2">
            
            <div class="sidebar-block">
                <h3 class="sidebar-title">🔥 Trending</h3>
                <?php foreach ($trending as $t): ?>
                    <a href="content/<?php echo $t['content_seourl']; ?>" style="display:flex; gap:10px; margin-bottom:12px; text-decoration:none;">
                        <img src="admin/assets/all_contents/<?php echo $t['content_kapakimg']; ?>" style="width:35px; height:35px; border-radius:4px; object-fit:cover;">
                        <span style="font-size:11px; color:#eee;"><?php echo mb_substr($t['content_name'], 0, 20); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="sidebar-block" style="border-color: rgba(255, 85, 165, 0.4);">
                <h3 class="sidebar-title">💬 Live All-Chat</h3>
                
                <div id="side-chat-box">
                    <p style="text-align:center; padding-top:20px;">Loading chat...</p>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="chat-input-area">
                        <input type="text" id="side-chat-msg" placeholder="Write message...">
                        <button onclick="sendSideMessage()">OK</button>
                    </div>
                <?php else: ?>
                    <p style="font-size:10px; color:#555; text-align:center;">Please <a href="login.php" style="color:#ff55a5;">login</a> to chat.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
    function loadSideMessages() {
        // chat_op.php dosyasını kullanarak mesajları çekiyoruz
        $.post('chat_op.php', { action: 'load', cat: 'GENERAL' }, function(data) {
            // Gelen datadaki 'oniklotho:' yazılarını temizleyerek basıyoruz
            let cleanData = data.replace(/oniklotho:/g, '');
            $('#side-chat-box').html(cleanData);
            $('#side-chat-box').scrollTop($('#side-chat-box')[0].scrollHeight);
        });
    }

    function sendSideMessage() {
        let msg = $('#side-chat-msg').val();
        if(msg != "") {
            $.post('chat_op.php', { action: 'send', msg: msg, cat: 'GENERAL' }, function() {
                $('#side-chat-msg').val('');
                loadSideMessages();
            });
        }
    }

    // 2 saniyede bir güncelle
    setInterval(loadSideMessages, 2000);
    // Sayfa açıldığında yükle
    $(document).ready(loadSideMessages);

    // Enter tuşu ile gönderim sağlama
    $(document).on('keypress', '#side-chat-msg', function(e) {
        if(e.which == 13) {
            sendSideMessage();
        }
    });
</script>

<?php require_once 'footer.php'; ?>