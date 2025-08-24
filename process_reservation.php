<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/enhanced_validation.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json'); // Always return JSON

if (! is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (! verify_csrf()) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db        = db();
$client_ip = get_client_ip();

$rate_limiter = new RateLimiter($db);

if ($rate_limiter->isLimited($client_ip, 'reservation', 3, 10)) {
    log_security_event('reservation_rate_limited', ['ip' => $client_ip]);
    echo json_encode(['success' => false, 'message' => 'Too many reservation attempts. Please try again later.']);
    exit;
}

// Collect and sanitize input
$data = [
    'name'             => sanitize_input($_POST['name'] ?? ''),
    'email'            => sanitize_input($_POST['email'] ?? ''),
    'phone'            => sanitize_input($_POST['phone'] ?? ''),
    'guests'           => (int) ($_POST['guests'] ?? 0),
    'date'             => sanitize_input($_POST['date'] ?? ''),
    'time'             => sanitize_input($_POST['time'] ?? ''),
    'special_requests' => sanitize_input($_POST['special_requests'] ?? ''),
];

// Validate
$validator = validate_reservation_data($data);
if ($validator->fails()) {
    $rate_limiter->recordAttempt($client_ip, 'reservation');
    echo json_encode(['success' => false, 'message' => $validator->getErrorsAsString()]);
    exit;
}

try {
    $user_id = current_user()['id'];

    $reservation_id = db_insert("
        INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, number_of_guests, special_requests, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ", [$user_id, $data['name'], $data['email'], $data['phone'], $data['date'], $data['time'], $data['guests'], $data['special_requests']], 'isssssis');

    if ($reservation_id > 0) {
        log_security_event('reservation_created', [
            'name'   => $data['name'],
            'email'  => $data['email'],
            'date'   => $data['date'],
            'time'   => $data['time'],
            'guests' => $data['guests'],
        ], $user_id);

        echo json_encode(['success' => true, 'message' => 'Your reservation has been submitted successfully! We will contact you shortly to confirm.']);
    } else {
        $rate_limiter->recordAttempt($client_ip, 'reservation');
        echo json_encode(['success' => false, 'message' => 'Failed to submit reservation. Please try again.']);
    }
} catch (Exception $e) {
    $rate_limiter->recordAttempt($client_ip, 'reservation');
    log_security_event('reservation_error', ['error' => $e->getMessage()], $user_id);
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your reservation.']);
}
exit;
