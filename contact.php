<?php
    require_once 'includes/db.php';
    require_once 'includes/flash.php';
    require_once 'includes/security.php';
    require_once 'includes/auth.php';
    require_once __DIR__ . '/includes/csrf.php';

    // Handle contact form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name  = sanitize_input($_POST['last_name'] ?? '');
        $email      = sanitize_input($_POST['email'] ?? '');
        $message    = sanitize_input($_POST['message'] ?? '');

        if (empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
            flash('error', 'Please fill in all fields.');
        } else {
            try {
                $result = db_insert(
                    "INSERT INTO contact_messages (first_name, last_name, email, message, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$first_name, $last_name, $email, $message],
                    'ssss'
                );

                if ($result) {
                    flash('success', 'Thank you for your message! We\'ll get back to you soon.');
                } else {
                    flash('error', 'Sorry, there was an error sending your message. Please try again.');
                }
            } catch (Exception $e) {
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
                <form method="POST" class="d-flex flex-column justify-content-center align-items-center w-100 gap-4">
                    <div class="flex-row d-flex w-100 gap-4">
                        <input type="text" name="first_name" placeholder="First name" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                        <input type="text" name="last_name" placeholder="Last name" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                    </div>
                    <div class="flex-column d-flex w-100 gap-4">
                        <input type="email" name="email" placeholder="Email" class="w-100 bg-transparent p-3 border border-1 rounded-3" required />
                        <textarea class="form-control" name="message" placeholder="Type your message here..." rows="5" required></textarea>
                        <button class="btn w-100 p-3 reservation-btn" type="submit" name="send_message">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
        <?php include 'includes/footer.php'; ?>
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
