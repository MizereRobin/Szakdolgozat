<?php
$title="Readers";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

if (isset($_SESSION["error"])) {
  echo '<div id="err" class="position-fixed top-40 start-50 translate-middle" style="z-index:1250;">
    <div class="alert alert-danger alert-dismissible fade show">
        Cannot delete reader, it has one or more LOG!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>

  <script>
  setTimeout(() => {
      let el = document.getElementById("err");
      if (el) {
          el.style.display = "none";
      }
  }, 4000);
  </script>
  ';

  unset($_SESSION["error"]);
}


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
      <th scope="col"><a href="newreader.php?type=1"><button class="btn btn-success">New +</button></a></th>
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

