<?php
header("Content-Type: application/json");

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$firstname = trim($data["firstname"] ?? "");
$username = trim($data["username"] ?? "");
$password = trim($data["password"] ?? "");

if ($firstname === "" || $username === "" || $password === "") {
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit;
}

// Check username
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    echo json_encode(["status" => "error", "message" => "Username already exists"]);
    exit;
}

// Hash password (plain text for now)
$hashed = $password;

// Insert
$stmt = $pdo->prepare("
    INSERT INTO users (fullname, username, password)
    VALUES (?, ?, ?)
");
if ($stmt->execute([$firstname, $username, $hashed])) {
    echo json_encode(["status" => "success", "message" => "Signup successful!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database insert failed"]);
}
?>
