<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservation-list.php');
    exit;
}

verify_csrf();

$reservation_id = (int)($_POST['reservation_id'] ?? $_POST['id'] ?? 0);
$status = sanitize_input($_POST['status'] ?? '');

if (!$reservation_id) {
    flash('error', 'Invalid reservation ID.');
    header('Location: reservation-list.php');
    exit;
}

if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
    flash('error', 'Invalid status.');
    header('Location: reservation-list.php');
    exit;
}

// Get reservation to verify it exists
$reservation = db_fetch_one("SELECT name FROM reservations WHERE id = ?", [$reservation_id], 'i');

if (!$reservation) {
    flash('error', 'Reservation not found.');
    header('Location: reservation-list.php');
    exit;
}

try {
    // Update the reservation status
    $affected_rows = db_execute(
        "UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?", 
        [$status, $reservation_id], 
        'si'
    );
    
    if ($affected_rows > 0) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Reservation status updated to "' . ucfirst($status) . '" successfully.',
                'new_status' => $status
            ]);
            exit;
        }
        flash('success', 'Reservation status updated to "' . ucfirst($status) . '" successfully.');
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update reservation status.']);
            exit;
        }
        flash('error', 'Failed to update reservation status.');
    }
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating the reservation status.']);
        exit;
    }
    flash('error', 'An error occurred while updating the reservation status.');
}

header('Location: reservation-list.php');
exit;
?>
