<?php
require_once 'header.php';

// Fetch recent scans
$gecmisSorgu = $db->query("SELECT * FROM bot_history ORDER BY id DESC LIMIT 15");
$gecmis = $gecmisSorgu->fetchAll(PDO::FETCH_ASSOC);

// Fetch last scan for each bot
$lastScans = [];
$checkLast = $db->query("SELECT bot_file, target_url FROM bot_history WHERE id IN (SELECT MAX(id) FROM bot_history GROUP BY bot_file)");
while ($row = $checkLast->fetch(PDO::FETCH_ASSOC)) {
    $lastScans[$row['bot_file']] = $row['target_url'];
}
?>

<style>
    /* CYBERPUNK 2077 NETWATCH THEME */
    body,
    .cyber-bg {
        background-color: #080202 !important;
        background-image: radial-gradient(circle at 50% 50%, #1a0505 0%, #050101 100%);
        color: #ff3333;
        font-family: 'Consolas', 'Courier New', monospace;
    }

    .cyber-panel {
        background: rgba(20, 5, 5, 0.85);
        border: 1px solid #ff2a2a;
        border-radius: 0;
        padding: 20px;
        position: relative;
        box-shadow: inset 0 0 20px rgba(255, 42, 42, 0.15), 0 0 10px rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .cyber-panel::before {
        content: 'NETWATCH_NODE // AUTHORIZED';
        position: absolute;
        top: -10px;
        left: 15px;
        background: #080202;
        padding: 0 8px;
        color: #ff2a2a;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 2px;
    }

    .cyber-panel::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 15px;
        height: 15px;
        border-bottom: 3px solid #00f0ff;
        border-right: 3px solid #00f0ff;
    }

    .cyber-title {
        color: #fff;
        text-shadow: 0 0 8px #ff2a2a;
        text-transform: uppercase;
        letter-spacing: 3px;
        border-bottom: 1px solid rgba(255, 42, 42, 0.4);
        padding-bottom: 8px;
        margin-bottom: 20px;
        font-size: 15px;
        font-weight: bold;
    }

    .cyber-input {
        background: #110202 !important;
        border: 1px solid #aa1111 !important;
        color: #ff9999 !important;
        font-family: monospace;
        border-radius: 0;
        padding: 12px;
        width: 100%;
        transition: 0.3s;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.5);
    }

    .cyber-input:focus {
        border-color: #00f0ff !important;
        color: #00f0ff !important;
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.3) !important;
        outline: none;
    }

    .cyber-label {
        color: #cc2222;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
    }

    .cyber-btn {
        background: #220505;
        border: 1px solid #ff2a2a;
        color: #ff2a2a;
        font-weight: bold;
        font-family: monospace;
        padding: 14px 20px;
        width: 100%;
        cursor: pointer;
        text-transform: uppercase;
        transition: all 0.2s;
        letter-spacing: 3px;
        position: relative;
        overflow: hidden;
    }

    .cyber-btn:hover {
        background: #ff2a2a;
        color: #000;
        box-shadow: 0 0 25px rgba(255, 42, 42, 0.6);
        text-shadow: none;
    }

    .cyber-btn:disabled {
        background: #110505;
        border-color: #441111;
        color: #441111;
        cursor: not-allowed;
        box-shadow: none;
    }

    .cyber-memory {
        background: #0a0101;
        border-left: 4px solid #00f0ff;
        padding: 12px;
        margin-top: 15px;
        font-size: 12px;
        line-height: 1.6;
        color: #bbb;
    }

    .cyber-list li {
        border-bottom: 1px dotted #330a0a;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cyber-list li:last-child {
        border-bottom: none;
    }

    .matrix-terminal {
        background: #030000;
        border: 1px solid #ff2a2a;
        padding: 15px;
        height: 650px;
        overflow-y: auto;
        color: #ff4444;
        font-size: 13px;
        font-family: monospace;
        box-shadow: inset 0 0 40px rgba(255, 0, 0, 0.1);
        position: relative;
    }

    .matrix-terminal::before {
        content: " ";
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
        z-index: 2;
        background-size: 100% 2px, 3px 100%;
        pointer-events: none;
    }

    .matrix-terminal::-webkit-scrollbar {
        width: 6px;
    }

    .matrix-terminal::-webkit-scrollbar-thumb {
        background: #ff2a2a;
    }

    .matrix-terminal::-webkit-scrollbar-track {
        background: #0a0202;
    }

    .blink_me {
        animation: blinker 1s linear infinite;
    }

    @keyframes blinker {
        50% {
            opacity: 0;
        }
    }

    .text-cyan {
        color: #00f0ff !important;
    }

    .text-yellow {
        color: #fcee0a !important;
    }

    .text-white {
        color: #fff !important;
    }
