<?php
require_once 'db.php';



if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
function Halal(string $message){
    die($message);
    session_destroy();
    session_regenerate_id();
}
if (!empty($_SESSION["IsAuth"]) && $_SESSION["IsAuth"] === true) {
    if (!isset($_SESSION["role"]) || !isset($_SESSION["username"])) {
        $_SESSION = [];
    } else {
        return;
    }
}

if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>401 - Hozzáférés megtagadva</title>
        <style>
            :root {
                color-scheme: dark;
            }
    
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
    
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #0f1115, #181c24);
                font-family: Arial, Helvetica, sans-serif;
                color: #e8ecf1;
            }
    
            .container {
                width: 100%;
                max-width: 520px;
                margin: 20px;
                padding: 40px 32px;
                background: rgba(24, 28, 36, 0.95);
                border: 1px solid #2a3140;
                border-radius: 18px;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45);
                text-align: center;
            }
    
            .code {
                font-size: 72px;
                font-weight: 700;
                color: #7aa2ff;
                margin-bottom: 12px;
                letter-spacing: 2px;
            }
    
            h1 {
                font-size: 28px;
                margin-bottom: 14px;
                color: #ffffff;
            }
    
            p {
                font-size: 16px;
                line-height: 1.6;
                color: #b8c0cc;
                margin-bottom: 28px;
            }
    
            .actions {
                display: flex;
                gap: 12px;
                justify-content: center;
                flex-wrap: wrap;
            }
    
            .btn {
                display: inline-block;
                padding: 12px 18px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                transition: 0.2s ease;
            }
    
            .btn-primary {
                background: #7aa2ff;
                color: #0f1115;
            }
    
            .btn-primary:hover {
                background: #92b4ff;
            }
    
            .btn-secondary {
                background: #222834;
                color: #d8dee9;
                border: 1px solid #323b4b;
            }
    
            .btn-secondary:hover {
                background: #2a3140;
            }
    
            .icon {
                font-size: 42px;
                margin-bottom: 14px;
                opacity: 0.9;
            }
    
            .footer {
                margin-top: 24px;
                font-size: 13px;
                color: #7f8896;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">🔒</div>
            <div class="code">401</div>
            <h1>Hozzáférés megtagadva</h1>
            <p>
                Ehhez az oldalhoz nincs megfelelő jogosultságod, vagy a hitelesítés szükséges.
                Kérlek jelentkezz be újra, vagy lépj vissza az előző oldalra.
            </p>
    
            <div class="actions">
                <a href="index.php" class="btn btn-primary">Bejelentkezés</a>
                <a href="https://www.google.hu" class="btn btn-secondary">Oldal elhagyása</a>
            </div>
    
            <div class="footer">
                401 Unauthorized
            </div>
        </div>
    </body>
    </html>';
    exit;
}

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$sql = "SELECT id, pass, role FROM admins WHERE `name` = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('HTTP/1.0 401 Unauthorized');
    exit("Invalid credentials.");
}

$row = $result->fetch_assoc();

if (!password_verify($password, $row["pass"])) {
    header('HTTP/1.0 401 Unauthorized');
    exit("Invalid credentials.");
}

session_regenerate_id(true);

$_SESSION["IsAuth"] = true;
$_SESSION["username"] = $username;
$_SESSION["role"] = (int)$row["role"];
$_SESSION["admin_id"] = (int)$row["id"];

//session_write_close();
return;