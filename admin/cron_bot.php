<?php
// Bu dosya sunucu tarafından otomatik çalıştırılacak
require_once 'baglan.php';

// Önce Oyun Botunu Tetikle
echo "--- Oyun Botu Başlatılıyor ---\n";
include 'bot_gamestorrent.php'; 

echo "\n--- NSFW Botu Başlatılıyor ---\n";
include 'bot_nsfw_gallery.php';

echo "\n[SYSTEM]: Tüm bot işlemleri tamamlandı: " . date("Y-m-d H:i:s");
?>