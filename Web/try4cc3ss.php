<?php
$input = $_GET['in'] ?? null;
$readerID = null;
$rfid = null;
if(isset($data)){
    [$readerID, $rfid] = explode("%20%", $input);
}


require_once 'db.php';

echo(GetAccess($readerID, $rfid));
?>