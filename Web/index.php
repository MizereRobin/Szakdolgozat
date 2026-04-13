<?php 
$title = "Admin Home";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

?>
<h1 class="display-3" style="justify-self:center;"> MAIN PAGE </h1>

<div id="button_table" style="margin-left:15%;">
<div class="row">
  <div class="col-sm-5">
    <div class="card text-white bg-secondary border-primary mb-3">
      <div class="card-body">
        <h5 class="card-title">READERS</h5>
        <p class="card-text">Manage existing readers or add a new one</p>
        <a href="readers.php" class="btn btn-primary">Readers</a>
      </div>
    </div>
  </div>
  <div class="col-sm-5">
    <div class="card text-white bg-secondary border-success mb-3">
      <div class="card-body">
        <h5 class="card-title">USERS</h5>
        <p class="card-text">Manage existing users or add a new one</p>
        <a href="users.php" class="btn btn-success">Users</a>
      </div>
    </div>
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-5">
    <div class="card text-white bg-secondary border-warning mb-3">
      <div class="card-body">
        <h5 class="card-title">LOG</h5>
        <p class="card-text">Read logs</p>
        <a href="log.php" class="btn btn-warning">Log</a>
      </div>
    </div>
  </div>
  <div class="col-sm-5">
    <div class="card text-white bg-secondary border-danger mb-3">
      <div class="card-body secondary">
        <h5 class="card-title">ADMINS</h5>
        <p class="card-text">Manage admins (only in admin1 role!)</p>
        <a href="admins.php" class="btn btn-danger">Admins</a>
      </div>
    </div>
  </div>
</div>
</div>