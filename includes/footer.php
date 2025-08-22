<?php
require_once __DIR__ . '/csrf.php';

function render_footer($show_reservation_form = true) {
?>
<!-- Made reservation form show by default on all pages -->
<?php if ($show_reservation_form): ?>
<section class="d-flex w-100 contactFooter-container justify-content-center align-items-center">
    <div class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
        <div class="d-flex flex-column w-75">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <h1 class="reservation-title text-center">Make a Reservation</h1>
                <p class="reservation-description text-center">Book your table for an unforgettable dining experience</p>
            </div>
            <div class="d-flex w-100">
                <?php if (is_logged_in()): ?>
                <form id="footerReservationForm" method="POST" action="process_reservation.php" class="d-flex flex-row justify-content-center align-items-center w-100 gap-3">
                    <?= csrf_field() ?>
                    <div class="flex-column d-flex w-100 gap-4">
                        <input id="footerName" type="text" name="name" placeholder="Name" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <input id="footerEmail" type="email" name="email" placeholder="Email" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <input type="date" id="footerDate" name="date" placeholder="Date" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                    </div>
                    <div class="flex-column d-flex w-100 gap-4">
                        <input type="time" id="footerTime" name="time" placeholder="Time" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required />
                        <input id="footerNumberGuests" type="number" name="guests" placeholder="Number of Guests" min="1" max="8" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <button class="btn w-100 p-3 reservation-btn" type="submit" id="footerReserveBtn">
                            <span class="btn-text">Reserve Now</span>
                            <span class="btn-spinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
                <div id="footerReservationMessage" class="mt-3 w-100" style="display: none;"></div>
                <?php else: ?>
                <div class="text-center p-4 border rounded bg-light w-100">
                    <h4 class="mb-3">Ready to Make a Reservation?</h4>
                    <p class="mb-4">Join Savoria to book your table and enjoy exclusive member benefits.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="auth/login.php" class="btn btn-Reserve">Login to Reserve</a>
                        <a href="auth/register.php" class="btn btn-outline-primary">Create Account</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<footer class="w-100 footer-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h3>Savoria</h3>
                <p>Experience culinary excellence in the heart of the city. Our passion for fine dining and exceptional service creates unforgettable moments.</p>
            </div>
            <div class="col-lg-4 mb-4">
                <h4>Contact Info</h4>
                <p><strong>Address:</strong> 123 Gourmet Street, Culinary District</p>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> info@savoria.com</p>
            </div>
            <div class="col-lg-4 mb-4">
                <h4>Opening Hours</h4>
                <p><strong>Monday - Thursday:</strong> 5:00 PM - 10:00 PM</p>
                <p><strong>Friday - Saturday:</strong> 5:00 PM - 11:00 PM</p>
                <p><strong>Sunday:</strong> 4:00 PM - 9:00 PM</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 text-center">
                <p>&copy; 2024 Savoria. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<!-- Enhanced AJAX handling for footer reservation form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for footer form
    const footerDateInput = document.getElementById('footerDate');
    if (footerDateInput) {
        footerDateInput.min = new Date().toISOString().split('T')[0];
    }
    
    // Footer reservation form AJAX handling
    const footerForm = document.getElementById('footerReservationForm');
    if (footerForm) {
        footerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('footerReserveBtn');
            const messageDiv = document.getElementById('footerReservationMessage');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnSpinner = submitBtn.querySelector('.btn-spinner');
            
            // Show loading state with spinner
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline-block';
            messageDiv.style.display = 'none';
            
            const formData = new FormData(this);
            
            fetch('process_reservation.php', {
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
                    messageDiv.innerHTML = '<strong>Success!</strong> ' + data.message;
                    this.reset();
                    // Reset minimum date
                    footerDateInput.min = new Date().toISOString().split('T')[0];
                    
                    // Show success animation
                    messageDiv.style.opacity = '0';
                    messageDiv.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        messageDiv.style.transition = 'all 0.3s ease';
                        messageDiv.style.opacity = '1';
                        messageDiv.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    messageDiv.className = 'alert alert-danger mt-3';
                    messageDiv.innerHTML = '<strong>Error!</strong> ' + data.message;
                }
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger mt-3';
                messageDiv.innerHTML = '<strong>Error!</strong> An error occurred. Please try again.';
            })
            .finally(() => {
                submitBtn.disabled = false;
                btnText.style.display = 'inline-block';
                btnSpinner.style.display = 'none';
            });
        });
    }
});
</script>
<?php
}
?>
