<?
include_once 'db.php';
$success = false;
switch($_GET['type']){
    case 'user':
        $success = RemoveUser($_GET['id']);
        break;
    case 'reader':
        $success = RemoveReader($_GET['id']);
        break;
    case 'admin':
        $success = RemoveAdmin($_GET['id']);
        break;
}
echo($success ? "Success" : "Failed");

?>