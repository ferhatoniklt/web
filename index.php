<?php
// 1. DATABASE CONNECTION AND ERROR HANDLING
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'baglan.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. PAGINATION CALCULATIONS (APK VE SOFTWARE EKLENDİ)
$limit = 12;
$g_p = isset($_GET['g_p']) ? (int) $_GET['g_p'] : 1;
$m_p = isset($_GET['m_p']) ? (int) $_GET['m_p'] : 1;
$n_p = isset($_GET['n_p']) ? (int) $_GET['n_p'] : 1;
$a_p = isset($_GET['a_p']) ? (int) $_GET['a_p'] : 1;
$s_p = isset($_GET['s_p']) ? (int) $_GET['s_p'] : 1; // Software için parametre

if ($g_p < 1) $g_p = 1;
if ($m_p < 1) $m_p = 1;
if ($n_p < 1) $n_p = 1;
if ($a_p < 1) $a_p = 1;
if ($s_p < 1) $s_p = 1;

$g_off = ($g_p - 1) * $limit;
$m_off = ($m_p - 1) * $limit;
$n_off = ($n_p - 1) * $limit;
$a_off = ($a_p - 1) * $limit;
$s_off = ($s_p - 1) * $limit; // Software Offset

// 3. FETCH ALL DATA
try {
    $g_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=1 AND content_aktiflik=1")->fetchColumn();
    $m_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=2 AND content_aktiflik=1")->fetchColumn();
    $n_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=4 AND content_aktiflik=1")->fetchColumn();
    $a_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=5 AND content_aktiflik=1")->fetchColumn();
    $s_total = $db->query("SELECT COUNT(*) FROM contents WHERE content_type=3 AND content_aktiflik=1")->fetchColumn(); // Software Toplam

    $g_pages = ceil($g_total / $limit);
    $m_pages = ceil($m_total / $limit);
    $n_pages = ceil($n_total / $limit);
    $a_pages = ceil($a_total / $limit);
    $s_pages = ceil($s_total / $limit); // Software Sayfa

    $tabGames = $db->query("SELECT * FROM contents WHERE content_type=1 AND content_aktiflik=1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    $tabMovies = $db->query("SELECT * FROM contents WHERE content_type=2 AND content_aktiflik=1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    $tabApk = $db->query("SELECT * FROM contents WHERE content_type=5 AND content_aktiflik=1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    $tabSoftware = $db->query("SELECT * FROM contents WHERE content_type=3 AND content_aktiflik=1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC); // Yazılım Vitrin
    
    // NSFW vitrinini kaldırdık, sadece arşivi çekeceğiz
    
    $sliderMovies = $db->query("SELECT * FROM contents WHERE content_type=2 AND content_aktiflik=1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $sliderGames = $db->query("SELECT * FROM contents WHERE content_type=1 AND content_aktiflik=1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    $arcGames = $db->query("SELECT * FROM contents WHERE content_type=1 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $g_off")->fetchAll(PDO::FETCH_ASSOC);
    $arcMovies = $db->query("SELECT * FROM contents WHERE content_type=2 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $m_off")->fetchAll(PDO::FETCH_ASSOC);
    $arcApk = $db->query("SELECT * FROM contents WHERE content_type=5 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $a_off")->fetchAll(PDO::FETCH_ASSOC);
    $arcSoftware = $db->query("SELECT * FROM contents WHERE content_type=3 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $s_off")->fetchAll(PDO::FETCH_ASSOC); // Yazılım Arşivi
    $arcNsfw = $db->query("SELECT * FROM contents WHERE content_type=4 AND content_aktiflik=1 ORDER BY id DESC LIMIT $limit OFFSET $n_off")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once 'header.php';
?>

<div class="container-fluid" style="padding: 0 40px;">

    <div class="row">
        <div class="col-12">
            <div class="ad-space">AD SPACE 1 (728x90)</div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-2 col-md-2 mb-2">
            <div class="box-col">
                <div class="box-title">CATEGORIES</div>
                <div class="t-list-container">
                    <a href="games" class="cat-link">🎮 Games</a>
                    <a href="movies" class="cat-link">🎬 Movies/TV</a>
                    <a href="software" class="cat-link">💻 Software</a>
                    <a href="apk" class="cat-link">📱 APK Mods</a>
                    <a href="nsfw" class="cat-link" style="color:var(--pink);">🔞 NSFW Hub</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 mb-4">
            <div class="box-col">
                <div class="box-title">GAMES RELEASES</div>
                <div class="t-list-container">
                    <?php foreach ($tabGames as $g): ?>
                        <a href="content/<?php echo $g['content_seourl']; ?>" class="t-row">
                            <img src="admin/assets/all_contents/<?php echo $g['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($g['content_name']); ?> Download Free" class="t-img">
                            <span class="t-name"><?php echo $g['content_name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 mb-4">
            <div class="box-col">
                <div class="box-title" style="color:#2f80ed;">MOVIES TV SERİES RELEASES</div>
                <div class="t-list-container">
                    <?php foreach ($tabMovies as $m): ?>
                        <a href="content/<?php echo $m['content_seourl']; ?>" class="t-row">
                            <img src="admin/assets/all_contents/<?php echo $m['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($m['content_name']); ?> Watch HD" class="t-img">
                            <span class="t-name"><?php echo $m['content_name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-2 mb-2">
            <div class="box-col" style="padding: 0;">
                <div class="box-title" style="margin: 15px 15px 10px 15px; border:none; color:#2f80ed;">LATEST MOVIES</div>
                <div class="v-slider">
                    <div class="v-track" id="track-movies">
                        <?php if (count($sliderMovies) > 0):
                            foreach ($sliderMovies as $sm): ?>
                                <div class="v-slide">
                                    <a href="content/<?php echo $sm['content_seourl']; ?>">
                                        <img src="admin/assets/all_contents/<?php echo $sm['content_kapakimg'] ?: 'nophoto.jpg'; ?>" alt="<?php echo htmlspecialchars($sm['content_name']); ?> Stream">
                                        <div class="v-cap"><?php echo mb_substr($sm['content_name'], 0, 30); ?>..</div>
                                    </a>
                                </div>
                            <?php endforeach; else: ?>
                            <div style="text-align:center; padding:50px; color:#555; width:100%;">No content found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-2 col-md-2 mb-2">
            <div class="box-col">
                <div class="box-title">LIVE CHAT</div>
                <div id="side-chat-box" style="height: 250px; overflow-y: auto; margin-bottom: 10px; padding-right: 5px;">Connecting...</div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="display:flex; gap:5px;">
                        <input type="text" id="side-chat-msg"
                            style="flex-grow:1; background:#0a0c10; color:#fff; border:1px solid rgba(255,255,255,0.1); padding:8px; border-radius:6px; font-size:12px;"
                            placeholder="Type a message..." autocomplete="off">
                        <button onclick="sendSideMessage()" id="chat-send-btn"
                            style="background:var(--pink); color:#fff; border:none; border-radius:6px; padding:0 15px; font-weight:bold; cursor:pointer; transition:0.3s;">OK</button>
                    </div>
                <?php else: ?>
                    <div style="padding:10px; background:rgba(255,85,165,0.1); border-radius:6px; font-size:12px; text-align:center;">
                        <a href="signin" style="color:var(--pink); font-weight:bold; text-decoration:none;">Sign in</a> to join chat!
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 mb-4">
            <div class="box-col">
                <div class="box-title" style="color:#9b59b6;">SOFTWARE RELEASES</div>
                <div class="t-list-container">
                    <?php foreach ($tabSoftware as $s): ?>
                        <a href="content/<?php echo $s['content_seourl']; ?>" class="t-row">
                            <img src="admin/assets/all_contents/<?php echo $s['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($s['content_name']); ?> Pre-Activated" class="t-img">
                            <span class="t-name"><?php echo $s['content_name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 mb-4">
            <div class="box-col">
                <div class="box-title" style="color:#00b894;">APK RELEASES</div>
                <div class="t-list-container">
                    <?php foreach ($tabApk as $a): ?>
                        <a href="content/<?php echo $a['content_seourl']; ?>" class="t-row">
                            <img src="admin/assets/all_contents/<?php echo $a['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($a['content_name']); ?> Premium APK" class="t-img">
                            <span class="t-name"><?php echo $a['content_name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-2 mb-2">
            <div class="box-col" style="padding: 0;">
                <div class="box-title" style="margin: 15px 15px 10px 15px; border:none;">LATEST GAMES</div>
                <div class="v-slider">
                    <div class="v-track" id="track-games">
                        <?php if (count($sliderGames) > 0):
                            foreach ($sliderGames as $sg): ?>
                                <div class="v-slide">
                                    <a href="content/<?php echo $sg['content_seourl']; ?>">
                                        <img src="admin/assets/all_contents/<?php echo $sg['content_kapakimg'] ?: 'nophoto.jpg'; ?>" alt="<?php echo htmlspecialchars($sg['content_name']); ?> Repack">
                                        <div class="v-cap"><?php echo mb_substr($sg['content_name'], 0, 30); ?>..</div>
                                    </a>
                                </div>
                            <?php endforeach; else: ?>
                            <div style="text-align:center; padding:50px; color:#555; width:100%;">No content found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row anchor-offset" id="games-archive">
        <div class="col-12">
            <h4 class="box-title" style="border:none; font-size:18px;">GAMES ARCHIVE</h4>
        </div>
        <?php foreach ($arcGames as $g): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="arc-card">
                    <a href="content/<?php echo $g['content_seourl']; ?>">
                        <img src="admin/assets/all_contents/<?php echo $g['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($g['content_name']); ?> Free Game" class="arc-img">
                    </a>
                    <div class="arc-title"><?php echo mb_substr($g['content_name'], 0, 20); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="pagi-box">
                <?php for ($i = 1; $i <= $g_pages; $i++):
                    if ($i == 1 || $i == $g_pages || ($i >= $g_p - 2 && $i <= $g_p + 2)): ?>
                        <a href="?g_p=<?php echo $i; ?>&m_p=<?php echo $m_p; ?>&n_p=<?php echo $n_p; ?>&a_p=<?php echo $a_p; ?>&s_p=<?php echo $s_p; ?>#games-archive"
                            class="pagi-btn <?php echo ($i == $g_p) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; endfor; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ad-space">AD SPACE 2</div>
        </div>
    </div>

    <div class="row anchor-offset" id="movies-archive">
        <div class="col-12">
            <h4 class="box-title" style="border:none; color:#2f80ed; font-size:18px;">MOVIES ARCHIVE</h4>
        </div>
        <?php foreach ($arcMovies as $m): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="arc-card">
                    <a href="content/<?php echo $m['content_seourl']; ?>">
                        <img src="admin/assets/all_contents/<?php echo $m['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($m['content_name']); ?> Full Movie" class="arc-img">
                    </a>
                    <div class="arc-title"><?php echo mb_substr($m['content_name'], 0, 20); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="pagi-box">
                <?php for ($i = 1; $i <= $m_pages; $i++):
                    if ($i == 1 || $i == $m_pages || ($i >= $m_p - 2 && $i <= $m_p + 2)): ?>
                        <a href="?m_p=<?php echo $i; ?>&g_p=<?php echo $g_p; ?>&n_p=<?php echo $n_p; ?>&a_p=<?php echo $a_p; ?>&s_p=<?php echo $s_p; ?>#movies-archive"
                            class="pagi-btn <?php echo ($i == $m_p) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; endfor; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ad-space">AD SPACE 3</div>
        </div>
    </div>

  
    <div class="row anchor-offset" id="apk-archive">
        <div class="col-12">
            <h4 class="box-title" style="border:none; color:#00b894; font-size:18px;">APK MODS ARCHIVE</h4>
        </div>
        <?php foreach ($arcApk as $a): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="arc-card">
                    <a href="content/<?php echo $a['content_seourl']; ?>">
                        <img src="admin/assets/all_contents/<?php echo $a['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($a['content_name']); ?> Mod Download" class="arc-img">
                    </a>
                    <div class="arc-title"><?php echo mb_substr($a['content_name'], 0, 20); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="pagi-box">
                <?php for ($i = 1; $i <= $a_pages; $i++):
                    if ($i == 1 || $i == $a_pages || ($i >= $a_p - 2 && $i <= $a_p + 2)): ?>
                        <a href="?a_p=<?php echo $i; ?>&m_p=<?php echo $m_p; ?>&g_p=<?php echo $g_p; ?>&n_p=<?php echo $n_p; ?>&s_p=<?php echo $s_p; ?>#apk-archive"
                            class="pagi-btn <?php echo ($i == $a_p) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; endfor; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ad-space">AD SPACE 4</div>
        </div>
    </div>
  <div class="row anchor-offset" id="software-archive">
        <div class="col-12">
            <h4 class="box-title" style="border:none; color:#9b59b6; font-size:18px;">SOFTWARE ARCHIVE</h4>
        </div>
        <?php foreach ($arcSoftware as $s): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="arc-card">
                    <a href="content/<?php echo $s['content_seourl']; ?>">
                        <img src="admin/assets/all_contents/<?php echo $s['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($s['content_name']); ?> Free Software" class="arc-img">
                    </a>
                    <div class="arc-title"><?php echo mb_substr($s['content_name'], 0, 20); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="pagi-box">
                <?php for ($i = 1; $i <= $s_pages; $i++):
                    if ($i == 1 || $i == $s_pages || ($i >= $s_p - 2 && $i <= $s_p + 2)): ?>
                        <a href="?s_p=<?php echo $i; ?>&m_p=<?php echo $m_p; ?>&g_p=<?php echo $g_p; ?>&n_p=<?php echo $n_p; ?>&a_p=<?php echo $a_p; ?>#software-archive"
                            class="pagi-btn <?php echo ($i == $s_p) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; endfor; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ad-space">AD SPACE 5</div>
        </div>
    </div>

    <div class="row mb-5 anchor-offset" id="nsfw-archive">
        <div class="col-12">
            <h4 class="box-title" style="border:none; color:#f2c94c; font-size:18px;">NSFW ARCHIVE (BLURRED)</h4>
        </div>
        <?php foreach ($arcNsfw as $n): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="arc-card">
                    <a href="content/<?php echo $n['content_seourl']; ?>">
                        <img src="admin/assets/all_contents/<?php echo $n['content_kapakimg']; ?>" alt="<?php echo htmlspecialchars($n['content_name']); ?> Pics" class="arc-img blur-img">
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="pagi-box">
                <?php for ($i = 1; $i <= $n_pages; $i++):
                    if ($i == 1 || $i == $n_pages || ($i >= $n_p - 2 && $i <= $n_p + 2)): ?>
                        <a href="?n_p=<?php echo $i; ?>&g_p=<?php echo $g_p; ?>&m_p=<?php echo $m_p; ?>&a_p=<?php echo $a_p; ?>&s_p=<?php echo $s_p; ?>#nsfw-archive"
                            class="pagi-btn <?php echo ($i == $n_p) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; endfor; ?>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function initSlider(trackId) {
            const track = document.getElementById(trackId);
            if (!track) return;
            const slides = track.querySelectorAll('.v-slide');
            if (slides.length <= 1) return;

            let index = 0;
            setInterval(() => {
                index++;
                if (index >= slides.length) index = 0;
                track.style.transform = `translateX(-${index * 100}%)`;
            }, 3000); 
        }

        initSlider('track-movies');
        setTimeout(() => initSlider('track-games'), 1500);
    });

    // LIVE CHAT LOGIC
    $(document).ready(function () {
        loadSideMessages();
        setInterval(loadSideMessages, 3000);
    });

    function loadSideMessages() {
        $.post('chat_op.php', { action: 'load', cat: 'GENERAL' }, function (data) {
            let chatBox = $('#side-chat-box');
            let isBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 50;

            chatBox.html(data.replace(/oniklotho:/g, ''));

            if (isBottom) {
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }
        });
    }

    function sendSideMessage() {
        let msgBox = $('#side-chat-msg');
        let btn = $('#chat-send-btn');
        let msg = msgBox.val();

        if (msg.trim() !== "") {
            msgBox.prop('disabled', true);
            btn.prop('disabled', true).css('opacity', '0.5');

            $.post('chat_op.php', { action: 'send', msg: msg, cat: 'GENERAL' }, function () {
                msgBox.val('').prop('disabled', false).focus();
                btn.prop('disabled', false).css('opacity', '1');
                loadSideMessages();
            }).fail(function () {
                alert("Mesaj gönderilemedi! Sunucu veya veritabanı bağlantısında bir sorun var.");
                msgBox.prop('disabled', false);
                btn.prop('disabled', false).css('opacity', '1');
            });
        }
    }

    // DÜZELTME: Klavyeden enter basıldığında gga harfleri çıkıyordu, o düzeltildi
    $(document).on('keypress', '#side-chat-msg', function (e) {
        if (e.which == 13) {
            e.preventDefault(); 
            sendSideMessage();
        }
    });
</script>

<?php require_once 'footer.php'; ?>