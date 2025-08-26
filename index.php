<?php
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/flash.php';
    require_once __DIR__ . '/includes/csrf.php';
    $current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
      rel="stylesheet" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous" />
    <link rel="stylesheet" href="css/main.css" />
  </head>
  <body>
       <header class="d-flex top-header w-100 position-absolute left-0">
      <div class="d-flex justify-content-between py-3 w-100 header-container align">
        <h1>Savoria</h1>
        <ul
          class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
          <li>
                <a href="index.php"
                   style="text-decoration: none; color:                                                        <?php echo $current_page === 'index.php' ? '#ea580c' : '#4b5563' ?>;">
                   Home
                </a>
            </li>
          <li>
            <a href="menu.php" style="color: white; text-decoration: none"
              >Menu</a
            >
          </li>
          <li>
            <a href="about.php" style="color: white; text-decoration: none"
              >About</a
            >
          </li>
          <li>
            <a href="contact.php" style="color: white; text-decoration: none"
              >Contact</a
            >
          </li>
          <li>
                      <a type="button" href="#reservationForm" style="color: white; text-decoration: none">Reservation</a>
          </li>
        </ul>
             <div class="d-flex gap-3">
          <?php if (is_logged_in()): ?>
            <div class="dropdown relative">
              <button class="btn btn-profile dropdown-toggle" style="color: white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20.5899 22C20.5899 18.13 16.7399 15 11.9999 15C7.25991 15 3.40991 18.13 3.40991 22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
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
            <a class="btn btn-Reserve" href="auth/register.php">Sign Up</a>
            <a class="btn btn-order" href="auth/login.php">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </header>
    <?php if (flash_show('success') || flash_show('error') || flash_show('warning') || flash_show('info')): ?>
      <div class="container-fluid" style="margin-top: 100px;">
        <div class="row">
          <div class="col-12">
            <?php flash_show_all(); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <section class="w-100 position-relative z-index">
      <img src="images/home/Home.png" alt="Home-img" class="w-100 img-mask" />
      <div class="overlay"></div>
    </section>

    <section class="d-flex flex-row w-100 features-restaurant">
      <div class="d-flex flex-row w-100 justify-content-between features gap-4">
        <div
          class="d-flex flex-column justify-content-center align-items-center">
          <svg
            width="33"
            height="36"
            viewBox="0 0 33 36"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M29.8281 0C28.7031 0 20.8281 2.25 20.8281 12.375V20.25C20.8281 22.732 22.8461 24.75 25.3281 24.75H27.5781V33.75C27.5781 34.9945 28.5836 36 29.8281 36C31.0727 36 32.0781 34.9945 32.0781 33.75V24.75V16.875V2.25C32.0781 1.00547 31.0727 0 29.8281 0ZM5.07812 1.125C5.07812 0.548438 4.64922 0.0703125 4.07266 0.00703125C3.49609 -0.05625 2.98281 0.323437 2.85625 0.878906L0.725781 10.4625C0.627344 10.9055 0.578125 11.3555 0.578125 11.8055C0.578125 15.1875 3.32344 17.9328 6.70547 17.9328C10.0875 17.9328 12.8328 15.1875 12.8328 11.8055C12.8328 11.3555 12.7836 10.9055 12.6852 10.4625L10.5547 0.878906C10.4281 0.323437 9.91484 -0.05625 9.33828 0.00703125C8.76172 0.0703125 8.33281 0.548438 8.33281 1.125V9C8.33281 9.62109 7.82656 10.125 7.20547 10.125C6.58437 10.125 6.07812 9.62109 6.07812 9V1.125H5.07812Z"
              fill="#ea580c" />
          </svg>
          <h3 class="text-center">Fine Dining</h3>
          <p class="text-center">
            Experience culinary excellence with our carefully crafted dishes
            made from the finest ingredients.
          </p>
        </div>
        <div
          class="d-flex flex-column justify-content-center align-items-center">
          <svg
            width="36"
            height="36"
            viewBox="0 0 36 36"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M18 0C8.05898 0 0 8.05898 0 18C0 27.941 8.05898 36 18 36C27.941 36 36 27.941 36 18C36 8.05898 27.941 0 18 0ZM18 6.75C21.7266 6.75 24.75 9.77344 24.75 13.5C24.75 17.2266 21.7266 20.25 18 20.25C14.2734 20.25 11.25 17.2266 11.25 13.5C11.25 9.77344 14.2734 6.75 18 6.75ZM18 31.5C13.5 31.5 9.5625 29.25 7.3125 25.6875C7.875 22.5 14.625 20.8125 18 20.8125C21.375 20.8125 28.125 22.5 28.6875 25.6875C26.4375 29.25 22.5 31.5 18 31.5Z"
              fill="#ea580c" />
          </svg>
          <h3 class="text-center">Premium Drinks</h3>
          <p class="text-center">
            Enjoy our extensive selection of premium wines, craft cocktails, and
            artisanal beverages.
          </p>
        </div>
        <div
          class="d-flex flex-column justify-content-center align-items-center">
          <svg
            width="36"
            height="32"
            viewBox="0 0 36 32"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M18 0L22.2656 10.7344L34.1406 12.4688L26.0703 20.2656L28.5312 32L18 26.5L7.46875 32L9.92969 20.2656L1.85938 12.4688L13.7344 10.7344L18 0Z"
              fill="#ea580c" />
          </svg>
          <h3 class="text-center">5-Star Service</h3>
          <p class="text-center">
            Our dedicated staff ensures every guest receives exceptional service
            and attention to detail.
          </p>
        </div>
      </div>
    </section>

    <section class="d-flex w-100">
      <div class="d-flex flex-column w-100 menu-restaurant gap-5">
        <div
          class="w-100 d-flex flex-column justify-content-center align-items-center gap-3">
          <h1>Our Signature Dishes</h1>
          <p>Discover our chef's carefully curated selection</p>
        </div>
        <div class="row">
          <div class="col-md-4 mb-4">
            <div class="card h-100"> <img
              src="images/pan-seared-scallops.png"
              class="card-img-top shadow"
              alt="Grilled Sea Bass Image" />
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Grilled Sea Bass</h5>
              <p class="card-text flex-grow-1">
                Fresh Mediterranean sea bass with herbs and lemon butter sauce
              </p>
            <div class="d-flex justify-content-between align-items-center mt-auto">  <span>$42</span></div></div></div>

            </div>
             <div class="col-md-4 mb-4">
              <div class="card h-100"> <img
              src="images/-prime-ribeye-steak----28-day-aged-beef-with-roast.png"
              class="card-img-top shadow"
                alt="Prime Ribeye Steak Image" />
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Prime Ribeye Steak</h5>
              <p class="card-text flex-grow-1">
                28-day aged beef with roasted vegetables and red wine jus
              </p>
            <div class="d-flex justify-content-between align-items-center mt-auto">   <span>$56</span></div></div></div></div>
               <div class="col-md-4 mb-4">
              <div class="card h-100"> <img
              src="images/-chocolate-symphony----dark-chocolate-mousse-with-.png"
              class="card-img-top shadow"
               alt="Chocolate Symphony Image" />
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Chocolate Symphony</h5>
              <p class="card-text flex-grow-1">
                 Dark chocolate mousse with berry compote and gold leaf
              </p>
            <div class="d-flex justify-content-between align-items-center mt-auto">   <span>$18</span></div></div></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="w-100 menu-section features-restaurant">
      <div class="d-flex flex-column w-100 menu-restaurant gap-5">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h2 class="section-title">Our Signature Menu</h2>
            <p class="section-subtitle">Discover our chef's carefully curated selection</p>
          </div>
        </div>
        <div class="row" id="menu-items">
          <!-- Menu items will be loaded here -->
        </div>
      </div>
    </section>

<script src="js/main.js"></script>
 <script>
document.addEventListener('DOMContentLoaded', () => {
  const dateInput = document.getElementById('date');
  if (dateInput) {
    dateInput.min = new Date().toISOString().split('T')[0];
  }
});
</script>

        <?php include 'includes/footer.php'; ?>
  </body>
</html>
