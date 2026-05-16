<?php
ob_start();
// Eski hali: require_once 'baglan.php';
// Yeni hali (Tam yol gösteriyoruz):
require_once __DIR__ . '/baglan.php';
ob_end_clean();

// --- AYARLAR & BOT İSİMLERİ ---
$botNames = [
    // --- Orijinal Temeller ---
    "Cinephile99", "GamerX", "MovieBuff", "PixelLord", "LustyGhost", "NoobMaster", "ProStreamer", 
    "SeriesAddict", "HotStuff", "TechGuy", "ShadowCrawler", "GhostProtocol", "CyberPunk88", 
    "Matrix_Neo", "GamerGirl92", "Anonymous_User", "CodeBreaker", "Berserk_Guts", "Phantom",

    // --- Cyber & Hacking ---
    "RootKit_V", "NetVandal", "SqlInjector", "NullPointer_X", "DarkShell", "AdminBypass", 
    "PacketLoss", "BufferOverflow", "Cryptic_X", "EthicalGamer", "VpnGhost", "Proxy_Slayer", 
    "Malware_Dev", "Firewall_X", "KeyLogger_V", "Backdoor_Man", "Linux_Lover", "Sudo_God", 
    "Terminal_Zero", "Kali_Soul", "Pentest_Pro", "ZeroDay_Hunter", "Wifi_Viper", "Bit_Vandal",

    // --- Berserk & Dark Anime ---
    "Behelit_Lord", "Casca_Fan", "BandOfHawk", "Eclipse_Sorrow", "Zodd_The_Immortal", 
    "SkullKnight_X", "GodHand_V", "Apostle_Bane", "DragonSlayer_V", "Marked_One", 
    "Brand_Of_Sacrifice", "Guts_Rage", "Berserker_Armor", "Interstice_Walker", "Puck_Vibe",

    // --- Gaming & LoL (Platinum Vibes) ---
    "Jungle_Diff", "Toxic_Vayne", "Yasuo_0_10", "Nexus_Crusher", "Elo_Terrorist", 
    "Kite_Master", "LastHit_God", "Gank_Or_Afk", "Baron_Stealer", "Carry_Me_Pls", 
    "MapAwareness_0", "Platinum_Skill", "RageQuit_X", "Pentakill_V", "Support_Main", 
    "Mid_Or_Feed", "Vision_Ward", "Dragon_Slayer_X", "SoloQ_Warrior", "Minion_Farmer",

    // --- Horror & Thriller (The Menu / Eden Lake Vibes) ---
    "The_Chef_V", "Final_Girl_88", "Survivalist_X", "Psychotic_Soul", "Slasher_Fan", 
    "Panic_Room", "Silent_Hill_V", "Red_Room_X", "Nightmare_Fuel", "JumpScare_God", 
    "Horror_Addict", "Thrill_Seeker", "Eden_Lake_S", "The_Menu_Fan", "Chef_Slowik",

    // --- Tech & Flutter & Music ---
    "Dart_Vader_X", "Flutter_Dev", "Widget_King", "Async_God", "Hot_Reload_V", 
    "Beat_Maker_88", "Rap_Lord_Izmir", "Flow_Master_V", "Trap_Ghost", "Bass_Booster", 
    "E_Liquid_Chef", "Vape_Cloud_X", "Coil_Burner", "Sub_Ohm_God", "Liquid_Lab",

    // --- Mix & Random (500'e Tamamlayanlar) ---
    "Acid_Vortex", "Alpha_Wolf", "Amber_Ghost", "Ancient_God", "Anomaly_S", "Apex_X", 
    "Arcane_Soul", "Archive_V", "Ashen_One", "Astral_X", "Atlas_Rise", "Atomic_N", 
    "Audio_X", "Azure_V", "Bad_Sector", "Bane_V", "Battery_X", "Beat_God", "Beyond_X", 
    "Bio_V", "Bit_L", "Black_Box", "Black_Ice", "Blade_V", "Blood_M", "Blue_S", 
    "Bolt_X", "Bone_X", "Brain_D", "Broken_L", "Buff_X", "Bullet_X", "Burn_X", 
    "Byte_X", "Carbon_X", "Chaos_T", "Chrome_X", "Circuit_X", "City_V", "Cloud_X", 
    "Code_X", "Cold_S", "Combat_X", "Cmd_X", "Complex_X", "Cookie_X", "Copper_X", 
    "Core_X", "Cortex_X", "Cosmic_X", "Crash_X", "Crit_X", "Crow_X", "Cryptic_X", 
    "Crystal_X", "Cyber_X", "Cyborg_X", "Dark_W", "Data_D", "Day_W", "Dead_C", 
    "Deadlock_X", "Death_N", "Deep_F", "Deep_Fr", "Default_X", "Delete_X", "Desert_X", 
    "Dev_X", "Diamond_X", "Digital_X", "Distort_X", "Divine_X", "Docker_X", "Doom_X", 
    "Double_X", "Down_X", "Dragon_X", "Dread_X", "Dream_X", "Drop_X", "Dust_X", 
    "Dying_X", "Echo_X", "Elastic_X", "Electric_X", "Element_X", "Emerald_X", "Empty_X", 
    "End_X", "Entropy_X", "Escape_X", "Eternal_X", "Ether_X", "Evil_X", "Evolution_X", 
    "Excalibur_X", "Exit_X", "Factor_X", "Falling_X", "False_X", "Fast_X", "Fatal_X", 
    "Fear_X", "Feral_X", "Fiber_X", "Final_X", "Fire_X", "Flame_X", "Flash_X", 
    "Flat_X", "Flicker_X", "Flow_X", "Force_X", "Forbidden_X", "Forgotten_X", "Fractal_X", 
    "Fragment_X", "Frost_X", "Full_X", "Future_X", "Galaxy_X", "Gamma_X", "Gank_X", 
    "Ghost_X", "Giga_X", "Global_X", "Grave_X", "Gray_Hat_X", "Green_X", "Grid_X", 
    "Grim_X", "Ground_X", "Hack_X", "Half_X", "Halo_X", "Hard_X", "Hard_R_X", 
    "Heal_X", "Heart_X", "Heavy_X", "Hidden_X", "High_P_X", "Horizon_X", "Host_X", 
    "Hot_X", "Hybrid_X", "Hyper_X", "Ice_X", "Icon_X", "Idle_X", "Ignite_X", 
    "Illus_X", "Impact_X", "Infin_X", "Input_X", "Inson_X", "Inst_X", "Inter_X", 
    "Into_X", "Iron_X", "Jade_X", "Java_X", "Jun_X", "Just_X", "Kill_X", "King_X", 
    "Kni_X", "Krak_X", "Lag_X", "Laser_X", "Last_X", "Lava_X", "Law_X", "Lead_X", 
    "Leg_X", "Lev_X", "Light_X", "Liq_X", "Live_X", "Load_X", "Lock_X", "Log_X", 
    "Lone_X", "Long_X", "Loop_X", "Lost_X", "Low_X", "Lun_X", "Mac_X", "Macro_X", 
    "Mad_X", "Mag_X", "Main_X", "Mal_X", "Man_X", "Marb_X", "Mast_X", "Mat_X", 
    "Mega_X", "Mem_X", "Merc_X", "Met_X", "Mic_X", "Mid_X", "Mind_X", "Mist_X", 
    "Mod_X", "Moon_X", "Moth_X", "Mov_X", "Neon_X", "Nev_X", "Next_X", "Night_X", 
    "Nitro_X", "No_C_X", "No_M_X", "No_S_X", "Noi_X", "Nom_X", "Non_X", "North_X", 
    "Nova_X", "Null_X", "Num_X", "Obsid_X", "Ocean_X", "Off_X", "Offl_X", "Old_X", 
    "Omeg_X", "On_X", "One_X", "Open_X", "Oper_X", "Opt_X", "Orb_X", "Out_X", 
    "Over_D_X", "Over_F_X", "Oxy_X", "Pac_X", "Para_X", "Paral_X", "Paran_X", 
    "Part_X", "Pass_X", "Patch_X", "Path_X", "Pent_X", "Phan_X", "Phase_X", 
    "Phoe_X", "Pix_X", "Plas_X", "Point_X", "Pois_X", "Pola_X", "Pow_X", "Pres_X", 
    "Pri_X", "Prio_X", "Priv_X", "Prog_X", "Proj_X", "Prot_X", "Proto_X", "Puls_X", 
    "Pure_X", "Pyra_X", "Quan_X", "Quar_X", "Quart_X", "Quick_X", "Quiet_X", 
    "Rad_X", "Radi_X", "Rage_X", "Rain_X", "Ram_X", "Rand_X", "Raw_X", "Reac_X", 
    "Real_X", "Reb_X", "Red_L_X", "Red_P_X", "Rede_X", "Refl_X", "Refle_X", 
    "Relo_X", "Remo_X", "Res_X", "Resi_X", "Reso_X", "Reson_X", "Retro_X", 
    "Reve_X", "Rich_X", "Riot_X", "Risi_X", "Road_X", "Robo_X", "Rogu_X", 
    "Root_X", "Rout_X", "Rust_X", "Safe_X", "Samu_X", "Sand_X", "Sate_X", 
    "Sava_X", "Scan_X", "Scre_X", "Scri_X", "Sear_X", "Seco_X", "Secr_X", 
    "Sect_X", "Secu_X", "Sent_X", "Serv_X", "Shad_X", "Shar_X", "Shie_X", 
    "Shoc_X", "Shor_X", "Shot_X", "Shre_X", "Shut_X", "Side_X", "Sign_X", 
    "Sili_X", "Silv_X", "Simu_X", "Sire_X", "Skel_X", "Sky_L_X", "Sky_N_X", 
    "Slas_X", "Slay_X", "Slee_X", "Slid_X", "Slow_X", "Smar_X", "Smok_X", 
    "Snak_X", "Snap_X", "Snow_X", "Soft_X", "Sola_X", "Soli_X", "Soni_X", 
    "Soul_X", "Soun_X", "Spac_X", "Spac_T_X", "Spar_X", "Spec_X", "Spee_X", 
    "Spid_X", "Spir_X", "Spiri_X", "Spli_X", "Spy_X", "Stag_X", "Star_D_X", 
    "Star_L_X", "Stat_X", "Stea_X", "Stee_X", "Stor_X", "Stre_X", "Sub_X", 
    "Supe_X", "Surf_X", "Surg_X", "Swit_X", "Symm_X", "Syna_X", "Syst_X", 
    "Tabl_X", "Tach_X", "Tact_X", "Tank_X", "Targ_X", "Task_X", "Tech_X", 
    "Tele_X", "Temp_X", "Term_X", "Tesl_X", "The_A_X", "The_B_X", "The_E_X", 
    "The_G_X", "The_O_X", "Ther_X", "Thir_X", "Thun_X", "Time_X", "Tita_X", 
    "Top_X", "Touc_X", "Toxi_X", "Trac_X", "Trac_B_X", "Traf_X", "Tran_X", 
    "Trans_X", "Tria_X", "Trig_X", "Trip_X", "Trop_X", "Tsun_X", "Turb_X", 
    "Twil_X", "Twin_X", "Ultr_X", "Unde_X", "Unkn_X", "Unli_X", "Upda_X", 
    "Upti_X", "User_X", "Vacu_X", "Valh_X", "Vamp_X", "Vand_X", "Vang_X", 
    "Vapo_X", "Vect_X", "Velo_X", "Veno_X", "Vers_X", "Vert_X", "Vibr_X", 
    "Vide_X", "Viki_X", "Virt_X", "Viru_X", "Visi_X", "Visu_X", "Void_X", 
    "Volt_X", "Volu_X", "Vort_X", "Wall_X", "Warp_X", "Watc_X", "Wate_X", 
    "Wave_X", "Web_M_X", "Whit_X", "Wild_X", "Wind_X", "Wire_X", "Wirel_X", 
    "Wiza_X", "Wolf_X", "Wond_X", "Work_X", "Worl_X", "Wrai_X", "Xeno_X", 
    "XFac_X", "Yell_X", "Zero_C_X", "Zero_G_X", "Zero_T_X", "Zig_X", 
    "Zodi_X", "Zomb_X", "Zone_X"
];
// --- YORUM HAVUZLARI ---
$movieComments = [
    "The cinematography in this was stunning!",
    "One of the best movies I've seen this year.",
    "Can't believe the ending, I didn't see that coming.",
    "Highly recommended for a movie night.",
    "The acting was top-notch, especially the lead role.",
    "Is there a sequel planned for this?"
];

