<?php
require_once 'baglan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $mail = htmlspecialchars(trim($_POST['mail']));
    $password = trim($_POST['password']);

    // Önce bu mail adresi var mı kontrol edelim
    $kontrol = $db->prepare("SELECT user_id FROM users WHERE user_mail = ?");
    $kontrol->execute([$mail]);

    if ($kontrol->rowCount() > 0) {
        echo "<script>alert('This email is already registered!'); window.history.back();</script>";
        exit;
    } else {
        $ekle = $db->prepare("INSERT INTO users (user_name, user_mail, user_password) VALUES (?, ?, ?)");
        $durum = $ekle->execute([$name, $mail, $password]);
        
        if($durum) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='signin';</script>";
        } else {
            echo "<script>alert('Error! Registration failed.'); window.history.back();</script>";
        }
        exit;
    }
}
?>