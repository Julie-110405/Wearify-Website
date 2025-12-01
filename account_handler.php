<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Include database connection
include(__DIR__ . "/config/db_connect.php");

$response = ['success' => false, 'message' => ''];
$user_id = $_SESSION['user_id'];

try {
    // Get current user data
    $stmt = $pdo->prepare("SELECT username, password, email FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $currentUser = $stmt->fetch();

    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    $updates = [];
    $params = [':user_id' => $user_id];
    $updateMessages = [];

    // Handle fullname update
    if (isset($_POST['fullname']) && !empty(trim($_POST['fullname']))) {
        $fullname = trim($_POST['fullname']);
        $updates[] = "fullname = :fullname";
        $params[':fullname'] = $fullname;
        $updateMessages[] = "Name updated";
        $response['new_fullname'] = $fullname;
    }

    // Handle username update
    if (isset($_POST['new_username']) && !empty(trim($_POST['new_username']))) {
        $new_username = trim($_POST['new_username']);
        $username_password = $_POST['username_password'] ?? '';

        // Simple plain text password check
        if ($username_password !== $currentUser['password']) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password for username change']);
            exit();
        }

        // Check if username already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :user_id");
        $checkStmt->execute([':username' => $new_username, ':user_id' => $user_id]);
        
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already taken']);
            exit();
        }

        $updates[] = "username = :username";
        $params[':username'] = $new_username;
        $updateMessages[] = "Username updated";
        $response['new_username'] = $new_username;
    }

    // Handle password update
    if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Simple plain text password check
        if ($current_password !== $currentUser['password']) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }

        // Validate new password length
        if (strlen($new_password) < 1) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 1 character']);
            exit();
        }

        // Store new password as plain text
        $updates[] = "password = :password";
        $params[':password'] = $new_password;
        $updateMessages[] = "Password updated";
    }

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $file_type = $_FILES['profile_photo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed']);
            exit();
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/uploads/profile_pics/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
            // Delete old profile picture if it exists and is not the default
            $oldPicStmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = :user_id");
            $oldPicStmt->execute([':user_id' => $user_id]);
            $oldPic = $oldPicStmt->fetchColumn();
            
            if ($oldPic && $oldPic !== 'default_profile.png' && file_exists(__DIR__ . '/' . $oldPic)) {
                unlink(__DIR__ . '/' . $oldPic);
            }

            $profile_pic_path = 'uploads/profile_pics/' . $new_filename;
            $updates[] = "profile_pic = :profile_pic";
            $params[':profile_pic'] = $profile_pic_path;
            $updateMessages[] = "Profile picture updated";
            $response['new_profile_pic'] = $profile_pic_path;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload profile picture']);
            exit();
        }
    }

    // Execute updates if any
    if (!empty($updates)) {
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :user_id";
        
        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($params);

        $response['success'] = true;
        $response['message'] = implode(', ', $updateMessages) . ' successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'No changes to save';
    }

} catch (PDOException $e) {
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>