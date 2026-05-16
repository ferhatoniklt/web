<?php
require_once 'baglan.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mail = trim($_POST['mail']);
    $password = trim($_POST['password']);

    if (empty($mail) || empty($password)) {
        echo "<script>alert('Please fill all fields!'); window.location.href='signin';</script>";
        exit;
    }

    $sorgu = $db->prepare("SELECT * FROM users WHERE user_mail = ? AND user_password = ?");
    $sorgu->execute([$mail, $password]);
    $user = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Oturum Verilerini Kaydet
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_mail'] = $user['user_mail'];
        
        // ÖNEMLİ: Yönlendirmeden hemen sonra exit; ekledik
        header("Location: index");
        exit;
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='signin';</script>";
        exit;
    }
} else {
    header("Location: signin");
    exit;
}
?>