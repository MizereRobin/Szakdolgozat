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
function GetAllLogs(): array {
    global $conn;
    $logs = [];
    $sql = "SELECT * FROM reader_logs";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                "id"        => $row["id"],
                "reader_id" => $row["reader_id"],
                "user_id"   => $row["user_key_id"], // nálad így van az oszlopnév
                "time"      => $row["time"],
                "success"   => $row["success"]
            ];
        }
    }

    return $logs;
}

function GetAllReaders(): array {
    global $conn;
    $readers = [];
    $sql = "SELECT * FROM readers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $reader = new Reader(
                intval($row["id"]),
                $row["name"],
                boolval($row["active"]),
                intval($row["role"]),
                $row["from"],
                $row["to"],
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
function GetAllAdmins(): array{
    #Másold át a log-ból az közelebb áll mindenhez is
    global $conn;
    $admins = [];
    $sql = "SELECT * FROM admins";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = [
                "id"        => $row["id"],
                "name" => $row["name"],
                "pass"   => $row["pass"],
                "role"      => $row["role"]
            ];// id name pass role
        }
    }
    return $admins;
}

// function GetAccess(int $readerID, int $cardID){
//     global $conn;
//     $sql = "SELECT count(u.id) FROM users u inner join readers r on u.role = reader.role WHERE id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows > 0) {
//         $row = $result->fetch_assoc();
//         return new Reader(
//             intval($row["id"]),
//             $row["name"],
//             boolval($row["active"]),
//             intval($row["role"]),
//             $row["from_date"],
//             $row["to_date"],
//             boolval($row["role_abs"])
//         );
//     }
//     return null;
// }
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
function GetUserRoleByRFID(int $rfid){
    global $conn;

    $sql = "SELECT role FROM users WHERE rfid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rfid);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['role'];
    }

    return null;
}
function GetAccess(int $readerID, int $rfid){
    $userRole = GetUserRoleByRFID($rfid);
    $currentReader = GetReaderById($readerID);
    $readerRole = $currentReader->GetRole();
    $readerIsActive = $currentReader->IsActive();
    $readerIsAbs = $currentReader->IsRoleAbs();

    if($readerIsActive){
        if($readerIsAbs){
            if($userRole == $readerRole){return 1;} return 0;
        }
        if($userRole >= $readerRole){return 1;} return 0;
    }
    return 0;
}
function GetLastReader(): ?int {
    global $conn;
    $sql = "SELECT MAX(id) FROM readers";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return isset($row[0]) ? (int)$row[0] : null;
}

function GetReaderById(int $id): ?Reader {
    global $conn;
    $sql = "SELECT * from readers WHERE id = ?";
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
            $row["from"],
            $row["to"],
            boolval($row["role_abs"])
        );
    }
    return null;
}

function AddAdmin(string $name, string $pass, int $role): int{
    global $conn;
    $sql = "INSERT INTO admins (name, pass, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $pass, $role);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return -1;
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
function AddReader(string $name): int {
    global $conn;
    $sql = "INSERT INTO readers (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
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

function RemoveUser(int $id): bool {
    global $conn;
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
function UpdateReader(int $id, string $name, int $active, int $role, string $from, string $to, int $role_abs): bool {
    global $conn;
    $sql = "UPDATE readers SET name = ?, active = ?, `role` = ?, `from` = ?, `to` = ?, role_abs = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $activeInt = $active ? 1 : 0;
    $roleAbsInt = $role_abs ? 1 : 0;
    $stmt->bind_param("siisssi", $name, $activeInt, $role, $from, $to, $roleAbsInt, $id);
    return $stmt->execute();
}
function GetLogByReaderId(int $id) : int {
    global $conn;
    $sql = "SELECT COUNT(*) FROM reader_logs WHERE reader_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return (int)$row[0];
    
}
//Nem vagyok benne biztos, hogy kellene törölni olyan olvasókat, akiknek már vannak logjai
function RemoveReader(int $id): bool {
    global $conn;
    $sql = "DELETE FROM readers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function RemoveAdmin(int $id): bool {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) return false;
    else{
    global $conn;
    $sql = "DELETE FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
    }
}
