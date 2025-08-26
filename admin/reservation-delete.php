<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

verify_csrf();

$reservation_id = (int) ($_POST['id'] ?? 0);

if (! $reservation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID.']);
    exit;
}

$reservation = db_fetch_one("SELECT name FROM reservations WHERE id = ?", [$reservation_id], 'i');

if (! $reservation) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
    exit;
}

try {
    $affected_rows = db_execute("DELETE FROM reservations WHERE id = ?", [$reservation_id], 'i');

    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Reservation for "' . htmlspecialchars($reservation['name'], ENT_QUOTES) . '" has been deleted successfully.',
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete reservation.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the reservation.']);
}
