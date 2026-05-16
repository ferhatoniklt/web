<?php 
require_once 'header.php'; 
$link = isset($_GET['link']) ? base64_decode($_GET['link']) : 'index.php';
?>

<main class="main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 text-center" style="margin-top: 100px; margin-bottom: 100px;">
                <h2 style="color: #fff;">Your download link is being prepared...</h2>
                <p style="color: #666;">Please wait <span id="timer" style="color: #ff55a5; font-weight: bold;">10</span> seconds.</p>
                
                <div style="width: 100%; height: 250px; background: rgba(255,255,255,0.05); margin: 30px 0; border: 1px dashed #444; line-height: 250px; color: #444;">
                    ADVERTISEMENT AREA
                </div>

                <a id="download-btn" href="<?php echo $link; ?>" class="sign__btn" style="display: none; width: 200px; margin: 0 auto; background: #00ff00; color: #000;">START DOWNLOAD</a>
            </div>
        </div>
    </div>
</main>

<script>
let count = 5;
let timer = setInterval(function() {
    count--;
    document.getElementById('timer').innerText = count;
    if (count <= 0) {
        clearInterval(timer);
        document.getElementById('download-btn').style.display = 'block';
        document.getElementById('timer').parentElement.style.display = 'none';
    }
}, 1000);
</script>

<?php require_once 'footer.php'; ?>