<?php

require_once "db.php";
$add = $_GET['type'];
$last_id = GetLastReader();
if($add){
    AddReader("NewReader".(string)($last_id+1));
    $new_id = GetLastReader();
    if($new_id>$last_id){
        echo($new_id."\n\n\nSikeres hozzáadás");
    }
} else {
    echo("#".$last_id);
}