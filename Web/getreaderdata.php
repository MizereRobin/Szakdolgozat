<?php

$id = $_GET['id'];
require_once 'db.php';
if(isset($_GET['id'])){
    $newReader = GetReaderById($id);
    $arr = array(
        'id' => $newReader->GetId(),
        'name' => $newReader->GetName(),
        'active' => $newReader->IsActive(),
        'role' => $newReader->GetRole(),
        'from' => $newReader->GetFrom(),
        'to' => $newReader->GetTo(),
        'abs' => $newReader->IsRoleAbs()
    );
    echo json_encode($arr);
}
else {echo('Unknown ID');}