<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/validation.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to place an order']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}
if (isset($input['csrf_token'])) {
    $_POST['csrf_token'] = $input['csrf_token'];
}
if (!verify_csrf()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$required_fields = ['cart', 'customer_name', 'customer_email', 'customer_phone', 'order_type'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

if (!is_array($input['cart']) || empty($input['cart'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

if (!filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}
if (!in_array($input['order_type'], ['pickup', 'delivery'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order type']);
    exit;
}

if ($input['order_type'] === 'delivery' && empty($input['delivery_address'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Delivery address is required']);
    exit;
}

try {
    db()->beginTransaction();
    
    $user_id = current_user()['id'];
    
    $total_amount = 0;
    $validated_items = [];
    
    foreach ($input['cart'] as $cart_item) {
        if (!isset($cart_item['id']) || !isset($cart_item['quantity']) || !isset($cart_item['price'])) {
            throw new Exception('Invalid cart item format');
        }
        $menu_item = db_fetch_one(
            "SELECT id, name, price, is_available FROM menu_items WHERE id = ? AND is_available = 1",
            [$cart_item['id']]
        );
        
        if (!$menu_item) {
            throw new Exception("Menu item not found or unavailable: " . $cart_item['id']);
        }
        if (abs($menu_item['price'] - $cart_item['price']) > 0.01) {
            throw new Exception("Price mismatch for item: " . $menu_item['name']);
        }
        
        $quantity = (int)$cart_item['quantity'];
        if ($quantity <= 0 || $quantity > 10) {
            throw new Exception("Invalid quantity for item: " . $menu_item['name']);
        }
        
        $item_total = $menu_item['price'] * $quantity;
        $total_amount += $item_total;
        
        $validated_items[] = [
            'menu_item_id' => $menu_item['id'],
            'quantity' => $quantity,
            'unit_price' => $menu_item['price'],
            'total_price' => $item_total,
            'special_instructions' => $cart_item['special_instructions'] ?? null
        ];
    }
    $order_id = db_insert(
        "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, total_amount, order_type, delivery_address, notes, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [
            $user_id,
            $input['customer_name'],
            $input['customer_email'],
            $input['customer_phone'],
            $total_amount,
            $input['order_type'],
            $input['delivery_address'] ?? null,
            $input['notes'] ?? null
        ],
        'isssdsss'
    );
    
    if (!$order_id) {
        throw new Exception('Failed to create order');
    }
    foreach ($validated_items as $item) {
        $item_inserted = db_insert(
            "INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, total_price, special_instructions) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $order_id,
                $item['menu_item_id'],
                $item['quantity'],
                $item['unit_price'],
                $item['total_price'],
                $item['special_instructions']
            ],
            'iiidds'
        );
        
        if (!$item_inserted) {
            throw new Exception('Failed to create order item');
        }
    }
    
    db()->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    db()->rollback();
    
    error_log("Checkout error: " . $e->getMessage());
    error_log("Checkout error trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your order. Please try again.'
    ]);
}
?>
