<?php
require_once 'includes/auth.php';
$current_user = get_current_user();
?>

<!-- Updated header with user authentication -->
<header class="d-flex top-header w-100 border-bottom border-dark" style="background-color: white">
    <div class="d-flex justify-content-between py-3 w-100 header-container">
        <h1 style="color: black">Savoria</h1>
        <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
            <li>
                <a href="index.php" style="color: #4b5563; text-decoration: none">Home</a>
            </li>
            <li>
                <a href="menu.php" style="color: #4b5563; text-decoration: none">Menu</a>
            </li>
            <li>
                <a href="about.php" style="color: #4b5563; text-decoration: none">About</a>
            </li>
            <li>
                <a href="contact.php" style="color: #4b5563; text-decoration: none">Contact</a>
            </li>
        </ul>
        <div class="d-flex gap-3 align-items-center">
            <a href="#" class="text-dark fs-4" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart"></i>
            </a>
            
            <?php if ($current_user): ?>
                <!-- Logged in user menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($current_user->first_name); ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="profile/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="profile/reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a></li>
                        <li><a class="dropdown-item" href="profile/orders.php"><i class="bi bi-bag me-2"></i>Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Guest user buttons -->
                <a class="btn btn-outline-primary" href="auth/login.php">Login</a>
                <a class="btn btn-primary" href="auth/register.php" style="background-color: #ea580c; border-color: #ea580c;">Register</a>
            <?php endif; ?>
            
            <a class="btn btn-Reserve" type="button" href="index.php#Reservation">Reserve Table</a>
            <a class="btn btn-order" type="button" href="menu.php">Order Now</a>
        </div>
    </div>
</header>
