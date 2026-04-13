<?php
$title="Add User";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"] ?? "";
    $rfid = $_POST["rfid"] ?? "";
    $role = (int)($_POST["role"] ?? 0);

    $name = trim($name);
    $rfid = trim($rfid);

    AddUser($name, $rfid, $role);

    header("Location: users.php");
    exit;
}
?>

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header">
      <h4>Add User</h4>
    </div>

    <div class="card-body">

      <form method="POST">

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input
            type="text"
            name="name"
            class="form-control"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">RFID</label>
          <input
            type="text"
            name="rfid"
            class="form-control"
            required
          >
          <div class="form-text">kártya UID / azonosító.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Role</label>
          <input
            type="number"
            name="role"
            class="form-control"
            min="0"
            value="0"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary">Add</button>
        <a href="users.php" class="btn btn-secondary">Back</a>

      </form>

    </div>
  </div>
</div>