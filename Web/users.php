<?php
require "login.php";
require "db.php"
include "nav.php"

echo('<table class="table table-striped table-hover">
<thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
      <th scope="col">RFID</th>
      <th scope="col">Role</th>
    </tr>
  </thead>
  <tbody>');
foreach (GetAllUsers() as $user) {
    echo('<tr>
    <th scope="row">'.$user->GetId().'</th>
    <td>'.$user->GetName().'</td>
    <td>'.$user->GetRfid().'</td>
    <td>'.$user->GetRole().'</td>
  </tr>');
}
echo(' </tbody>
</table>');
?>

