<?php
require_once 'header.php'; // Veritabanı ve session bağlantıları

// Dosya Yolları (Admin klasöründen ana dizine çıktığını varsayıyoruz)
$logFile = '../logs/google_index.log';
$cacheFile = '../cache/sitemap_cache.xml';

// --- AKSİYONLAR (MEMORY PURGE) ---
if (isset($_GET['action'])) {
    $sysMsg = "";
    if ($_GET['action'] == 'purge_logs') {
        if (file_exists($logFile)) { file_put_contents($logFile, ''); }
        $sysMsg = "INDEX_LOGS_PURGED_SUCCESSFULLY";
    }
    elseif ($_GET['action'] == 'purge_cache') {
        if (file_exists($cacheFile)) { unlink($cacheFile); }
        $sysMsg = "SITEMAP_CACHE_DESTROYED";
    }
    elseif ($_GET['action'] == 'purge_history') {
        $db->query("TRUNCATE TABLE bot_history");
        $sysMsg = "BOT_HISTORY_WIPED_CLEAN";
    }
    
    // Yenileme
    echo "<script>window.location.href='logcontrol.php?sysmsg=" . urlencode($sysMsg) . "';</script>";
    exit;
}

// --- LOG DOSYASI OKUMA (Son 100 Satır) ---
$logContent = "> NO_INDEX_LOG_DATA_FOUND. API HAS NOT EXECUTED YET.";
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false && count($lines) > 0) {
        $lastLines = array_slice($lines, -100); // Ekranı dondurmamak için son 100 satır
        $logContent = implode("<br>", $lastLines);
        // Log renklendirme (Hacker stili)
        $logContent = str_replace('[INFO]', '<span class="text-cyan">[INFO]</span>', $logContent);
        $logContent = str_replace('[HATA]', '<span class="text-red">[FATAL]</span>', $logContent);
        $logContent = str_replace('[UYARI]', '<span class="text-yellow">[WARN]</span>', $logContent);
        $logContent = str_replace('[BASLIK]', '<span class="text-white" style="font-weight:bold;">[SYS]</span>', $logContent);
    }
}

// --- SITEMAP CACHE DURUMU ---
$cacheStats = "<span class='text-red'>OFFLINE_OR_DESTROYED</span>";
if (file_exists($cacheFile)) {
    $size = round(filesize($cacheFile) / 1024, 2) . ' KB';
    $time = date('Y-m-d H:i:s', filemtime($cacheFile));
    $cacheStats = "<span class='text-cyan'>ONLINE</span> | <span class='text-yellow'>SIZE:</span> $size | <span class='text-yellow'>LAST_BUILD:</span> $time";
}

