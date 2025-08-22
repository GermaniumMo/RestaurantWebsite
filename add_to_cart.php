<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/security.php';

// Require user to be logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    verify_csrf($input['csrf_token'] ?? '');
    
    $menu_item_id = (int)($input['menu_item_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);
    $user_id = current_user()['id'];
    
    // Validation
    if (!$menu_item_id) {
        throw new Exception('Invalid menu item ID.');
    }
    
    if ($quantity < 1 || $quantity > 10) {
        throw new Exception('Quantity must be between 1 and 10.');
    }
    
    // Verify menu item exists and is available
    $menu_item = db_fetch_one(
        "SELECT id, name, price, is_available FROM menu_items WHERE id = ? AND is_available = 1",
        [$menu_item_id],
        'i'
    );
    
    if (!$menu_item) {
        throw new Exception('Menu item not found or not available.');
    }
    
    // Check if item already exists in cart
    $existing_cart_item = db_fetch_one(
        "SELECT id, quantity FROM cart_items WHERE user_id = ? AND menu_item_id = ?",
        [$user_id, $menu_item_id],
        'ii'
    );
    
    if ($existing_cart_item) {
        // Update existing cart item
        $new_quantity = $existing_cart_item['quantity'] + $quantity;
        if ($new_quantity > 10) {
            $new_quantity = 10;
        }
        
        db_execute(
            "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?",
            [$new_quantity, $existing_cart_item['id']],
            'ii'
        );
    } else {
        // Insert new cart item
        db_insert(
            "INSERT INTO cart_items (user_id, menu_item_id, quantity, created_at) VALUES (?, ?, ?, NOW())",
            [$user_id, $menu_item_id, $quantity],
            'iii'
        );
    }
    
    // Get updated cart count
    $cart_count = db_fetch_one(
        "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?",
        [$user_id],
        'i'
    );
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $menu_item['name'] . ' added to cart!',
        'cart_count' => (int)($cart_count['total'] ?? 0)
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
