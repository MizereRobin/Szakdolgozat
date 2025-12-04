<?php
// router.php - egyszerű front controller
// Feltételezzük, hogy a URL a következő formátumban érkezik:
// /api/users   vagy   /api/auth/token   stb.

// Normalize path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$path = preg_replace('#^' . preg_quote($base) . '#', '', $uri);
$path = trim($path, '/');

$parts = explode('/', $path);

// Egyszerű routing
// pl: /api/users -> users.php
if (count($parts) >= 2 && $parts[0] === 'api') {
    $resource = $parts[1];

    switch ($resource) {
        case 'users':
            require __DIR__ . '/users.php';
            break;

        case 'auth':
            // pl. POST /api/auth/token -> token generálása login alapján (demó)
            require __DIR__ . '/auth.php';
            break;

        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Resource not found']);
            exit;
    }
} else {
    // root info
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Simple PHP REST API root. Use /api/users or /api/auth']);
}
