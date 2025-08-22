<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    $db = get_db_connection();
    
    $stmt = $db->prepare("
        SELECT mi.*, mc.name as category_name 
        FROM menu_items mi 
        LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
        WHERE mi.is_available = 1 AND mi.is_featured = 1 
        ORDER BY mi.created_at DESC 
        LIMIT 6
    ");
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading menu items'
    ]);
}
?>
