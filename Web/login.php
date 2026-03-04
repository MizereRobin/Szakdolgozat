<?php

session_start();

if (isset($_SESSION["IsAuth"]) && $_SESSION["IsAuth"] === true) {
    return;
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {

    header('WWW-Authenticate: Basic realm="Admin panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Authentication required";
    exit;
}

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

require "db.php";

$sql = "SELECT password FROM admins WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    header('HTTP/1.0 401 Unauthorized');
    exit("Invalid credentials");
}

$row = $result->fetch_assoc();

if (!password_verify($password, $row["password"])) {

    header('HTTP/1.0 401 Unauthorized');
    exit("Invalid credentials");
}

session_regenerate_id(true);
$_SESSION["IsAuth"] = true;
$_SESSION["username"] = $username;