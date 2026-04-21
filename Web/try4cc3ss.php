<?php
$input = $_GET['in'] ?? null;
$readerID = null;
$rfid = null;
require_once 'db.php';
if(isset($input)){
    [$readerID, $rfid] = explode("#", $input);
    echo(GetAccess($readerID, $rfid));
}
else{echo(00);}

?>