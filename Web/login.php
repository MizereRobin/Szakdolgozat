<?php
require_once 'db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
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
    echo "Authentication required";
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

session_write_close();
return;