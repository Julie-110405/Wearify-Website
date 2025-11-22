<?php
header("Content-Type: application/json");
session_start();

// Enable debugging
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

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if ($username === "" || $password === "") {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Fetch user from DB
$stmt = $conn->prepare("SELECT id, fullname, username, email, password FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit;
}

$user = $result->fetch_assoc();

// Check plain-text password (⚠ recommended to change to hashed later)
if ($password === $user['password']) {

    // Save user session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['fullname'] = $user['fullname'];

    echo json_encode([
        "status" => "success",
        "message" => "Login successful! Welcome " . $user['fullname'],
        "user_id" => $user['id']
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
}

$stmt->close();
$conn->close();
?>
