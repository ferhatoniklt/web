<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Arasaka v11.0 - Total Control</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { background: #0b101a; color: #fff; font-family: sans-serif; margin: 0; padding: 10px; }
        .panel { background: #151f30; border: 2px solid #fcee0a; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        h2 { color: #fcee0a; margin: 0 0 15px 0; font-size: 18px; text-align: center; letter-spacing: 2px; }
        label { font-size: 11px; color: #00f3ff; font-weight: bold; display: block; margin-top: 10px; text-transform: uppercase; }
        textarea { width: 100%; height: 100px; background: #000; color: #fcee0a; border: 1px solid #00f3ff; font-size: 12px; padding: 10px; box-sizing: border-box; margin-top: 5px; }
        input[type="file"] { margin: 10px 0; color: #00f3ff; font-size: 12px; }
        .btns { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        button { padding: 18px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; text-transform: uppercase; font-size: 14px; transition: 0.3s; }
        #btn-gen { background: #00f3ff; color: #000; }
        #btn-save { background: #ff003c; color: #fff; display: none; }
        
        #output-area { display: flex; flex-direction: column; align-items: center; gap: 60px; margin-top: 30px; }

        /* VAKIFBANK / AKARYAKIT UZUN FİŞ TASARIMI */
        .receipt-card { 
            background: #fff !important; 
            width: 380px; 
            padding: 50px 30px; 
            color: #000 !important; 
            font-family: 'Courier New', Courier, monospace;
            font-weight: 900; 
            text-transform: uppercase; 
            line-height: 1.3; 
            font-size: 15px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            min-height: 900px;
            display: flex;
            flex-direction: column;
        }
        .h-rec { text-align: center; margin-bottom: 25px; font-size: 16px; line-height: 1.1; }
        .div-rec { border-top: 2px dashed #000; margin: 15px 0; }
        .row-rec { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .center-box { text-align: center; margin: 20px 0; font-size: 22px; border: 0px solid #000; padding: 10px; font-weight: 900; }
        .total-line { font-size: 24px; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; display: flex; justify-content: space-between; font-weight: 900; }
        .bank-block { margin: 20px 0; font-size: 13px; line-height: 1.5; }
        .logo-slot { text-align: center; margin-top: auto; padding-top: 40px; }
        .logo-slot img { max-width: 220px; max-height: 110px; filter: grayscale(1) contrast(1.8); margin-bottom: 15px; }
        .footer-note { text-align: center; font-size: 12px; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>

<div class="panel">
    <h2>ARASAKA v11.0 (BANK-SPEC FINAL)</h2>
    
    <label>ADRES VE FİRMA HAVUZU (İSİM - ADRES):</label>
    <textarea id="company-pool">SEDAT İLGİ PETROL - AYRANCILAR MH. TORBALI İZMİR
ARASAKA STATION - BUCA DOĞUŞ CD. NO:112 İZMİR</textarea>

    <label>VERİ LİSTESİ (Tarih - Başlık - Toplam):</label>
    <textarea id="expense-data" placeholder="01.04.2026 YEDEK PARÇA 8.250,50 TL"></textarea>

    <label>3 ADET LOGO YÜKLE:</label>
    <input type="file" id="logo-input" accept="image/*" multiple>

    <div class="btns">
        <button id="btn-gen" onclick="renderFiş()">SİSTEMİ TETİKLE (GENERATE)</button>
        <button id="btn-save" onclick="saveImages()">PNG OLARAK KAYDET</button>
    </div>
</div>

<div id="output-area"></div>

<script>
let uploadedLogos = [];

document.getElementById('logo-input').onchange = function(e) {
    uploadedLogos = [];
    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = (ev) => uploadedLogos.push(ev.target.result);
        reader.readAsDataURL(file);
    });
};

// Sayı ayıklama motoru - TL, boşluk ve sembolleri temizler
function parseSafe(val) {
    if(!val) return 0;
    let s = val.toString().replace(/TL/g, "").trim();
    if (s.includes('.') && s.includes(',')) s = s.replace(/\./g, "").replace(',', '.');
    else s = s.replace(',', '.');
    let num = parseFloat(s.replace(/[^0-9.]/g, ""));
    return num || 0;
}

function trMoney(n) {
    return n.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderFiş() {
    const data = document.getElementById('expense-data').value.trim();
    const pool = document.getElementById('company-pool').value.trim().split('\n');
    const output = document.getElementById('output-area');
    
    if(!data) return;
    output.innerHTML = "";

    data.split('\n').forEach((line, i) => {
        const parts = line.trim().split(/\s+/);
        if(parts.length >= 3) {
            const date = parts[0];
            const totalStr = parts[parts.length-1].includes('TL') ? parts[parts.length-2] + parts[parts.length-1] : parts[parts.length-1];
            const total = parseSafe(totalStr);
            const kdv = total - (total / 1.20);
            const title = parts.slice(1, parts.length-1).join(" ").replace(/TL/g, "");

            const compInfo = pool[Math.floor(Math.random() * pool.length)].split(' - ');
            const currentLogo = uploadedLogos.length > 0 ? uploadedLogos[Math.floor(Math.random() * uploadedLogos.length)] : "";

            output.innerHTML += `
                <div class="receipt-card">
                    <div class="h-rec">
                        <strong>${compInfo[0]}</strong><br>
                        TİC. VE SAN. LTD. ŞTİ.<br>
                        ${compInfo[1]}<br>
                        TEL: 0232 ${Math.floor(800+Math.random()*100)} ${Math.floor(10+Math.random()*90)} ${Math.floor(10+Math.random()*90)}
                    </div>
                    <div class="row-rec"><span>TARİH: ${date}</span><span>SAAT: 15:${10+i}</span></div>
                    <div class="row-rec"><span>FİŞ NO: ${3500+i}</span><span>Z NO: ${820+i}</span></div>
                    
                    <div class="center-box">BİLGİ FİŞİ</div>
                    
                    <div class="div-rec"></div>
                    <div class="row-rec"><span>${title}</span><span>*${trMoney(total - kdv)}</span></div>
                    <div class="row-rec"><span>TOPKDV (%20)</span><span>*${trMoney(kdv)}</span></div>
                    <div class="total-line"><span>TOPLAM</span><span>*${trMoney(total)}</span></div>
                    
                    <div class="div-rec"></div>
                    <div style="text-align:center; font-weight:900; font-size:20px; margin: 15px 0;">SATIŞ</div>
                    <div class="row-rec" style="font-size:22px; font-weight:900;">
                        
                        <span>*${trMoney(total)}</span>
                    </div>

                    <div class="bank-block">
                        ÜYE İŞYERİ NO: 06900000${Math.floor(1000+Math.random()*9000)}<br>
                        TERMINAL NO: 01639${Math.floor(100+Math.random()*900)}<br>
                        BATCH NO: 000${Math.floor(10+Math.random()*90)}<br>
                        AID: A0000000041010<br>
                        REF NO: ${Math.floor(100000000000 + Math.random()*900000000000)}<br>
                        ONAY KODU: ${Math.floor(100000 + Math.random()*800000)}<br>
                        TVR: 8080008000 &nbsp; TSI: 6800
                    </div>

                    <div class="logo-slot">
                        ${currentLogo ? `<img src="${currentLogo}">` : '<strong>BANKA ONAYLANDI</strong>'}
                        <div class="footer-note">
                            TUTAR KARŞILIĞI MAL VEYA HİZMET ALDIM<br>
                            BU BELGEYİ SAKLAYINIZ<br>
                            MÜŞTERİ NÜSHASI
                        </div>
                    </div>
                    
                    <div class="div-rec"></div>
                    <div style="text-align:center; font-size:12px;">MALI DEĞERİ YOKTUR<br>MF AT 000020</div>
                </div>
            `;
        }
    });
    document.getElementById('btn-save').style.display = "block";
}

async function saveImages() {
    const cards = document.querySelectorAll('.receipt-card');
    for (let card of cards) {
        const canvas = await html2canvas(card, { scale: 3 });
        const link = document.createElement('a');
        link.download = `Perfect_Slip_${Math.random().toString(36).substr(2, 5)}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }
}
</script>
</body>
</html>