$gameComments = [
    "Runs perfectly on my Steam Deck!",
    "Anyone found the secret level in this one?",
    "The graphics are insane, definitely worth the download.",
    "Is this the latest update? Thanks admin.",
    "Much better than the previous version.",
    "Addictive gameplay, I've been playing for hours."
];

$nsfwComments = [
    "Amazing gallery, thanks for sharing!",
    "The quality is crystal clear.",
    "Best update in this section so far.",
    "Keep them coming, love your choice!",
    "Absolutely stunning content.",
    "Can't wait for the next update here."
];

// 1. Veritabanından rastgele bir içerik çek (Türüne göre)
$sorgu = $db->query("SELECT id, content_name, content_type FROM contents WHERE content_aktiflik = 1 ORDER BY RAND() LIMIT 1");
$content = $sorgu->fetch(PDO::FETCH_ASSOC);

if ($content) {
    $cid = $content['id'];
    $type = $content['content_type']; // 1: Oyun, 2: Film, 3: NSFW (Senin sistemine göre ayarla)
    $name = $content['content_name'];

    // 2. Türüne göre yorum seç
    if ($type == 2) { // Film
        $comment = $movieComments[array_rand($movieComments)];
    } elseif ($type == 1) { // Oyun
        $comment = $gameComments[array_rand($gameComments)];
    } else { // NSFW veya Diğer
        $comment = $nsfwComments[array_rand($nsfwComments)];
    }

    $botName = $botNames[array_rand($botNames)];
    $finalComment = "<strong>" . $botName . ":</strong> " . $comment;

    // 3. Comments tablosuna ekle
    try {
        $kaydet = $db->prepare("INSERT INTO comments SET
            user_id = :uid,
            content_id = :cid,
            comment_text = :text,
            comment_date = NOW()
        ");

        $insert = $kaydet->execute([
            'uid' => 1, // Sistemde kayıtlı olan bir admin/bot ID'si
            'cid' => $cid,
            'text' => $finalComment
        ]);

        if ($insert) {
            echo "[SUCCESS]: Bot ($botName) commented on '$name' ($type)";
        }
    } catch (Exception $e) {
        echo "[ERROR]: " . $e->getMessage();
    }
}
?>