<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

// Require admin role
require_role('admin');

// Get dashboard statistics
try {
    $stats = [
        'total_users' => db_fetch_one("SELECT COUNT(*) as count FROM users")['count'],
        'total_reservations' => db_fetch_one("SELECT COUNT(*) as count FROM reservations")['count'],
        'total_menu_items' => db_fetch_one("SELECT COUNT(*) as count FROM menu_items")['count'],
        'pending_reservations' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")['count'],
        'todays_reservations' => db_fetch_one("SELECT COUNT(*) as count FROM reservations WHERE reservation_date = CURDATE()")['count'],
        'featured_items' => db_fetch_one("SELECT COUNT(*) as count FROM menu_items WHERE is_featured = 1")['count']
    ];
    
    // Get recent reservations
    $recent_reservations = db_fetch_all(
        "SELECT r.*, u.name as user_name FROM reservations r 
         LEFT JOIN users u ON r.user_id = u.id 
         ORDER BY r.created_at DESC LIMIT 5"
    );
    
    // Get recent users
    $recent_users = db_fetch_all(
        "SELECT id, name, email, role, created_at FROM users 
         ORDER BY created_at DESC LIMIT 5"
    );
    
} catch (Exception $e) {
    $stats = [
        'total_users' => 0,
        'total_reservations' => 0,
        'total_menu_items' => 0,
        'pending_reservations' => 0,
        'todays_reservations' => 0,
        'featured_items' => 0
    ];
    $recent_reservations = [];
    $recent_users = [];
}

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .admin-sidebar {
            background-color: #111827;
            min-height: 100vh;
            padding-top: 2rem;
        }
        .admin-content {
            background-color: #faf9f7;
            min-height: 100vh;
            padding: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #ea580c;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ea580c;
            font-family: "Cormorant Garamond", serif;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .admin-nav-link {
            color: #9ca3af;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: block;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
            font-family: "Cormorant Garamond", serif;
            font-size: 1.1rem;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background-color: #ea580c;
            color: white;
        }
        .recent-activity {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="px-3">
                    <h2 class="text-white mb-4" style="font-family: 'Cormorant Garamond', serif;">
                        <a href="../index.php" style="color: #ea580c; text-decoration: none;">Savoria</a>
                        <small class="d-block text-muted" style="font-size: 0.8rem;">Admin Panel</small>
                    </h2>
                    
                    <nav class="nav flex-column">
                        <a href="index.php" class="admin-nav-link active">
                            <i class="me-2">📊</i> Dashboard
                        </a>
                        <a href="menu-list.php" class="admin-nav-link">
                            <i class="me-2">🍽️</i> Menu Items
                        </a>
                        <a href="reservation-list.php" class="admin-nav-link">
                            <i class="me-2">📅</i> Reservations
                        </a>
                        <a href="user-list.php" class="admin-nav-link">
                            <i class="me-2">👥</i> Users
                        </a>
                        <a href="category-list.php" class="admin-nav-link">
                            <i class="me-2">📂</i> Categories
                        </a>
                        <hr class="my-3" style="border-color: #374151;">
                        <a href="../index.php" class="admin-nav-link">
                            <i class="me-2">🏠</i> Back to Site
                        </a>
                        <form method="POST" action="../auth/logout.php" class="mt-2">
                            <button type="submit" class="admin-nav-link border-0 bg-transparent w-100 text-start">
                                <i class="me-2">🚪</i> Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 style="font-family: 'Cormorant Garamond', serif; color: #111827;">Dashboard</h1>
                        <p class="text-muted">Welcome back, <?= htmlspecialchars($user['name']) ?>!</p>
                    </div>
                    <div class="text-muted">
                        <?= date('l, F j, Y') ?>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php flash_show_all(); ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['total_reservations']) ?></div>
                            <div class="stat-label">Total Reservations</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['total_menu_items']) ?></div>
                            <div class="stat-label">Menu Items</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['pending_reservations']) ?></div>
                            <div class="stat-label">Pending Reservations</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['todays_reservations']) ?></div>
                            <div class="stat-label">Today's Reservations</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($stats['featured_items']) ?></div>
                            <div class="stat-label">Featured Items</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="recent-activity">
                            <h3 class="mb-3" style="font-family: 'Cormorant Garamond', serif;">Quick Actions</h3>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="menu-create.php" class="btn btn-primary w-100">
                                        <i class="me-2">➕</i> Add Menu Item
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="reservation-list.php?status=pending" class="btn btn-warning w-100">
                                        <i class="me-2">⏳</i> Pending Reservations
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="user-create.php" class="btn btn-success w-100">
                                        <i class="me-2">👤</i> Add User
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="category-create.php" class="btn btn-info w-100">
                                        <i class="me-2">📂</i> Add Category
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="recent-activity">
                            <h3 class="mb-3" style="font-family: 'Cormorant Garamond', serif;">Recent Reservations</h3>
                            <?php if (empty($recent_reservations)): ?>
                                <p class="text-muted">No reservations yet.</p>
                            <?php else: ?>
                                <?php foreach ($recent_reservations as $reservation): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?= htmlspecialchars($reservation['name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($reservation['reservation_date'])) ?> 
                                                    at <?= date('g:i A', strtotime($reservation['reservation_time'])) ?>
                                                    • <?= $reservation['number_of_guests'] ?> guests
                                                </small>
                                            </div>
                                            <span class="badge bg-<?= $reservation['status'] === 'pending' ? 'warning' : ($reservation['status'] === 'confirmed' ? 'success' : 'secondary') ?>">
                                                <?= ucfirst($reservation['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <a href="reservation-list.php" class="btn btn-outline-primary btn-sm">View All Reservations</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="recent-activity">
                            <h3 class="mb-3" style="font-family: 'Cormorant Garamond', serif;">Recent Users</h3>
                            <?php if (empty($recent_users)): ?>
                                <p class="text-muted">No users yet.</p>
                            <?php else: ?>
                                <?php foreach ($recent_users as $recent_user): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?= htmlspecialchars($recent_user['name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($recent_user['email']) ?>
                                                    • Joined <?= date('M j, Y', strtotime($recent_user['created_at'])) ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?= $recent_user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= ucfirst($recent_user['role']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <a href="user-list.php" class="btn btn-outline-primary btn-sm">View All Users</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
