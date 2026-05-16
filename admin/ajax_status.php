<?php
require_once '../baglan.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $aktiflik = $_POST['aktiflik'];

    $guncelle = $db->prepare("UPDATE contents SET content_aktiflik = ? WHERE id = ?");
    $guncelle->execute([$aktiflik, $id]);
}
?>