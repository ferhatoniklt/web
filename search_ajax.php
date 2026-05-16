<?php
require_once 'baglan.php';

if (isset($_POST['query'])) {
    $search = "%" . $_POST['query'] . "%";
    $sorgu = $db->prepare("SELECT * FROM contents WHERE content_name LIKE ? AND content_type != 4 AND content_aktiflik = 1 LIMIT 8");
    $sorgu->execute([$search]);
    $sonuclar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    if (count($sonuclar) > 0) {
        foreach ($sonuclar as $rs) {
            echo '
            <a href="content/' . $rs['content_seourl'] . '" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #222; text-decoration: none; transition: 0.3s;">
                <img src="admin/assets/all_contents/' . $rs['content_kapakimg'] . '" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 12px;">
                <div>
                    <h4 style="color: #fff; font-size: 13px; margin: 0;">' . $rs['content_name'] . '</h4>
                    <span style="color: #ff55a5; font-size: 11px;">' . ($rs['content_type'] == 1 ? 'GAME' : 'MOVIE') . '</span>
                </div>
            </a>';
        }
    } else {
        echo '<div style="padding: 15px; color: #888; font-size: 13px;">No results found abi...</div>';
    }
}
?>
