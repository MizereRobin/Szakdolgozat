<?php
include_once 'nav.php';
require_once 'db.php';
require_once 'login.php';

if($role !== 1){
    header('HTTP/1.0 401 Unauthorized');
    exit("Admin role not correct.");
}

echo('<table class="table table-dark table-striped table-hover" style="margin:5%;width:90%">
<thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
      <th scope="col">pass</th>
      <th scope="col">Role</th>
    </tr>
  </thead>
  <tbody>');
  foreach ($admins as $admin) {
    echo('<tr>
    <th scope="row">'.$admin["id"].'</th>
    <td>'.$admin["name"].'</td>
    <td>'.$admin["pass"].'</td>
    <td>'.$admin["role"].'</td>
  </tr>');
} # id name pass role
echo(' </tbody>
</table>');

