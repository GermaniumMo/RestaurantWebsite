<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/security.php';

// Require user to be logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please log in to manage cart.']);
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
    
    $action = sanitize_input($input['action'] ?? '');
    $cart_item_id = (int)($input['cart_item_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);
    $user_id = current_user()['id'];
    
    if (!in_array($action, ['update_quantity', 'remove_item'])) {
        throw new Exception('Invalid action.');
    }
    
    if (!$cart_item_id) {
        throw new Exception('Invalid cart item ID.');
    }
    
    // Verify cart item belongs to current user
    $cart_item = db_fetch_one(
        "SELECT id FROM cart_items WHERE id = ? AND user_id = ?",
        [$cart_item_id, $user_id],
        'ii'
    );
    
    if (!$cart_item) {
        throw new Exception('Cart item not found.');
    }
    
    if ($action === 'remove_item') {
        // Remove item from cart
        db_execute("DELETE FROM cart_items WHERE id = ?", [$cart_item_id], 'i');
        $message = 'Item removed from cart.';
    } else {
        // Update quantity
        if ($quantity < 1 || $quantity > 10) {
            throw new Exception('Quantity must be between 1 and 10.');
        }
        
        db_execute(
            "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?",
            [$quantity, $cart_item_id],
            'ii'
        );
        $message = 'Cart updated successfully.';
    }
    
    // Get updated cart data
    $cart_items = db_fetch_all(
        "SELECT ci.id, ci.quantity, mi.name, mi.price 
         FROM cart_items ci 
         JOIN menu_items mi ON ci.menu_item_id = mi.id 
         WHERE ci.user_id = ? 
         ORDER BY ci.created_at DESC",
        [$user_id],
        'i'
    );
    
    $cart_total = 0;
    $cart_count = 0;
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
        $cart_count += $item['quantity'];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_items' => $cart_items,
        'cart_total' => $cart_total,
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
