<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow CORS if needed
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
    $firstname = trim($data["firstname"] ?? "");
    $username = trim($data["username"] ?? "");
    $password = trim($data["password"] ?? "");

    // Validate required fields
    if ($firstname === "" || $username === "" || $password === "") {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Validate username length
    if (strlen($username) < 3) {
        echo json_encode(["status" => "error", "message" => "Username must be at least 3 characters"]);
        exit;
    }

    // Validate password length
    if (strlen($password) < 1) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 1 character"]);
        exit;
    }

    // Check if username already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $checkStmt->execute([':username' => $username]);
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        echo json_encode(["status" => "error", "message" => "Username already exists"]);
        exit;
    }

    // Generate email from fullname: "Carl" -> "Carl@wearify.com"
    $auto_email = $firstname . "@wearify.com";

    // Check if this email already exists (in case of duplicate names)
    $checkEmailStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $checkEmailStmt->execute([':email' => $auto_email]);
    $existingEmail = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);
    
    // If email exists, add a number suffix
    $email_counter = 1;
    $final_email = $auto_email;
    while ($existingEmail) {
        $email_counter++;
        $final_email = $firstname . $email_counter . "@wearify.com";
        $checkEmailStmt->execute([':email' => $final_email]);
        $existingEmail = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);
    }

    // Store password as plain text (for school project)
    // In production, use: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $plain_password = $password;

    // Insert new user into database with auto-generated email
    $insertStmt = $pdo->prepare("
        INSERT INTO users (fullname, email, username, password, created_at, updated_at)
        VALUES (:fullname, :email, :username, :password, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    
    $result = $insertStmt->execute([
        ':fullname' => $firstname,
        ':email' => $final_email,
        ':username' => $username,
        ':password' => $plain_password
    ]);

    if ($result) {
        echo json_encode([
            "status" => "success", 
            "message" => "Signup successful! Redirecting to login...",
            "email" => $final_email // Optional: send back generated email
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to create account. Please try again."
        ]);
    }

} catch (PDOException $e) {
    // Check for duplicate entry error
    if ($e->getCode() == 23000) {
        echo json_encode([
            "status" => "error", 
            "message" => "Username already exists"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    // General error
    echo json_encode([
        "status" => "error", 
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>