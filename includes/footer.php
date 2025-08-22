<section class="d-flex w-100 contactFooter-container justify-content-center align-items-center">
    <div class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
        <div class="d-flex flex-column w-75">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <h1 class="reservation-title text-center">Make a Reservation</h1>
                <p class="reservation-description text-center">Book your table for an unforgettable dining experience</p>
            </div>
            <div class="d-flex w-100">
                <form id="reservationForm" method="POST" action="process_reservation.php" class="d-flex flex-row justify-content-center align-items-center w-100 gap-3">
                    <div class="flex-column d-flex w-100 gap-4">
                        <input id="name" type="text" name="name" placeholder="Name" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <input id="email" type="email" name="email" placeholder="Email" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <input type="date" id="date" name="date" placeholder="Date" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                    </div>
                    <div class="flex-column d-flex w-100 gap-4">
                        <input type="time" id="time" name="time" placeholder="Time" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required />
                        <input id="numberGuests" type="number" name="guests" placeholder="Number of Guests" class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form" required/>
                        <button class="btn w-100 p-3 reservation-btn" type="submit" id="reserveBtn">Reserve Now</button>
                    </div>
                </form>
            </div>
        </div>


    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
