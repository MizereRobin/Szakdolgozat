<?php
include_once 'db.php';

$success = false;

switch($_GET['type']){

    case 'user':
        $success = RemoveUser($_GET['id']);
        break;

    case 'reader':
        session_start();

        if(GetLogByReaderId($_GET['id']) < 1){
            $success = RemoveReader($_GET['id']);
        } else {
            $_SESSION["error"] = "error";
            header("Location: readers.php");
            exit;
        }

        header("Location: readers.php");
        exit;

    case 'admin':
        $success = RemoveAdmin($_GET['id']);
        break;
}

echo ($success ? "Success" : "Failed");
?>