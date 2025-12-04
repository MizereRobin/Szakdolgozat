<?php
// auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Példa: POST /api/auth/token  JSON { "username": "...", "password": "..." }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['username']) || !isset($body['password'])) {
    json_response(['success' => false, 'message' => 'Hiányzó username/password'], 400);
}

$username = $body['username'];
$password = $body['password'];

// Demo: egyszerű users tábla ellenőrzés (jelszó legyen hashed a db-ben)
$stmt = $pdo->prepare('SELECT id, username, password_hash FROM users_auth WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['success' => false, 'message' => 'Hibás belépési adatok'], 401);
}

// Token generálás
$token = generate_token($user['id']);
json_response(['success' => true, 'token' => $token, 'expires_in' => $GLOBALS['config']['token_ttl'] ?? 3600]);
