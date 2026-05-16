<?php 
ob_start();
session_start();
require_once 'baglan.php';

// Eğer zaten giriş yapmışsa direkt panele gönder
if(isset($_SESSION['admin_login'])) {
    header("Location: admin/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); // Basit giriş için MD5, ileride password_hash'e geçeriz.

    $sorgu = $db->prepare("SELECT * FROM admins WHERE admin_email = ? AND admin_password = ?");
    $sorgu->execute([$email, $password]);
    $admin = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        header("Location: admin/index.php");
    } else {
        // Hacker stili hata mesajı
        $hata = "[FATAL_ERROR]: ACCESS DENIED. INVALID CREDENTIALS.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Oniklotho - SYSTEM UPLINK</title>
    <style>
        /* CYBERPUNK 2077 NETWATCH THEME - LOGIN GATEWAY */
        body, html { 
            height: 100%; 
            margin: 0; 
            background-color: #080202; 
            background-image: radial-gradient(circle at 50% 50%, #1a0505 0%, #050101 100%); 
            color: #ff3333; 
            font-family: 'Consolas', 'Courier New', monospace; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden;
        }

        .cyber-login-wrapper { 
            width: 100%; 
            max-width: 400px; 
            padding: 20px; 
            position: relative;
            z-index: 10;
        }

        .cyber-panel { 
            background: rgba(15, 2, 2, 0.9); 
            border: 1px solid #ff2a2a; 
            padding: 40px 30px; 
            position: relative; 
            box-shadow: inset 0 0 20px rgba(255, 42, 42, 0.15), 0 0 30px rgba(0,0,0,0.9); 
            backdrop-filter: blur(5px); 
        }

        .cyber-panel::before { 
            content: 'NETWATCH_NODE // RESTRICTED'; 
            position: absolute; 
            top: -10px; 
            left: 15px; 
            background: #080202; 
            padding: 0 8px; 
            color: #ff2a2a; 
            font-size: 11px; 
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
            text-shadow: 0 0 10px #ff2a2a; 
            text-transform: uppercase; 
            letter-spacing: 4px; 
            border-bottom: 1px dashed rgba(255, 42, 42, 0.4); 
            padding-bottom: 15px; 
            margin-bottom: 30px; 
            font-size: 22px; 
            font-weight: bold; 
            text-align: center; 
        }
        
        .cyber-input-group { 
            margin-bottom: 25px; 
        }

        .cyber-label { 
            color: #cc2222; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold; 
        }

        .cyber-input { 
            background: #0a0101 !important; 
            border: 1px solid #aa1111 !important; 
            color: #00f0ff !important; 
            font-family: monospace; 
            border-radius: 0; 
            padding: 12px 15px; 
            width: 100%; 
            transition: 0.3s; 
            box-shadow: inset 0 0 5px rgba(0,0,0,0.5); 
            box-sizing: border-box; 
            font-size: 14px;
        }

        .cyber-input:focus { 
            border-color: #00f0ff !important; 
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.3) !important; 
            outline: none; 
        }

        .cyber-input::placeholder { 
            color: #441111; 
        }

        .cyber-btn { 
            background: #220505; 
            border: 1px solid #ff2a2a; 
            color: #ff2a2a; 
            font-weight: bold; 
            font-family: monospace; 
            padding: 15px; 
            width: 100%; 
            cursor: pointer; 
            text-transform: uppercase; 
            transition: all 0.2s; 
            letter-spacing: 3px; 
            font-size: 14px; 
            display: block; 
            text-align: center; 
            margin-top: 10px; 
        }

        .cyber-btn:hover { 
            background: #ff2a2a; 
            color: #000; 
            box-shadow: 0 0 20px rgba(255, 42, 42, 0.6); 
        }

        .cyber-error { 
            background: rgba(255, 42, 42, 0.1); 
            border-left: 4px solid #ff2a2a; 
            color: #fcee0a; 
            padding: 10px; 
            font-size: 12px; 
            margin-bottom: 25px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: bold;
        }
        
        .sys-info { 
            text-align: center; 
            font-size: 10px; 
            color: #666; 
            margin-top: 25px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
        }

        /* Lazer Tarama Efekti */
        .scan-line { 
            position: fixed; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 3px; 
            background: rgba(0, 240, 255, 0.4); 
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.6); 
            opacity: 0.6; 
            animation: scan 8s linear infinite; 
            pointer-events: none; 
            z-index: 9999; 
        }
        
        @keyframes scan { 
            0% { top: -10%; } 
            100% { top: 110%; } 
        }
        
        .blink_me { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style>
</head>
<body>
    
    <div class="scan-line"></div>

    <div class="cyber-login-wrapper">
        <div class="cyber-panel">
            
            <div class="cyber-title">
                [ O.N.I.K.L.O.T.H.O ]
            </div>

            <?php if(isset($hata)): ?>
                <div class="cyber-error">
                    > <?php echo $hata; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="cyber-input-group">
                    <label class="cyber-label">> SYS.AUTHORIZATION_ID</label>
                    <input type="text" name="email" class="cyber-input" placeholder="Enter uplink email..." required autocomplete="off">
                </div>

                <div class="cyber-input-group">
                    <label class="cyber-label">> SYS.SECURITY_KEY</label>
                    <input type="password" name="password" class="cyber-input" placeholder="Enter master password..." required>
                </div>

                <button class="cyber-btn" type="submit">EXECUTE_LOGIN</button>
            </form>
            
            <div class="sys-info">
                STATUS: <span class="blink_me" style="color: #00f0ff;">AWAITING CREDENTIALS...</span><br>
                SECURE CONNECTION PROTOCOL V3.1
            </div>

        </div>
    </div>

</body>
</html>
