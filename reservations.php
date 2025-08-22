<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';

// Require user to be logged in
require_login();

$user = current_user();

// Get user's reservations
$reservations = db_fetch_all(
    "SELECT * FROM reservations WHERE (user_id = ? OR email = ?) AND status != 'cancelled' ORDER BY reservation_date DESC, reservation_time DESC",
    [$user['id'], $user['email']],
    'is'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="d-flex top-header w-100 position-absolute left-0">
        <div class="d-flex justify-content-between py-3 w-100 header-container">
            <h1><a href="index.php" style="color: white; text-decoration: none;">Savoria</a></h1>
            <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
                <li><a href="index.php" style="color: white; text-decoration: none">Home</a></li>
                <li><a href="menu.php" style="color: white; text-decoration: none">Menu</a></li>
                <li><a href="about.php" style="color: white; text-decoration: none">About</a></li>
                <li><a href="contact.php" style="color: white; text-decoration: none">Contact</a></li>
            </ul>
            <div class="d-flex gap-3">
                <div class="dropdown">
                    <button class="btn btn-Reserve dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($user['name']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php if (has_role('admin')): ?>
                            <li><a class="dropdown-item" href="admin/index.php">Admin Dashboard</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="reservations.php">My Reservations</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="auth/logout.php" class="d-inline">
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="introduction-section">
        <div class="introduction-container">
            <div class="text-center">
                <h1>My Reservations</h1>
                <p>View and manage your dining reservations</p>
            </div>
        </div>
    </section>

    <!-- Flash Messages -->
    <div class="container mt-4">
        <?php flash_show_all(); ?>
    </div>

    <!-- Reservations -->
    <section class="container py-5" id="reservationsSection">
        <?php if (empty($reservations)): ?>
            <div class="text-center py-5" id="emptyState">
                <h3>No Reservations Yet</h3>
                <p class="text-muted mb-4">You haven't made any reservations yet. Book your table now!</p>
                <a href="index.php#Reservation" class="btn btn-primary">Make a Reservation</a>
            </div>
        <?php else: ?>
            <div class="row" id="reservationsGrid">
                <?php foreach ($reservations as $reservation): ?>
                    <div class="col-md-6 col-lg-4 mb-4 reservation-card">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong><?= date('M j, Y', strtotime($reservation['reservation_date'])) ?></strong>
                                <span class="badge bg-<?= 
                                    $reservation['status'] === 'pending' ? 'warning' : 
                                    ($reservation['status'] === 'confirmed' ? 'success' : 
                                    ($reservation['status'] === 'completed' ? 'info' : 'danger')) ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></h5>
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
                                        <p class="text-muted small"><?= htmlspecialchars($reservation['special_requests']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Booked on <?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></small>
                                <?php if (
                                    $reservation['status'] === 'pending' &&
                                    strtotime($reservation['reservation_date'].' '.$reservation['reservation_time']) > time()
                                ): ?>
                                    <div class="mt-2">
                                        <button type="button"
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

    <!-- Hidden CSRF token -->
    <input type="hidden" id="csrf_token" value="<?= csrf_token() ?>">

    <!-- Cancel Reservation Modal -->
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

    <!-- Footer (unchanged) -->
    <section class="d-flex w-100 contactFooter-container justify-content-center align-items-center">
        <div class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
            <div class="d-flex w-100 justify-content-center restaurant-info">
                <div class="d-flex flex-column gap-3" style="width: 288px">
                    <h1 class="title-info">Savoria</h1>
                    <p class="description-info">Experience the art of fine dining in an elegant atmosphere.</p>
                </div>
                <div class="col-3 col-md-3 mb-3">
                    <h5 class="contact-title">Contact</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">123 Gourmet Street</li>
                        <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">(555) 123-4567</a></li>
                        <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">info@savoria.com</a></li>
                    </ul>
                </div>
                <div class="col-3 col-md-3 mb-3">
                    <h5 class="contact-title">Hours</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">Mon-Thu: 11:00 am - 10:00 pm</li>
                        <li class="nav-item mb-2">Fri-Sat: 11:00 am - 11:00 pm</li>
                        <li class="nav-item mb-2">Sun: 11:00 am - 9:00 pm</li>
                    </ul>
                </div>
                <div class="d-flex flex-column">
                    <h5 class="contact-title">Follow Us</h5>
                    <ul class="list-unstyled d-flex">
                        <li class="ms-3"><a class="link-body-emphasis" href="#">FB</a></li>
                        <li class="ms-3"><a class="link-body-emphasis" href="#">IG</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top w-100 justify-content-center align-items-center">
                <p class="text-center w-100 footer-copyright">&copy; 2024 Company, Inc. All rights reserved.</p>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.cancel-reservation-btn');
        const csrfToken = document.getElementById('csrf_token').value;
        const modalEl = document.getElementById('cancelReservationModal');
        const bsModal = new bootstrap.Modal(modalEl);
        const confirmBtn = document.getElementById('confirmCancelBtn');
        let currentReservationId = null;
        let currentButton = null;

        // attach listeners to each cancel button
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.reservationId;
                currentButton = this;
                bsModal.show();
            });
        });

        // confirm in modal
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

                // if server redirected (not JSON), make a best effort to parse text
                const contentType = response.headers.get('content-type') || '';
                const result = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: 'Unexpected response. Check server logs.' };

                if (result.success) {
                    const card = currentButton.closest('.reservation-card');
                    if (card) {
                        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            card.remove();
                            showEmptyStateIfNoneLeft();
                        }, 500);
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

        // lightweight alert helper (top-center)
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
            el.style.cssText = 'top:20px; left:50%; transform:translateX(-50%); z-index:9999; min-width:300px;';
            el.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 5000);
        }
    });
    </script>
</body>
</html>
