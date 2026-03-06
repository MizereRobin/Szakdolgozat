<?php

$title = "Logs";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

echo('<table class="table table-dark table-striped table-hover" style="margin:5%;width:90%">
<thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Reader id</th>
      <th scope="col">user_id</th>
      <th scope="col">time</th>
      <th scope="col">succ</th>
    </tr>
  </thead>
  <tbody>');
  $logs = GetAllLogs();
  
foreach ($logs as $log) {
    echo('<tr>
    <th scope="row">'.$log["id"].'</th>
    <td>'.$log["reader_id"].'</td>
    <td>'.$log["user_id"].'</td>
    <td>'.$log["time"].'</td>
    <td>'.$log["success"].'</td>
  </tr>');
}   //id reader_id user_key_id time success
echo(' </tbody>
</table>');
?>