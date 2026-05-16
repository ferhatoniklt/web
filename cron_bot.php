<?php
// Zaman ve hafıza sınırlarını zorlayalım
set_time_limit(0); 
ini_set('memory_limit', '512M');
ignore_user_abort(true);

require_once __DIR__ . '/baglan.php';

echo "--- Master Bot Baslatildi ---\n";

// 1. OYUN BOTU
echo "Checking Games...\n";
@include __DIR__ . '/bot_gamestorrent.php';
echo "Games Done.\n";

// 2. FILM BOTU
echo "Checking Movies...\n";
@include __DIR__ . '/bot_movie.php';
echo "Movies Done.\n";

// 3. NSFW BOTU
echo "Checking NSFW...\n";
@include __DIR__ . '/bot_nsfw_gallery.php';
echo "NSFW Done.\n";

echo "--- Tüm Islemler Tamamlandi ---";
?>