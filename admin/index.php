<?php 
// --- AJAX CANLI VERİ KÖPRÜSÜ (Sayfa yenilenmeden verileri çeker) ---
if (isset($_GET['ajax_live'])) {
    require_once 'baglan.php';
    header('Content-Type: application/json');
    
    // Güncel Rakamlar
    $total = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1")->fetchColumn();
    $queue = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND google_ping = 0")->fetchColumn();
    
    // Son 1 İçerik
    $lastContent = $db->query("SELECT id, content_name, content_type FROM contents ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    // Son 1 Arama (Tablo varsa)
    $lastSearch = null;
    try {
        $lastSearch = $db->query("SELECT id, query FROM search_logs ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}

    echo json_encode([
        'total' => $total,
        'queue' => $queue,
        'content' => $lastContent,
        'search' => $lastSearch
    ]);
    exit;
}

require_once 'header.php'; 

// --- 1. VERİTABANI İLK YÜKLEME İSTATİSTİKLERİ ---
try {
    $totalItems = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1")->fetchColumn();
    $safeTotal = $totalItems > 0 ? $totalItems : 1;
    $todayItems = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
    $indexQueue = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND google_ping = 0")->fetchColumn();

    $countMovies = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND content_type = 2")->fetchColumn();
    $countGames = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND content_type = 1")->fetchColumn();
    $countNsfw = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND content_type = 4")->fetchColumn();
    $countApk = $db->query("SELECT COUNT(*) FROM contents WHERE content_aktiflik = 1 AND content_type = 5")->fetchColumn();

    $pctMovies = round(($countMovies / $safeTotal) * 100);
    $pctGames = round(($countGames / $safeTotal) * 100);
    $pctNsfw = round(($countNsfw / $safeTotal) * 100);
    $pctApk = round(($countApk / $safeTotal) * 100);

} catch (PDOException $e) {
    $totalItems = $todayItems = $indexQueue = 0;
    $pctMovies = $pctGames = $pctNsfw = $pctApk = 0;
    $countMovies = $countGames = $countNsfw = $countApk = 0;
}
?>

<style>
    /* CYBERPUNK 2077 NETWATCH THEME - HUD EDITION */
    body, .cyber-bg { background-color: #080202 !important; background-image: radial-gradient(circle at 50% 50%, #1a0505 0%, #050101 100%); color: #ff3333; font-family: 'Consolas', 'Courier New', monospace; }
    
    .cyber-panel { background: rgba(20, 5, 5, 0.85); border: 1px solid #ff2a2a; border-radius: 0; padding: 25px; position: relative; box-shadow: inset 0 0 20px rgba(255, 42, 42, 0.15), 0 0 10px rgba(0,0,0,0.8); backdrop-filter: blur(5px); }
    .cyber-panel::before { content: 'MAIN_HUD // OVERSEER'; position: absolute; top: -10px; left: 15px; background: #080202; padding: 0 8px; color: #ff2a2a; font-size: 10px; font-weight: bold; letter-spacing: 2px; }
    .cyber-panel::after { content: ''; position: absolute; bottom: -2px; right: -2px; width: 15px; height: 15px; border-bottom: 3px solid #00f0ff; border-right: 3px solid #00f0ff; }
    
    .cyber-title { color: #fff; text-shadow: 0 0 8px #ff2a2a; text-transform: uppercase; letter-spacing: 3px; border-bottom: 1px solid rgba(255, 42, 42, 0.4); padding-bottom: 8px; margin-bottom: 20px; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: space-between; }
    
    .stat-box { background: #0a0101; border: 1px solid #330a0a; padding: 20px; text-align: center; position: relative; overflow: hidden; transition: 0.3s; }
    .stat-box:hover { border-color: #ff2a2a; box-shadow: inset 0 0 15px rgba(255,42,42,0.2); transform: translateY(-3px); }
    .stat-value { font-size: 32px; font-weight: bold; margin-bottom: 5px; color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.5); font-family: impact, sans-serif; letter-spacing: 2px;}
    .stat-label { font-size: 11px; color: #ff2a2a; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; }
    
    .progress-container { background: #110202; border: 1px solid #330a0a; height: 15px; width: 100%; position: relative; margin-top: 5px; margin-bottom: 15px; }
    .progress-bar { height: 100%; position: relative; display: flex; align-items: center; justify-content: flex-end; padding-right: 5px; font-size: 9px; color: #000; font-weight: bold; }
    
    /* CANLI TERMİNAL KUTUSU */
    .matrix-terminal { background: #030000; border: 1px solid #00f0ff; padding: 15px; height: 250px; overflow-y: hidden; color: #bbb; font-size: 12px; font-family: monospace; box-shadow: inset 0 0 40px rgba(0, 240, 255, 0.1); position: relative; }
    .matrix-terminal::before { content: " "; display: block; position: absolute; top: 0; left: 0; bottom: 0; right: 0; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); z-index: 2; background-size: 100% 2px, 3px 100%; pointer-events: none; }
    .log-line { margin-bottom: 4px; border-bottom: 1px dashed rgba(255,255,255,0.05); padding-bottom: 2px; animation: fadeIn 0.3s ease-in;}
    
    .neon-cyan { background: #00f0ff; box-shadow: 0 0 10px #00f0ff; }
    .neon-yellow { background: #fcee0a; box-shadow: 0 0 10px #fcee0a; }
    .neon-magenta { background: #ff00ff; box-shadow: 0 0 10px #ff00ff; }
    .neon-red { background: #ff2a2a; box-shadow: 0 0 10px #ff2a2a; }
    
    .text-cyan { color: #00f0ff !important; }
    .text-yellow { color: #fcee0a !important; }
    .text-magenta { color: #ff00ff !important; }
    .text-red { color: #ff2a2a !important; }
    .text-green { color: #00ff00 !important; }

    .cyber-btn { background: #220505; border: 1px solid #ff2a2a; color: #ff2a2a; font-weight: bold; font-family: monospace; padding: 12px 20px; cursor: pointer; text-transform: uppercase; transition: all 0.2s; letter-spacing: 2px; font-size: 12px; text-decoration: none; display: block; text-align: center; }
    .cyber-btn:hover { background: #ff2a2a; color: #000; box-shadow: 0 0 15px rgba(255, 42, 42, 0.6); text-decoration: none; }
    
    .blink_me { animation: blinker 1s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    @keyframes fadeIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
</style>

<main class="main cyber-bg">
    <div class="container-fluid" style="padding-top: 30px; padding-bottom: 50px;">
        
        <div class="row mb-4">
            <div class="col-12">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid rgba(255,42,42,0.3); padding-bottom: 10px;">
                    <div>
                        <h1 style="color: #fff; font-size: 28px; font-weight: bold; margin:0; text-transform: uppercase; letter-spacing: 4px; text-shadow: 0 0 10px #ff2a2a;">[ O.N.I.K.L.O.T.H.O ]</h1>
                        <span style="color: #00f0ff; font-size: 12px; letter-spacing: 2px;">=> COMMAND_CENTER_ACTIVE</span>
                    </div>
                    <span class="blink_me" style="background: #ff2a2a; color: #000; padding: 4px 12px; font-size: 12px; font-weight: bold;">LIVE SYSTEM</span>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-12 col-xl-8">
                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div class="cyber-title">
                        => GLOBAL_TELEMETRY 
                        <span style="font-size: 11px; color: #666; font-weight: normal;">SYNCED: <span id="sync-status" class="text-green">REAL-TIME</span></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3">
                            <div class="stat-box">
                                <div class="stat-value text-cyan" id="stat-total"><?php echo number_format($totalItems); ?></div>
                                <div class="stat-label">TOTAL ASSETS</div>
                                <div style="position: absolute; top: 0; left: 0; width: 2px; height: 100%; background: #00f0ff;"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="stat-box">
                                <div class="stat-value text-yellow" id="stat-today">+<?php echo number_format($todayItems); ?></div>
                                <div class="stat-label">ACQUIRED IN 24H</div>
                                <div style="position: absolute; top: 0; left: 0; width: 2px; height: 100%; background: #fcee0a;"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="stat-box">
                                <div class="stat-value text-red" id="stat-queue"><?php echo number_format($indexQueue); ?></div>
                                <div class="stat-label">GOOGLE INDEX QUEUE</div>
                                <div style="position: absolute; top: 0; left: 0; width: 2px; height: 100%; background: #ff2a2a;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cyber-panel" style="margin-bottom: 25px; border-color: #00f0ff;">
                    <div class="cyber-title" style="color:#00f0ff; border-bottom-color: rgba(0, 240, 255, 0.4);">
                        => NETWORK_TRAFFIC_MONITOR
                        <span class="blink_me" style="font-size: 10px; color:#fcee0a;">LISTENING...</span>
                    </div>
                    <div class="matrix-terminal" id="live-terminal">
                        <div class="log-line"><span class="text-white">netrunner@oniklotho:~/deck$</span> ./run_network_sniffer --live</div>
                        <div class="log-line"><span class="text-cyan">[SYS] Establishing secure uplink to Databank... OK.</span></div>
                        <div class="log-line"><span class="text-cyan">[SYS] Monitoring active cron daemons and user search vectors...</span></div>
                        </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="cyber-panel" style="margin-bottom: 25px; height: calc(100% - 25px);">
                    <div class="cyber-title">=> DATABANK_DISTRIBUTION</div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #fff; font-weight: bold; letter-spacing: 1px;">
                        <span>CINEMA_ARCHIVE</span>
                        <span class="text-cyan"><?php echo $countMovies; ?> ASSETS (<?php echo $pctMovies; ?>%)</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar neon-cyan" style="width: <?php echo $pctMovies; ?>%;"><?php echo $pctMovies > 5 ? $pctMovies.'%' : ''; ?></div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #fff; font-weight: bold; letter-spacing: 1px;">
                        <span>GAMING_TORRENTS</span>
                        <span class="text-yellow"><?php echo $countGames; ?> ASSETS (<?php echo $pctGames; ?>%)</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar neon-yellow" style="width: <?php echo $pctGames; ?>%;"><?php echo $pctGames > 5 ? $pctGames.'%' : ''; ?></div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #fff; font-weight: bold; letter-spacing: 1px;">
                        <span>APK_MODS</span>
                        <span class="text-magenta"><?php echo $countApk; ?> ASSETS (<?php echo $pctApk; ?>%)</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar neon-magenta" style="width: <?php echo $pctApk; ?>%;"><?php echo $pctApk > 5 ? $pctApk.'%' : ''; ?></div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #fff; font-weight: bold; letter-spacing: 1px;">
                        <span>ADULT_VAULT</span>
                        <span class="text-red"><?php echo $countNsfw; ?> ASSETS (<?php echo $pctNsfw; ?>%)</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar neon-red" style="width: <?php echo $pctNsfw; ?>%;"><?php echo $pctNsfw > 5 ? $pctNsfw.'%' : ''; ?></div>
                    </div>
                    
                    <div style="margin-top: 40px;">
                        <div class="cyber-title">=> QUICK_EXECUTE</div>
                        <a href="bot_control.php" class="cyber-btn" style="margin-bottom:10px;">> LAUNCH_BOT_CONTROL</a>
                        <a href="logcontrol.php" class="cyber-btn" style="border-color:#00f0ff; color:#00f0ff; margin-bottom:10px;">> VIEW_DIAGNOSTICS</a>
                        <a href="search_radar.php" class="cyber-btn" style="border-color:#fcee0a; color:#fcee0a;">> SEARCH_INFILTRATOR</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    // --- O.N.I.K.L.O.T.H.O LIVE RADAR & HAREKET SİSTEMİ ---
    let lastContentId = null;
    let lastSearchId = null;
    let terminal = document.getElementById('live-terminal');
    
    // Rastgele sistem kontrol mesajları (Ekranın sürekli hareket etmesi için)
    const systemMessages = [
        "[OK] Bypassing sector 7G security protocols...",
        "[OK] Memory sectors stabilized. No leaks detected.",
        "[OK] Ping received from Cloudflare gateway.",
        "[SYS] Scanning DB for orphaned links... Clear.",
        "[SYS] Recalibrating index algorithm...",
        "[OK] Firewall node active. Blocking unauthorized crawlers.",
        "..."
    ];

    function addLog(message, isAlert = false) {
        let div = document.createElement('div');
        div.className = 'log-line';
        let time = new Date().toLocaleTimeString('en-US', { hour12: false });
        div.innerHTML = `<span style="color:#555;">[${time}]</span> ${message}`;
        terminal.appendChild(div);
        
        // Terminali çok şişirmemek için eski logları sil (Son 12 satırı tut)
        if(terminal.children.length > 12) {
            terminal.removeChild(terminal.children[0]);
        }
    }

    function fetchLiveData() {
        // Her 3 saniyede bir veritabanına sinsi ping
        fetch('index.php?ajax_live=1')
            .then(res => res.json())
            .then(data => {
                // 1. İSTATİSTİKLERİ CANLI GÜNCELLE
                document.getElementById('stat-total').innerText = data.total.toLocaleString();
                document.getElementById('stat-queue').innerText = data.queue.toLocaleString();
                
                let hasAction = false;

                // 2. YENİ İÇERİK EKLENDİYSE (Cron çalıştıysa veya panelden eklediysen)
                if (data.content && data.content.id !== lastContentId) {
                    if(lastContentId !== null) { // İlk yüklemede uyarı verme, sonrakilerde ver
                        addLog(`<span class="text-green font-weight-bold">[NEW_ASSET_DETECTED]</span> => <span class="text-white">${data.content.content_name}</span> has been injected to DB!`, true);
                        hasAction = true;
                    }
                    lastContentId = data.content.id;
                }

                // 3. YENİ BİR ARAMA YAPILDIYSA (Zihin Radarı)
                if (data.search && data.search.id !== lastSearchId) {
                    if(lastSearchId !== null) {
                        addLog(`<span class="text-yellow font-weight-bold">[USER_INTERCEPT]</span> => Target is searching for: <span class="text-magenta">"${data.search.query}"</span>`, true);
                        hasAction = true;
                    }
                    lastSearchId = data.search.id;
                }

                // 4. EĞER HİÇBİR ŞEY OLMADIYSA BİLE EKRAN HAREKET ETSİN DİYE SAHTE KOD YAZ
                if(!hasAction) {
                    // %30 ihtimalle rastgele sistem mesajı atsın (çok hızlı akmasın diye)
                    if(Math.random() > 0.7) {
                        let randomMsg = systemMessages[Math.floor(Math.random() * systemMessages.length)];
                        addLog(`<span class="text-cyan">${randomMsg}</span>`);
                    }
                }
            })
            .catch(err => console.log("Radar parazitlendi..."));
    }

    // Ekranı canlandır: Her 3 saniyede bir verileri çek
    setInterval(fetchLiveData, 3000);
    // İlk yüklemede ID'leri sabitlemek için 1 kere çalıştır
    fetchLiveData();
</script>

<?php require_once 'footer.php'; ?>
