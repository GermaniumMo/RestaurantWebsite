<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../classes/Reservation.php';
require_once '../includes/pagination.php';
require_admin_login();

$database = new Database();
$db = $database->getConnection();
$reservation = new Reservation($db);

$message = '';
$error = '';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 15;

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $reservation->id = sanitize_input($_POST['id']);
        $reservation->status = sanitize_input($_POST['status']);
        
        if ($reservation->update_status()) {
            $message = 'Reservation status updated successfully!';
        } else {
            $error = 'Failed to update reservation status.';
        }
    } elseif ($_POST['action'] === 'delete') {
        $reservation->id = sanitize_input($_POST['id']);
        if ($reservation->delete()) {
            $message = 'Reservation deleted successfully!';
        } else {
            $error = 'Failed to delete reservation.';
        }
    }
}

// Get total count and create pagination
$total_count = $reservation->getTotalCount();
$pagination = new Pagination($total_count, $items_per_page, $page);

// Get paginated reservations
$stmt = $reservation->readAllPaginated($pagination->getLimit(), $pagination->getOffset());
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Savoria Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .admin-header {
            background-color: #111827;
            color: white;
            padding: 1rem 0;
        }
        .admin-sidebar {
            background-color: #1f2937;
            min-height: calc(100vh - 76px);
            padding: 2rem 0;
        }
        .admin-sidebar .nav-link {
            color: #9ca3af;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #ea580c;
            color: white;
        }
        .admin-title {
            font-family: "Cormorant Garamond", serif;
            font-size: 2rem;
            font-weight: 700;
            color: #ea580c;
        }
        .pagination-info {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0" style="color: #ea580c; font-family: 'Cormorant Garamond', serif;">Savoria Admin</h1>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['admin_username']; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 admin-sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu-items.php">
                            <i class="bi bi-card-list"></i> Menu Items
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reservations.php">
                            <i class="bi bi-calendar-check"></i> Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="py-4">
                    <h1 class="admin-title">Reservations</h1>

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Pagination Info -->
                    <div class="pagination-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo $pagination->getRecordInfo(); ?></strong>
                            </div>
                            <div>
                                Page <?php echo $pagination->getCurrentPage(); ?> of <?php echo $pagination->getTotalPages(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reservations Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Guests</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reservations)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <h5>No reservations found</h5>
                                                    <p class="text-muted">Reservations will appear here when customers make bookings.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($reservations as $res): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($res['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($res['email']); ?></td>
                                                    <td><?php echo format_date($res['date']); ?></td>
                                                    <td><?php echo format_time($res['time']); ?></td>
                                                    <td><?php echo $res['number_of_guests']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $res['status'] === 'confirmed' ? 'success' : ($res['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                            <?php echo ucfirst($res['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                                <input type="hidden" name="status" value="confirmed">
                                                                <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                                                                    <i class="bi bi-check"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                                <input type="hidden" name="status" value="cancelled">
                                                                <button type="submit" class="btn btn-sm btn-warning" title="Cancel">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Links -->
                    <?php if ($pagination->getTotalPages() > 1): ?>
                        <div class="mt-4">
                            <?php echo $pagination->generatePaginationLinks('reservations.php'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
