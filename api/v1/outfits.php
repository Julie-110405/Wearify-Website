<?php
// api/v1/outfits.php

// 1. INCLUDE CONFIG AND SET UP HEADERS
require_once '../../config/db_connect.php'; 
header('Content-Type: application/json');

// Allow DELETE method for handling outfit deletion
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
}

// *** SIMULATION: In a real app, the user_id would come from a session or token ***
$user_id = 1;

// 2. CHECK REQUEST METHOD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- LOGIC FOR SAVING A NEW OUTFIT ---
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing action parameter.']);
        exit;
    }
    
    $action = $data['action'];
    
    if ($action === 'save') {
        // Save a new outfit with selected items
        if (!isset($data['items']) || !is_array($data['items'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing items array.']);
            exit;
        }
        
        $items = $data['items']; // Array of item_ids
        
        if (empty($items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Outfit must contain at least one item.']);
            exit;
        }
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Verify all items belong to the user
            $placeholders = str_repeat('?,', count($items) - 1) . '?';
            $stmt = $pdo->prepare("SELECT item_id FROM items WHERE item_id IN ($placeholders) AND user_id = ?");
            $params = array_merge($items, [$user_id]);
            $stmt->execute($params);
            $valid_items = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($valid_items) !== count($items)) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'One or more items do not belong to you.']);
                exit;
            }
            
            // Insert outfit
            $stmt = $pdo->prepare("INSERT INTO outfits (user_id, created_at) VALUES (?, NOW())");
            $stmt->execute([$user_id]);
            $outfit_id = $pdo->lastInsertId();
            
            // Insert outfit items
            $stmt = $pdo->prepare("INSERT INTO outfit_items (outfit_id, item_id) VALUES (?, ?)");
            foreach ($items as $item_id) {
                $stmt->execute([$outfit_id, $item_id]);
            }
            
            $pdo->commit();
            
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Outfit saved successfully!',
                'outfit_id' => $outfit_id
            ]);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: Could not save outfit.']);
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- LOGIC FOR RETRIEVING OUTFITS ---
    
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'list') {
        // Get all saved outfits for the user
        try {
            $stmt = $pdo->prepare("
                SELECT o.outfit_id, o.created_at,
                       GROUP_CONCAT(oi.item_id) as item_ids,
                       GROUP_CONCAT(i.category) as categories,
                       GROUP_CONCAT(i.image_url) as image_urls
                FROM outfits o
                LEFT JOIN outfit_items oi ON o.outfit_id = oi.outfit_id
                LEFT JOIN items i ON oi.item_id = i.item_id
                WHERE o.user_id = ?
                GROUP BY o.outfit_id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $outfits = $stmt->fetchAll();
            
            // Format the response
            $formatted_outfits = [];
            foreach ($outfits as $outfit) {
                $item_ids = $outfit['item_ids'] ? explode(',', $outfit['item_ids']) : [];
                $categories = $outfit['categories'] ? explode(',', $outfit['categories']) : [];
                $image_urls = $outfit['image_urls'] ? explode(',', $outfit['image_urls']) : [];
                
                $items = [];
                for ($i = 0; $i < count($item_ids); $i++) {
                    $items[] = [
                        'item_id' => $item_ids[$i],
                        'category' => $categories[$i] ?? '',
                        'image_url' => $image_urls[$i] ?? ''
                    ];
                }
                
                $formatted_outfits[] = [
                    'outfit_id' => $outfit['outfit_id'],
                    'created_at' => $outfit['created_at'],
                    'items' => $items
                ];
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $formatted_outfits]);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: Could not fetch outfits.']);
        }
        
    } elseif ($action === 'randomize') {
        // Generate a random outfit from user's items
        try {
            // Get all items grouped by category
            $stmt = $pdo->prepare("SELECT item_id, category, image_url FROM items WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $all_items = $stmt->fetchAll();
            
            if (empty($all_items)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No items found. Please add some items first.']);
                exit;
            }
            
            // Group items by category
            $items_by_category = [];
            foreach ($all_items as $item) {
                $category = strtolower($item['category']);
                if (!isset($items_by_category[$category])) {
                    $items_by_category[$category] = [];
                }
                $items_by_category[$category][] = $item;
            }
            
            // Select one random item from each available category
            $random_outfit = [];
            $categories = ['upper', 'lower', 'shoes', 'eyewear', 'bag', 'headwear', 'accessory', 'socks'];
            
            foreach ($categories as $category) {
                if (isset($items_by_category[$category]) && !empty($items_by_category[$category])) {
                    $random_item = $items_by_category[$category][array_rand($items_by_category[$category])];
                    $random_outfit[] = $random_item;
                }
            }
            
            if (empty($random_outfit)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No items found in any category.']);
                exit;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'data' => $random_outfit,
                'message' => 'Random outfit generated successfully!'
            ]);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: Could not generate random outfit.']);
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // --- LOGIC FOR DELETING AN OUTFIT ---
    
    $outfit_id = $_DELETE['outfit_id'] ?? null;
    
    if (!$outfit_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing outfit ID for deletion.']);
        exit;
    }
    
    try {
        // Verify outfit belongs to user
        $stmt = $pdo->prepare("SELECT outfit_id FROM outfits WHERE outfit_id = ? AND user_id = ?");
        $stmt->execute([$outfit_id, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Outfit not found or unauthorized.']);
            exit;
        }
        
        // Delete outfit items first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM outfit_items WHERE outfit_id = ?");
        $stmt->execute([$outfit_id]);
        
        // Delete outfit
        $stmt = $pdo->prepare("DELETE FROM outfits WHERE outfit_id = ?");
        $stmt->execute([$outfit_id]);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Outfit deleted successfully.']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: Could not delete outfit.']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>