<?php
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    require_once 'includes/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us - Savoria Restaurant</title>
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

    <section class="introduction-section">
        <div class="introduction-container d-flex flex-column justify-content-center align-items-center gap-3">
            <h1>Our Story</h1>
            <p>Discover the passion and dedication behind Savoria, where culinary excellence meets warm hospitality.</p>
        </div>
    </section>

    <section>
      <div class="about-container d-flex flex-row gap-5">
        <div class="d-flex flex-column gap-4 justify-content-center">
          <h2 class="legacy-title">A Legacy of Excellence</h2>
          <div class="d-flex flex-column gap-5 align-items-center">
            <p class="legacy-description">
              Founded in 2010, Savoria has been serving exceptional cuisine and
              creating memorable dining experiences for over a decade. Our
              commitment to quality ingredients, innovative recipes, and
              impeccable service has made us a beloved destination for food
              enthusiasts.
            </p>
            <p class="legacy-description">
              Every dish we serve tells a story of tradition, creativity, and
              passion for gastronomy. Our team of talented chefs combines
              classical techniques with modern interpretations to create
              unforgettable culinary experiences.
            </p>
          </div>
        </div>
        <img src="images/about/restaurantAbout.png" alt="" />
      </div>
    </section>

    <section class="coreValues-section">
      <div
        class="d-flex flex-column coreValues-container gap-5 align-items-center">
        <h2>Our Core Values</h2>
        <div class="d-flex flex-row gap-4">
          <div class="card" style="border: none;">
            <div class="card-body p-5">
              <svg
                width="36"
                height="32"
                viewBox="0 0 36 32"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M19.125 4.74992C13.5984 4.74992 8.92266 8.37102 7.33359 13.3632C9.69609 12.1679 12.3609 11.4999 15.1875 11.4999H21.375C21.9938 11.4999 22.5 12.0062 22.5 12.6249C22.5 13.2437 21.9938 13.7499 21.375 13.7499H20.25H15.1875C14.0203 13.7499 12.8883 13.8835 11.7984 14.1296C9.97734 14.5445 8.28281 15.2827 6.77813 16.2882C2.69297 19.0093 0 23.657 0 28.9374V30.0624C0 30.9976 0.752344 31.7499 1.6875 31.7499C2.62266 31.7499 3.375 30.9976 3.375 30.0624V28.9374C3.375 25.5132 4.83047 22.4335 7.15781 20.2749C8.55 25.5835 13.3805 29.4999 19.125 29.4999H19.1953C28.4836 29.4507 36 20.296 36 9.01086C36 6.01555 35.4727 3.16789 34.5164 0.601484C34.3336 0.116328 33.6234 0.137422 33.3773 0.594453C32.0555 3.06945 29.4398 4.74992 26.4375 4.74992H19.125Z"
                  fill="#F97316" />
              </svg>
              <h5 class="card-title mb-4 mt-2 coreValues-cardTitle">
                Sustainability
              </h5>
              <p class="card-text coreValues-cardText">
                We're committed to sourcing local, organic ingredients and
                implementing eco-friendly practices throughout our operations.
              </p>
            </div>
          </div>
          <div class="card" style="border: none;">
            <div class="card-body p-5">
              <svg
                width="38"
                height="36"
                viewBox="0 0 38 36"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M20.9383 1.26562C20.5656 0.492188 19.7781 0 18.9133 0C18.0484 0 17.2679 0.492188 16.8883 1.26562L12.3672 10.568L2.27029 12.0586C1.42654 12.1852 0.723415 12.7758 0.463258 13.5844C0.203102 14.393 0.414039 15.2859 1.01873 15.8836L8.34529 23.1328L6.6156 33.3773C6.47498 34.2211 6.82654 35.0789 7.52263 35.5781C8.21873 36.0773 9.13982 36.1406 9.8992 35.7398L18.9203 30.9234L27.9414 35.7398C28.7008 36.1406 29.6219 36.0844 30.3179 35.5781C31.014 35.0719 31.3656 34.2211 31.225 33.3773L29.4883 23.1328L36.8148 15.8836C37.4195 15.2859 37.6375 14.393 37.3703 13.5844C37.1031 12.7758 36.407 12.1852 35.5633 12.0586L25.4594 10.568L20.9383 1.26562Z"
                  fill="#F97316" />
              </svg>
              <h5 class="card-title mb-4 mt-2 coreValues-cardTitle">
                Excellence
              </h5>
              <p class="card-text coreValues-cardText">
                Every detail matters, from ingredient selection to plate
                presentation, ensuring an exceptional dining experience.
              </p>
            </div>
          </div>
          <div class="card" style="border: none;">
            <div class="card-body p-5">
              <svg
                width="37"
                height="32"
                viewBox="0 0 37 32"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M3.675 19.122L16.3805 30.9837C16.9078 31.4759 17.6039 31.7501 18.3281 31.7501C19.0523 31.7501 19.7484 31.4759 20.2758 30.9837L32.9813 19.122C35.1188 17.1322 36.3281 14.3408 36.3281 11.4228V11.015C36.3281 6.10013 32.7773 1.90951 27.9328 1.10091C24.7266 0.566539 21.4641 1.6142 19.1719 3.90638L18.3281 4.75013L17.4844 3.90638C15.1922 1.6142 11.9297 0.566539 8.72344 1.10091C3.87891 1.90951 0.328125 6.10013 0.328125 11.015V11.4228C0.328125 14.3408 1.5375 17.1322 3.675 19.122Z"
                  fill="#F97316" />
              </svg>
              <h5 class="card-title mb-4 mt-2 coreValues-cardTitle">
                Community
              </h5>
              <p class="card-text coreValues-cardText">
                We believe in creating lasting relationships with our guests,
                suppliers, and the local community.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section>
      <div class="team-container d-flex flex-column gap-5">
        <h2 class="text-center">Meet Our Team</h2>
        <div class="row row-cols-1 row-cols-md-4 g-5">
          <div class="col">
            <div class="card h-100 justify-content-between align-items-center">
              <img
                src="images/about/sarah-johnson---executive-chef.png"
                class="card-img-top"
                alt="..." />
              <div class="card-body">
                <h5 class="card-title coreValues-cardTitle text-center">
                  Sarah Johnson
                </h5>
                <p class="card-text coreValues-cardText text-center">
                  Executive Chef
                </p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 justify-content-between align-items-center">
              <img
                src="images/about/----david-williams-pastry-chef.png"
                class="card-img-top"
                alt="..." />
              <div class="card-body">
                <h5 class="card-title coreValues-cardTitle text-center">
                  David Williams
                </h5>
                <p class="card-text coreValues-cardText text-center">
                  Pastry Chef
                </p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 justify-content-between align-items-center">
              <img
                src="images/about/--john-martinez----restaurant-manager.png"
                class="card-img-top"
                alt="..." />
              <div class="card-body">
                <h5 class="card-title coreValues-cardTitle text-center">
                  John Martinez
                </h5>
                <p class="card-text coreValues-cardText text-center">
                  Restaurant Manager
                </p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 justify-content-between align-items-center">
              <img
                src="images/about/--michael-doe---head-sommelier.png"
                class="card-img-top"
                alt="..." />
              <div class="card-body">
                <h5 class="card-title coreValues-cardTitle text-center">
                  Michael Doe
                </h5>
                <p class="card-text coreValues-cardText text-center">
                  Head Sommelier
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
   <script src="js/main.js"></script>
    <?php include 'includes/footer.php'; ?>

  </body>
</html>
