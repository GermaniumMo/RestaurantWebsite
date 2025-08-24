<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/security.php'; // sanitize_input

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Verify CSRF token
verify_csrf();

// Get and sanitize POST data
$reservation_id = (int) ($_POST['id'] ?? 0);
$status         = sanitize_input($_POST['status'] ?? '');

// Validate reservation ID
if (! $reservation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID.']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (! in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

// Check if reservation exists
$reservation = db_fetch_one("SELECT name FROM reservations WHERE id = ?", [$reservation_id], 'i');
if (! $reservation) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
    exit;
}

try {
    // Update reservation
    $affected_rows = db_execute(
        "UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?",
        [$status, $reservation_id],
        'si'
    );

    if ($affected_rows > 0) {
        echo json_encode([
            'success'    => true,
            'message'    => 'Reservation status updated to "' . ucfirst($status) . '" successfully.',
            'new_status' => $status,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reservation status.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the reservation status.']);
}
