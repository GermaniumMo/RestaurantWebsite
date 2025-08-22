<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: category-list.php');
    exit;
}

verify_csrf();

$category_id = (int)($_POST['category_id'] ?? $_POST['id'] ?? 0);

if (!$category_id) {
    flash('error', 'Invalid category ID.');
    header('Location: category-list.php');
    exit;
}

// Get category to verify it exists
$category = db_fetch_one("SELECT name FROM categories WHERE id = ?", [$category_id], 'i');

if (!$category) {
    flash('error', 'Category not found.');
    header('Location: category-list.php');
    exit;
}

// Check if category has menu items
$menu_count = db_fetch_one("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?", [$category_id], 'i');

try {
    // Start transaction
    db()->beginTransaction();
    
    // If category has menu items, set their category_id to NULL
    if ($menu_count['count'] > 0) {
        db_execute("UPDATE menu_items SET category_id = NULL WHERE category_id = ?", [$category_id], 'i');
    }
    
    // Delete the category
    $affected_rows = db_execute("DELETE FROM categories WHERE id = ?", [$category_id], 'i');
    
    if ($affected_rows > 0) {
        // Commit transaction
        db()->commit();
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Category "' . $category['name'] . '" has been deleted successfully.' . 
                           ($menu_count['count'] > 0 ? ' ' . $menu_count['count'] . ' menu items were uncategorized.' : '')
            ]);
            exit;
        }
        flash('success', 'Category "' . $category['name'] . '" has been deleted successfully.' . 
                        ($menu_count['count'] > 0 ? ' ' . $menu_count['count'] . ' menu items were uncategorized.' : ''));
    } else {
        // Rollback transaction
        db()->rollback();
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
            exit;
        }
        flash('error', 'Failed to delete category.');
    }
} catch (Exception $e) {
    // Rollback transaction
    db()->rollback();
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the category.']);
        exit;
    }
    flash('error', 'An error occurred while deleting the category.');
}

header('Location: category-list.php');
exit;
?>
