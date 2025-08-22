<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to view cart.']);
    exit;
}

try {
    $user_id = current_user()['id'];
    
    $cart_items = db_fetch_all(
        "SELECT ci.id, ci.menu_item_id, ci.quantity, mi.name, mi.price 
         FROM cart_items ci 
         JOIN menu_items mi ON ci.menu_item_id = mi.id 
         WHERE ci.user_id = ? 
         ORDER BY ci.created_at DESC",
        [$user_id],
        'i'
    );
    
    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading cart.']);
}
?>
