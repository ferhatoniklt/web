<?php
/**
 * [ O.N.I.K.L.O.T.H.O ] - GOOGLE INDEXING API CONNECT
 * Bu dosya Google Search Console mülkünüze hızlı index sinyalleri gönderir.
 */

function sendGoogleIndex($url)
{
    // 1. Dosya Yolu Kontrolü
    $jsonKeyFile = __DIR__ . '/google-key.json'; 
    if (!file_exists($jsonKeyFile)) {
        return "[SYSTEM_ERROR]: google-key.json dosyası dizinde bulunamadı. Lütfen FTP'yi kontrol et abi.";
    }

    // 2. JSON Verisini Çözme
    $data = json_decode(file_get_contents($jsonKeyFile), true);
    if (!isset($data['client_email']) || !isset($data['private_key'])) {
        return "[AUTH_ERROR]: JSON dosyasının içeriği geçersiz veya bozuk.";
    }

    $client_email = $data['client_email'];
    $private_key = $data['private_key'];

    // 3. JWT (Json Web Token) Hazırlığı
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $iat = time();
    $exp = $iat + 3600; // 1 saatlik erişim izni
    $payload = json_encode([
        'iss' => $client_email,
        'scope' => 'https://www.googleapis.com/auth/indexing',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $iat,
        'exp' => $exp
    ]);

    // JWT Token İmzalaması
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $private_key, OPENSSL_ALGO_SHA256)) {
        return "[SSL_ERROR]: OpenSSL imzalama başarısız. Sunucuda OpenSSL desteğini kontrol et abi.";
    }

    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    // 4. Google'dan Access Token Alalım
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $response_data = json_decode($response, true);
    curl_close($ch);

    if (!isset($response_data['access_token'])) {
        return "[TOKEN_ERROR]: Google'dan erişim anahtarı alınamadı. Yanıt: " . $response;
    }

    $accessToken = $response_data['access_token'];

    // 5. Indexing API'ye URL Bildirimi
    $content = json_encode([
        'url' => $url,
        'type' => 'URL_UPDATED' // Yeni veya güncellenmiş içerik sinyali
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://indexing.googleapis.com/v3/urlNotifications:publish');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    $finalResult = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 6. Sonuç Analizi
    if ($httpCode == 200) {
        return "SUCCESS"; // Her şey yolunda
    } else {
        return "[API_CODE_$httpCode]: " . $finalResult; // Bir hata var
    }
}
?>