<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');    // keep errors out of the UI
ini_set('log_errors', '1');        // ensure they hit error_log

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/db.php'; // ✅ make sure DB helpers exist

require_role('admin');

$ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

function respond($ok, $message, $redirect = 'menu-list.php') {
    global $ajax;
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok, 'message' => $message]);
        exit;
    }
    if ($ok) {
        flash('success', $message);
    } else {
        flash('error', $message);
    }
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('[menu-delete] Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    respond(false, 'Invalid request method.');
}

try {
    verify_csrf();
    error_log('[menu-delete] CSRF verification passed');
} catch (Throwable $e) {
    error_log('[menu-delete] CSRF verification FAILED: ' . $e->getMessage());
    respond(false, 'Security validation failed. Please try again.');
}

$menu_item_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
error_log('[menu-delete] Received id=' . $menu_item_id);

if ($menu_item_id <= 0) {
    error_log('[menu-delete] Invalid id');
    respond(false, 'Invalid menu item ID.');
}

$item = db_fetch_one("SELECT id, name FROM menu_items WHERE id = ?", [$menu_item_id], 'i');
if (!$item) {
    error_log('[menu-delete] Item not found for id=' . $menu_item_id);
    respond(false, 'Menu item not found.');
}

try {
    $affected = db_execute("DELETE FROM menu_items WHERE id = ?", [$menu_item_id], 'i');
    error_log('[menu-delete] DELETE affected rows=' . (int)$affected);

    if ($affected > 0) {
        respond(true, 'Menu item "' . $item['name'] . '" has been deleted successfully.');
    } else {
        // MySQL might return 0 if row existed but cannot be deleted due to FK, or already gone
        error_log('[menu-delete] No rows affected; possible constraints or already deleted.');
        respond(false, 'Failed to delete menu item. It might be in use by other data.');
    }
} catch (Throwable $e) {
    error_log('[menu-delete] Exception during delete: ' . $e->getMessage());
    // If you have a helper like db_last_error(), log it:
    if (function_exists('db_last_error')) {
        error_log('[menu-delete] db_last_error: ' . db_last_error());
    }
    respond(false, 'An error occurred while deleting the menu item.');
}
