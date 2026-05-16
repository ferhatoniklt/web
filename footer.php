<!-- footer -->
<footer class="footer">
	<div class="container">
		<div class="row">
		
		<div class="row">
			<div class="col-12">
				<div class="footer__content">
					<div class="footer__links">
						<a href="privacy.html">Privacy policy</a>
						<a href="privacy.html">Terms and conditions</a>
					</div>
					<small class="footer__copyright">© ONIKLOTHO, 2026. Created by <a
							href="https://themeforest.net/user/dmitryvolkov/portfolio" target="_blank">F.K
							</a>.</small>
				</div>
			</div>
		</div>
	</div>
</footer>
<!-- end footer -->
<script>
	// Sayfadaki her şey yüklendikten sonra çalıştır
	window.addEventListener('load', function () {
		var $owl = $('#subscriptions');

		// Eğer carousel zaten başlatılmışsa önce yok et (çakışmayı önlemek için)
		if ($owl.hasClass('owl-loaded')) {
			$owl.trigger('destroy.owl.carousel');
		}

		// Yeniden başlat
		$owl.owlCarousel({
			mouseDrag: true,
			touchDrag: true,
			dots: false,
			loop: true,            // Sonsuz döngü
			autoplay: true,        // Otomatik kayma aktif
			autoplayTimeout: 2000, // 2 saniyede bir kayar (hızlı denemek için 2000 yaptık)
			autoplayHoverPause: true, // Fare gelince durur
			smartSpeed: 1000,      // Geçiş yumuşaklığı
			margin: 30,
			responsive: {
				0: { items: 2 },
				576: { items: 3 },
				768: { items: 4 },
				1200: { items: 6 }
			}
		});
	});
</script>

<script>
	// Sayfa tamamen yüklendiğinde çalıştır
	$(window).on('load', function () {

		// 1. Üst Carousel (Hero) Ayarı
		var heroCarousel = $('#flixtv-hero');
		if (heroCarousel.length) {
			heroCarousel.trigger('destroy.owl.carousel'); // Varsa eski ayarları sil
			heroCarousel.owlCarousel({
				mouseDrag: true,
				touchDrag: true,
				dots: false,
				loop: true,           // Sonsuz döngü şart
				autoplay: true,       // Otomatik kayma
				autoplayTimeout: 3000, // 3 saniyede bir
				autoplayHoverPause: true,
				smartSpeed: 800,
				margin: 20,
				responsive: {
					0: { items: 2 },
					576: { items: 2 },
					768: { items: 3 },
					1200: { items: 4 }
				}
			});
		}

		// 2. Alt Carousel (Subscriptions/Games) Ayarı
		var subCarousel = $('#subscriptions');
		if (subCarousel.length) {
			subCarousel.trigger('destroy.owl.carousel');
			subCarousel.owlCarousel({
				mouseDrag: true,
				touchDrag: true,
				dots: false,
				loop: true,
				autoplay: true,
				autoplayTimeout: 2500,
				autoplayHoverPause: true,
				smartSpeed: 800,
				margin: 30,
				responsive: {
					0: { items: 2 },
					576: { items: 3 },
					768: { items: 4 },
					1200: { items: 6 }
				}
			});
		}
	});
</script>


<!-- JS -->
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/slider-radio.js"></script>
<script src="js/select2.min.js"></script>
<script src="js/smooth-scrollbar.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/plyr.min.js"></script>
<script src="js/main.js"></script>

<div id="age-verify"
	style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; align-items: center; justify-content: center; text-align: center; color: #fff; font-family: sans-serif;">
	<div
		style="background: #151f30; padding: 40px; border-radius: 20px; border: 1px solid #ff55a5; max-width: 500px; margin: 20px;">
		<h2 style="color: #ff55a5;">ADULT CONTENT</h2>
		<p>This website contains adult-themed content and is only suitable for those 18 years or older. Are you over 18?
		</p>
		<div style="display: flex; gap: 20px; justify-content: center; margin-top: 30px;">
			<button onclick="verifyAge()"
				style="background: #ff55a5; color: #fff; border: none; padding: 10px 40px; border-radius: 8px; cursor: pointer; font-weight: bold;">YES,
				I AM 18+</button>
			<button onclick="window.location.href='https://google.com'"
				style="background: transparent; color: #666; border: 1px solid #666; padding: 10px 40px; border-radius: 8px; cursor: pointer;">NO</button>
		</div>
	</div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#live_search").keyup(function(){
        var input = $(this).val();
        if(input != ""){
            $.ajax({
                url: "search_ajax.php",
                method: "POST",
                data: {query: input},
                success: function(data){
                    $("#search_results").html(data).fadeIn();
                }
            });
        } else {
            $("#search_results").fadeOut();
        }
    });

    // Dışarı tıklayınca sonuçları kapat
    $(document).mouseup(function(e) {
        var container = $(".header__search");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $("#search_results").fadeOut();
        }
    });
});
</script>


<script>
	function verifyAge() {
		document.getElementById('age-verify').style.display = 'none';
		localStorage.setItem('ageVerified', 'true');
	}
	// Eğer daha önce onayladıysa bir daha sorma
	if (localStorage.getItem('ageVerified') === 'true') {
		document.getElementById('age-verify').style.display = 'none';
	}
</script>
</body>

</html>