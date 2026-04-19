<?php
require_once 'login.php';
$title = "LOGOUT";
require 'nav.php';

$_SESSION["IsAuth"] = 0;
$_SESSION["username"] = false;
$_SESSION["role"] = -1;
$_SESSION["admin_id"] = -1;
session_gc();
echo("ELVILEG KILÉPTÉL\n");
echo("IsAuth:".session_id()."!");
Halal("Logged out");
die("Logged out");

