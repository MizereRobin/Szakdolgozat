<?php
$title="Users";
include_once 'nav.php';
require_once 'db.php';
require_once 'login.php';

echo('<table class="table table-dark table-striped table-hover" style="margin:5%;width:90%">
<thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
      <th scope="col">RFID</th>
      <th scope="col">Role</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>');
foreach (GetAllUsers() as $user) {
    echo('<tr>
    <th scope="row">'.$user->GetId().'</th>
    <td>'.$user->GetName().'</td>
    <td>'.$user->GetRfid().'</td>
    <td>'.$user->GetRole().'</td>
    <td style="width:5%;">
      <a href="editUser.php?id='.$user->GetId().'"><button type="button" class="btn btn-warning">Modify</button></a>
    </td>
  </tr>');
}
echo(' </tbody>
</table>');
?>

