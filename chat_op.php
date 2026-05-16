<?php
require_once 'baglan.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$category = isset($_POST['cat']) ? $_POST['cat'] : 'GENERAL';

// --- MESAJLARI YÜKLE ---
if ($action == 'load') {
    if ($category == 'GENERAL') { $cid = 0; }
    elseif ($category == 'GAMES') { $cid = -1; }
    else { $cid = -2; }

    $sorgu = $db->prepare("SELECT comments.*, users.user_name 
                           FROM comments 
                           LEFT JOIN users ON comments.user_id = users.user_id 
                           WHERE comments.content_id = ? 
                           ORDER BY comments.comment_id DESC LIMIT 50");
    $sorgu->execute([$cid]);
    $mesajlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    $mesajlar = array_reverse($mesajlar);

    if (count($mesajlar) > 0) {
        foreach ($mesajlar as $m) {
            
            // KONTROL: Gerçek insan mı, Bot mu?
            if (!empty($m['user_name'])) {
                // GERÇEK KULLANICI: İsimleri pembe yap, HTML'i engelle (XSS Koruması)
                $username = '<strong style="color:#ff55a5;">' . htmlspecialchars($m['user_name']) . ':</strong> ';
                $text = htmlspecialchars($m['comment_text'] ?? '');
            } else {
                // BOT / SİSTEM: Veritabanında &lt;strong&gt; olarak kayıtlı HTML'i önce decode et (çöz)
                $username = '';
                $ham_metin = $m['comment_text'] ?? '';
                $cozulmus_metin = htmlspecialchars_decode($ham_metin, ENT_QUOTES);
                
                // Çözülen metnin içinden sadece izin verdiğimiz etiketlerin çalışmasına izin ver
                $text = strip_tags($cozulmus_metin, '<strong><b><span><i><em><font><br><p>'); 
            }
            
            echo '<div style="margin-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.03); padding-bottom:4px; word-wrap: break-word;">';
            echo '<span style="color:#eee; font-size:12px;">' . $username . $text . '</span>';
            echo '</div>';
        }
    } else {
        echo '<div style="color:#555; font-size:11px; text-align:center; padding:10px;">No messages yet. Be the first to chat!</div>';
    }
    exit;
}

// --- MESAJ GÖNDER ---
if ($action == 'send') {
    if (!isset($_SESSION['user_id'])) {
        exit;
    }

    $msg = isset($_POST['msg']) ? trim(strip_tags($_POST['msg'])) : '';
    
    if (!empty($msg)) {
        $cid = ($category == 'GENERAL') ? 0 : ($category == 'GAMES' ? -1 : -2);
        $user_id = $_SESSION['user_id'];

        $kaydet = $db->prepare("INSERT INTO comments SET user_id = :uid, content_id = :cid, comment_text = :text, comment_date = NOW()");
        $kaydet->execute([
            'uid' => $user_id,
            'cid' => $cid,
            'text' => $msg
        ]);
    }
    exit;
}
?>