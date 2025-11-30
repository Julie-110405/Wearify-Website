<?php
header("Content-Type: application/json");
session_start();

// Enable debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Include DB
require_once __DIR__ . '/config/db_connect.php';

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
$stmt = $pdo->prepare("SELECT id, fullname, username, password FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit;
}

// Check plain-text password
if ($password === $user['password']) {
    // Save user session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['firstname'] = $user['fullname']; // Treat fullname as firstname

    echo json_encode([
        "status" => "success",
        "message" => "Login successful! Welcome " . $user['fullname'],
        "user_id" => $user['id']
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
}
?>
