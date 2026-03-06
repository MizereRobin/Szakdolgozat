<?php
$title="Readers";
include_once 'nav.php';
require_once 'db.php';
require_once 'login.php';

echo('<table class="table table-dark table-striped table-hover" style="margin:5%;width:90%">
<thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
      <th scope="col">Active</th>
      <th scope="col">Role</th>
      <th scope="col">From</th>
      <th scope="col">To</th>
      <th scope="col">Absolute</th>
    </tr>
  </thead>
  <tbody>');
  $readers = GetAllReaders();
  #echo($readers.count);
foreach ($readers as $reader) {
    echo('<tr>
    <th scope="row">'.$reader->GetId().'</th>
    <td>'.$reader->GetName().'</td>
    <td>'.$reader->IsActive().'</td>
    <td>'.$reader->GetRole().'</td>
    <td>'.$reader->GetFrom().'</td>
    <td>'.$reader->GetTo().'</td>
    <td>'.$reader->IsRoleAbs().'</td>
    <td style="width:5%;">
      <a href="editReader.php?id='.$reader->GetId().'"><button type="button" class="btn btn-warning">Modify</button></a>
    </td>
  </tr>');
}   //id, name, active, role, from_date, to_date, role_abs
echo(' </tbody>
</table>');
?>

