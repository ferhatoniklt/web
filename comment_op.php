<?php
require_once 'baglan.php';
session_start();

// 1. Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to comment!'); window.location.href='signin';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen verileri temizle
    $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
    $user_id = $_SESSION['user_id'];
    
    // İŞTE BURASI DÜZELTİLDİ: $_POST['comment'] yerine $_POST['comment_text'] yazdık
    $comment_text = trim(htmlspecialchars($_POST['comment_text'] ?? ''));

    // Boş yorum kontrolü
    if (empty($comment_text)) {
        echo "<script>alert('Please write something before posting!'); window.history.back();</script>";
        exit;
    }

    if ($content_id > 0) {
        // Yorumu veritabanına ekle
        $ekle = $db->prepare("INSERT INTO comments (content_id, user_id, comment_text) VALUES (?, ?, ?)");
        $durum = $ekle->execute([$content_id, $user_id, $comment_text]);

        if ($durum) {
            // Başarılıysa sayfayı yenile (geldiği sayfaya geri gönder)
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            echo "<script>alert('Error! Your comment could not be saved.'); window.history.back();</script>";
            exit;
        }
    } else {
        header("Location: index");
        exit;
    }
} else {
    header("Location: index");
    exit;
}
?>