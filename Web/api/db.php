<?php
// db.php
$config = require __DIR__ . '/config.php';
$dbcfg = $config['db'];

$dsn = "mysql:host={$dbcfg['host']};dbname={$dbcfg['dbname']};charset={$dbcfg['charset']}";

try {
    $pdo = new PDO($dsn, $dbcfg['user'], $dbcfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Adatbázis kapcsolat sikertelen: ' . $e->getMessage()]);
    exit;
}
