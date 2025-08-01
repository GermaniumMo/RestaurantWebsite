<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_admin_login();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = array();

// Total menu items
$query = "SELECT COUNT(*) as count FROM menu_items WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['menu_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total reservations today
$query = "SELECT COUNT(*) as count FROM reservations WHERE date = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['reservations_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending reservations
$query = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Unread messages
$query = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['unread_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent reservations
$query = "SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Savoria</title>
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
        .stat-card {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            font-family: "Cormorant Garamond", serif;
        }
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .admin-title {
            font-family: "Cormorant Garamond", serif;
            font-size: 2rem;
            font-weight: 700;
            color: #ea580c;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu-items.php">
                            <i class="bi bi-card-list"></i> Menu Items
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php">
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
                    <h1 class="admin-title">Dashboard</h1>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['menu_items']; ?></div>
                                <div class="stat-label">Active Menu Items</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['reservations_today']; ?></div>
                                <div class="stat-label">Reservations Today</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['pending_reservations']; ?></div>
                                <div class="stat-label">Pending Reservations</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                                <div class="stat-label">Unread Messages</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Reservations</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_reservations)): ?>
                                        <p class="text-muted">No recent reservations</p>
                                    <?php else: ?>
                                        <?php foreach ($recent_reservations as $reservation): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($reservation['name']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo format_date($reservation['date']); ?> at <?php echo format_time($reservation['time']); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $reservation['status'] === 'confirmed' ? 'success' : ($reservation['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($reservation['status']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Messages</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_messages)): ?>
                                        <p class="text-muted">No recent messages</p>
                                    <?php else: ?>
                                        <?php foreach ($recent_messages as $message): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr($message['message'], 0, 50)) . '...'; ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $message['status'] === 'read' ? 'success' : ($message['status'] === 'unread' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($message['status']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
