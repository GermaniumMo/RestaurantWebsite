<?php
require_once __DIR__ . '/auth.php';
?>
<header class="d-flex top-header w-100 border-bottom border-dark" style="background-color: white">
    <div class="d-flex justify-content-between py-3 w-100 header-container">
        <h1 style="color: black"><a href="index.php" style="color: black; text-decoration: none;">Savoria</a></h1>
        <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
            <li><a href="index.php" style="color: #4b5563; text-decoration: none">Home</a></li>
            <li><a href="menu.php" style="color: #4b5563; text-decoration: none">Menu</a></li>
            <li><a href="about.php" style="color: #4b5563; text-decoration: none">About</a></li>
            <li><a href="contact.php" style="color: #4b5563; text-decoration: none">Contact</a></li>
        </ul>
        <div class="d-flex gap-3">
            <?php if (is_logged_in()): ?>
                <?php $user = current_user(); ?>
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
                <a class="btn btn-order" type="button" href="menu.php">Order Now</a>
            <?php else: ?>
                <a class="btn btn-Reserve" href="auth/login.php">Login to Reserve</a>
                <a class="btn btn-order" href="auth/register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
