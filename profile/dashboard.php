<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_admin_login();

$user = get_current_user();
if (!$user) {
    redirect('../auth/login.php');
}

// Get user's recent reservations and orders
$recent_reservations = $user->getUserReservations(5, 0);
$recent_orders = $user->getUserOrders(5, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Savoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
            color: white;
            padding: 3rem 0;
        }
        .dashboard-title {
            font-family: "Cormorant Garamond", serif;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .dashboard-card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border-radius: 1rem;
        }
        .nav-pills .nav-link {
            color: #6b7280;
            border-radius: 0.5rem;
        }
        .nav-pills .nav-link.active {
            background-color: #ea580c;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title mb-2">Welcome back, <?php echo htmlspecialchars($user->first_name); ?>!</h1>
                    <p class="mb-0 opacity-75">Manage your reservations, orders, and profile</p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-outline-light me-2">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle p-2 me-3" style="background-color: #ea580c !important;">
                                <i class="bi bi-person-circle text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user->email); ?></small>
                            </div>
                        </div>
                        
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reservations.php">
                                    <i class="bi bi-calendar-check me-2"></i> Reservations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="orders.php">
                                    <i class="bi bi-bag me-2"></i> Orders
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo count($recent_reservations); ?></h3>
                                        <p class="mb-0 opacity-75">Recent Reservations</p>
                                    </div>
                                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo count($recent_orders); ?></h3>
                                        <p class="mb-0 opacity-75">Recent Orders</p>
                                    </div>
                                    <i class="bi bi-bag fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reservations -->
                <div class="card dashboard-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Reservations</h5>
                        <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_reservations)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                                <h6>No reservations yet</h6>
                                <p class="text-muted">Make your first reservation to see it here.</p>
                                <a href="../index.php#Reservation" class="btn btn-primary">Make Reservation</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Guests</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_reservations as $reservation): ?>
                                            <tr>
                                                <td><?php echo format_date($reservation['date']); ?></td>
                                                <td><?php echo format_time($reservation['time']); ?></td>
                                                <td><?php echo $reservation['number_of_guests']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $reservation['status'] === 'confirmed' ? 'success' : ($reservation['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($reservation['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-bag-x fs-1 text-muted mb-3"></i>
                                <h6>No orders yet</h6>
                                <p class="text-muted">Place your first order to see it here.</p>
                                <a href="../menu.php" class="btn btn-primary">Order Now</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo format_date($order['created_at']); ?></td>
                                                <td><?php echo htmlspecialchars($order['items'] ?? 'N/A'); ?></td>
                                                <td><?php echo format_price($order['total_amount']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'preparing' ? 'warning' : 'info'); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
