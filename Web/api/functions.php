<?php
// functions.php
$config = require __DIR__ . '/config.php';

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/* ------------------------
   TOKEN (HMAC-SHA256) egyszerű megvalósítás
   token formátum: base64url(payloadJSON) . '.' . signatureHex
   payload: { "sub": userId, "iat": timestamp, "exp": timestamp }
   ------------------------ */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode($data) {
    $pad = 4 - (strlen($data) % 4);
    if ($pad < 4) $data .= str_repeat('=', $pad);
    return base64_decode(strtr($data, '-_', '+/'));
}

function generate_token($userId) {
    global $config;
    $payload = [
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + $config['token_ttl']
    ];
    $payload_json = json_encode($payload);
    $payload_b64 = base64url_encode($payload_json);
    $sig = hash_hmac('sha256', $payload_b64, $config['token_secret']);
    return $payload_b64 . '.' . $sig;
}

function verify_token($token) {
    global $config;
    if (!$token) return false;
    $parts = explode('.', $token);
    if (count($parts) !== 2) return false;
    [$payload_b64, $sig] = $parts;
    $expected_sig = hash_hmac('sha256', $payload_b64, $config['token_secret']);
    if (!hash_equals($expected_sig, $sig)) return false;
    $payload_json = base64url_decode($payload_b64);
    $payload = json_decode($payload_json, true);
    if (!$payload) return false;
    if (isset($payload['exp']) && time() > $payload['exp']) return false;
    return $payload; // visszaadjuk a payloadot (pl. sub - user id)
}

/* ------------------------
   Titkosítás / Dekódolás (sym. AES-256-CBC)
   - Minden esetben ugyanazt a kulcsot használjuk (config['encryption_key'])
   - visszaadott adat base64(iv . ciphertext)
   ------------------------ */
function encryption_key_bytes() {
    global $config;
    // AES-256 igényel 32 byte kulcsot
    return hash('sha256', $config['encryption_key'], true);
}

function encrypt_data($plaintext) {
    $key = encryption_key_bytes();
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}

function decrypt_data($b64) {
    $key = encryption_key_bytes();
    $raw = base64_decode($b64);
    $ivlen = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($raw, 0, $ivlen);
    $ciphertext = substr($raw, $ivlen);
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return $plaintext;
}

/* ------------------------
   HTTP helper: kiveszi az Authorization fejlécet (Bearer ...)
   ------------------------ */
function get_bearer_token() {
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    if (!$headers) return null;
    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}