// --- BOT GEÇMİŞİ ÇEKME ---
$historySorgu = $db->query("SELECT * FROM bot_history ORDER BY id DESC LIMIT 50");
$botHistory = $historySorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* CYBERPUNK 2077 NETWATCH THEME */
    body, .cyber-bg { background-color: #080202 !important; background-image: radial-gradient(circle at 50% 50%, #1a0505 0%, #050101 100%); color: #ff3333; font-family: 'Consolas', 'Courier New', monospace; }
    
    .cyber-panel { background: rgba(20, 5, 5, 0.85); border: 1px solid #ff2a2a; border-radius: 0; padding: 20px; position: relative; box-shadow: inset 0 0 20px rgba(255, 42, 42, 0.15), 0 0 10px rgba(0,0,0,0.8); backdrop-filter: blur(5px); }
    .cyber-panel::before { content: 'DIAGNOSTICS_NODE // AUTHORIZED'; position: absolute; top: -10px; left: 15px; background: #080202; padding: 0 8px; color: #ff2a2a; font-size: 10px; font-weight: bold; letter-spacing: 2px; }
    .cyber-panel::after { content: ''; position: absolute; bottom: -2px; right: -2px; width: 15px; height: 15px; border-bottom: 3px solid #00f0ff; border-right: 3px solid #00f0ff; }
    
    .cyber-title { color: #fff; text-shadow: 0 0 8px #ff2a2a; text-transform: uppercase; letter-spacing: 3px; border-bottom: 1px solid rgba(255, 42, 42, 0.4); padding-bottom: 8px; margin-bottom: 20px; font-size: 15px; font-weight: bold; }
    
    .cyber-btn { background: #220505; border: 1px solid #ff2a2a; color: #ff2a2a; font-weight: bold; font-family: monospace; padding: 8px 15px; cursor: pointer; text-transform: uppercase; transition: all 0.2s; letter-spacing: 2px; font-size: 11px; text-decoration: none; display: inline-block;}
    .cyber-btn:hover { background: #ff2a2a; color: #000; box-shadow: 0 0 15px rgba(255, 42, 42, 0.6); text-decoration: none;}
    
    .matrix-terminal { background: #030000; border: 1px solid #ff2a2a; padding: 15px; height: 500px; overflow-y: auto; color: #bbb; font-size: 12px; font-family: monospace; box-shadow: inset 0 0 40px rgba(255, 0, 0, 0.1); position: relative; word-wrap: break-word;}
    .matrix-terminal::before { content: " "; display: block; position: absolute; top: 0; left: 0; bottom: 0; right: 0; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); z-index: 2; background-size: 100% 2px, 3px 100%; pointer-events: none; }
    
    .matrix-terminal::-webkit-scrollbar { width: 6px; }
    .matrix-terminal::-webkit-scrollbar-thumb { background: #ff2a2a; }
    .matrix-terminal::-webkit-scrollbar-track { background: #0a0202; }
    
    .cyber-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .cyber-table th { color: #00f0ff; text-align: left; padding: 10px; border-bottom: 1px solid #ff2a2a; text-transform: uppercase; letter-spacing: 1px; }
    .cyber-table td { padding: 8px 10px; border-bottom: 1px dotted #330a0a; color: #bbb; }
    .cyber-table tr:hover td { background: rgba(255, 42, 42, 0.05); color: #fff; }

    .blink_me { animation: blinker 1s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    
    .text-cyan { color: #00f0ff !important; }
    .text-yellow { color: #fcee0a !important; }
    .text-white { color: #fff !important; }
    .text-red { color: #ff2a2a !important; }

    .sys-msg-banner { background: #00f0ff; color: #000; padding: 10px; text-align: center; font-weight: bold; letter-spacing: 2px; margin-bottom: 20px; animation: slideDown 0.5s ease-out; }
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
            <div class="col-12 col-md-6">
                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div class="cyber-title">=> SITEMAP_CACHE_MONITOR</div>
                    <p style="font-size: 13px; color: #bbb; margin-bottom: 15px;">
                        > STATUS: <?php echo $cacheStats; ?><br>
                        > LOCATION: <span class="text-white">/cache/sitemap_cache.xml</span>
                    </p>
                    <a href="?action=purge_cache" class="cyber-btn" onclick="return confirm('WARNING: Destroy sitemap cache?');">> PURGE_CACHE</a>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div class="cyber-title">=> SYSTEM_HEALTH</div>
                    <p style="font-size: 13px; color: #bbb; margin-bottom: 15px;">
                        > GOOGLE_INDEX_API: <span class="text-cyan">STANDBY</span><br>
                        > CRON_DAEMON: <span class="text-yellow">UNKNOWN (CHECK HOSTING)</span>
                    </p>
                    <a href="logcontrol.php" class="cyber-btn" style="border-color:#00f0ff; color:#00f0ff;">> REFRESH_DATA</a>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255, 42, 42, 0.4); padding-bottom: 8px;">
                        <span class="cyber-title" style="border:none; margin:0; padding:0;">=> INDEX_SYNDICATE_LOGS</span>
                        <a href="?action=purge_logs" class="cyber-btn" style="padding: 4px 10px; font-size: 10px;" onclick="return confirm('WARNING: Erase index logs permanently?');">> PURGE_LOGS</a>
                    </div>
                    
                    <div class="matrix-terminal" id="log-terminal">
                        <span class="text-white">netrunner@oniklotho:~/logs$</span> tail -n 100 google_index.log<br><br>
                        <?php echo $logContent; ?>
                        <br><br><span class="blink_me text-yellow">_</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="cyber-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255, 42, 42, 0.4); padding-bottom: 8px;">
                        <span class="cyber-title" style="border:none; margin:0; padding:0;">=> INFILTRATION_HISTORY</span>
                        <a href="?action=purge_history" class="cyber-btn" style="padding: 4px 10px; font-size: 10px;" onclick="return confirm('WARNING: Wipe entire bot database history?');">> WIPE_DB</a>
                    </div>
                    
                    <div class="matrix-terminal" style="padding: 0;">
                        <table class="cyber-table">
                            <thead>
                                <tr>
                                    <th>PROTOCOL</th>
                                    <th>TARGET_VECTOR</th>
                                    <th>TIMESTAMP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($botHistory) > 0): foreach ($botHistory as $b): ?>
                                <tr>
                                    <td><strong class="text-cyan"><?php echo str_replace(['bot_', '.php'], '', strtoupper($b['bot_file'])); ?></strong></td>
                                    <td><?php echo mb_substr(htmlspecialchars($b['target_url']), 0, 40) . '...'; ?></td>
                                    <td class="text-yellow" style="font-size:10px;"><?php echo date('Y-m-d H:i', strtotime($b['scan_date'])); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center; padding: 20px;">> NO_INFILTRATION_DATA_FOUND</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    // Terminali otomatik olarak en alta kaydır
    window.onload = function() {
        var term = document.getElementById("log-terminal");
        term.scrollTop = term.scrollHeight;
    }
</script>

<?php require_once 'footer.php'; ?>