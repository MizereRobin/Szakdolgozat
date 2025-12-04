<?php
// users.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/*
GET /users?id=123
 - lekér egy usert id alapján (ha nincs id -> listáz)
 - ellenőrzi a token meglétét (példa: csak hitelesített kliens kérdezhet le)
POST /users
 - JSON body: { "name": "...", "role": 2 }
 - létrehoz egy usert és visszaadja az id-t
*/

// Token ellenőrzés minden meghívás elején:
$token = get_bearer_token();
$payload = verify_token($token);
if (!$payload) {
    json_response(['success' => false, 'message' => 'Érvénytelen vagy hiányzó token.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // GET paraméter lehet: id
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            json_response(['success' => false, 'message' => 'User nem található.'], 404);
        }
        json_response(['success' => true, 'data' => $user]);
    } else {
        // lista (limit pl. 100)
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
        $stmt = $pdo->prepare('SELECT id, name, role FROM users LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $list = $stmt->fetchAll();
        json_response(['success' => true, 'data' => $list]);
    }
} elseif ($method === 'POST') {
    // JSON body olvasása
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || !isset($body['name']) || !isset($body['role'])) {
        json_response(['success' => false, 'message' => 'Hiányzó mezők (name, role).'], 400);
    }
    $name = trim($body['name']);
    $role = intval($body['role']);

    $stmt = $pdo->prepare('INSERT INTO users (name, role) VALUES (?, ?)');
    $stmt->execute([$name, $role]);
    $newId = $pdo->lastInsertId();

    // példa: készítünk egy titkosított megjegyzést is (encrypt)
    $noteEncrypted = encrypt_data("User#$newId created by {$payload['sub']} at " . date('c'));
    $stmt2 = $pdo->prepare('INSERT INTO user_notes (user_id, note_enc) VALUES (?, ?)');
    $stmt2->execute([$newId, $noteEncrypted]);

    json_response(['success' => true, 'message' => 'User létrehozva', 'id' => $newId], 201);
} else {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}
