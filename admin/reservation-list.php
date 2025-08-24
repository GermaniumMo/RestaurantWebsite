<?php
    define('ADMIN_PAGE', true);
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/flash.php';
    require_once __DIR__ . '/../includes/security.php'; // sanitize_input

    // Require admin role
    require_role('admin');

    $page_title    = 'Reservations';
    $page_subtitle = 'Manage restaurant reservations';
    $current_page  = 'reservations';

    // Pagination and filters
    $page          = max(1, (int) ($_GET['page'] ?? 1));
    $search        = sanitize_input($_GET['search'] ?? '');
    $status_filter = $_GET['status'] ?? '';
    $date_filter   = $_GET['date'] ?? '';
    $limit         = ITEMS_PER_PAGE;
    $offset        = ($page - 1) * $limit;

    // Build query conditions
    $where_conditions = [];
    $params           = [];
    $types            = '';

    if (! empty($search)) {
        $where_conditions[] = "(r.name LIKE ? OR r.email LIKE ? OR r.phone LIKE ?)";
        $search_param       = "%$search%";
        $params[]           = $search_param;
        $params[]           = $search_param;
        $params[]           = $search_param;
        $types .= 'sss';
    }

    if (! empty($status_filter)) {
        $where_conditions[] = "r.status = ?";
        $params[]           = $status_filter;
        $types .= 's';
    }

    if (! empty($date_filter)) {
        $where_conditions[] = "r.reservation_date = ?";
        $params[]           = $date_filter;
        $types .= 's';
    }

    $where_clause = ! empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get total count
    $count_sql   = "SELECT COUNT(*) as total FROM reservations r $where_clause";
    $total_items = db_fetch_one($count_sql, $params, $types)['total'];
    $total_pages = ceil($total_items / $limit);

    // Get reservations
    $sql = "SELECT r.*, u.name as user_name
FROM reservations r
LEFT JOIN users u ON r.user_id = u.id
$where_clause
ORDER BY r.reservation_date DESC, r.reservation_time DESC, r.created_at DESC
LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $reservations = db_fetch_all($sql, $params, $types);

    // Get status counts
    $status_counts = [
        'pending'   => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")['count'],
        'confirmed' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'")['count'],
        'cancelled' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled'")['count'],
        'completed' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'completed'")['count'],
    ];

    include 'shared/header.php';
?>

<meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Reservations</h3>
            <small class="text-muted"><?php echo number_format($total_items) ?> total reservations</small>
        </div>
        <div class="d-flex gap-2">
            <a href="?status=pending" class="btn btn-outline-warning btn-sm">
                Today's Pending (<?php echo $status_counts['pending'] ?>)
            </a>
            <a href="?date=<?php echo date('Y-m-d') ?>" class="btn btn-outline-info btn-sm">
                Today's Reservations
            </a>
        </div>
    </div>

    <!-- Quick Status Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group btn-group-sm" role="group">
                <a href="?" class="btn                                       <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline-primary' ?>">All (<?php echo number_format($total_items) ?>)</a>
                <a href="?status=pending" class="btn                                                     <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending (<?php echo $status_counts['pending'] ?>)</a>
                <a href="?status=confirmed" class="btn                                                       <?php echo $status_filter === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?>">Confirmed (<?php echo $status_counts['confirmed'] ?>)</a>
                <a href="?status=completed" class="btn                                                       <?php echo $status_filter === 'completed' ? 'btn-info' : 'btn-outline-info' ?>">Completed (<?php echo $status_counts['completed'] ?>)</a>
                <a href="?status=cancelled" class="btn                                                       <?php echo $status_filter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled (<?php echo $status_counts['cancelled'] ?>)</a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..."
                   value="<?php echo htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending"                                        <?php echo $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed"                                          <?php echo $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed"                                          <?php echo $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled"                                          <?php echo $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Reservations Table -->
    <?php if (empty($reservations)): ?>
        <div class="text-center py-5">
            <p class="text-muted">No reservations found.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($reservation['name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($reservation['email']) ?>
<?php if ($reservation['phone']): ?><br><?php echo htmlspecialchars($reservation['phone']) ?><?php endif; ?>
<?php if ($reservation['user_name']): ?><br><span class="badge bg-info">User:<?php echo htmlspecialchars($reservation['user_name']) ?></span><?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo date('M j, Y', strtotime($reservation['reservation_date'])) ?></strong>
                                <br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($reservation['reservation_time'])) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $reservation['number_of_guests'] ?> guests</span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo
                                                          $reservation['status'] === 'pending' ? 'warning' :
                                                      ($reservation['status'] === 'confirmed' ? 'success' :
                                                          ($reservation['status'] === 'completed' ? 'info' : 'danger')) ?>">
                                    <?php echo ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="reservation-edit.php?id=<?php echo $reservation['id'] ?>" class="btn btn-outline-primary">Edit</a>

                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-outline-success"
                                                data-update-status="true"
                                                data-id="<?php echo $reservation['id'] ?>"
                                                data-status="confirmed">
                                            Confirm
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" class="btn btn-outline-danger"
                                            data-delete="true"
                                            data-id="<?php echo $reservation['id'] ?>"
                                            data-name="<?php echo htmlspecialchars($reservation['name'], ENT_QUOTES) ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php if ($reservation['special_requests']): ?>
                            <tr class="table-light">
                                <td colspan="6">
                                    <small><strong>Special Requests:</strong>                                                                              <?php echo htmlspecialchars($reservation['special_requests']) ?></small>
                                </td>
                            </tr>
                        <?php endif; ?>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Reservations pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1 ?>&search=<?php echo urlencode($search) ?>&status=<?php echo urlencode($status_filter) ?>&date=<?php echo urlencode($date_filter) ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item<?php echo $i === $page ? ' active' : '' ?>">
                            <a class="page-link" href="?page=<?php echo $i ?>&search=<?php echo urlencode($search) ?>&status=<?php echo urlencode($status_filter) ?>&date=<?php echo urlencode($date_filter) ?>"><?php echo $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1 ?>&search=<?php echo urlencode($search) ?>&status=<?php echo urlencode($status_filter) ?>&date=<?php echo urlencode($date_filter) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
<?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the reservation for "<span id="deleteReservationName"></span>"?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="statusModalText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="statusModalBtn"></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let pendingStatus = { id: null, status: null, button: null };
    let deleteId = null;

    const container = document.querySelector('.admin-card');
    if (!container) return;

    const statusModalEl = document.getElementById("statusModal");
    const statusModal = new bootstrap.Modal(statusModalEl);

    const deleteModalEl = document.getElementById("deleteModal");
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    statusModalEl.addEventListener('shown.bs.modal', () => {
        document.getElementById("statusModalBtn").focus();
    });

    deleteModalEl.addEventListener('shown.bs.modal', () => {
        document.getElementById("confirmDeleteBtn").focus();
    });

    container.addEventListener('click', function(e) {
        const btn = e.target.closest("[data-delete='true'], [data-update-status='true']");
        if (!btn) return;

        // Delete
        if (btn.dataset.delete === "true") {
            deleteId = btn.dataset.id;
            document.getElementById("deleteReservationName").textContent = btn.dataset.name;
            deleteModal.show();
            return;
        }

        // Status update
        if (btn.dataset.updateStatus === "true") {
            const id = btn.dataset.id;
            const status = btn.dataset.status;
            const row = btn.closest("tr");
            const name = row.querySelector("strong")?.textContent || "this customer";

            pendingStatus = { id, status, button: btn };

            const title = {
                confirmed: "Confirm Reservation",
                completed: "Mark as Completed",
                cancelled: "Cancel Reservation"
            }[status] || "Update Reservation";

            const text = {
                confirmed: `Are you sure you want to confirm the reservation for "${name}"?`,
                completed: `Are you sure you want to mark the reservation for "${name}" as completed?`,
                cancelled: `Are you sure you want to cancel the reservation for "${name}"?`
            }[status] || `Update reservation for "${name}"?`;

            const btnClass = {
                confirmed: "btn-success",
                completed: "btn-info",
                cancelled: "btn-danger"
            }[status] || "btn-primary";

            const btnText = {
                confirmed: "Yes, Confirm",
                completed: "Yes, Complete",
                cancelled: "Yes, Cancel"
            }[status] || "Yes, Update";

            document.getElementById("statusModalTitle").textContent = title;
            document.getElementById("statusModalText").textContent = text;
            const modalBtn = document.getElementById("statusModalBtn");
            modalBtn.className = "btn " + btnClass;
            modalBtn.textContent = btnText;

            statusModal.show();
        }
    });

    document.getElementById("statusModalBtn").addEventListener("click", function() {
        const { id, status, button } = pendingStatus;
        if (!id || !status || !button) return;

        statusModal.hide();
        updateStatus(id, status, button);
    });

    document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
        if (!deleteId) return;
        deleteModal.hide();
        deleteReservation(deleteId);
    });

    function updateStatus(id, status, button) {
        const csrfToken = document.querySelector("meta[name='csrf-token']").content;

        fetch("reservation-update-status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload page to reflect changes
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    function deleteReservation(id) {
        const csrfToken = document.querySelector("meta[name='csrf-token']").content;

        fetch("reservation-delete.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload page to reflect changes
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }
});
</script>

<?php
    include 'shared/footer.php';
?>
