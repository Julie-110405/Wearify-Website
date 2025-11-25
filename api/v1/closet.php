<?php
session_start();
require_once '../../config/db_connect.php';
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit;
}

$user_id = $_SESSION['user_id']; // Logged-in user ID

function respond($success, $payload = [], $status = 200) {
    http_response_code($status);
    if ($success) {
        echo json_encode(array_merge(['success' => true], $payload));
    } else {
        $message = is_string($payload) ? $payload : 'Unexpected error';
        echo json_encode(['success' => false, 'message' => $message]);
    }
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // ============================
    //          UPLOAD
    // ============================
    if ($method === 'POST') {

        if (!isset($_FILES['item_image'], $_POST['category'])) {
            respond(false, 'Missing image or category', 400);
        }

        $file = $_FILES['item_image'];
        $category = trim($_POST['category']);
        $uploadDir = '../../public/uploads/';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            respond(false, 'Upload error code: ' . $file['error'], 500);
        }

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            respond(false, 'Unable to create upload directory', 500);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('item_', true) . ($ext ? '.' . $ext : '');
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            respond(false, 'Failed to move uploaded file', 500);
        }

        $imagePath = '/Wearify-Website/public/uploads/' . $fileName;

        $stmt = $pdo->prepare("
            INSERT INTO items (user_id, category, image_url)
            VALUES (:user_id, :category, :image_url)
        ");
        $stmt->execute([
            ':user_id'   => $user_id,
            ':category'  => $category,
            ':image_url' => $imagePath
        ]);

        respond(true, [
            'item_id'   => (int)$pdo->lastInsertId(),
            'category'  => $category,
            'image_url' => $imagePath
        ], 201);
    }

    // ============================
    //          FETCH USER ITEMS
    // ============================
    elseif ($method === 'GET') {

        $stmt = $pdo->prepare("
            SELECT item_id, category, image_url
            FROM items
            WHERE user_id = :user_id
            ORDER BY item_id DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        respond(true, ['data' => $items], 200);
    }

    // ============================
    //          DELETE ITEM
    // ============================
    elseif ($method === 'DELETE') {

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!isset($payload['item_id'])) {
            respond(false, 'Missing item_id', 400);
        }

        $item_id = (int)$payload['item_id'];

        $stmt = $pdo->prepare("
            SELECT image_url
            FROM items
            WHERE item_id = :item_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':item_id' => $item_id,
            ':user_id' => $user_id
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            respond(false, 'Item not found or unauthorized', 404);
        }

        // Delete record
        $stmt = $pdo->prepare("
            DELETE FROM items
            WHERE item_id = :item_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':item_id' => $item_id,
            ':user_id' => $user_id
        ]);

        // Delete file
        $imagePath = '../../' . $item['image_url'];
        if (is_file($imagePath)) {
            unlink($imagePath);
        }

        respond(true, ['message' => 'Item deleted'], 200);
    }

    else {
        respond(false, 'Method not allowed', 405);
    }

} catch (PDOException $e) {
    respond(false, 'Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    respond(false, 'Server error: ' . $e->getMessage(), 500);
}
?>