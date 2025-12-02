<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

try {
    // Read JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Check if JSON is valid
    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
        exit;
    }

    // Get and sanitize input
    $username = trim($data["username"] ?? "");
    $password = trim($data["password"] ?? "");

    // Validate required fields
    if ($username === "" || $password === "") {
        echo json_encode(["status" => "error", "message" => "Username and password are required"]);
        exit;
    }

    // Get user from database
    $stmt = $pdo->prepare("
        SELECT id, username, password, fullname, email, profile_pic 
        FROM users 
        WHERE username = :username
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
        exit;
    }

    // Check password (plain text for school project)
    // In production, use: password_verify($password, $user['password'])
    if ($password !== $user['password']) {
        echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
        exit;
    }

    // Login successful - create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['fullname'] = $user['fullname'];

    echo json_encode([
        "status" => "success", 
        "message" => "Login successful!",
        "redirect" => "home.php"
    ]);

} catch (PDOException $e) {
    // Database error
    echo json_encode([
        "status" => "error", 
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    // General error
    echo json_encode([
        "status" => "error", 
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>