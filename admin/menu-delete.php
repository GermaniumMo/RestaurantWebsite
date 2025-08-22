<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu-list.php');
    exit;
}

verify_csrf();

$menu_item_id = (int)($_POST['menu_id'] ?? $_POST['id'] ?? 0);

if (!$menu_item_id) {
    flash('error', 'Invalid menu item ID.');
    header('Location: menu-list.php');
    exit;
}

// Get menu item to verify it exists
$menu_item = db_fetch_one("SELECT name FROM menu_items WHERE id = ?", [$menu_item_id], 'i');

if (!$menu_item) {
    flash('error', 'Menu item not found.');
    header('Location: menu-list.php');
    exit;
}

try {
    // Delete the menu item
    $affected_rows = db_execute("DELETE FROM menu_items WHERE id = ?", [$menu_item_id], 'i');
    
    if ($affected_rows > 0) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Menu item "' . $menu_item['name'] . '" has been deleted successfully.'
            ]);
            exit;
        }
        flash('success', 'Menu item "' . $menu_item['name'] . '" has been deleted successfully.');
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete menu item.']);
            exit;
        }
        flash('error', 'Failed to delete menu item.');
    }
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the menu item.']);
        exit;
    }
    flash('error', 'An error occurred while deleting the menu item.');
}

header('Location: menu-list.php');
exit;
?>
