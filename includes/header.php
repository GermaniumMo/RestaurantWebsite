<?php
require_once __DIR__ . '/auth.php';

function render_header($style = 'default', $current_page = '') {
    $header_class = 'd-flex top-header w-100';
    $container_style = '';
    $text_color = '#4b5563';
    $logo_color = 'black';
    
    if ($style === 'transparent') {
        $header_class .= ' position-absolute';
        $container_style = 'z-index: 1000;';
        $text_color = 'white';
        $logo_color = 'white';
    } else {
        $header_class .= ' border-bottom border-dark';
        $container_style = 'background-color: white;';
    }
?>
<header class="<?= $header_class ?>" style="<?= $container_style ?>">
    <div class="d-flex justify-content-between py-3 w-100 header-container">
        <h1 style="color: <?= $logo_color ?>">
            <a href="index.php" style="color: <?= $logo_color ?>; text-decoration: none;">Savoria</a>
        </h1>
        <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
            <li>
                <a href="index.php" 
                   style="color: <?= $current_page === 'home' ? '#ea580c' : $text_color ?>; text-decoration: none; font-weight: <?= $current_page === 'home' ? '600' : 'normal' ?>">
                   Home
                </a>
            </li>
            <li>
                <a href="menu.php" 
                   style="color: <?= $current_page === 'menu' ? '#ea580c' : $text_color ?>; text-decoration: none; font-weight: <?= $current_page === 'menu' ? '600' : 'normal' ?>">
                   Menu
                </a>
            </li>
            <li>
                <a href="about.php" 
                   style="color: <?= $current_page === 'about' ? '#ea580c' : $text_color ?>; text-decoration: none; font-weight: <?= $current_page === 'about' ? '600' : 'normal' ?>">
                   About
                </a>
            </li>
            <li>
                <a href="contact.php" 
                   style="color: <?= $current_page === 'contact' ? '#ea580c' : $text_color ?>; text-decoration: none; font-weight: <?= $current_page === 'contact' ? '600' : 'normal' ?>">
                   Contact
                </a>
            </li>
            <!-- Added Reservation button to navigation -->
            <li>
                <a href="#reservation" 
                   style="color: <?= $text_color ?>; text-decoration: none;"
                   onclick="document.querySelector('.contactFooter-container').scrollIntoView({behavior: 'smooth'});">
                   Reservation
                </a>
            </li>
        </ul>
        <div class="d-flex gap-3">
            <?php if (is_logged_in()): ?>
                <?php $user = current_user(); ?>
                <!-- Replaced username with Profile label and human SVG icon -->
                <div class="dropdown">
                    <button class="btn btn-Reserve dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20.5899 22C20.5899 18.13 16.7399 15 11.9999 15C7.25991 15 3.40991 18.13 3.40991 22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Profile
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
                <a class="btn btn-order" href="menu.php">Order Now</a>
            <?php else: ?>
                <a class="btn btn-Reserve" href="auth/login.php">Login</a>
                <a class="btn btn-order" href="auth/register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php
}
?>
