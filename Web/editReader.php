<?php

$id = $_GET["id"];
$title = "Edit Reader";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $active = (int)$_POST["active"];
    $role = (int)$_POST["role"];
    $from = $_POST["from"];
    $to = $_POST["to"];
    $role_abs = (int)$_POST["role_abs"];

    UpdateReader($id, $name, $active, $role, $from, $to, $role_abs);

    header("Location: readers.php");
    exit;
}

$reader = GetReaderById($id);

?>
<div class="container mt-5">
<div class="card shadow">
<div class="card-header">
<h4>Edit Reader</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label class="form-label">Reader name</label>
<input type="text" name="name" class="form-control" value="<?=$reader->GetName()?>" required>
</div>

<div class="mb-3">
<label class="form-label">Active</label>
<select name="active" class="form-select">
<option value="1" <?= $reader->IsActive() ? "selected" : "" ?>>Active</option>
<option value="0" <?= !$reader->IsActive() ? "selected" : "" ?>>Disabled</option>
</select>
</div>

<div class="mb-3">
<label class="form-label">Requested Role</label>
<input type="number" name="role" class="form-control" value="<?=$reader->GetRole()?>" required>
</div>

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Access from</label>
<input type="time" name="from" class="form-control" value="<?=$reader->GetFrom()?>" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Access until</label>
<input type="time" name="to" class="form-control" value="<?=$reader->GetTo()?>" required>
</div>

</div>

<div class="mb-3">
<label class="form-label">Absolute role check</label>
<select name="role_abs" class="form-select">
<option value="0" <?= !$reader->IsRoleAbs() ? "selected" : "" ?>>Minimum role</option>
<option value="1" <?= $reader->IsRoleAbs()  ? "selected" : "" ?>>Exact role required</option>
</select>
</div>

<button type="submit" class="btn btn-primary">Save</button>
<a href="readers.php" class="btn btn-secondary">Back</a>
<!-- Itt hívnám a PHP függvényt valahol -->
</form>

</div>
</div>
</div>