<?php
header("Content-Type: application/json");

// Enable debugging (comment out in production)
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Include DB
require 'db.php';

// Read JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON received."]);
    exit;
}

// Extract fields safely
$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Validate empty fields
if ($fullname === "" || $username === "" || $email === "" || $password === "") {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Optional username length validation
if (strlen($username) < 3) {
    echo json_encode(["status" => "error", "message" => "Username must be at least 3 characters."]);
    exit;
}

// Check email duplication
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
    exit;
}
$check->close();

// Insert user (plain text password)
$stmt = $conn->prepare("
    INSERT INTO users (fullname, username, email, password)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error: prepare() failed"]);
    exit;
}

$stmt->bind_param("ssss", $fullname, $username, $email, $password);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Signup successful!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Signup failed. Database error."]);
}

$stmt->close();
$conn->close();
?>
