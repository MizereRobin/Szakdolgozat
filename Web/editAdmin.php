<?php
$id = $_GET["id"] ?? null;
$title="Edit User";

require_once 'login.php';
include_once 'nav.php';
require_once 'db.php';

if($role !== 1){
    header('HTTP/1.0 401 Unauthorized');
    exit("Admin role not correct.");
}
if (!$id) {
    exit("Missing user id");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"] ?? "";
    $rfid = $_POST["rfid"] ?? "";
    $role = (int)($_POST["role"] ?? 0);

    // Minimál sanity check (opcionális)
    $name = trim($name);
    $rfid = trim($rfid);

    UpdateUser((int)$id, $name, $rfid, $role);

    header("Location: users.php");
    exit;
}

$user = GetUserById((int)$id);

if (!$user) {
    exit("User not found");
}
?>

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header">
      <h4>Edit User</h4>
    </div>

    <div class="card-body">

      <form method="POST">

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input
            type="text"
            name="name"
            class="form-control"
            value="<?= htmlspecialchars($user->GetName()) ?>"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">RFID</label>
          <input
            type="text"
            name="rfid"
            class="form-control"
            value="<?= htmlspecialchars($user->GetRfid()) ?>"
            required
          >
          <div class="form-text">Pl. kártya UID / azonosító.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Role</label>
          <input
            type="number"
            name="role"
            class="form-control"
            value="<?= (int)$user->GetRole() ?>"
            min="0"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="users.php" class="btn btn-secondary">Back</a>

      </form>

    </div>
  </div>
</div>