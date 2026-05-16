<?php 
require_once 'baglan.php';
// Header'ı en başta çağırabiliriz çünkü detay sayfası gibi özel meta verilere (title/desc) ihtiyacı yok
require_once 'header.php';

$searchTerm = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

if (!empty($searchTerm)) {
    // Arama sorgusunu yapıyoruz
    $sorgu = $db->prepare("SELECT * FROM contents WHERE content_name LIKE ? AND content_aktiflik = 1 AND content_type != 4");

    $sorgu->execute(["%$searchTerm%"]);
    $sonuclar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="main">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="main__title" style="color: #fff; margin-top: 30px; border-left: 3px solid #ff55a5; padding-left: 15px;">
                    Search Results for: <span style="color: #ff55a5;">"<?php echo $searchTerm; ?>"</span>
                </h2>
            </div>
            
            <?php if (!empty($sonuclar)): foreach ($sonuclar as $item): ?>
                <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
                    <div class="card">
                        <a href="content/<?php echo $item['content_seourl']; ?>" class="card__cover">
                            <img src="admin/assets/all_contents/<?php echo $item['content_kapakimg']; ?>" alt="<?php echo $item['content_name']; ?>">
                        </a>
                        
                        <h3 class="card__title">
                            <a href="content/<?php echo $item['content_seourl']; ?>"><?php echo $item['content_name']; ?></a>
                        </h3>
                        
                        <span class="card__category" style="color: #ff55a5; font-size: 12px; font-weight: bold;">
                            <?php echo strtoupper($item['content_category']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="col-12" style="margin-top: 50px; text-align: center;">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" style="opacity: 0.2; margin-bottom: 20px;">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <p style="color: #666; font-size: 16px;">Sorry, we couldn't find any results matching your search.</p>
                    <a href="index.php" class="sign__btn" style="width: 200px; margin: 20px auto;">BACK TO HOME</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>