</style>

<main class="main cyber-bg">
    <div class="container-fluid" style="padding-top: 30px; padding-bottom: 50px;">
        <div class="row">

            <div class="col-12 col-lg-5">

                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h4 style="margin:0; color:#ff2a2a; font-size:16px; font-weight:bold; letter-spacing: 2px;">[
                            O.N.I.K.L.O.T.H.O C.O.R.E ]</h4>
                        <span class="blink_me"
                            style="background:#ff2a2a; color:#000; padding:2px 10px; font-size:11px; font-weight:bold;">UPLINK
                            SECURE</span>
                    </div>
                    <p style="color:#aa4444; font-size:12px; margin-top:12px; margin-bottom:0; font-family: monospace;">
                        > ENCRYPTION: SHA-256 ACTIVE<br>
                        > STREMIO API: <span class="text-cyan">CONNECTED</span><br>
                        > ROOT PRIVILEGES: <span class="text-yellow">GRANTED</span>
                    </p>
                </div>

                <div class="cyber-panel" style="margin-bottom: 25px;">
                    <div class="cyber-title">=> ACQUIRE_TARGET</div>
                    <div class="row">
                        <div class="col-12">
                            <label class="cyber-label">SYS.SELECT_PROTOCOL()</label>
                            <select id="bot_type" class="cyber-input" style="margin-bottom: 20px;">
                                <option value="">> AWAITING_INPUT...</option>
                                <option value="bot_movie.php">[ CINEMETA_V3 ] 🎬 Movie Scraper (Stremio)</option>
                                <option value="bot_tvseries.php">[ CINEMETA_V3 ] 📺 TV Series Scraper (Stremio)</option>
                                <option value="bot_gamestorrent.php">[ FITGIRL_API ] 🎮 Game Repacks</option>
                                <option value="bot_games2.php">[ 1377X_API ] 🏴‍☠️ DODI/KaOsKrew Games</option>
                                <option value="bot_apk.php">[ APPS2APP ] 📱 APK Mods & Premium</option>
                                <option value="bot_nsfw_gallery.php">[ PORNPICS ] 🔥 NSFW Image Galleries</option>
                                <option value="bot_nsfw_video.php">[ XVIDEOS ] 🎥 NSFW Video Database</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div id="target_url_container" style="display:none;">
                                <label class="cyber-label">TARGET_URL_ENDPOINT</label>
                                <input type="text" id="target_url" class="cyber-input" placeholder="https://..."
                                    style="margin-bottom: 10px;">
                            </div>

                            <div id="movie_page_container" style="display:none;">
                                <label class="cyber-label text-cyan">1. MASS INFILTRATION (PAGE SCAN)</label>
                                <div style="display: flex; align-items: center; gap:10px; margin-bottom: 20px;">
                                    <span class="text-cyan" style="font-size:20px;">></span>
                                    <input type="number" id="movie_page" class="cyber-input" value="1" min="1"
                                        style="font-size: 18px; font-weight: bold; width: 120px; text-align: center; border-color:#00f0ff !important;">
                                </div>

                                <label class="cyber-label text-yellow">2. SURGICAL STRIKE (SINGLE IMDB ID)</label>
                                <div style="display: flex; align-items: center; gap:10px;">
                                    <span class="text-yellow" style="font-size:20px;">></span>
                                    <input type="text" id="movie_single" class="cyber-input"
                                        placeholder="e.g., tt1234567"
                                        style="border-color:#fcee0a !important; color:#fcee0a !important;">
                                </div>
                                <small
                                    style="color:#773333; font-size:10px; margin-top:8px; display:block; font-weight:bold;">
                                    <label class="cyber-label text-yellow"><a href="https://web.strem.io/#/"
                                            target="_blank">https://web.strem.io/#/ </a></label>
                                </small>
                            </div>

                            <div id="memory_box" class="cyber-memory" style="display:none;">
                                <div id="last_scan_info"></div>
                            </div>
                        </div>

                        <div class="col-12" style="margin-top: 25px;">
                            <button type="button" id="start-bot" class="cyber-btn">> EXECUTE_BREACH</button>
                        </div>
                    </div>
                </div>

                <div class="cyber-panel">
                    <div class="cyber-title">=> OPERATION_LOGS</div>
                    <ul class="cyber-list" id="history-ul"
                        style="list-style: none; padding: 0; margin: 0; font-size: 11px;">
                        <?php foreach ($gecmis as $g): ?>
                            <li>
                                <div>
                                    <strong class="text-cyan"
                                        style="font-size:12px;">[<?php echo strtoupper(str_replace(['bot_', '.php'], '', $g['bot_file'])); ?>]</strong><br>
                                    <span
                                        style="color: #aa4444; font-size: 10px;"><?php echo htmlspecialchars($g['target_url']); ?></span>
                                </div>
                                <span style="color: #666;"><?php echo date('d/m H:i', strtotime($g['scan_date'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="cyber-panel" style="padding:0;">
                    <div
                        style="background:#110202; border-bottom:1px solid #ff2a2a; padding:12px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <span
                            style="color:#ff2a2a; font-size:13px; font-weight:bold; letter-spacing:1px;">netrunner@oniklotho:~/deck$</span>
                        <span id="bot-status"
                            style="color:#fff; font-size:10px; background:#000; padding:4px 10px; border:1px solid #666; letter-spacing:1px;">IDLE</span>
                    </div>
                    <div id="bot-console" class="matrix-terminal">
                        <span class="text-white">Oniklotho Cyber-OS v4.0 Initialized.</span><br>
                        <span style="color: #ff4444;">Bypassing corporate firewalls... Success.</span><br>
                        <span class="text-cyan">ICE barriers disabled. Standing by for command.</span><br>
                        <span class="text-yellow">Ready. <span class="blink_me">_</span></span><br>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    const lastScans = <?php echo json_encode($lastScans); ?>;

    document.getElementById('bot_type').addEventListener('change', function () {
        const urlInput = document.getElementById('target_url');
        const urlContainer = document.getElementById('target_url_container');
        const info = document.getElementById('last_scan_info');
        const memoryBox = document.getElementById('memory_box');
        const moviePageContainer = document.getElementById('movie_page_container');
        const selectedBot = this.value;

        // DEFAULT DIRECTORIES
        let defaultUrls = {
            'bot_gamestorrent.php': 'https://fitgirl-repacks.site/page/1/',
            'bot_games2.php': 'https://1377x.to/user/DODI/1/',
            'bot_nsfw_gallery.php': 'https://www.pornpics.com/recent/1/',
            'bot_apk.php': 'https://apps2app.com/page/1/',
            'bot_nsfw_video.php': 'https://www.xvideos.com/new/1'
        };

        if (selectedBot) {
            memoryBox.style.display = 'block';

            // STREMIO PROTOCOLS (MOVIE OR TV SERIES)
            if (selectedBot === 'bot_movie.php' || selectedBot === 'bot_tvseries.php') {
                urlContainer.style.display = 'none';
                moviePageContainer.style.display = 'block';

                let lastUrl = lastScans[selectedBot];
                let nextPg = 1;
                let lastPgInfo = "NO PREVIOUS DATA FOUND.";

                if (lastUrl) {
                    let match = lastUrl.match(/Sayfa:\s*(\d+)/i);
                    if (match) {
                        let lastPg = parseInt(match[1]);
                        nextPg = lastPg + 1;
                        lastPgInfo = `<span style="color:#884444;">LAST KNOWN COORDINATE:</span> <b class="text-white">PAGE ${lastPg}</b>`;
                    }
                }

                document.getElementById('movie_page').value = nextPg;
                document.getElementById('movie_single').value = '';
                info.innerHTML = `${lastPgInfo}<br><span style="color:#884444;">RECOMMENDED VECTOR:</span> <b class="text-cyan" style="font-size:14px;">=> PAGE ${nextPg}</b><br><br><span class="text-yellow">* Client-Side Engine Active. Stealth: 100%.</span>`;

                // STANDARD SCRAPERS
            } else {
                urlContainer.style.display = 'block';
                moviePageContainer.style.display = 'none';

                let lastUrl = lastScans[selectedBot];
                if (lastUrl && lastUrl.includes('page')) {
                    let nextPageUrl = lastUrl.replace(/(\/page\/|page=)(\d+)/, (match, p1, p2) => p1 + (parseInt(p2) + 1));
                    if (nextPageUrl === lastUrl) nextPageUrl = defaultUrls[selectedBot];
                    urlInput.value = nextPageUrl;
                    info.innerHTML = `<span style="color:#884444;">LAST KNOWN URL:</span><br><b style="color:#666;">${lastUrl}</b><br><br><span style="color:#884444;">NEXT AUTO-TARGET:</span><br><b class="text-cyan">=> ${nextPageUrl}</b>`;
                } else if (lastUrl && lastUrl.includes('/new/')) {
                    let nextPageUrl = lastUrl.replace(/\/new\/(\d+)/, (match, p1) => '/new/' + (parseInt(p1) + 1));
                    urlInput.value = nextPageUrl;
                    info.innerHTML = `<span style="color:#884444;">LAST KNOWN URL:</span><br><b style="color:#666;">${lastUrl}</b><br><br><span style="color:#884444;">NEXT AUTO-TARGET:</span><br><b class="text-cyan">=> ${nextPageUrl}</b>`;
                } else {
                    urlInput.value = defaultUrls[selectedBot] || '';
                    info.innerHTML = "<span class='text-cyan'>TARGET FOLDER EMPTY. INITIATING SEQUENCE ONE.</span>";
                }
            }
        } else {
            urlContainer.style.display = 'none';
            moviePageContainer.style.display = 'none';
            memoryBox.style.display = 'none';
        }
    });

    document.getElementById('start-bot').addEventListener('click', function () {
        const consoleBox = document.getElementById('bot-console');
        const statusBox = document.getElementById('bot-status');
        const botFile = document.getElementById('bot_type').value;
        const btn = this;

        if (!botFile) { alert("ERROR: NO PROTOCOL SELECTED!"); return; }

        // ==========================================
        // STREMIO ENGINE (MOVIE & TV SERIES)
        // ==========================================
        if (botFile === 'bot_movie.php' || botFile === 'bot_tvseries.php') {
            const pageNum = document.getElementById('movie_page').value;
            const singleInput = document.getElementById('movie_single').value.trim();
            const stremioType = (botFile === 'bot_movie.php') ? 'movie' : 'series';

            btn.disabled = true;

            // SURGICAL STRIKE (IMDB ID)
            if (singleInput !== "") {
                let imdbMatch = singleInput.match(/tt\d{7,10}/);
                if (!imdbMatch) {
                    consoleBox.innerHTML += `<br><span style='color:#ff2a2a;'>[ERR] INVALID IMDB FORMAT. ABORTING.</span>`;
                    btn.disabled = false;
                    return;
                }

                let imdbId = imdbMatch[0];
                consoleBox.innerHTML += `<br><span class="text-white">netrunner@oniklotho:~/deck$</span> ./inject_stremio --target=${imdbId} --type=${stremioType}`;
                statusBox.innerHTML = "EXTRACTING DATA...";
                statusBox.style.background = "#fcee0a";
                statusBox.style.color = "#000";

                let apiUrl = `https://v3-cinemeta.strem.io/meta/${stremioType}/${imdbId}.json`;

                fetch(apiUrl)
                    .then(res => res.json())
                    .then(async data => {
                        if (!data || !data.meta) {
                            consoleBox.innerHTML += `<br><span style='color:#ff2a2a;'>[ERR] ASSET NOT FOUND IN DB.</span>`;
                            btn.disabled = false; return;
                        }

                        let item = data.meta;
                        consoleBox.innerHTML += `<br><span class='text-cyan'>[SYS] TARGET LOCKED: ${item.name}. UPLOADING TO MAINFRAME...</span><br>`;

                        let payload = {
                            imdb_id: item.id,
                            title: item.name,
                            year: item.releaseInfo ? item.releaseInfo.substring(0, 4) : new Date().getFullYear().toString(),
                            genres: item.genres ? item.genres.join(', ') : 'Entertainment',
                            poster: item.poster
                        };

                        try {
                            let response = await fetch('../' + botFile, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            let text = await response.text();
                            consoleBox.innerHTML += "<br>" + text;
                        } catch (e) {
                            consoleBox.innerHTML += `<br><span style='color:#ff2a2a;'>[ERR] PACKET LOSS DETECTED. INJECTION FAILED.</span>`;
                        }

                        await fetch(`../${botFile}?action=history&page=TekilID:${imdbId}`);

                        consoleBox.innerHTML += `<br>----------------------------------------<br><span style='color:#ff2a2a; font-weight:bold;'>[SUCCESS] INFILTRATION COMPLETE. CONNECTION SEVERED.</span>`;
                        statusBox.innerHTML = "COMPLETED";
                        statusBox.style.background = "#ff2a2a";
                        statusBox.style.color = "#000";
                        consoleBox.scrollTop = consoleBox.scrollHeight;
                        setTimeout(() => { location.reload(); }, 2000);
                    })
                    .catch(err => {
                        consoleBox.innerHTML += `<br><span style='color:#ff2a2a; font-weight:bold;'>[FATAL] CONNECTION REFUSED BY CINEMETA.</span>`;
                        btn.disabled = false;
                    });

                return;
            }

            // MASS INFILTRATION (PAGE BASED)
            consoleBox.innerHTML += `<br><span class="text-white">netrunner@oniklotho:~/deck$</span> ./run_mass_scan --type=${stremioType} --page=${pageNum}`;
            consoleBox.innerHTML += `<br><span class="text-cyan">[OK] BROWSER ENGINE INITIALIZED.</span>`;
            statusBox.innerHTML = "INFILTRATING...";
            statusBox.style.background = "#fcee0a";
            statusBox.style.color = "#000";

            let skip = (pageNum - 1) * 20;
            let apiUrl = `https://v3-cinemeta.strem.io/catalog/${stremioType}/top/skip=${skip}.json`;

            fetch(apiUrl)
                .then(res => res.json())
                .then(async data => {
                    if (!data || !data.metas || data.metas.length === 0) {
                        consoleBox.innerHTML += `<br><span style='color:#ff2a2a;'>[ERR] NO DATA FOUND AT COORDINATE (PAGE ${pageNum}).</span>`;
                        btn.disabled = false; return;
                    }

                    let items = data.metas;
                    consoleBox.innerHTML += `<br><span class='text-cyan'>[SYS] DATA POOL LOCATED. EXTRACTING ${items.length} ASSETS TO MAINFRAME...</span><br>`;

                    for (let i = 0; i < items.length; i++) {
                        let item = items[i];
                        let payload = {
                            imdb_id: item.id,
                            title: item.name,
                            year: item.releaseInfo ? item.releaseInfo.substring(0, 4) : new Date().getFullYear().toString(),
                            genres: item.genres ? item.genres.join(', ') : 'Entertainment',
                            poster: item.poster
                        };

                        try {
                            let response = await fetch('../' + botFile, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            let text = await response.text();
                            consoleBox.innerHTML += "<br>" + text;
                            consoleBox.scrollTop = consoleBox.scrollHeight;
                        } catch (e) {
                            consoleBox.innerHTML += `<br><span style='color:#ff2a2a;'>[ERR] PACKET DROP: ${item.name}.</span>`;
                        }
                    }

                    await fetch(`../${botFile}?action=history&page=${pageNum}`);
                    consoleBox.innerHTML += `<br>----------------------------------------<br><span style='color:#ff2a2a; font-weight:bold;'>[SUCCESS] MASS INFILTRATION COMPLETE. DB UPDATED.</span>`;
                    statusBox.innerHTML = "COMPLETED";
                    statusBox.style.background = "#ff2a2a";
                    statusBox.style.color = "#000";
                    btn.disabled = false;

                    setTimeout(() => { location.reload(); }, 2000);
                })
                .catch(err => {
                    consoleBox.innerHTML += `<br><span style='color:#ff2a2a; font-weight:bold;'>[FATAL] NETWORK INTERFERENCE DETECTED.</span>`;
                    btn.disabled = false;
                });
            return;
        }

        // ==========================================
        // OTHER PROTOCOLS (SERVER SIDE EXECUTION)
        // ==========================================
        const targetUrl = document.getElementById('target_url').value;
        if (!targetUrl) { alert("ERROR: TARGET URL EMPTY!"); return; }

        consoleBox.innerHTML += `<br><span class="text-white">netrunner@oniklotho:~/deck$</span> ./execute_crawler --target="${targetUrl}"`;
        statusBox.innerHTML = "EXTRACTING...";
        statusBox.style.background = "#fcee0a";
        statusBox.style.color = "#000";
        btn.disabled = true;

        fetch('../' + botFile + '?target=' + encodeURIComponent(targetUrl))
            .then(response => response.text())
            .then(data => {
                consoleBox.innerHTML += "<br>" + data;
                consoleBox.innerHTML += `<br><span style='color: #ff2a2a; font-weight:bold;'>[SUCCESS] OPERATION FINISHED. SYSTEM REBOOTING...</span>`;
                statusBox.innerHTML = "COMPLETED";
                statusBox.style.background = "#ff2a2a";
                statusBox.style.color = "#000";
                btn.disabled = false;
                consoleBox.scrollTop = consoleBox.scrollHeight;

                setTimeout(() => { location.reload(); }, 2000);
            })
            .catch(error => {
                consoleBox.innerHTML += `<br><span style="color: #ff2a2a;">[FATAL ERROR]:</span> ${error}`;
                btn.disabled = false;
                statusBox.innerHTML = "ERROR";
                statusBox.style.background = "#ff2a2a";
            });
    });
</script>

<?php require_once 'footer.php'; ?>