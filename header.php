<head>
    <base href="https://oniklotho.xyz/">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-2K2TMRJS4C"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-2K2TMRJS4C');
</script>
    <?php
    // 1. CANONICAL URL AYARLAMASI (Kopya İçerik ve Filtreleme Sorunlarını Çözer)
    $current_url = "https://oniklotho.xyz" . $_SERVER['REQUEST_URI'];
    if (isset($detay) && !empty($detay)) {
        // Detay sayfasındaysak sadece temiz URL'yi ver
        $canonical_url = "https://oniklotho.xyz/content/" . htmlspecialchars($detay['content_seourl']);
    } else {
        // Sayfalama parametrelerini (?g_p=2 vb.) SEO dostu tutmak için temizle
        $canonical_url = strtok($current_url, '?');
    }
    ?>
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <?php
    // === DİNAMİK SEO VE META ETİKETLERİ BAŞLANGIÇ ===
    if (isset($detay) && !empty($detay)):

        // Değişkenleri güvenli hale getir
        $tip = isset($detay['content_type']) ? (int) $detay['content_type'] : 1;
        $baslik = htmlspecialchars($detay['content_name']);
        $versiyon = htmlspecialchars($detay['content_size'] ?? 'Latest Version');
        $kategori = htmlspecialchars($detay['content_category'] ?? 'Premium Content');
        $kapak = htmlspecialchars($detay['content_kapakimg'] ?? 'nophoto.jpg');
        $kapak_full_path = "https://oniklotho.xyz/admin/assets/all_contents/" . $kapak;

        // 🚀 ZEKİ SEO METİN OLUŞTURUCU
        if ($tip == 5) {
            $seo_title = "Download $baslik MOD APK $versiyon (Premium Unlocked) - Oniklotho";
            $seo_desc = "Get $baslik $versiyon MOD APK for free on Oniklotho. Enjoy premium features unlocked, ad-free experience, and secure direct download.";
            $seo_key = "$baslik mod apk, $baslik premium, free android mods, apk mods download, oniklotho";
            $schema_type = "SoftwareApplication";
            $schema_cat = "MobileApplication";
        } elseif ($tip == 2) {
            $seo_title = "Watch $baslik Free Online (Full HD Streaming) - Oniklotho";
            $seo_desc = "Stream $baslik online for free in high definition. No registration, no ads. Experience the best cinema on Oniklotho Movies.";
            $seo_key = "watch $baslik free, stream $baslik online, free movies fhd, oniklotho cinema";
            $schema_type = "Movie";
            $schema_cat = "Movie";
        } elseif ($tip == 4) {
            $seo_title = "$baslik - Free High Quality NSFW Gallery - Oniklotho";
            $seo_desc = "Explore the $baslik gallery on Oniklotho. Premium quality images and exclusive content for free.";
            $seo_key = "$baslik nsfw, free adult galleries, oniklotho nsfw, high quality adult content";
            $schema_type = "ImageGallery";
            $schema_cat = "AdultContent";
        } else {
            $seo_title = "Download $baslik Free Repack ($versiyon) - Oniklotho Games";
            $seo_desc = "Download $baslik highly compressed repack for free. Direct links and torrents available with working crack. Size: $versiyon.";
            $seo_key = "$baslik repack, $baslik free download, compressed games, torrent download, $kategori, oniklotho";
            $schema_type = "SoftwareApplication";
            $schema_cat = "GameApplication";
        }
        ?>
        <title><?php echo $seo_title; ?></title>
        <meta name="description" content="<?php echo $seo_desc; ?>">
        <meta name="keywords" content="<?php echo $seo_key; ?>">

        <link rel="preload" as="image" href="<?php echo $kapak_full_path; ?>">

        <meta property="og:title" content="<?php echo $seo_title; ?>">
        <meta property="og:description" content="<?php echo $seo_desc; ?>">
        <meta property="og:image" content="<?php echo $kapak_full_path; ?>">
        <meta property="og:url" content="<?php echo $canonical_url; ?>">
        <meta property="og:type" content="article">
        <meta name="twitter:card" content="summary_large_image">

        <script type="application/ld+json">
                    {
                      "@context": "https://schema.org",
                      "@type": "<?php echo $schema_type; ?>",
                      "name": "<?php echo $baslik; ?>",
                      "operatingSystem": "Windows, Android",
                      "applicationCategory": "<?php echo $schema_cat; ?>",
                      "offers": {
                        "@type": "Offer",
                        "price": "0",
                        "priceCurrency": "USD"
                      },
                      "image": "<?php echo $kapak_full_path; ?>",
                      "description": "<?php echo $seo_desc; ?>"
                    }
                    </script>

        <?php
        // === ANASAYFA / LİSTELEME SEO ===
    else:
        ?>
        <title>Oniklotho - Premium Repack Games,Movies & APK</title>
        <meta name="description"
            content="Download the latest repack games, premium movies, and exclusive APK mods for free. High-speed direct links and daily updates on Oniklotho.">
        <meta name="keywords"
            content="fitgirl repack, free pc games, movie streaming free, mod apk download, oniklotho, crack games, torrent archive">

        <meta property="og:title" content="Oniklotho - Ultimate Digital Content Archive">
        <meta property="og:description" content="Explore the best collection of free repack games, movies, and APK mods.">
        <meta property="og:image" content="https://oniklotho.xyz/img/logo.png">
        <meta property="og:url" content="<?php echo $canonical_url; ?>">
        <meta property="og:type" content="website">
    <?php endif; ?>

    <meta name="robots" content="index, follow">
    <meta name="author" content="Oniklotho">
    <meta name="yandex-verification" content="a81fa6e9b48504a0" />

    <link rel="stylesheet" href="css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="headercss.css">

    <link rel="icon" type="image/png" href="img/favicon.ico" sizes="32x32">
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <style>
        .logout-btn {
            color: var(--pink);
            font-size: 12px;
            margin-left: 10px;
            border: 1px solid var(--pink);
            padding: 4px 10px;
            border-radius: 4px;
            text-decoration: none;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: var(--pink);
            color: #fff;
        }

        .signin-btn {
            background: var(--pink);
            color: #fff;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(255, 85, 165, 0.3);
        }

        .signin-btn:hover {
            background: #ff77b5;
            transform: translateY(-2px);
        }

        .header-nav a {
            margin: 0 15px;
            font-weight: 600;
            text-decoration: none;
            color: #fff;
            transition: 0.3s;
        }

        .header-nav a:hover {
            color: var(--pink);
        }

        /* CANLI ARAMA KUTUSU TASARIMI */
        .search-form {
            display: flex;
            align-items: center;
            background: #151f30;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: 0.3s;
            width: 100%;
        }

        .search-form:focus-within {
            border-color: var(--pink);
            box-shadow: 0 0 10px rgba(255, 85, 165, 0.2);
        }

        .search-input {
            width: 100%;
            background: transparent;
            border: none;
            color: #fff;
            padding: 10px 15px;
            font-size: 13px;
            outline: none;
        }

        .search-input::placeholder {
            color: #888;
        }

        .search-btn {
            background: transparent;
            border: none;
            padding: 0 15px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn:hover {
            opacity: 0.7;
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 15px;
            right: 15px;
            background: #0a101d;
            border: 1px solid var(--pink);
            border-radius: 8px;
            margin-top: 5px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.8);
            display: none;
            z-index: 10000;
        }

        .search-results-dropdown::-webkit-scrollbar {
            width: 5px;
        }

        .search-results-dropdown::-webkit-scrollbar-thumb {
            background: var(--pink);
            border-radius: 5px;
        }

        /* MOBİL UYUM */
        @media (max-width: 767px) {
            .search-wrapper {
                margin-top: 15px;
                margin-bottom: 5px;
            }

            .search-results-dropdown {
                left: 0;
                right: 0;
            }
        }
    </style>
