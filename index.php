<?php
require_once 'get_menu_items.php';
require_once 'includes/functions.php';

// Get featured menu items (first 3 items)
$featuredItems = array_slice(getMenuItems(), 0, 3);

// Check for reservation success/error messages
$reservationSuccess = isset($_SESSION['reservation_success']) && $_SESSION['reservation_success'];
$reservationError = isset($_SESSION['reservation_error']) && $_SESSION['reservation_error'];

// Clear the session messages
unset($_SESSION['reservation_success']);
unset($_SESSION['reservation_error']);
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
    <?php if ($reservationSuccess): ?>
      <div id="reservationAlert" style="
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        font-weight: 500;
        font-size: 16px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
      ">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0f5132" viewBox="0 0 16 16">
          <path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0zM6.93 11.588l-3.39-3.39 1.06-1.06 2.33 2.33 4.95-4.95 1.06 1.06-6.01 6.01z"/>
        </svg>
        An email has been sent to confirm your reservation.
      </div>
      <script>
        setTimeout(() => {
          const alert = document.getElementById('reservationAlert');
          if (alert) alert.remove();
        }, 5000);
      </script>
    <?php endif; ?>

    <?php if ($reservationError): ?>
      <div id="reservationAlert" style="
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        font-weight: 500;
        font-size: 16px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
      ">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#721c24" viewBox="0 0 16 16">
          <path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
        </svg>
        Failed to submit reservation. Please try again.
      </div>
      <script>
        setTimeout(() => {
          const alert = document.getElementById('reservationAlert');
          if (alert) alert.remove();
        }, 5000);
      </script>
    <?php endif; ?>

    <header class="d-flex top-header w-100 position-absolute left-0">
      <div class="d-flex justify-content-between py-3 w-100 header-container">
        <h1>Savoria</h1>
        <ul
          class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
          <li>
            <a href="index.php" style="color: white; text-decoration: none"
              >Home</a
            >
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
        </ul>
        <div class="d-flex gap-3">
          <a class="btn btn-Reserve" type="button" href="#Reservation">Reserve Table</a>
          <a class="btn btn-order" type="button" href="menu.php">Order Now</a>
        </div>
      </div>
    </header>
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
              d="M29.8281 0C28.7031 0 20.8281 2.25 20.8281 12.375V20.25C20.8281 22.732 22.8461 24.75 25.3281 24.75H27.5781V33.75C27.5781 34.9945 28.5836 36 29.8281 36C31.0727 36 32.0781 34.9945 32.0781 33.75V24.75V16.875V2.25C32.0781 1.00547 31.0727 0 29.8281 0ZM5.07812 1.125C5.07812 0.548438 4.64922 0.0703125 4.07266 0.00703125C3.49609 -0.05625 2.98281 0.323437 2.85625 0.878906L0.725781 10.4625C0.627344 10.9055 0.578125 11.3555 0.578125 11.8055C0.578125 15.0328 3.04609 17.6836 6.20312 17.9719V33.75C6.20312 34.9945 7.20859 36 8.45312 36C9.69766 36 10.7031 34.9945 10.7031 33.75V17.9719C13.8602 17.6836 16.3281 15.0328 16.3281 11.8055C16.3281 11.3555 16.2789 10.9055 16.1805 10.4625L14.05 0.878906C13.9234 0.316406 13.3961 -0.05625 12.8266 0.00703125C12.257 0.0703125 11.8281 0.548438 11.8281 1.125V10.5609C11.8281 10.9406 11.5188 11.25 11.1391 11.25C10.7805 11.25 10.4852 10.9758 10.45 10.6172L9.57109 1.02656C9.52188 0.442969 9.03672 0 8.45312 0C7.86953 0 7.38438 0.442969 7.33516 1.02656L6.46328 10.6172C6.42812 10.9758 6.13281 11.25 5.77422 11.25C5.39453 11.25 5.08516 10.9406 5.08516 10.5609V1.125H5.07812ZM8.47422 11.8125H8.45312H8.43203L8.45312 11.7633L8.47422 11.8125Z"
              fill="#EA580C" />
          </svg>
          <h3>Fine Dining</h3>
          <p class="text-center">
            Experience culinary excellence with our masterfully crafted dishes.
          </p>
        </div>
        <div
          class="d-flex flex-column justify-content-center align-items-center">
          <svg
            width="21"
            height="36"
            viewBox="0 0 21 36"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M1.99138 2.06016C2.08982 0.9 3.06716 0 4.23435 0H17.7343C18.9015 0 19.8789 0.9 19.9773 2.06016L20.9617 13.9008C21.3836 18.9633 17.9734 23.407 13.2343 24.4898V31.5H16.6093C17.8539 31.5 18.8593 32.5055 18.8593 33.75C18.8593 34.9945 17.8539 36 16.6093 36H10.9843H5.35935C4.11482 36 3.10935 34.9945 3.10935 33.75C3.10935 32.5055 4.11482 31.5 5.35935 31.5H8.73435V24.4969C3.99529 23.4141 0.585129 18.9703 1.007 13.9078L1.99138 2.06719V2.06016ZM5.92888 9H16.0398L15.6672 4.5H6.30154L5.92888 9Z"
              fill="#EA580C" />
          </svg>

          <h3>Premium Drinks</h3>
          <p class="text-center">
            Extensive selection of fine wines and craft cocktails.
          </p>
        </div>
        <div
          class="d-flex flex-column justify-content-center align-items-center">
          <svg
            width="38"
            height="36"
            viewBox="0 0 38 36"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M20.6883 1.26562C20.3157 0.492188 19.5282 0 18.6633 0C17.7985 0 17.018 0.492188 16.6383 1.26562L12.1172 10.568L2.02035 12.0586C1.1766 12.1852 0.473476 12.7758 0.213319 13.5844C-0.0468368 14.393 0.1641 15.2859 0.768788 15.8836L8.09535 23.1328L6.36566 33.3773C6.22504 34.2211 6.5766 35.0789 7.27269 35.5781C7.96879 36.0773 8.88988 36.1406 9.64926 35.7398L18.6704 30.9234L27.6914 35.7398C28.4508 36.1406 29.3719 36.0844 30.068 35.5781C30.7641 35.0719 31.1157 34.2211 30.975 33.3773L29.2383 23.1328L36.5649 15.8836C37.1696 15.2859 37.3875 14.393 37.1203 13.5844C36.8532 12.7758 36.1571 12.1852 35.3133 12.0586L25.2094 10.568L20.6883 1.26562Z"
              fill="#EA580C" />
          </svg>
          <h3>5-Star Service</h3>
          <p class="text-center">
            Impeccable service that exceeds your expectations.
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
        <div class="d-flex flex-row justify-content-between gap-4">
          <?php foreach ($featuredItems as $item): ?>
          <div class="card" style="width: 24.666rem" data-item='<?php echo json_encode($item); ?>'>
            <img
              src="<?php echo htmlspecialchars($item['image_url']); ?>"
              class="card-img-top shadow"
              alt="<?php echo htmlspecialchars($item['name']); ?>" />
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
              <p class="card-text">
                <?php echo htmlspecialchars($item['description']); ?>
              </p>
              <span><?php echo format_price($item['price']); ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <section
      class="d-flex w-100 contactFooter-container justify-content-center align-items-center">
      <div
        class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
        <div class="d-flex flex-column w-75">
          <div
            class="d-flex flex-column justify-content-center align-items-center">
            <h1 class="reservation-title text-center">Make a Reservation</h1>
            <p class="reservation-description text-center">
              Book your table for an unforgettable dining experience
            </p>
          </div>
          <div class="d-flex w-100">
            <form id="Reservation"
              action="process_reservation.php"
              method="POST"
              class="d-flex flex-row justify-content-center align-items-center w-100 gap-3">
              <div class="flex-column d-flex w-100 gap-4">
                <input id="name"
                  type="text"
                  name="name"
                  placeholder="Name"
                  class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                <input
                id="email"
                  type="email"
                  name="email"
                  placeholder="Email"
                  class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                <input
                  type="date"
                  id="date"
                  name="date"
                  placeholder="Date"
                  class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
              </div>
              <div class="flex-column d-flex w-100 gap-4">
                <input
                  type="time"
                  id="time"
                  name="time"
                  placeholder="Time"
                  class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required />
                <input
                id="numberGuests"
                  type="number"
                  name="NumberofGuests"
                  placeholder="Number of Guests"
                  class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                <button class="btn w-100 p-3 reservation-btn" type="submit" id="reserveBtn" disabled>
                  Reserve Now
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="d-flex w-100 justify-content-center restaurant-info">
          <div class="d-flex flex-column gap-3" style="width: 288px">
            <h1 class="title-info">Savoria</h1>
            <p class="description-info">
              Experience the art of fine dining in an elegant atmosphere.
            </p>
          </div>
          <div class="col-3 col-md-3 mb-3">
            <h5 class="contact-title">Contact</h5>
            <ul class="nav flex-column">
              <li class="nav-item mb-2">
                <svg
                  width="12"
                  height="16"
                  viewBox="0 0 12 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M6.74062 15.6C8.34375 13.5938 12 8.73125 12 6C12 2.6875 9.3125 0 6 0C2.6875 0 0 2.6875 0 6C0 8.73125 3.65625 13.5938 5.25938 15.6C5.64375 16.0781 6.35625 16.0781 6.74062 15.6ZM6 4C6.53043 4 7.03914 4.21071 7.41421 4.58579C7.78929 4.96086 8 5.46957 8 6C8 6.53043 7.78929 7.03914 7.41421 7.41421C7.03914 7.78929 6.53043 8 6 8C5.46957 8 4.96086 7.78929 4.58579 7.41421C4.21071 7.03914 4 6.53043 4 6C4 5.46957 4.21071 4.96086 4.58579 4.58579C4.96086 4.21071 5.46957 4 6 4Z"
                    fill="#9CA3AF" />
                </svg>
                123 Gourmet Street
              </li>
              <li class="nav-item mb-2">
                <a href="#" class="nav-link p-0 text-body-secondary">
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M5.15312 0.768722C4.9125 0.187472 4.27812 -0.121903 3.67188 0.0437222L0.921875 0.793722C0.378125 0.943722 0 1.43747 0 1.99997C0 9.73122 6.26875 16 14 16C14.5625 16 15.0563 15.6218 15.2063 15.0781L15.9563 12.3281C16.1219 11.7218 15.8125 11.0875 15.2312 10.8468L12.2312 9.59685C11.7219 9.38435 11.1313 9.53122 10.7844 9.95935L9.52188 11.5C7.32188 10.4593 5.54062 8.6781 4.5 6.4781L6.04063 5.21872C6.46875 4.86872 6.61562 4.28122 6.40312 3.77185L5.15312 0.771847V0.768722Z"
                      fill="#9CA3AF" />
                  </svg>
                  (555) 123-4567</a
                >
              </li>
              <li class="nav-item mb-2">
                <a href="#" class="nav-link p-0 text-body-secondary">
                  <svg
                    width="16"
                    height="12"
                    viewBox="0 0 16 12"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M1.5 0C0.671875 0 0 0.671875 0 1.5C0 1.97187 0.221875 2.41562 0.6 2.7L7.4 7.8C7.75625 8.06563 8.24375 8.06563 8.6 7.8L15.4 2.7C15.7781 2.41562 16 1.97187 16 1.5C16 0.671875 15.3281 0 14.5 0H1.5ZM0 3.5V10C0 11.1031 0.896875 12 2 12H14C15.1031 12 16 11.1031 16 10V3.5L9.2 8.6C8.4875 9.13438 7.5125 9.13438 6.8 8.6L0 3.5Z"
                      fill="#9CA3AF" />
                  </svg>
                  info@savoria.com</a
                >
              </li>
            </ul>
          </div>
          <div class="col-3 col-md-3 mb-3">
            <h5 class="contact-title">Hours</h5>
            <ul class="nav flex-column">
              <li class="nav-item mb-2">Mon-Thu: 11:00 am - 10:00 pm</li>
              <li class="nav-item mb-2">Fri-Sat: 11:00 am - 11:00 pm</li>
              <li class="nav-item mb-2">Sun: 11:00 am - 9:00 pm</li>
            </ul>
          </div>
          <div class="d-flex flex-column">
            <h5 class="contact-title">Follow Us</h5>
            <ul class="list-unstyled d-flex">
              <li class="ms-3">
                <a class="link-body-emphasis" href="#">
                  <svg
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M19.6875 10C19.6875 4.64844 15.3516 0.3125 10 0.3125C4.64844 0.3125 0.3125 4.64844 0.3125 10C0.3125 14.8352 3.85508 18.843 8.48633 19.5703V12.8004H6.02539V10H8.48633V7.86562C8.48633 5.43789 9.93164 4.09687 12.1453 4.09687C13.2055 4.09687 14.3141 4.28594 14.3141 4.28594V6.66875H13.0922C11.8891 6.66875 11.5137 7.41562 11.5137 8.18164V10H14.2004L13.7707 12.8004H11.5137V19.5703C16.1449 18.843 19.6875 14.8352 19.6875 10Z"
                      fill="#9CA3AF" />
                  </svg>
                </a>
              </li>
              <li class="ms-3">
                <a class="link-body-emphasis" href="#">
                  <svg
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M8.75391 4.50781C6.26953 4.50781 4.26562 6.51172 4.26562 8.99609C4.26562 11.4805 6.26953 13.4844 8.75391 13.4844C11.2383 13.4844 13.2422 11.4805 13.2422 8.99609C13.2422 6.51172 11.2383 4.50781 8.75391 4.50781ZM8.75391 11.9141C7.14844 11.9141 5.83594 10.6055 5.83594 8.99609C5.83594 7.38672 7.14453 6.07812 8.75391 6.07812C10.3633 6.07812 11.6719 7.38672 11.6719 8.99609C11.6719 10.6055 10.3594 11.9141 8.75391 11.9141ZM14.4727 4.32422C14.4727 4.90625 14.0039 5.37109 13.4258 5.37109C12.8438 5.37109 12.3789 4.90234 12.3789 4.32422C12.3789 3.74609 12.8477 3.27734 13.4258 3.27734C14.0039 3.27734 14.4727 3.74609 14.4727 4.32422ZM17.4453 5.38672C17.3789 3.98438 17.0586 2.74219 16.0312 1.71875C15.0078 0.695312 13.7656 0.375 12.3633 0.304687C10.918 0.222656 6.58594 0.222656 5.14062 0.304687C3.74219 0.371094 2.5 0.691406 1.47266 1.71484C0.445313 2.73828 0.128906 3.98047 0.0585937 5.38281C-0.0234375 6.82812 -0.0234375 11.1602 0.0585937 12.6055C0.125 14.0078 0.445313 15.25 1.47266 16.2734C2.5 17.2969 3.73828 17.6172 5.14062 17.6875C6.58594 17.7695 10.918 17.7695 12.3633 17.6875C13.7656 17.6211 15.0078 17.3008 16.0312 16.2734C17.0547 15.25 17.375 14.0078 17.4453 12.6055C17.5273 11.1602 17.5273 6.83203 17.4453 5.38672ZM15.5781 14.1562C15.2734 14.9219 14.6836 15.5117 13.9141 15.8203C12.7617 16.2773 10.0273 16.1719 8.75391 16.1719C7.48047 16.1719 4.74219 16.2734 3.59375 15.8203C2.82812 15.5156 2.23828 14.9258 1.92969 14.1562C1.47266 13.0039 1.57813 10.2695 1.57813 8.99609C1.57813 7.72266 1.47656 4.98438 1.92969 3.83594C2.23438 3.07031 2.82422 2.48047 3.59375 2.17187C4.74609 1.71484 7.48047 1.82031 8.75391 1.82031C10.0273 1.82031 12.7656 1.71875 13.9141 2.17187C14.6797 2.47656 15.2695 3.06641 15.5781 3.83594C16.0352 4.98828 15.9297 7.72266 15.9297 8.99609C15.9297 10.2695 16.0352 13.0078 15.5781 14.1562Z"
                      fill="#9CA3AF" />
                  </svg>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div
          class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top w-100 justify-content-center align-items-center">
          <p class="text-center w-100 footer-copyright">
            &copy; 2024 Company, Inc. All rights reserved.
          </p>
        </div>
      </div>
    </section>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        let currentPage = window.location.pathname.split("/").pop();

        let navLinks = document.querySelectorAll("header a");

        navLinks.forEach((link) => {
          if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
          }
        });
      });
    </script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
  </body>
</html>
