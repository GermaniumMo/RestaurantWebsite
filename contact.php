<?php
require_once 'includes/db.php';
require_once 'includes/flash.php';
require_once 'includes/security.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    header('Content-Type: application/json');
}

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    verify_csrf();
    
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
            exit;
        }
        flash('error', 'Please fill in all fields.');
    } else {
        try {
            $result = db_insert(
                "INSERT INTO contact_messages (first_name, last_name, email, message, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$first_name, $last_name, $email, $message],
                'ssss'
            );
            
            if ($result) {
                if ($is_ajax) {
                    echo json_encode(['success' => true, 'message' => 'Thank you for your message! We\'ll get back to you soon.']);
                    exit;
                }
                flash('success', 'Thank you for your message! We\'ll get back to you soon.');
            } else {
                if ($is_ajax) {
                    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again.']);
                    exit;
                }
                flash('error', 'Sorry, there was an error sending your message. Please try again.');
            }
        } catch (Exception $e) {
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again.']);
                exit;
            }
            flash('error', 'Sorry, there was an error sending your message. Please try again.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Savoria Restaurant</title>
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
    <?php include 'includes/header.php'; ?>
    
    <?php 
    flash_show_all(); 
    ?>
    
    <section class="contact-section">
        <div class="contact-container d-flex flex-column justify-content-center align-items-center gap-3">
            <h1>Get in Touch</h1>
            <p>We'd love to hear from you. Whether you have a question about our menu, want to make a reservation, or need assistance, we're here to help.</p>
        </div>
    </section>
    <section>
        <div class="contact-container">  
            <div class="d-flex flex-row gap-4 justify-content-center m-0 align-items-center w-100">      
                <div class="card m-0" style="width: 25rem;">
                    <div class="card-body justify-content-center align-items-center d-flex flex-column gap-3 contact-card">
                        <img src="images/contact/location.png" alt="location logo">
                        <h6 class="card-subtitle mb-2 text-body-secondary">Visit Us</h6>
                        <p class="card-text m-0 no-shadow">123 Restaurant Street</p>
                        <p class="card-text m-0 no-shadow">New York, NY 10001</p>
                    </div>
                </div>
                <div class="card m-0" style="width: 25rem;">
                    <div class="card-body justify-content-center align-items-center d-flex flex-column gap-3 contact-card">
                        <img src="images/contact/phoneIcon.png" alt="location logo">
                        <h6 class="card-subtitle mb-2 text-body-secondary">Call Us</h6>
                        <p class="card-text m-0 no-shadow">+1 (555) 123-4567</p>
                        <p class="card-text m-0 no-shadow">+1 (555) 987-6543</p>
                    </div>
                </div>
                <div class="card m-0" style="width: 25rem;">
                    <div class="card-body justify-content-center align-items-center d-flex flex-column gap-3 contact-card">
                        <img src="images/contact/clockIcon.png" alt="location logo">
                        <h6 class="card-subtitle mb-2 text-body-secondary">Opening Hours</h6>
                        <p class="card-text m-0 no-shadow">Mon-Sat: 11:00 AM - 11:00 PM</p>
                        <p class="card-text m-0 no-shadow">Sunday: 12:00 PM - 9:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="message-section">
        <div class="d-flex justify-content-center contact-container">
            <div class="d-flex w-50 flex-column form-box justify-content-center align-items-center px-4 py-5 rounded-3">  
                <h2 class="mb-5">Send us a Message</h2>
                <!-- Updated form to work with PHP backend -->
                <form id="contactForm" method="POST" class="d-flex flex-column justify-content-center align-items-center w-100 gap-4">
                    <?= csrf_field() ?>
                    <div class="flex-row d-flex w-100 gap-4">
                        <input type="text" name="first_name" placeholder="First name" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                        <input type="text" name="last_name" placeholder="Last name" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                    </div>
                    <div class="flex-column d-flex w-100 gap-4">
                        <input type="email" name="email" placeholder="Email" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                        <textarea class="form-control" name="message" placeholder="Type your message here..." rows="5" required></textarea>
                        <button class="btn w-100 p-3 reservation-btn" type="submit" name="send_message" id="contactSubmitBtn">Send Message</button>
                    </div>
                </form>
                <div id="contactMessage" class="mt-3 w-100" style="display: none;"></div>
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
                    <?php if (is_logged_in()): ?>
                        <!-- Show functional reservation form for logged-in users -->
                        <form id="Reservation"
                            action="process_reservation.php"
                            method="POST"
                            class="d-flex flex-row justify-content-center align-items-center w-100 gap-3">
                            <?= csrf_field() ?>
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
                                    id="guests"
                                    type="number"
                                    name="guests"
                                    placeholder="Number of Guests"
                                    min="1"
                                    max="8"
                                    class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                                <button class="btn w-100 p-3 reservation-btn" type="submit">
                                    Reserve Now
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Show login prompt for guests -->
                        <div class="d-flex flex-column justify-content-center align-items-center w-100 gap-4 text-center p-4 border rounded bg-light">
                            <h4>Ready to Make a Reservation?</h4>
                            <p>Join Savoria to book your table and enjoy exclusive member benefits.</p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="auth/login.php" class="btn reservation-btn">Login to Reserve</a>
                                <a href="auth/register.php" class="btn btn-outline-primary">Create Account</a>
                            </div>
                            <p class="text-muted small">Already have an account? <a href="auth/login.php">Sign in here</a></p>
                        </div>
                    <?php endif; ?>
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

        <?php include 'includes/footer.php'; ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let currentPage = window.location.pathname.split("/").pop();

                let navLinks = document.querySelectorAll("header a");

                navLinks.forEach((link) => {
                    if (link.getAttribute("href") === currentPage) {
                        link.classList.add("active");
                    }
                });
                
                // Set minimum date to today for reservation form
                const dateInput = document.getElementById('date');
                if (dateInput) {
                    dateInput.min = new Date().toISOString().split('T')[0];
                }
                
                document.getElementById('contactForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = document.getElementById('contactSubmitBtn');
                    const messageDiv = document.getElementById('contactMessage');
                    const originalText = submitBtn.textContent;
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Sending...';
                    messageDiv.style.display = 'none';
                    
                    const formData = new FormData(this);
                    
                    fetch('contact.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        messageDiv.style.display = 'block';
                        if (data.success) {
                            messageDiv.className = 'alert alert-success mt-3';
                            messageDiv.textContent = data.message;
                            this.reset();
                        } else {
                            messageDiv.className = 'alert alert-danger mt-3';
                            messageDiv.textContent = data.message;
                        }
                    })
                    .catch(error => {
                        messageDiv.style.display = 'block';
                        messageDiv.className = 'alert alert-danger mt-3';
                        messageDiv.textContent = 'An error occurred. Please try again.';
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
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
