<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Arasaka v24.4 - Full Identity & Grid Sync</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { background: #0b101a; color: #fff; font-family: sans-serif; padding: 20px; margin: 0; }
        .panel { background: #151f30; padding: 25px; border-radius: 12px; border: 2px solid #fcee0a; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        textarea { width: 100%; height: 100px; background: #000; color: #fcee0a; padding: 12px; border: 1px solid #00f3ff; box-sizing: border-box; font-family: monospace; }
        .upload-section { background: #1e293b; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px dashed #00f3ff; }
        .btn-group { display: flex; gap: 10px; margin-top: 15px; }
        button { flex: 1; padding: 20px; font-weight: bold; border: none; cursor: pointer; text-transform: uppercase; font-size: 15px; border-radius: 6px; transition: 0.3s; }
        #btn-gen { background: #fcee0a; color: #000; }
        #btn-save { background: #ff003c; color: #fff; display: none; }
        
        #output-area { display: flex; flex-direction: column; align-items: center; gap: 50px; padding-bottom: 100px; }

        .receipt-card { 
            background: #fff !important; 
            width: 530px; 
            padding: 60px 45px; 
            color: #000 !important; 
            font-family: 'Courier New', Courier, monospace; 
            font-weight: 950; 
            text-transform: uppercase; 
            line-height: 1.35; 
            letter-spacing: 1.1px; 
            font-size: 22px; 
            min-height: 2050px; 
            display: flex; 
            flex-direction: column; 
            box-sizing: border-box; 
            -webkit-text-stroke: 0.5px #000;
        }

        .h-rec { text-align: center; margin-bottom: 25px; font-size: 24px; line-height: 1.2; }
        .h-rec strong { font-size: 29px; display: block; margin-bottom: 5px; }
        .star-line { text-align: center; letter-spacing: 2px; font-weight: 950; margin: 5px 0; font-size: 26px; }
        
        .plate-box { text-align: center; font-size: 38px; font-weight: 950; margin-top: 5px; }
        .unit-sales-row { display: flex; justify-content: space-between; align-items: center; padding: 2px 0; font-size: 22px; margin-bottom: 5px; }

        .div-rec { border-top: 7px dashed #000; margin: 25px 0; }
        .row-rec { display: flex; justify-content: space-between; margin-bottom: 8px; }

        .tax-row { display: flex; justify-content: space-between; margin-top: 5px; }
        .total-rec { font-size: 44px; border-top: 8px solid #000; padding-top: 15px; margin-top: 25px; display: flex; justify-content: space-between; font-weight: 950; }

        .bank-agreement { text-align: center; font-size: 16px; line-height: 1.2; margin: 15px 0; font-weight: 900; }
        .card-logo-area { text-align: center; margin: 15px 0; }
        .card-logo-img { max-height: 70px; max-width: 240px; filter: grayscale(1) contrast(3); margin-bottom: 5px; }
        .holder-text { font-size: 18px; font-weight: 950; margin-bottom: 15px; }

        .qr-section { text-align: center; margin-top: 15px; }
        .qr-mock { width: 220px; height: 220px; margin: 0 auto 15px auto; background-size: contain; background-repeat: no-repeat; background-position: center; }
        .footer-info { text-align: center; font-size: 17px; line-height: 1.4; font-weight: 950; }
    </style>
</head>
<body>

<div class="panel">
    <h2>ARASAKA v24.4 (FIRM & GRID SYNC)</h2>
    <label>ADRES HAVUZU:</label>
    <textarea id="address-pool">SEDAT İLGİ PETROL - AYRANCILAR MH. TORBALI İZMİR
ARASAKA STATION - BUCA DOĞUŞ CD. NO:112 İZMİR
KLOTHO FUEL - TORBALI MH. 4502 SK. NO:5 İZMİR</textarea>

    <div class="upload-section">
        <label style="color:#fcee0a; display:block; margin-bottom:10px;">LOGO HAVUZU (Bankaları Seç):</label>
        <input type="file" id="logo-input" multiple accept="image/*">
    </div>

    <label style="margin-top:15px; display:block;">PHP VERİ LİSTESİ:</label>
    <textarea id="expense-data" placeholder="Veriyi buraya yapıştır..."></textarea>
    
    <div class="btn-group">
        <button id="btn-gen" onclick="fislereDok()">FİŞLERİ OLUŞTUR</button>
        <button id="btn-save" onclick="topluKaydet()">HD PNG KAYDET</button>
    </div>
</div>

<div id="output-area"></div>

<script>
let uploadedLogos = [];
document.getElementById('logo-input').addEventListener('change', function(e) {
    uploadedLogos = [];
    const files = e.target.files;
    for(let i=0; i<files.length; i++) {
        const reader = new FileReader();
        reader.onload = function(event) { uploadedLogos.push(event.target.result); };
        reader.readAsDataURL(files[i]);
    }
});

function parseSafe(v) { return parseFloat(v.toString().replace(',', '.')) || 0; }
function trMoney(n, d = 2) { return n.toLocaleString('tr-TR', { minimumFractionDigits: d, maximumFractionDigits: d }); }
function ran(min, max) { return Math.floor(Math.random() * (max - min + 1) + min); }

function fislereDok() {
    const rawData = document.getElementById('expense-data').value.trim();
    const addressPool = document.getElementById('address-pool').value.trim().split('\n');
    const output = document.getElementById('output-area');
    if(!rawData) return;
    output.innerHTML = "";

    rawData.split('\n').forEach((line, i) => {
        const p = line.split('\t');
        if(p.length >= 5) {
            const date = p[0].replace(/-/g, '.');
            const plaka_id = p[1];
            const litre = parseSafe(p[2]);
            const birim = parseSafe(p[3]);
            const total = parseSafe(p[p.length - 1]);
            const matrah = p.length > 5 ? parseSafe(p[4]) : total / 1.20;
            const kdv = p.length > 5 ? parseSafe(p[5]) : total - matrah;
            const auth = ran(100000, 999999);
            
            // FİRMA ADI VE ADRES HAVUZDAN ÇEKİLİYOR
            const randomAddrLine = addressPool[ran(0, addressPool.length-1)].split(' - ');
            const company = randomAddrLine[0].trim();
            const address = randomAddrLine[1] || "";

            let logoHtml = "";
            if(uploadedLogos.length > 0) {
                const randomLogo = uploadedLogos[ran(0, uploadedLogos.length - 1)];
                logoHtml = `<div class="card-logo-area"><img src="${randomLogo}" class="card-logo-img"><br><div class="holder-text">KART HAMİLİ NÜSHASIDIR</div></div>`;
            }

            output.innerHTML += `
                <div class="receipt-card">
                    <div class="h-rec">
                        <strong>${company}</strong>
                        AKARYAKIT TİCARET A.Ş.<br>${address}<br>
                        TEL: 0232 ${ran(810, 895)} ${ran(10, 99)} ${ran(10, 99)}
                    </div>
                    <div class="div-rec"></div>
                    <div class="row-rec"><span>TARİH: ${date}</span><span>SAAT: ${ran(10,22)}:${ran(10,55)}</span></div>
                    <div class="row-rec"><span>FİŞ NO: 00${ran(100, 999)}</span><span>BATCH: ${ran(100, 999)}</span></div>
                    
                    <div class="star-line">**************************</div>
                    <div class="plate-box">${plaka_id}</div>
                    
                    <div class="unit-sales-row">
                        <span>${trMoney(litre, 3)} LT X ${trMoney(birim)}</span>
                        <span>SATIŞ</span>
                    </div>
                    <div class="star-line">**************************</div>

                    <div class="row-rec"><span>MİKTAR (LT):</span><span>${trMoney(litre, 3)}</span></div>
                    <div class="row-rec"><span>BİRİM FİYAT:</span><span>${trMoney(birim)}</span></div>
                    <div class="row-rec" style="margin-top:10px; font-size:26px;"><strong>MOTORİN (ULTRA)</strong><strong>*${trMoney(total)}</strong></div>
                    <div class="div-rec"></div>
                    
                    <div class="meta-text" style="font-size:18px;">KART: **** **** **** ${ran(1000, 9999)}</div>
                    <div class="meta-text" style="font-size:18px;">RRN: ${ran(1000, 9999)}${ran(1000, 9999)} &nbsp; ONAY: ${auth}</div>
                    
                    <div class="bank-agreement">
                        *** ŞİFRE GİRİLDİ ***<br>TEMASSIZ İŞLEM<br>
                        BU BELGEYİ SAKLAYINIZ<br>TUTAR KARŞILIĞI MAL VE HİZMET ALDIM.
                    </div>

                    <div class="tax-row"><span>MATRAH</span><span>${trMoney(matrah)}</span></div>
                    <div class="tax-row"><span>TOPKDV (%20)</span><span>${trMoney(kdv)}</span></div>
                    <div class="total-rec"><span>TOPLAM</span><span>*${trMoney(total)}</span></div>
                    
                    <div class="row-rec" style="margin-top:20px; font-size:30px; font-weight:950;">
                        <span>KREDİ KARTI</span>
                        <span>${trMoney(total)}</span>
                    </div>

                    ${logoHtml}

                    <div class="qr-section">
                        <div class="qr-mock" style="background-image: url('https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=VAT-${auth}')"></div>
                        <div class="footer-info">
                            AID: A0000000041010<br>
                            Z NO: ${ran(1000, 5000)} &nbsp; EKÜ NO: ${ran(1, 9)}<br><br>
                            YAKIT ALIMINIZ İÇİN TEŞEKKÜR EDERİZ
                        </div>
                    </div>
                </div>`;
        }
    });
    document.getElementById('btn-save').style.display = "block";
}

async function topluKaydet() {
    const cards = document.querySelectorAll('.receipt-card');
    const btn = document.getElementById('btn-save');
    btn.innerText = "KAYDEDİLİYOR...";
    for (let i = 0; i < cards.length; i++) {
        const canvas = await html2canvas(cards[i], { scale: 4, useCORS: true, logging: false });
        const link = document.createElement('a');
        link.download = `Arasaka_Fis_${i+1}.png`;
        link.href = canvas.toDataURL("image/png");
        link.click();
        await new Promise(r => setTimeout(r, 600));
    }
    btn.innerText = "HD PNG KAYDET";
}
</script>
</body>
</html>
