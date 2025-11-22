<?php
header("Content-Type: application/json");

// Include database connection
require 'db.php';

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$fullname = trim($data["fullname"] ?? "");
$username = trim($data["username"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if ($fullname === "" || $username === "" || $email === "" || $password === "") {
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit;
}

// Check username
$checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkUser->bind_param("s", $username);
$checkUser->execute();
$checkUser->store_result();
if ($checkUser->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username already exists"]);
    exit;
}
$checkUser->close();

// Check email
$checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();
if ($checkEmail->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
    exit;
}
$checkEmail->close();

// Hash password (plain text for now)
$hashed = $password;

// Insert
$stmt = $conn->prepare("
    INSERT INTO users (fullname, username, email, password)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $fullname, $username, $email, $hashed);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Signup successful!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database insert failed"]);
}

$stmt->close();
$conn->close();
?>
