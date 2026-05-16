<?php
require_once 'header.php';

// --- AKSİYON: RADARI TEMİZLE ---
if (isset($_GET['action']) && $_GET['action'] == 'purge_radar') {
    $db->query("TRUNCATE TABLE search_logs");
    echo "<script>window.location.href='search_radar.php?sysmsg=RADAR_WIPED_CLEAN';</script>";
    exit;
}

// Tablo var mı kontrolü (İlk girişte hata vermemesi için)
try {
    $db->query("SELECT 1 FROM search_logs LIMIT 1");
    $tablo_var = true;
} catch (PDOException $e) {
    $tablo_var = false;
}

$enCokArananlar = [];
$sonAramalar = [];

if ($tablo_var) {
    // En Çok Aranan 10 Kelime (Trendler)
    $trendSorgu = $db->query("SELECT query, COUNT(*) as tekrar_sayisi FROM search_logs GROUP BY query ORDER BY tekrar_sayisi DESC LIMIT 10");
    $enCokArananlar = $trendSorgu->fetchAll(PDO::FETCH_ASSOC);

    // En Son Yapılan 50 Arama (Canlı Akış)
    $canliSorgu = $db->query("SELECT query, search_date FROM search_logs ORDER BY id DESC LIMIT 50");
    $sonAramalar = $canliSorgu->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    /* CYBERPUNK 2077 NETWATCH THEME - RADAR EDITION */
    body, .cyber-bg { background-color: #080202 !important; background-image: radial-gradient(circle at 50% 50%, #1a0505 0%, #050101 100%); color: #ff3333; font-family: 'Consolas', 'Courier New', monospace; }
    
    .cyber-panel { background: rgba(20, 5, 5, 0.85); border: 1px solid #ff2a2a; border-radius: 0; padding: 20px; position: relative; box-shadow: inset 0 0 20px rgba(255, 42, 42, 0.15), 0 0 10px rgba(0,0,0,0.8); backdrop-filter: blur(5px); }
    .cyber-panel::before { content: 'SEARCH_INFILTRATOR // ACTIVE'; position: absolute; top: -10px; left: 15px; background: #080202; padding: 0 8px; color: #00f0ff; font-size: 10px; font-weight: bold; letter-spacing: 2px; }
    .cyber-panel::after { content: ''; position: absolute; bottom: -2px; right: -2px; width: 15px; height: 15px; border-bottom: 3px solid #ff2a2a; border-right: 3px solid #ff2a2a; }
    
    .cyber-title { color: #fff; text-shadow: 0 0 8px #ff2a2a; text-transform: uppercase; letter-spacing: 3px; border-bottom: 1px solid rgba(255, 42, 42, 0.4); padding-bottom: 8px; margin-bottom: 20px; font-size: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;}
    
    .matrix-terminal { background: #030000; border: 1px solid #ff2a2a; padding: 15px; height: 500px; overflow-y: auto; color: #bbb; font-size: 12px; font-family: monospace; box-shadow: inset 0 0 40px rgba(255, 0, 0, 0.1); position: relative; }
    .matrix-terminal::before { content: " "; display: block; position: absolute; top: 0; left: 0; bottom: 0; right: 0; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); z-index: 2; background-size: 100% 2px, 3px 100%; pointer-events: none; }
    
    .matrix-terminal::-webkit-scrollbar { width: 6px; }
    .matrix-terminal::-webkit-scrollbar-thumb { background: #00f0ff; }
    .matrix-terminal::-webkit-scrollbar-track { background: #0a0202; }
    
    .cyber-table { width: 100%; border-collapse: collapse; font-size: 12px; z-index: 5; position: relative;}
    .cyber-table th { color: #fcee0a; text-align: left; padding: 10px; border-bottom: 1px solid #ff2a2a; text-transform: uppercase; letter-spacing: 1px; }
    .cyber-table td { padding: 8px 10px; border-bottom: 1px dotted #330a0a; color: #fff; }
    .cyber-table tr:hover td { background: rgba(0, 240, 255, 0.1); color: #00f0ff; }

    .cyber-btn { background: #220505; border: 1px solid #ff2a2a; color: #ff2a2a; font-weight: bold; font-family: monospace; padding: 8px 15px; cursor: pointer; text-transform: uppercase; transition: all 0.2s; letter-spacing: 2px; font-size: 10px; text-decoration: none; display: inline-block;}
    .cyber-btn:hover { background: #ff2a2a; color: #000; box-shadow: 0 0 15px rgba(255, 42, 42, 0.6); text-decoration: none;}

    .trend-badge { background: #ff2a2a; color: #000; padding: 2px 6px; font-weight: bold; border-radius: 2px; font-size: 10px; margin-right: 10px;}

    .blink_me { animation: blinker 1s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    
    .text-cyan { color: #00f0ff !important; }
    .text-yellow { color: #fcee0a !important; }
    .text-red { color: #ff2a2a !important; }

    .sys-msg-banner { background: #fcee0a; color: #000; padding: 10px; text-align: center; font-weight: bold; letter-spacing: 2px; margin-bottom: 20px; animation: slideDown 0.5s ease-out; }
    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<main class="main cyber-bg">
    <div class="container-fluid" style="padding-top: 30px; padding-bottom: 50px;">
        
        <?php if(isset($_GET['sysmsg'])): ?>
            <div class="sys-msg-banner">
                >>> SYSTEM_OVERRIDE: <?php echo htmlspecialchars($_GET['sysmsg']); ?> <<<
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-12 col-xl-5">
                <div class="cyber-panel" style="margin-bottom: 25px; height: calc(100% - 25px);">
                    <div class="cyber-title">
                        => GLOBAL_TRENDS
                        <span class="blink_me text-cyan" style="font-size:10px;">ANALYZING...</span>
                    </div>
                    
                    <p style="color: #bbb; font-size: 12px; margin-bottom: 20px;">
                        > These are the most highly requested assets. If missing, deploy bots immediately to satisfy user demand.
                    </p>

                    <table class="cyber-table">
                        <thead>
                            <tr>
                                <th>TARGET_QUERY</th>
                                <th style="text-align: right;">HITS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($enCokArananlar) > 0): foreach ($enCokArananlar as $index => $trend): ?>
                            <tr>
                                <td>
                                    <span class="trend-badge">#<?php echo $index + 1; ?></span> 
                                    <span class="text-cyan" style="font-weight:bold; font-size: 14px;"><?php echo htmlspecialchars($trend['query']); ?></span>
                                </td>
                                <td style="text-align: right; color: #fcee0a; font-weight: bold;">
                                    <?php echo $trend['tekrar_sayisi']; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="2">> NO_TREND_DATA_AVAILABLE</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="cyber-panel">
                    <div class="cyber-title">
                        => LIVE_INTERCEPT_FEED
                        <a href="?action=purge_radar" class="cyber-btn" onclick="return confirm('WARNING: Erase all search memory?');">> PURGE_RADAR</a>
                    </div>
                    
                    <div class="matrix-terminal">
                        <div style="margin-bottom: 15px; border-bottom: 1px dashed #ff2a2a; padding-bottom: 10px;">
                            <span class="text-white">netrunner@oniklotho:~/radar$</span> ./tail_queries --live<br>
                            <span class="text-cyan">[SYS] Intercepting user search queries in real-time...</span>
                        </div>

                        <table class="cyber-table">
                            <thead>
                                <tr>
                                    <th>TIMESTAMP</th>
                                    <th>USER_QUERY</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($sonAramalar) > 0): foreach ($sonAramalar as $arama): ?>
                                <tr>
                                    <td class="text-yellow" style="width: 140px;">[<?php echo date('m-d H:i', strtotime($arama['search_date'])); ?>]</td>
                                    <td style="font-size: 13px;"><?php echo htmlspecialchars($arama['query']); ?></td>
                                    <td class="text-cyan" style="width: 80px;">INTERCEPTED</td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="3">> AWAITING_USER_INPUT...</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <br><span class="blink_me text-red">_</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>