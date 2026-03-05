<?php
require 'nav.php';
if($role !== 1){
    header('HTTP/1.0 401 Unauthorized');
    exit("Admin role not correct.");
}