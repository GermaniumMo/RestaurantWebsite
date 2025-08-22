<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-list.php');
    exit;
}

verify_csrf();

$user_id = (int)($_POST['user_id'] ?? $_POST['id'] ?? 0);
$current_user_id = current_user()['id'];

if (!$user_id) {
    flash('error', 'Invalid user ID.');
    header('Location: user-list.php');
    exit;
}

// Prevent self-deletion
if ($user_id === $current_user_id) {
    flash('error', 'You cannot delete your own account.');
    header('Location: user-list.php');
    exit;
}

// Get user to verify it exists
$user = db_fetch_one("SELECT name FROM users WHERE id = ?", [$user_id], 'i');

if (!$user) {
    flash('error', 'User not found.');
    header('Location: user-list.php');
    exit;
}

try {
    // Delete the user (this will cascade delete related reservations due to foreign key)
    $affected_rows = db_execute("DELETE FROM users WHERE id = ?", [$user_id], 'i');
    
    if ($affected_rows > 0) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'User "' . $user['name'] . '" has been deleted successfully.'
            ]);
            exit;
        }
        flash('success', 'User "' . $user['name'] . '" has been deleted successfully.');
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
            exit;
        }
        flash('error', 'Failed to delete user.');
    }
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the user.']);
        exit;
    }
    flash('error', 'An error occurred while deleting the user.');
}

header('Location: user-list.php');
exit;
?>
