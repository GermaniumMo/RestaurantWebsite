<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    $featured_items = db_fetch_all(
        "SELECT * FROM menu_items 
         WHERE is_featured = 1 AND is_available = 1 
         ORDER BY display_order ASC, name ASC 
         LIMIT 6"
    );
    
    echo json_encode($featured_items);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load featured items']);
}
?>
