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
    <link rel="stylesheet" href="css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <title>Oniklotho - Admin Panel</title>

    <style>
        /* Checkbox ve Tablo Düzenlemeleri */
        .main__table input[type="checkbox"] {
            -webkit-appearance: checkbox !important;
            appearance: checkbox !important;
            width: 18px !important;
            height: 18px !important;
            cursor: pointer;
            accent-color: #ff55a5;
        }

        .form__gallery {
            position: relative;
            height: 100px;
            background-color: #151f30;
            border: 1px dashed #ff55a5;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .form__gallery label {
            cursor: pointer;
            color: #fff;
            margin: 0;
        }

        .form__gallery input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header__content">
            <a href="index.php" class="header__logo">
                <img src="img/logo.svg" alt="">
            </a>
            <button class="header__btn" type="button">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <div class="sidebar">
        <a href="index.php" class="sidebar__logo">
        </a>

        <div class="sidebar__user">
            <div class="sidebar__user-img">
                <img src="img/user.svg" alt="">
            </div>
            <div class="sidebar__user-title">
                <span>Yönetici</span>
                <p><?php echo $_SESSION['admin_name']; ?></p>
            </div>
            <a href="logout.php" class="sidebar__user-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                        d="M4,12a1,1,0,0,0,1,1h7.59l-2.3,2.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l4-4a1,1,0,0,0,.21-.33,1,1,0,0,0,0-.76,1,1,0,0,0-.21-.33l-4-4a1,1,0,1,0-1.42,1.42L12.59,11H5A1,1,0,0,0,4,12ZM17,2H7A3,3,0,0,0,4,5V8A1,1,0,0,0,6,8V5A1,1,0,0,1,7,4H17a1,1,0,0,1,1,1V19a1,1,0,0,1-1,1H7a1,1,0,0,1-1-1V16a1,1,0,0,0-2,0v3a3,3,0,0,0,3,3H17a3,3,0,0,0,3-3V5A3,3,0,0,0,17,2Z" />
                </svg>
            </a>
        </div>

        <ul class="sidebar__nav">
            <li class="sidebar__nav-item">
                <a href="../index.php" target="_blank" class="sidebar__nav-link"><svg xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24">
                        <path
                            d="M20,8h0L14,2.74a3,3,0,0,0-4,0L4,8a3,3,0,0,0-1,2.26V19a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V10.25A3,3,0,0,0,20,8ZM14,20H10V15a1,1,0,0,1,1-1h2a1,1,0,0,1,1,1Zm5-1a1,1,0,0,1-1,1H16V15a3,3,0,0,0-3-3H11a3,3,0,0,0-3,3v5H6a1,1,0,0,1-1-1V10.25a1,1,0,0,1,.34-.75l6-5.25a1,1,0,0,1,1.32,0l6,5.25a1,1,0,0,1,.34.75Z" />
                    </svg> <span>WEB FRONT</span></a>
            </li>

            <li class="sidebar__nav-item">
                <a href="index.php" class="sidebar__nav-link"><svg xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24">
                        <path
                            d="M20,8h0L14,2.74a3,3,0,0,0-4,0L4,8a3,3,0,0,0-1,2.26V19a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V10.25A3,3,0,0,0,20,8ZM14,20H10V15a1,1,0,0,1,1-1h2a1,1,0,0,1,1,1Zm5-1a1,1,0,0,1-1,1H16V15a3,3,0,0,0-3-3H11a3,3,0,0,0-3,3v5H6a1,1,0,0,1-1-1V10.25a1,1,0,0,1,.34-.75l6-5.25a1,1,0,0,1,1.32,0l6,5.25a1,1,0,0,1,.34.75Z" />
                    </svg> <span>Panel</span></a>
            </li>
            <li class="sidebar__nav-item">
                <a href="contents.php" class="sidebar__nav-link"><svg xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24">
                        <path
                            d="M10,13H3a1,1,0,0,0-1,1v7a1,1,0,0,0,1,1h7a1,1,0,0,0,1-1V14A1,1,0,0,0,10,13ZM9,20H4V15H9ZM21,2H14a1,1,0,0,0-1,1v7a1,1,0,0,0,1,1h7a1,1,0,0,0,1-1V3A1,1,0,0,0,21,2ZM20,9H15V4h5Zm1,4H14a1,1,0,0,0-1,1v7a1,1,0,0,0,1,1h7a1,1,0,0,0,1-1V14A1,1,0,0,0,21,13Zm-1,7H15V15h5ZM10,2H3A1,1,0,0,0,2,3v7a1,1,0,0,0,1,1h7a1,1,0,0,0,1-1V3A1,1,0,0,0,10,2ZM9,9H4V4H9Z" />
                    </svg> <span>İçerik Kataloğu</span></a>
            </li>
            <li class="sidebar__nav-item">
                <a href="content_add.php" class="sidebar__nav-link"><svg xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24">
                        <path d="M19,11H13V5a1,1,0,0,0-2,0v6H5a1,1,0,0,0,0,2h6v6a1,1,0,0,0,2,0V13h6a1,1,0,0,0,0-2Z" />
                    </svg> <span>İçerik Ekle</span></a>
            </li>

            <li class="sidebar__nav-item">
                <a href="bot_control.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Bot Yönetimi</span>
                </a>
            </li>

            
            <li class="sidebar__nav-item">
                <a href="logcontrol.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Loglar</span>
                </a>
            </li>

                 <li class="sidebar__nav-item">
                <a href="search_radar.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Search Logs</span>
                </a>
            </li>
            
                <li class="sidebar__nav-item">
                <a href="fis.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Fis Log</span>
                </a>
            </li>
              
                <li class="sidebar__nav-item">
                <a href="toplufis.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Parca</span>
                </a>
            </li>
            
              
                <li class="sidebar__nav-item">
                <a href="benzin.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Bezin</span>
                </a>
            </li>
              
                <li class="sidebar__nav-item">
                <a href="fis.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Fis Log</span>
                </a>
            </li>
            
              
                <li class="sidebar__nav-item">
                <a href="benzinay.php" class="sidebar__nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M21.3,10.08a1,1,0,0,0-1.25-.74L18.4,10a8,8,0,0,0-12.8,0L3.95,9.34a1,1,0,0,0-1.25.74,1,1,0,0,0,.74,1.25l1.65.43a8,8,0,0,0,0,4.48l-1.65.43a1,1,0,0,0-.74,1.25,1,1,0,0,0,1,.75,1,1,0,0,0,.25-.03l1.65-.43a8,8,0,0,0,12.8,0l1.65.43a1,1,0,0,0,.25.03,1,1,0,0,0,1-.75,1,1,0,0,0-.74-1.25l-1.65-.43a8,8,0,0,0,0-4.48l1.65-.43A1,1,0,0,0,21.3,10.08ZM12,18a6,6,0,1,1,6-6A6,6,0,0,1,12,18Zm3-7a1,1,0,1,0,1,1A1,1,0,0,0,15,11Zm-6,0a1,1,0,1,0,1,1A1,1,0,0,0,9,11Z" />
                    </svg>
                    <span>Brnzinaylik</span>
                </a>
            </li>
        </ul>
    </div>