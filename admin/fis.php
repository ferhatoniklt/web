<?php
ob_start();
session_start();
require_once '../baglan.php';

// Güvenlik Kontrolü
if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Arasaka Multi-Terminal v4.2</title>
    <link rel="stylesheet" href="css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap');

        :root {
            --cy-yellow: #fcee0a;
            --cy-blue: #00f3ff;
            --cy-red: #ff003c;
            --cy-bg: #0b101a;
        }

        body {
            background-color: var(--cy-bg);
            color: var(--cy-blue);
            font-family: 'Rajdhani', sans-serif;
            margin: 0;
            padding-top: 80px;
            display: flex;
            background-image: radial-gradient(circle, rgba(0, 243, 255, 0.05) 0%, transparent 70%);
        }

        .main-content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .admin-panel-card {
            background: #151f30;
            border: 3px solid var(--cy-yellow);
            border-left: 10px solid var(--cy-yellow);
            padding: 25px;
            width: 450px;
            box-shadow: 8px 8px 0px var(--cy-red);
            margin-bottom: 30px;
            clip-path: polygon(0 0, 95% 0, 100% 5%, 100% 100%, 5% 100%, 0% 95%);
        }

        .admin-panel-card h1 { font-family: 'Orbitron', sans-serif; font-size: 14px; color: var(--cy-yellow); margin-bottom: 20px; border-bottom: 1px solid var(--cy-red); padding-bottom: 5px; }

        .control-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 11px; color: var(--cy-red); margin-bottom: 5px; font-weight: bold; }
        
        .admin-panel-card input, .admin-panel-card select {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--cy-blue);
            padding: 10px;
            color: var(--cy-yellow);
            font-family: 'Orbitron', sans-serif;
            outline: none;
            font-size: 13px;
        }

        .btn-container { display: flex; gap: 10px; width: 100%; }
        .btn-cy {
            flex: 1;
            padding: 15px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            clip-path: polygon(5% 0, 100% 0, 95% 100%, 0 100%);
        }
        .btn-print { background: var(--cy-red); color: white; }
        .btn-jpg { background: var(--cy-blue); color: black; }

        /* Resim Dönüştürme Alanı - Çerçeveyi Resme Dahil Etmemek İçin Ayrı Stil */
        #capture-area {
            background: white;
            padding: 10px;
            /* Ekranda görmen için çok hafif bir çizgi, resme dahil olmaz */
            outline: 1px solid rgba(0, 243, 255, 0.3);
        }

        .receipt {
            background-color: white;
            width: 50mm;
            min-height: 110mm;
            color: #000;
            font-family: 'Courier New', Courier, monospace;
            padding: 2px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .h-rec { text-align: center; font-size: 10px; margin-bottom: 8px; }
        .div-rec { border-top: 1px dashed #000; margin: 6px 0; }
        .row-rec { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 2px; }
        .plate-rec { text-align: center; font-size: 20px; border: 2px solid #000; margin: 8px 0; padding: 3px; }
        .total-rec { font-size: 14px; margin-top: 10px; display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 5px; }
        .qr-rec { text-align: center; margin: 15px 0; }
        #qr-image { width: 110px; height: 110px; border: 1px solid #000; }
        .f-rec { text-align: center; font-size: 8px; margin-top: 10px; }

        [contenteditable="true"]:hover { background: #ffffd0; cursor: crosshair; }

        @media print {
            .sidebar, .header, .admin-panel-card, .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100%; }
            #capture-area { outline: none; padding: 0; }
            .receipt { width: 100%; }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header__content">
            <a href="index.php" class="header__logo"><img src="img/logo.svg" alt="Oniklotho"></a>
        </div>
    </header>

    <div class="sidebar">
        <div class="sidebar__user">
            <div class="sidebar__user-title">
                <span>Yönetici</span>
                <p><?php echo $_SESSION['admin_name']; ?></p>
            </div>
        </div>
        <ul class="sidebar__nav">
            <li class="sidebar__nav-item"><a href="index.php" class="sidebar__nav-link"><span>Geri Dön</span></a></li>
        </ul>
    </div>

    <main class="main-content">
        <div class="admin-panel-card no-print">
            <h1>// ARASAKA_RECEIPT_V4.2_FIXED</h1>
            
            <div class="control-grid">
                <div class="input-group">
                    <label>ŞABLON:</label>
                    <select id="receipt-selector" onchange="loadReceipt()">
                        <option value="petrol">PETROL (QR VAR)</option>
                        <option value="market">MARKET (QR YOK)</option>
                        <option value="otopark">OTOPARK (QR YOK)</option>
                        <option value="muminler">MÜMİNLER OTOMOTİV (POS)</option>
                        
                    </select>
                </div>
                <div class="input-group">
                    <label>QR VERİSİ:</label>
                    <input type="text" id="qr-data" value="34CAY597-UTTS" oninput="updateQR()">
                </div>
            </div>

            <div class="btn-container">
                <button class="btn-cy btn-print" onclick="window.print()">YAZDIR</button>
                <button class="btn-cy btn-jpg" onclick="generateJPG()">JPG KAYDET</button>
            </div>
        </div>

        <div id="capture-area">
            <div class="receipt" id="receipt-target"></div>
        </div>
    </main>

    <script>
        const templates = {
         
                     muminler: `
                <div class="h-rec" style="font-size: 11px; line-height: 1.2;">
                    <span contenteditable="true">MÜMİNLER OTOMOTİV</span><br>
                    <span contenteditable="true">TİC. VE SAN LTD. ŞTİ.</span><br>
                    <span contenteditable="true">ŞB. 677/36 SK. NO: 16-6 SAN. SİT.</span><br>
                    <span contenteditable="true">BUCA İZMİR</span><br>
                    <span contenteditable="true">EGE VD. 6270007908</span><br>
                    <span contenteditable="true">TEL : 0232 282 09 08</span><br>
                    <span contenteditable="true">TEŞEKKÜR EDERİZ</span>
                </div>
                <div class="div-rec"></div>
                <div class="row-rec" style="margin-top: 5px;">
                    <span contenteditable="true">06-05-2026</span>
                    <span contenteditable="true">FİŞ NO: 21</span>
                </div>
                <div class="row-rec">
                    <span contenteditable="true">SAAT: 14:21</span>
                </div>
                <div style="text-align: center; margin: 10px 0; font-weight: bold; font-size: 14px;" contenteditable="true">BİLGİ FİŞİ</div>
                <div class="row-rec">
                    <span>TÜR:</span>
                    <span contenteditable="true">FATURA SERİ NO</span>
                </div>
                <div class="row-rec">
                    <span>MÜŞTERİ VKN:</span>
                    <span contenteditable="true">11111111111</span>
                </div>
                <div class="row-rec">
                    <span>FATURA:</span>
                    <span contenteditable="true">65101</span>
                </div>
                <div class="div-rec"></div>
                <div class="row-rec">
                    <span contenteditable="true">YEDEK PARÇA</span>
                    <span contenteditable="true">*8.000,00</span>
                </div>
                <div class="row-rec">
                    <span contenteditable="true">TOPKDV</span>
                    <span contenteditable="true">*1.333,33</span>
                </div>
                <div class="total-rec">
                    <span>TOPLAM</span>
                    <span contenteditable="true">*8.000,00</span>
                </div>
                <div class="row-rec" style="margin-top:5px;">
                    <span contenteditable="true">KREDİ KARTI</span>
                    <span contenteditable="true">*8.000,00</span>
                </div>
                <div class="div-rec"></div>
                <div style="text-align: center; font-size: 11px; font-weight: bold; margin: 5px 0;" contenteditable="true">MALİ DEĞERİ YOK<br>İRSALİYE YERİNE GEÇMEZ</div>
                <div class="div-rec"></div>
                <div style="text-align: center; font-size: 14px; margin-bottom: 5px;" contenteditable="true">SATIŞ</div>
                <div class="row-rec" style="font-size: 11px; font-weight: bold;">
                    <span contenteditable="true">8.000,00 TL</span>
                    <span contenteditable="true">9040</span>
                </div>
                <div class="row-rec" style="font-size: 8px;">
                    <span contenteditable="true">İŞYERİ NO: 06900001114576</span>
                    <span contenteditable="true">POS NO: 01639147</span>
                </div>
                <div class="row-rec" style="font-size: 8px;">
                    <span contenteditable="true">İŞLEM NO:JP0022 BATCH NO:0693</span>
                    <span contenteditable="true">14:22</span>
                </div>
                <div class="row-rec" style="font-size: 8px;">
                    <span contenteditable="true">REF NO: 612614979088</span>
                </div>
                <div class="f-rec" style="text-align: left; font-size: 8px;">
                    <span contenteditable="true">ONAY KODU: 828895</span><br><br>
                    <span contenteditable="true">TUTAR KARŞILIĞI MAL VEYA HİZMET ALDIM</span><br>
                    <span contenteditable="true">BU BELGEYİ SAKLAYINIZ (MÜŞTERİ NÜSHASI)</span>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Vak%C4%B1fBank_logo.svg/512px-Vak%C4%B1fBank_logo.svg.png" style="width: 120px; filter: grayscale(100%) contrast(200%);">
                </div>
                <div class="f-rec" contenteditable="true">EKÜ NO: 1 Z NO: 1.792<br>MF AT 0000207771</div>
            `,
            
            petrol: `
                <div class="h-rec" contenteditable="true">YILDIZ PETROL VE TİC. LTD. ŞTİ.<br>KARACAAĞAÇ MAH. 4021 SOKAK NO:1<br>35390 BUCA/İZMİR<br>TEL: 02328570011</div>
                <div class="div-rec"></div>
                <div class="row-rec"><span>MERSIS:</span><span contenteditable="true">0965004108100015</span></div>
                <div class="row-rec"><span>SICIL:</span><span contenteditable="true">827</span></div>
                <div class="div-rec"></div>
                <div class="row-rec"><span contenteditable="true">03-05-2026</span><span contenteditable="true">12:30</span></div>
                <div class="row-rec"><span>FIS NO:</span><span contenteditable="true">0029</span></div>
                <div class="plate-rec" contenteditable="true">34CAY597</div>
                <div class="row-rec"><span contenteditable="true">46,540 LT X 73,07</span></div>
                <div class="row-rec"><span contenteditable="true">MOTORIN SVPD</span><span contenteditable="true">*3.400,68</span></div>
                <div class="div-rec"></div>
                <div class="total-rec"><span>TOPLAM</span><span contenteditable="true">*3.400,68</span></div>
                <div class="qr-rec">
                    <div style="font-weight: bold; font-size: 16px;" contenteditable="true">UTTS</div>
                    <img id="qr-image" src="">
                </div>
                <div class="f-rec" contenteditable="true">EKÜ NO: 1 Z NO: 1.204<br>AFAU 0000004245</div>
            `,
            market: `
                <div class="h-rec" contenteditable="true">ONIKLOTHO SUPERMARKET<br>ŞİRİNYER ŞUBESİ<br>İZMİR / BUCA</div>
                <div class="div-rec"></div>
                <div class="row-rec"><span contenteditable="true">03-05-2026</span><span contenteditable="true">18:45</span></div>
                <div class="div-rec"></div>
                <div class="row-rec"><span contenteditable="true">EKMEK TAM BUGDAY</span><span contenteditable="true">15.00</span></div>
                <div class="row-rec"><span contenteditable="true">SÜT YARIM YAĞLI</span><span contenteditable="true">32.50</span></div>
                <div class="div-rec"></div>
                <div class="total-rec"><span>TOPLAM</span><span contenteditable="true">47.50</span></div>
                <div class="f-rec" contenteditable="true">TEŞEKKÜR EDERİZ</div>
            `,
            otopark: `
                <div class="h-rec" contenteditable="true">BUCA BELEDİYESİ<br>OTOPARK İŞLETMELERİ</div>
                <div class="div-rec"></div>
                <div class="plate-rec" contenteditable="true">35 IZMR 35</div>
                <div class="row-rec"><span>GİRİŞ:</span><span contenteditable="true">03.05.2026 09:00</span></div>
                <div class="row-rec"><span>ÇIKIŞ:</span><span contenteditable="true">03.05.2026 19:00</span></div>
                <div class="div-rec"></div>
                <div class="total-rec"><span>ÜCRET</span><span contenteditable="true">120.00 TL</span></div>
                <div class="f-rec" contenteditable="true">İYİ YOLCULUKLAR</div>
            `
        };

        function loadReceipt() {
            const selector = document.getElementById('receipt-selector');
            const target = document.getElementById('receipt-target');
            target.innerHTML = templates[selector.value];
            if(selector.value === "petrol") { updateQR(); }
        }

        function updateQR() {
            const data = document.getElementById('qr-data').value;
            const qrImg = document.getElementById('qr-image');
            if(qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data)}`;
            }
        }

        async function generateJPG() {
            const area = document.getElementById('capture-area');
            // JPG oluştururken ölçeği artırıp temiz zemin alıyoruz
            const canvas = await html2canvas(area, { 
                scale: 3, 
                backgroundColor: "#ffffff",
                logging: false,
                useCORS: true
            });
            const link = document.createElement('a');
            link.download = `rec_${Date.now()}.jpg`;
            link.href = canvas.toDataURL("image/jpeg", 0.9);
            link.click();
        }

        window.onload = loadReceipt;
    </script>
</body>
</html>
