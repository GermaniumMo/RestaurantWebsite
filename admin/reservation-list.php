<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/security.php'; // Added missing security.php include for sanitize_input function

// Require admin role
require_role('admin');

$page_title = 'Reservations';
$page_subtitle = 'Manage restaurant reservations';
$current_page = 'reservations';

// Pagination and filters
$page = max(1, (int)($_GET['page'] ?? 1));
$search = sanitize_input($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(r.name LIKE ? OR r.email LIKE ? OR r.phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_filter)) {
    $where_conditions[] = "r.reservation_date = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM reservations r $where_clause";
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

// Get status counts for quick filters
$status_counts = [
    'pending' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")['count'],
    'confirmed' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'")['count'],
    'cancelled' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled'")['count'],
    'completed' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'completed'")['count']
];

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Reservations</h3>
            <small class="text-muted"><?= number_format($total_items) ?> total reservations</small>
        </div>
        <div class="d-flex gap-2">
            <a href="?status=pending" class="btn btn-outline-warning btn-sm">
                Pending (<?= $status_counts['pending'] ?>)
            </a>
            <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-outline-info btn-sm">
                Today's Reservations
            </a>
        </div>
    </div>

    <!-- Quick Status Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group btn-group-sm" role="group">
                <a href="?" class="btn <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-primary' ?>">
                    All (<?= number_format($total_items) ?>)
                </a>
                <a href="?status=pending" class="btn <?= $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                    Pending (<?= $status_counts['pending'] ?>)
                </a>
                <a href="?status=confirmed" class="btn <?= $status_filter === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?>">
                    Confirmed (<?= $status_counts['confirmed'] ?>)
                </a>
                <a href="?status=completed" class="btn <?= $status_filter === 'completed' ? 'btn-info' : 'btn-outline-info' ?>">
                    Completed (<?= $status_counts['completed'] ?>)
                </a>
                <a href="?status=cancelled" class="btn <?= $status_filter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    Cancelled (<?= $status_counts['cancelled'] ?>)
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                                    <strong><?= htmlspecialchars($reservation['name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($reservation['email']) ?>
                                        <?php if ($reservation['phone']): ?>
                                            <br><?= htmlspecialchars($reservation['phone']) ?>
                                        <?php endif; ?>
                                        <?php if ($reservation['user_name']): ?>
                                            <br><span class="badge bg-info">User: <?= htmlspecialchars($reservation['user_name']) ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong><?= date('M j, Y', strtotime($reservation['reservation_date'])) ?></strong>
                                <br>
                                <small class="text-muted"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $reservation['number_of_guests'] ?> guests</span>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    $reservation['status'] === 'pending' ? 'warning' : 
                                    ($reservation['status'] === 'confirmed' ? 'success' : 
                                    ($reservation['status'] === 'completed' ? 'info' : 'danger')) ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="reservation-edit.php?id=<?= $reservation['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="updateStatus(<?= $reservation['id'] ?>, 'confirmed')">
                                            Confirm
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $reservation['id'] ?>, '<?= htmlspecialchars($reservation['name'], ENT_QUOTES) ?>')">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php if ($reservation['special_requests']): ?>
                            <tr class="table-light">
                                <td colspan="6">
                                    <small><strong>Special Requests:</strong> <?= htmlspecialchars($reservation['special_requests']) ?></small>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&date=<?= urlencode($date_filter) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&date=<?= urlencode($date_filter) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&date=<?= urlencode($date_filter) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
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
                <form method="POST" action="reservation-delete.php" class="d-inline">
                    <input type="hidden" name="id" id="deleteReservationId">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusUpdateForm" method="POST" action="reservation-update-status.php" style="display: none;">
    <input type="hidden" name="id" id="statusUpdateId">
    <input type="hidden" name="status" id="statusUpdateStatus">
    <?= csrf_field() ?>
</form>

<?php
$extra_js = '
<script>
function confirmDelete(id, name) {
    document.getElementById("deleteReservationId").value = id;
    document.getElementById("deleteReservationName").textContent = name;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}

function updateStatus(id, status) {
    if (confirm("Are you sure you want to " + status + " this reservation?")) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = "Updating...";
        
        // Get CSRF token
        const csrfToken = document.querySelector("input[name=\"csrf_token\"]").value;
        
        fetch("reservation-update-status.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id=${id}&status=${status}&csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the status badge in the table
                const row = button.closest("tr");
                const statusBadge = row.querySelector(".badge");
                statusBadge.className = "badge bg-" + (status === "confirmed" ? "success" : "info");
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
                // Remove the confirm button if status was changed to confirmed
                if (status === "confirmed") {
                    button.remove();
                }
                
                // Show success message
                showAlert("Reservation " + status + " successfully!", "success");
            } else {
                showAlert(data.message || "Error updating reservation status", "danger");
                button.disabled = false;
                button.textContent = originalText;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("Error updating reservation status", "danger");
            button.disabled = false;
            button.textContent = originalText;
        });
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector(".admin-card");
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
';

include 'shared/footer.php';
?>
