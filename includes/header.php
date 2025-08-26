<?php
    require_once __DIR__ . '/auth.php';

    $current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="d-flex top-header w-100 border-bottom border-dark sticky-top" style="background-color: white; top: 0 !important;">
    <div class="d-flex justify-content-between py-3 w-100 header-container align-items-center">
        <h1 style="color: black">
            <a href="index.php" style="color: black; text-decoration: none;">Savoria</a>
        </h1>
        <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
            <li>
                <a href="index.php"
                   style="text-decoration: none; color:                                                                                                               <?php echo $current_page === 'index.php' ? '#ea580c' : '#4b5563' ?>;">
                   Home
                </a>
            </li>
            <li>
                <a href="menu.php"
                   style="text-decoration: none; color:                                                                                                               <?php echo $current_page === 'menu.php' ? '#ea580c' : '#4b5563' ?>;">
                   Menu
                </a>
            </li>
            <li>
                <a href="about.php"
                   style="text-decoration: none; color:                                                                                                               <?php echo $current_page === 'about.php' ? '#ea580c' : '#4b5563' ?>;">
                   About
                </a>
            </li>
            <li>
                <a href="contact.php"
                   style="text-decoration: none; color:                                                                                                               <?php echo $current_page === 'contact.php' ? '#ea580c' : '#4b5563' ?>;">
                   Contact
                </a>
            </li>
            <li>
                      <a type="button" href="#reservationForm" style="color: #4b5563; text-decoration: none">Reservation</a>
          </li>
        </ul>
        <div class="d-flex gap-3">
            <?php if (is_logged_in()): ?>
<?php $user = current_user(); ?>
                <div class="dropdown">
                    <button class="btn btn-profile dropdown-toggle" style="color: #ea580c" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20.5899 22C20.5899 18.13 16.7399 15 11.9999 15C7.25991 15 3.40991 18.13 3.40991 22" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                       <li class="dropdown-item" style="pointer-events: none; color: #ea580c">
        <?php echo htmlspecialchars($user['name']) ?>
    </li>
    <li><hr class="dropdown-divider"></li>

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
