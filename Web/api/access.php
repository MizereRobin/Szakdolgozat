<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Csak POST engedélyezett
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "0";
    exit;
}

// Bejövő titkosított adat (nyers body)
$rawInput = file_get_contents("php://input");
if (!$rawInput) {
    echo "0";
    exit;
}

// AES-256 dekódolás
$decrypted = decrypt_data($rawInput);
if (!$decrypted || strpos($decrypted, ";") === false) {
    echo "0";
    exit;
}

list($readerId, $rfid) = explode(";", $decrypted);

// 1) Reader ellenőrzése
$stmt = $pdo->prepare("SELECT * FROM reader WHERE id = ?");
$stmt->execute([$readerId]);
$reader = $stmt->fetch();

if (!$reader || $reader['active'] == 0) {
    echo "0";
    exit;
}

// 2) RFID ellenőrzése
$stmt = $pdo->prepare("SELECT * FROM `keys` WHERE RFID = ?");
$stmt->execute([$rfid]);
$key = $stmt->fetch();

if (!$key) {
    // Log: ismeretlen RFID
    $stmt = $pdo->prepare("INSERT INTO read_log (reader_id, key_id, success) VALUES (?, 0, 0)");
    $stmt->execute([$readerId]);
    echo "0";
    exit;
}

// 3) Beléptetési feltétel
$allow = false;

if ($reader['role_abs'] == 0) {
    // Minimum role szükséges
    if ($key['role'] >= $reader['role']) {
        $allow = true;
    }
} else {
    // Abszolút role: csak pontos egyezés
    if ($key['role'] == $reader['role']) {
        $allow = true;
    }
}

// 4) Logolás
$stmt = $pdo->prepare("INSERT INTO read_log (reader_id, key_id, success) VALUES (?, ?, ?)");
$stmt->execute([$readerId, $key['id'], $allow ? 1 : 0]);

// 5) Arduino válasz
echo $allow ? "1" : "0";
exit;
