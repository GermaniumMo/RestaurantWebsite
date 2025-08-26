<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';

require_role('admin');

$page_title = 'Edit Reservation';
$current_page = 'reservations';

$reservation_id = (int)($_GET['id'] ?? 0);
if (!$reservation_id) {
    flash('error', 'Invalid reservation ID.');
    header('Location: reservation-list.php');
    exit;
}

$reservation = db_fetch_one(
    "SELECT r.*, u.name as user_name FROM reservations r 
     LEFT JOIN users u ON r.user_id = u.id 
     WHERE r.id = ?", 
    [$reservation_id], 'i'
);

if (!$reservation) {
    flash('error', 'Reservation not found.');
    header('Location: reservation-list.php');
    exit;
}

$page_subtitle = 'Edit reservation for ' . $reservation['name'];
$errors = [];
$form_data = $reservation;

if (!empty($form_data['reservation_date'])) {
    $form_data['reservation_date'] = date('Y-m-d', strtotime($form_data['reservation_date']));
}

if (!empty($form_data['reservation_time'])) {
    $time = DateTime::createFromFormat('H:i:s', $form_data['reservation_time']);
    if (!$time) {
        $time = DateTime::createFromFormat('h:i A', $form_data['reservation_time']);
    }
    if ($time) {
        $form_data['reservation_time'] = $time->format('H:i');
    } else {
        $form_data['reservation_time'] = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'reservation_date' => sanitize_input($_POST['reservation_date'] ?? ''),
        'reservation_time' => sanitize_input($_POST['reservation_time'] ?? ''),
        'number_of_guests' => (int)($_POST['number_of_guests'] ?? 0),
        'special_requests' => sanitize_input($_POST['special_requests'] ?? ''),
        'status' => sanitize_input($_POST['status'] ?? '')
    ];

    if (!empty($_POST['reservation_time'])) {
        $time = DateTime::createFromFormat('h:i A', $_POST['reservation_time']);
        if (!$time) {
            $time = DateTime::createFromFormat('H:i', $_POST['reservation_time']);
        }
        $form_data['reservation_time'] = $time ? $time->format('H:i') : '';
    }

    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required.';
    }
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required.';
    }
    if (empty($form_data['reservation_date']) || !DateTime::createFromFormat('Y-m-d', $form_data['reservation_date'])) {
        $errors['reservation_date'] = 'Valid date is required.';
    }
    if (empty($form_data['reservation_time']) || !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $form_data['reservation_time'])) {
        $errors['reservation_time'] = 'Valid time is required.';
    }
    if ($form_data['number_of_guests'] < 1 || $form_data['number_of_guests'] > 20) {
        $errors['number_of_guests'] = 'Number of guests must be between 1 and 20.';
    }
    if (!in_array($form_data['status'], ['pending', 'confirmed', 'cancelled', 'completed'])) {
        $errors['status'] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        try {
            $affected_rows = db_execute(
                "UPDATE reservations SET name = ?, email = ?, phone = ?, reservation_date = ?, 
                 reservation_time = ?, number_of_guests = ?, special_requests = ?, status = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $form_data['name'],
                    $form_data['email'],
                    $form_data['phone'] ?: null,
                    $form_data['reservation_date'],
                    $form_data['reservation_time'],
                    $form_data['number_of_guests'],
                    $form_data['special_requests'] ?: null,
                    $form_data['status'],
                    $reservation_id
                ],
                'sssssissi'
            );

            if ($affected_rows >= 0) {
                flash('success', 'Reservation updated successfully!');
                header('Location: reservation-list.php');
                exit;
            } else {
                $errors['general'] = 'Failed to update reservation. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred while updating the reservation.';
        }
    }
}

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Edit Reservation</h3>
        <a href="reservation-list.php" class="btn btn-outline-secondary">
            <i class="me-2">←</i> Back to Reservations
        </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <?= csrf_field() ?>
        
        <div class="col-md-6">
            <label for="name" class="form-label">Customer Name *</label>
            <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email *</label>
            <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="email" name="email" value="<?= htmlspecialchars($form_data['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone" 
                   value="<?= htmlspecialchars($form_data['phone']) ?>">
        </div>

        <div class="col-md-6">
            <label for="status" class="form-label">Status *</label>
            <select class="form-select <?= !empty($errors['status']) ? 'is-invalid' : '' ?>" 
                    id="status" name="status" required>
                <option value="pending" <?= $form_data['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $form_data['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $form_data['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $form_data['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <?php if (!empty($errors['status'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['status']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label for="reservation_date" class="form-label">Reservation Date *</label>
            <input type="date" class="form-control <?= !empty($errors['reservation_date']) ? 'is-invalid' : '' ?>" 
                   id="reservation_date" name="reservation_date" 
                   value="<?= htmlspecialchars($form_data['reservation_date']) ?>" required>
            <?php if (!empty($errors['reservation_date'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['reservation_date']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label for="reservation_time" class="form-label">Reservation Time *</label>
            <input type="time" class="form-control <?= !empty($errors['reservation_time']) ? 'is-invalid' : '' ?>" 
                   id="reservation_time" name="reservation_time" 
                   value="<?= htmlspecialchars($form_data['reservation_time']) ?>" required>
            <?php if (!empty($errors['reservation_time'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['reservation_time']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label for="number_of_guests" class="form-label">Number of Guests *</label>
            <input type="number" min="1" max="20" 
                   class="form-control <?= !empty($errors['number_of_guests']) ? 'is-invalid' : '' ?>" 
                   id="number_of_guests" name="number_of_guests" 
                   value="<?= htmlspecialchars($form_data['number_of_guests']) ?>" required>
            <?php if (!empty($errors['number_of_guests'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['number_of_guests']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label for="special_requests" class="form-label">Special Requests</label>
            <textarea class="form-control" id="special_requests" name="special_requests" rows="3" 
                      placeholder="Any special requests or dietary restrictions..."><?= htmlspecialchars($form_data['special_requests']) ?></textarea>
        </div>

        <?php if ($reservation['user_name']): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <strong>Registered User:</strong> This reservation is linked to user account: <?= htmlspecialchars($reservation['user_name']) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Reservation</button>
                <a href="reservation-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include 'shared/footer.php'; ?>