</head>

<body>
    <header class="header-main">
        <div class="container-fluid" style="padding: 0 40px;">
            <div class="row align-items-center">

                <div class="col-6 col-md-2 text-left">
                    <a href="./">
                        <img src="img/logo.png" style="max-height: 45px;" alt="Oniklotho - Logo">
                    </a>
                </div>

                <div class="col-md-4 header-nav d-none d-md-flex justify-content-center">
                    <a href="./"
                        style="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'color: var(--pink);' : ''; ?>">HOME</a>
                    <a href="games">GAMES</a>
                    <a href="movies">MOVIES</a>
                    <a href="apk">APK</a>
                </div>
                <?php
                // --- O.N.I.K.L.O.T.H.O: SEARCH INFILTRATOR (Zihin Radarı) ---
                if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
                    $aranan_kelime = trim($_GET['q']);

                    // search_logs tablosu yoksa sessizce oluştur
                    try {
                        $db->query("SELECT id FROM search_logs LIMIT 1");
                    } catch (PDOException $e) {
                        $db->query("CREATE TABLE search_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query VARCHAR(255) NOT NULL,
            search_date DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    }

                    // Aranan kelimeyi kaydet (Spam'ı önlemek için çok uzun kelimeleri kırp)
                    $aranan_kelime = mb_substr($aranan_kelime, 0, 100);
                    $logla = $db->prepare("INSERT INTO search_logs (query) VALUES (?)");
                    $logla->execute([$aranan_kelime]);
                }
                // -------------------------------------------------------------
                ?>
                <div class="col-12 col-md-4 position-relative search-wrapper" style="z-index: 9999;">
                    <form action="search.php" method="GET" class="search-form" autocomplete="off">
                        <input type="text" name="q" id="live-search" class="search-input"
                            placeholder="Search games, movies, mods..." required>
                        <button type="submit" class="search-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="#ff55a5" stroke-width="2"
                                    stroke-linecap="round" />
                            </svg>
                        </button>
                    </form>
                    <div id="search-results" class="search-results-dropdown"></div>
                </div>

                <div class="col-6 col-md-2 text-right d-none d-md-block">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-info">
                            <span style="font-size:12px; font-weight:bold; color:#ccc;">Hi,
                                <?php echo mb_substr(htmlspecialchars($_SESSION['user_name']), 0, 8); ?></span>
                            <a href="logout" class="logout-btn">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="signin" class="signin-btn">Sign In</a>
                    <?php endif; ?>
                </div>

                <div class="col-6 text-right d-block d-md-none" style="position: absolute; right: 40px; top: 15px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout" class="logout-btn">Logout</a>
                    <?php else: ?>
                        <a href="signin" class="signin-btn" style="padding: 6px 12px; font-size: 11px;">Sign In</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            const searchInput = $('#live-search');
            const resultsBox = $('#search-results');

            // Klavyeden harf girildikçe tetiklenir
            searchInput.on('keyup', function () {
                let query = $(this).val();

                // En az 2 harf yazıldığında aramaya başla
                if (query.length >= 2) {
                    $.ajax({
                        url: 'search_ajax.php',
                        method: 'POST',
                        data: { query: query },
                        success: function (data) {
                            resultsBox.html(data).fadeIn();
                        }
                    });
                } else {
                    resultsBox.fadeOut();
                }
            });

            // Ekranda boş bir yere tıklanırsa arama kutusunu gizle
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search-wrapper').length) {
                    resultsBox.fadeOut();
                }
            });
        });
    </script>