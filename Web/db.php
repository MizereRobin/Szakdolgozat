<?php
$servername = "mysql.nethely.hu";
$username = "szakdogad8keon";
$password = "9BncCkPGQdTzSsn";
$dbname = "szakdogad8keon";
$port = "3306";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
} catch (Exception $e) { echo "Connection failed: " . $e->getMessage(); }

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<script>console.log('Database Connected successfully');</script>";

class Reader{
    private int $id;
    private string $name;
    private bool $active;
    private int $role;
    private string $from;
    private string $to;
    private bool $role_abs;

    public function __construct(int $id, string $name, bool $active, int $role, string $from, string $to, bool $role_abs) {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
        $this->role = $role;
        $this->from = $from;
        $this->to = $to;
        $this->role_abs = $role_abs;
    }

    public function SetActivity(bool $active): void {
        $this->active = $active;
    }
    public function SetRole(int $role): void {
        $this->role = $role;
    }
    public function SetFromTo(string $from, string $to): void {
        $this->from = $from;
        $this->to = $to;
    }

    public function GetId(): int {
        return $this->id;
    }
    public function GetName(): string {
        return $this->name;
    }
    public function IsActive(): bool {
        return $this->active;
    }
    public function GetRole(): int {
        return $this->role;
    }
    public function GetFrom(): string {
        return $this->from;
    }
    public function GetTo(): string {
        return $this->to;
    }
    public function IsRoleAbs(): bool {
        return $this->role_abs;
    }

}
class User{
    private int $id;
    private string $name;
    private string $rfid;
    private int $role;

    public function __construct(int $id, string $name, string $rfid, int $role) {
        $this->id = $id;
        $this->name = $name;
        $this->rfid = $rfid;
        $this->role = $role;
    }

    public function GetId(): int {
        return $this->id;
    }
    public function GetName(): string {
        return $this->name;
    }
    public function GetRfid(): string {
        return $this->rfid;
    }
    public function GetRole(): int {
        return $this->role;
    }


}

function GetAllReaders(): array {
    global $conn;
    $readers = [];
    $sql = "SELECT id, name, active, role, from_date, to_date, role_abs FROM readers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $reader = new Reader(
                intval($row["id"]),
                $row["name"],
                boolval($row["active"]),
                intval($row["role"]),
                $row["from_date"],
                $row["to_date"],
                boolval($row["role_abs"])
            );
            $readers[] = $reader;
        }
    }
    return $readers;
}
function GetAllUsers(): array {
    global $conn;
    $users = [];
    $sql = "SELECT id, name, rfid, role FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $user = new User(
                intval($row["id"]),
                $row["name"],
                $row["rfid"],
                intval($row["role"])
            );
            $users[] = $user;
        }
    }
    return $users;
}

function GetUserById(int $id): ?User {
    global $conn;
    $sql = "SELECT id, name, rfid, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return new User(
            intval($row["id"]),
            $row["name"],
            $row["rfid"],
            intval($row["role"])
        );
    }
    return null;
}
function GetReaderById(int $id): ?Reader {
    global $conn;
    $sql = "SELECT id, name, active, role, from_date, to_date, role_abs FROM readers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return new Reader(
            intval($row["id"]),
            $row["name"],
            boolval($row["active"]),
            intval($row["role"]),
            $row["from_date"],
            $row["to_date"],
            boolval($row["role_abs"])
        );
    }
    return null;
}
function AddUser(string $name, string $rfid, int $role): int {
    global $conn;
    $sql = "INSERT INTO users (name, rfid, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $rfid, $role);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return -1;
}
function AddReader(string $name, bool $active, int $role, string $from, string $to, bool $role_abs): int {
    global $conn;
    $sql = "INSERT INTO readers (name, active, role, from_date, to_date, role_abs) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $activeInt = $active ? 1 : 0;
    $roleAbsInt = $role_abs ? 1 : 0;
    $stmt->bind_param("siisss", $name, $activeInt, $role, $from, $to, $roleAbsInt);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return -1;
}
function UpdateUser(int $id, string $name, string $rfid, int $role): bool {
    global $conn;
    $sql = "UPDATE users SET name = ?, rfid = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $name, $rfid, $role, $id);
    return $stmt->execute();
}
function UpdateReader(int $id, string $name, bool $active, int $role, string $from, string $to, bool $role_abs): bool {
    global $conn;
    $sql = "UPDATE readers SET name = ?, active = ?, role = ?, from_date = ?, to_date = ?, role_abs = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $activeInt = $active ? 1 : 0;
    $roleAbsInt = $role_abs ? 1 : 0;
    $stmt->bind_param("siisssi", $name, $activeInt, $role, $from, $to, $roleAbsInt, $id);
    return $stmt->execute();
}
