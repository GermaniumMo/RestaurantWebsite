<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();

$user = current_user();
$reservations = db_fetch_all(
    "SELECT * FROM reservations 
     WHERE (user_id = ? OR email = ?) 
       AND status IN ('pending','confirmed','completed')
     ORDER BY reservation_date DESC, reservation_time DESC",
    [$user['id'], $user['email']],
    'is'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Reservations - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <section class="introduction-section">
        <div class="introduction-container text-center">
            <h1>My Reservations</h1>
            <p>View and manage your dining reservations</p>
        </div>
    </section>
    <div class="container mt-4">
        <?php flash_show_all(); ?>
    </div>
    <section class="container py-5" id="reservationsSection">
        <?php if (empty($reservations)): ?>
            <div class="text-center py-5" id="emptyState">
                <h3>No Reservations Yet</h3>
                <p class="text-muted mb-4">You haven't made any reservations yet. Book your table now!</p>
                <a href="index.php#Reservation" class="btn btn-primary">Make a Reservation</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="reservationsGrid">
                <?php foreach ($reservations as $reservation): ?>
                    <div class="col d-flex reservation-card">
                        <div class="card h-100 flex-fill d-flex flex-column">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong><?= date('M j, Y', strtotime($reservation['reservation_date'])) ?></strong>
                                <span class="badge bg-<?= 
                                    $reservation['status'] === 'pending' ? 'warning' : 
                                    ($reservation['status'] === 'confirmed' ? 'success' : 
                                    ($reservation['status'] === 'completed' ? 'info' : 'secondary')) ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </div>

                            <div class="card-body flex-grow-1">
                                <h5 class="card-title mb-3"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></h5>
                                <p class="card-text">
                                    <strong>Guests:</strong> <?= (int)$reservation['number_of_guests'] ?><br>
                                    <strong>Name:</strong> <?= htmlspecialchars($reservation['name']) ?><br>
                                    <strong>Email:</strong> <?= htmlspecialchars($reservation['email']) ?>
                                    <?php if (!empty($reservation['phone'])): ?>
                                        <br><strong>Phone:</strong> <?= htmlspecialchars($reservation['phone']) ?>
                                    <?php endif; ?>
                                </p>

                                <?php if (!empty($reservation['special_requests'])): ?>
                                    <div class="mt-3">
                                        <strong>Special Requests:</strong>
                                        <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($reservation['special_requests'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer text-muted mt-auto">
                                <small>Booked on <?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></small>
                                <?php if (
                                    $reservation['status'] === 'pending' &&
                                    strtotime($reservation['reservation_date'].' '.$reservation['reservation_time']) > time()
                                ): ?>
                                    <div class="mt-2">
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm cancel-reservation-btn"
                                            data-reservation-id="<?= (int)$reservation['id'] ?>">
                                            Cancel Reservation
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <input type="hidden" id="csrf_token" value="<?= csrf_token() ?>" />
    <div class="modal fade" id="cancelReservationModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cancel Reservation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to cancel this reservation?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, keep it</button>
            <button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes, cancel</button>
          </div>
        </div>
      </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.cancel-reservation-btn');
        const csrfToken = document.getElementById('csrf_token').value;
        const modalEl = document.getElementById('cancelReservationModal');
        const bsModal = new bootstrap.Modal(modalEl);
        const confirmBtn = document.getElementById('confirmCancelBtn');

        let currentReservationId = null;
        let currentButton = null;
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.reservationId;
                currentButton = this;
                bsModal.show();
            });
        });

        confirmBtn.addEventListener('click', async function () {
            if (!currentReservationId || !currentButton) return;

            const formData = new URLSearchParams();
            formData.append('id', currentReservationId);
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('cancel-reservation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData.toString()
                });

                const contentType = (response.headers.get('content-type') || '').toLowerCase();
                const result = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: 'Unexpected response. Check server logs.' };

                if (result.success) {
                    const col = currentButton.closest('.reservation-card');
                    if (col) {
                        col.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        col.style.opacity = '0';
                        col.style.transform = 'translateY(-6px)';
                        setTimeout(() => {
                            col.remove();
                            showEmptyStateIfNoneLeft();
                        }, 400);
                    }
                    showAlert('success', result.message || 'Reservation cancelled successfully.');
                } else {
                    showAlert('error', result.message || 'Failed to cancel reservation.');
                }
            } catch (error) {
                console.error('Cancel error:', error);
                showAlert('error', 'An error occurred while cancelling.');
            } finally {
                bsModal.hide();
                currentReservationId = null;
                currentButton = null;
            }
        });
        function showEmptyStateIfNoneLeft() {
            if (document.querySelectorAll('.reservation-card').length === 0) {
                const section = document.getElementById('reservationsSection');
                section.innerHTML = `
                    <div class="text-center py-5" id="emptyState">
                        <h3>No Reservations Yet</h3>
                        <p class="text-muted mb-4">You haven't made any reservations yet. Book your table now!</p>
                        <a href="index.php#Reservation" class="btn btn-primary">Make a Reservation</a>
                    </div>
                `;
            }
        }
        function showAlert(type, message) {
            const cls = {
                success: 'alert-success',
                error: 'alert-danger',
                warning: 'alert-warning',
                info: 'alert-info'
            }[type] || 'alert-info';

            const existing = document.getElementById('dynamicAlert');
            if (existing) existing.remove();

            const el = document.createElement('div');
            el.id = 'dynamicAlert';
            el.className = `alert ${cls} alert-dismissible fade show position-fixed`;
            el.style.cssText = 'top:20px; left:50%; transform:translateX(-50%); z-index:1056; min-width:300px;';
            el.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4500);
        }
    });
    </script>
</body>
</html>
