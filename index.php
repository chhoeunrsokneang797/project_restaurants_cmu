<?php include 'include/cn.php';

$catResult = $myCon->query("SELECT CategoryID, CategoryName FROM Categories");
while ($row = $catResult->fetch_assoc()) {
    if ($row['CategoryName'] == 'Food') {
        $foodCatID = $row['CategoryID'];
    } elseif ($row['CategoryName'] == 'Dessert') {
        $dessertCatID = $row['CategoryID'];
    } elseif ($row['CategoryName'] == 'Drink') {
        $drinkCatID = $row['CategoryID'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php' ?>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Bayon&family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');
    /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
    .font-kh {
        font-family: 'Kantumruy Pro', sans-serif;
        font-weight: 400;
        font-style: normal;
    }
</style>

<body>
    <?php include 'include/topbar.php' ?>

    <!-- Navbar & Hero Start -->
    <div class="container-fluid position-relative p-0">

        <!-- Carousel Start -->
        <div class="carousel-header">
            <div id="carouselId" class="carousel slide" data-bs-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-bs-target="#carouselId" data-bs-slide-to="0" class="active"></li>
                    <li data-bs-target="#carouselId" data-bs-slide-to="1"></li>
                    <li data-bs-target="#carouselId" data-bs-slide-to="2"></li>
                </ol>
                <div class="carousel-inner" role="listbox">
                    <div class="carousel-item active">
                        <img src="Banners/front-of-resturant.png" class="img-fluid" alt="Image">
                        <div class="carousel-caption">
                            <div class="p-3" style="max-width: 900px;">
                                <h4 class="text-white text-uppercase fw-bold mb-4" style="letter-spacing: 3px;">Welcome
                                    To</h4>
                                <h2 class="display-4 text-capitalize text-white mb-4">AngKor Siemreap Restaurant</h2>
                                <p class="mb-5 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting
                                    industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                                    1500s,
                                </p>
                                <div class="d-flex align-items-center justify-content-center">
                                    <a class="btn-hover-bg btn btn-primary rounded-pill text-white py-3 px-5"
                                        href="menu.php">Discover Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="Banners/restaurant.png" class="img-fluid" alt="Image">
                        <div class="carousel-caption">
                            <div class="p-3" style="max-width: 900px;">
                                <h4 class="text-white text-uppercase fw-bold mb-4" style="letter-spacing: 3px;">Welcome
                                    To</h4>
                                <h2 class="display-4 text-capitalize text-white mb-4">AngKor Siemreap Restaurant</h2>
                                <p class="mb-5 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting
                                    industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                                    1500s,
                                </p>
                                <div class="d-flex align-items-center justify-content-center">
                                    <a class="btn-hover-bg btn btn-primary rounded-pill text-white py-3 px-5"
                                        href="menu.php">Discover Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="Banners/wating-area.png" class="img-fluid" alt="Image">
                        <div class="carousel-caption">
                            <div class="p-3" style="max-width: 900px;">
                                <h4 class="text-white text-uppercase fw-bold mb-4" style="letter-spacing: 3px;">Welcome
                                    To</h4>
                                <h2 class="display-4 text-capitalize text-white mb-4">AngKor Siemreap Restaurant</h2>
                                <p class="mb-5 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting
                                    industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                                    1500s,
                                </p>
                                <div class="d-flex align-items-center justify-content-center">
                                    <a class="btn-hover-bg btn btn-primary rounded-pill text-white py-3 px-5"
                                        href="menu.php">Discover Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselId" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon btn bg-primary" aria-hidden="false"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselId" data-bs-slide="next">
                    <span class="carousel-control-next-icon btn bg-primary" aria-hidden="false"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <!-- Carousel End -->

        <!-- About Start -->
        <section class="about-section py-3">
            <div class="about-container">

                <!-- eyebrow label -->
                <div class="about-eyebrow">
                    <span class="eyebrow-line"></span>
                    <span class="eyebrow-text">About Us</span>
                    <span class="eyebrow-line"></span>
                </div>

                <!-- layout -->
                <div class="about-layout">

                    <!-- LEFT: main image + two thumbnails below -->
                    <div class="about-images">
                        <div style="position:relative;">
                            <img src="Banners/BannerAboutUs1.png" alt="ANG KOR SIEM REAP" class="about-img-main">
                        </div>

                        <div class="about-img-row">
                            <img src="Images/about.jpg" alt="Restaurant interior" class="about-img-secondary">
                            <!-- navy tile with years stat -->
                            <div class="years-card">
                                <span class="years-number">10+</span>
                                <span class="years-label">Years</span>
                            </div>
                            <!-- <div style=" background: var(--navy); border-radius: 12px; height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                                <span style="font-family:'Playfair Display',serif;font-size:30px;font-weight:600;color:#fff;line-height:1;">10+</span>
                                <span style="font-size:11px;color:rgba(255,255,255,0.65);letter-spacing:1.5px;text-transform:uppercase;">Years</span>
                            </div> -->
                        </div>
                    </div>

                    <!-- RIGHT: overlapping white card -->
                    <div class="about-card">

                        <h2>Welcome to <em>ANGKOR<br>SIEM REAP</em></h2>
                        <div class="about-divider"></div>

                        <p>Seamless reservations, authentic Khmer cuisine, unforgettable dining moments. We connect you
                            with
                            top restaurants so every visit becomes a lasting memory.</p>

                        <!-- 3 stat boxes -->
                        <div class="about-stats">
                            <div class="stat-box filled">
                                <div class="stat-num">500+</div>
                                <div class="stat-lbl">Restaurants</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-num">20K+</div>
                                <div class="stat-lbl">Happy Guests</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-num">98%</div>
                                <div class="stat-lbl">Satisfaction</div>
                            </div>
                        </div>

                        <!-- feature checklist 2-col grid -->
                        <ul class="about-features">
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                Easy Table Reservations
                            </li>
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                Top Restaurant Partners
                            </li>
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                Instant Confirmation
                            </li>
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                Flexible Booking Options
                            </li>
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                User-Friendly Interface
                            </li>
                            <li>
                                <span class="feat-dot"><i class="fa fa-check" style="font-size:8px;"></i></span>
                                24/7 Customer Support
                            </li>
                        </ul>

                        <!-- CTA buttons -->
                        <div class="about-cta">
                            <a href="about.php" class="btn-primary-c">
                                Read More <i class="fa fa-arrow-right" style="font-size:11px;"></i>
                            </a>
                            <a href="contact.php" class="btn-ghost-c">
                                <i class="fa fa-phone" style="font-size:11px;"></i> Contact Us
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </section>
        <!-- About End -->

        <!-- Services Start -->
        <div class="container-fluid service py-0">
            <div class="container py-5">
                <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                    <h5 class="section-title px-3">Searvices</h5>
                    <h1 class="mb-0">Our Services</h1>
                </div>
                <!-- ROW 1: 4 equal icon cards -->
                <div class="cards-row-1">

                    <!-- Card: Quality Foods -->
                    <div class="svc-card">
                        <span class="card-number">01</span>
                        <div class="card-icon-wrap">
                            <i class="fa fa-utensils"></i>
                        </div>
                        <span class="svc-badge">Cuisine</span>
                        <h4 class="svc-title">Quality Foods</h4>
                        <p class="svc-desc">Farm-fresh ingredients sourced daily. Authentic Khmer recipes perfected over
                            generations, served with modern elegance.</p>
                        <a href="services.php" class="svc-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <div class="svc-card">
                        <span class="card-number">02</span>
                        <div class="card-icon-wrap">
                            <i class="fa fa-calendar-check"></i>
                        </div>
                        <span class="svc-badge">Booking</span>
                        <h4 class="svc-title">Table Booking</h4>
                        <p class="svc-desc">Reserve your perfect table in seconds — choose your date, time, and seating
                            preference with instant confirmation.</p>
                        <a href="services.php" class="svc-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <!-- Card: Hotel Reservation -->
                    <div class="svc-card">
                        <span class="card-number">03</span>
                        <div class="card-icon-wrap">
                            <i class="fa fa-hotel"></i>
                        </div>
                        <span class="svc-badge">Stay</span>
                        <h4 class="svc-title">Hotel Reservation</h4>
                        <p class="svc-desc">From boutique guesthouses to 5-star resorts — handpicked accommodations
                            bookable
                            in just a few clicks.</p>
                        <a href="services.php" class="svc-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <!-- Card: 24/7 Support -->
                    <div class="svc-card">
                        <span class="card-number">04</span>
                        <div class="card-icon-wrap">
                            <i class="fa fa-headset"></i>
                        </div>
                        <span class="svc-badge">Support</span>
                        <h4 class="svc-title">24/7 Support</h4>
                        <p class="svc-desc">Our dedicated team is always here — day or night — to assist with changes,
                            special requests, or any questions.</p>
                        <a href="services.php" class="svc-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                </div>
            </div>
        </div>
        <!-- Services End -->

        <!-- Destination Start -->
        <div class="container-fluid destination py-3">
            <div class="container py-3">
                <div class="mx-auto text-center mb-3" style="max-width: 900px;">
                    <h5 class="section-title px-3">Ang Kor</h5>
                    <h1 class="mb-0">SIEM REAB</h1>
                </div>
                <div class="tab-class text-center">

                    <ul class="nav nav-pills custom-tabs">

                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#tab-all">
                                <span>All</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#tab-Foods">
                                <span>Foods</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#tab-Dessert">
                                <span>Dessert</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#tab-Drinks">
                                <span>Drinks</span>
                            </a>
                        </li>

                    </ul>
                    <div class="tab-content">
                        <!-- All Items -->
                        <div id="tab-all" class="tab-pane fade show active p-2">
                            <!-- <div class="row g-4 "> -->
                            <div class="container-fluid packages py-5">
                                <div class="container py-2">
                                    <!-- <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                                        <h5 class="section-title px-3">Ank Kor</h5>
                                        <h1 class="mb-0">SIEM REAB MENUS</h1>
                                    </div> -->
                                    <div class="packages-carousel owl-carousel">
                                        <?php
                                        $sql = "SELECT * FROM MenuItems WHERE CategoryID = $foodCatID AND Status='Available' ORDER BY MenuItemID ASC";
                                        $qr = $myCon->query($sql);
                                        while ($row = $qr->fetch_assoc()) {
                                            $imagePath = 'images/trash/' . $row['Image'];
                                            echo '
                                            <div class="destination-item">
                                                <div class="destination-img">
                                                    <img class="img-fluid rounded w-100" src="' . $imagePath . '"
                                                        alt="' . $row['ItemName'] . '">
                                                    <div class="destination-overlay p-2">
                                                        <h5 class="btn btn-primary text-white rounded-pill border py-2 px-4 font-kh">
                                                            ' . $row['ItemName'] . '
                                                        </h5>

                                                        <div
                                                            class="d-flex align-items-center justify-content-between w-100">
                                                            <h5 class="text-white mb-0">
                                                                ANG KOR FOOD
                                                            </h5>

                                                            <!-- <span class="text-warning fw-bold">
                                                                $ ' . $row['Price'] . ' , 2); 
                                                            </span> -->
                                                        </div>

                                                    </div>

                                                    <div class="search-icon position-absolute top-3 end-3">

                                                        <!-- <button
                                                            onclick="addToCart(' . $row['ItemName'] . ', ' . $row['Price'] . ')"
                                                            class="btn btn-light text-primary rounded-circle">

                                                            <i class="fas fa-shopping-cart"></i>

                                                        </button> -->
                                                    </div>
                                                </div>
                                            </div>
                                            ';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items-Foods -->
                        <div id="tab-Foods" class="tab-pane fade">
                            <!-- <div class="row g-4 "> -->
                            <div class="container-fluid packages py-5">
                                <div class="container py-2">
                                    <!-- <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                                        <h5 class="section-title px-3">Ank Kor</h5>
                                        <h1 class="mb-0">SIEM REAB MENUS</h1>
                                    </div> -->
                                    <div class="packages-carousel owl-carousel">
                                        <?php
                                        $sql = "SELECT * FROM MenuItems WHERE CategoryID = $foodCatID AND Status='Available' ORDER BY MenuItemID ASC";
                                        $qr = $myCon->query($sql);
                                        while ($row = $qr->fetch_assoc()) {
                                            $imagePath = 'images/trash/' . $row['Image'];
                                            echo '
                                                <div class="destination-item">
                                                    <div class="destination-img">
                                                        <img class="img-fluid rounded w-100" src="' . $imagePath . '"
                                                            alt="' . $row['ItemName'] . '">
                                                    <div class="destination-overlay p-2">
                                                        <h5 class="btn btn-primary text-white rounded-pill border py-2 px-4">
                                                            ' . $row['ItemName'] . '
                                                        </h5>

                                                        <div
                                                            class="d-flex align-items-center justify-content-between w-100">
                                                            <h5 class="text-white mb-0">
                                                                ANG KOR FOOD
                                                            </h5>
                                                            <!-- <span class="text-warning fw-bold">
                                                                $ ' . $row['Price'] . ' , 2); 
                                                            </span> -->
                                                        </div>
                                                    </div>

                                                    <div class="search-icon position-absolute top-3 end-3">
                                                        <!-- <button
                                                            onclick="addToCart(' . $row['ItemName'] . ', ' . $row['Price'] . ')"
                                                            class="btn btn-light text-primary rounded-circle">

                                                            <i class="fas fa-shopping-cart"></i>

                                                        </button> -->
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items-Dessert -->
                        <div id="tab-Dessert" class="tab-pane fade">
                            <!-- <div class="row g-4 "> -->
                            <div class="container-fluid packages py-5">
                                <div class="container py-2">
                                    <!-- <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                                        <h5 class="section-title px-3">Ank Kor</h5>
                                        <h1 class="mb-0">SIEM REAB MENUS</h1>
                                    </div> -->
                                    <div class="packages-carousel owl-carousel">
                                        <?php
                                        $sql = "SELECT * FROM MenuItems WHERE CategoryID = $dessertCatID AND Status='Available' ORDER BY MenuItemID ASC";
                                        $qr = $myCon->query($sql);
                                        while ($row = $qr->fetch_assoc()) {
                                            $imagePath = 'images/trash/' . $row['Image'];
                                            echo '
                                                <div class="destination-item">
                                                    <div class="destination-img">
                                                        <img class="img-fluid rounded w-100" src="' . $imagePath . '"
                                                            alt="' . $row['ItemName'] . '">
                                                    <div class="destination-overlay p-2">
                                                        <h5 class="btn btn-primary text-white rounded-pill border py-2 px-4">
                                                            ' . $row['ItemName'] . '
                                                        </h5>

                                                        <div
                                                            class="d-flex align-items-center justify-content-between w-100">
                                                            <h5 class="text-white mb-0">
                                                                ANG KOR FOOD
                                                            </h5>
                                                            <!-- <span class="text-warning fw-bold">
                                                                $ ' . $row['Price'] . ' , 2); 
                                                            </span> -->
                                                        </div>
                                                    </div>

                                                    <div class="search-icon position-absolute top-3 end-3">
                                                        <!-- <button
                                                            onclick="addToCart(' . $row['ItemName'] . ', ' . $row['Price'] . ')"
                                                            class="btn btn-light text-primary rounded-circle">

                                                            <i class="fas fa-shopping-cart"></i>

                                                        </button> -->
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items-Drinks -->
                        <div id="tab-Drinks" class="tab-pane fade">
                            <!-- <div class="row g-4 "> -->
                            <div class="container-fluid packages py-5">
                                <div class="container py-2">
                                    <!-- <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                                        <h5 class="section-title px-3">Ank Kor</h5>
                                        <h1 class="mb-0">SIEM REAB MENUS</h1>
                                    </div> -->
                                    <div class="packages-carousel owl-carousel">
                                        <?php
                                        $sql = "SELECT * FROM MenuItems WHERE CategoryID = $drinkCatID AND Status='Available' ORDER BY MenuItemID ASC";
                                        $qr = $myCon->query($sql);
                                        while ($row = $qr->fetch_assoc()) {
                                            $imagePath = 'images/trash/' . $row['Image'];
                                            echo '
                                                <div class="destination-item">
                                                    <div class="destination-img">
                                                        <img class="img-fluid rounded w-100" src="' . $imagePath . '"
                                                            alt="' . $row['ItemName'] . '">
                                                    <div class="destination-overlay p-2">
                                                        <h5 class="btn btn-primary text-white rounded-pill border py-2 px-4">
                                                            ' . $row['ItemName'] . '
                                                        </h5>

                                                        <div
                                                            class="d-flex align-items-center justify-content-between w-100">
                                                            <h5 class="text-white mb-0">
                                                                ANG KOR FOOD
                                                            </h5>
                                                            <!-- <span class="text-warning fw-bold">
                                                                $ ' . $row['Price'] . ' , 2); 
                                                            </span> -->
                                                        </div>
                                                    </div>

                                                    <div class="search-icon position-absolute top-3 end-3">
                                                        <!-- <button
                                                            onclick="addToCart(' . $row['ItemName'] . ', ' . $row['Price'] . ')"
                                                            class="btn btn-light text-primary rounded-circle">

                                                            <i class="fas fa-shopping-cart"></i>

                                                        </button> -->
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- Destination End -->

        <!-- Packages Start -->
        <div class="container-fluid packages py-0">
            <div class="container py-0">
                <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                    <h5 class="section-title px-3">Featured Dishes</h5>
                    <h1 class="mb-0">ANG KOR SIEM REAP</h1>
                </div>
                <div class="packages-carousel owl-carousel">

                    <div class="packages-item">
                        <div class="packages-img">
                            <img src="Banners/inside-restaurant1.png"
                                class="img-fluid w-100 rounded-top" alt="Image" style="height: 250px;">
                            <div class="packages-info d-flex border border-start-0 border-end-0 position-absolute"
                                style="width: 100%; bottom: 0; left: 0; z-index: 5;">
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-map-marker-alt me-2"></i>Siem Reap</small>
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-calendar-alt me-2"></i>Monday - Sunday</small>
                            </div>
                        </div>
                        <div class="packages-content bg-light">
                            <div class="p-4 pb-0">
                                <h5 class="mb-0">Golden Pumpkin Restaurant</h5>
                                <small class="text-uppercase">Restaurant</small>
                                <div class="mb-3">
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                </div>
                                <p class="mb-4" style="font-size: small;">Pumpkin is a nutrient-rich vegetable packed
                                    with vitamins and minerals that support heart health and may help lower cholesterol.
                                    A longtime favorite in Cambodian sweet desserts, Golden Pumpkin also creatively uses
                                    it in savory dishes.</p>
                            </div>
                            <div class="row bg-primary rounded-bottom mx-0">
                                <div class="col-6 text-start px-0">
                                    <a href="#" class="btn-hover btn text-white py-2 px-4">Read More</a>
                                </div>
                                <div class="col-6 text-end px-0">
                                    <a href="booking.php" class="btn-hover btn text-white py-2 px-4">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="packages-item">
                        <div class="packages-img">
                            <img src="Banners/inside-restaurant2.png" class="img-fluid w-100 rounded-top" alt="Image"
                                style="height: 250px;">
                            <div class="packages-info d-flex border border-start-0 border-end-0 position-absolute"
                                style="width: 100%; bottom: 0; left: 0; z-index: 5;">
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-map-marker-alt me-2"></i>Siem Reap</small>
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-calendar-alt me-2"></i>Monday - Friday</small>
                            </div>

                        </div>
                        <div class="packages-content bg-light">
                            <div class="p-4 pb-0">
                                <h5 class="mb-0">JOMNO Modern Khmer Cuisine</h5>
                                <small class="text-uppercase">Restaurant</small>
                                <div class="mb-3">
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                </div>
                                <p class="mb-4" style="font-size: small;">Welcome to Jomno, a unique Siem Reap
                                    restaurant that reimagines the spirit of Cambodia through an unforgettable culinary
                                    journey, honoring tradition while celebrating the present.</p>
                            </div>
                            <div class="row bg-primary rounded-bottom mx-0">
                                <div class="col-6 text-start px-0">
                                    <a href="#" class="btn-hover btn text-white py-2 px-4">Read More</a>
                                </div>
                                <div class="col-6 text-end px-0">
                                    <a href="booking.php" class="btn-hover btn text-white py-2 px-4">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="packages-item">
                        <div class="packages-img">
                            <img src="Banners/inside-restaurant3.png" class="img-fluid w-100 rounded-top" alt="Image"
                                style="height: 250px;">
                            <div class="packages-info d-flex border border-start-0 border-end-0 position-absolute"
                                style="width: 100%; bottom: 0; left: 0; z-index: 5;">
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-map-marker-alt me-2"></i>Siem Reap</small>
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-calendar-alt me-2"></i>Monday - Sunday</small>
                            </div>

                        </div>
                        <div class="packages-content bg-light">
                            <div class="p-4 pb-0">
                                <h5 class="mb-0">Sokkhak River Lounge</h5>
                                <small class="text-uppercase">Restaurant</small>
                                <div class="mb-3">
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                </div>
                                <p class="mb-4" style="font-size: small;">This stunning, three-story building includes a
                                    café on the ground floor, an impressive wine bar on the middle level and the crown
                                    jewel
                                    – a lush, stylish and spacious rooftop cocktail bar with sweeping views of Siem Reap
                                    River.</p>
                            </div>
                            <div class="row bg-primary rounded-bottom mx-0">
                                <div class="col-6 text-start px-0">
                                    <a href="#" class="btn-hover btn text-white py-2 px-4">Read More</a>
                                </div>
                                <div class="col-6 text-end px-0">
                                    <a href="booking.php" class="btn-hover btn text-white py-2 px-4">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="packages-item">
                        <div class="packages-img">
                            <img src="Banners/inside-restaurant4.png" class="img-fluid w-100 rounded-top" alt="Image"
                                style="height: 250px;">
                            <div class="packages-info d-flex border border-start-0 border-end-0 position-absolute"
                                style="width: 100%; bottom: 0; left: 0; z-index: 5;">
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-map-marker-alt me-2"></i>Siem Reap</small>
                                <small class="flex-fill text-center border-end py-2"><i
                                        class="fa fa-calendar-alt me-2"></i>Monday - Sunday</small>
                            </div>

                        </div>
                        <div class="packages-content bg-light">
                            <div class="p-4 pb-0">
                                <h5 class="mb-0">Thayland Trip</h5>
                                <small class="text-uppercase">Restaurant</small>
                                <div class="mb-3">
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                </div>
                                <p class="mb-4" style="font-size: small;">The restaurant features a main dining area bar
                                    and lounge downstairs with private dining rooms available Upstairs is suitable for
                                    private functions gala dinners banquets and culinary cooking workshops.</p>
                            </div>
                            <div class="row bg-primary rounded-bottom mx-0">
                                <div class="col-6 text-start px-0">
                                    <a href="#" class="btn-hover btn text-white py-2 px-4">Read More</a>
                                </div>
                                <div class="col-6 text-end px-0">
                                    <a href="booking.php" class="btn-hover btn text-white py-2 px-4">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Packages End -->

        <!-- Travel Guide Start -->
        <div class="container-fluid bg-light guide py-3">
            <div class="container py-5">
                <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                    <h5 class="section-title px-3">Team behind the flavor</h5>
                    <h1 class="mb-0">Our Chefs & Partners</h1>
                </div>
                <div class="row g-4 row-cols-1 row-cols-md-3 row-cols-lg-5">

                    <!-- Card 1 -->
                    <div class="col">
                        <div class="guide-item">
                            <div class="guide-img">
                                <div class="guide-img-efects">
                                    <img src="Images/NameClient/Rith.png" class="img-fluid w-100 rounded-top"
                                        alt="Chef Marcus" style="height: 220px; object-fit: cover;">
                                </div>
                                <div class="guide-icon rounded-pill p-1">
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-twitter" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-instagram" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="guide-title text-center rounded-bottom p-4">
                                <div class="guide-title-inner">
                                    <span class="role-badge">Executive Chef</span>
                                    <h4 class="mt-2" style="font-size: 15px;">Chef San Sovannarith</h4>
                                    <div class="divider-line mx-auto my-2"></div>
                                    <p class="mb-0" style="font-size: 13px;">20 years of culinary mastery</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col">
                        <div class="guide-item">
                            <div class="guide-img">
                                <div class="guide-img-efects">
                                    <img src="Images/NameClient/Lay.jpg" class="img-fluid w-100 rounded-top"
                                        alt="Chef Marcus" style="height: 220px; object-fit: cover;">
                                </div>
                                <div class="guide-icon rounded-pill p-1">
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-twitter" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-instagram" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="guide-title text-center rounded-bottom p-4">
                                <div class="guide-title-inner">
                                    <span class="role-badge">Pastry Chef</span>
                                    <h4 class="mt-2" style="font-size: 15px;">Tang KimLay</h4>
                                    <div class="divider-line mx-auto my-2"></div>
                                    <p class="mb-0" style="font-size: 13px;">Cambodian-inspired desserts</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col">
                        <div class="guide-item">
                            <div class="guide-img">
                                <div class="guide-img-efects">
                                    <img src="Images/NameClient/Mey.JPG" class="img-fluid w-100 rounded-top"
                                        alt="Chef Marcus" style="height: 220px; object-fit: cover;">
                                </div>
                                <div class="guide-icon rounded-pill p-1">
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-twitter" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-instagram" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="guide-title text-center rounded-bottom p-4">
                                <div class="guide-title-inner">
                                    <span class="role-badge">Executive Chef</span>
                                    <h4 class="mt-2" style="font-size: 15px;">Chef Yong LeapMey</h4>
                                    <div class="divider-line mx-auto my-2"></div>
                                    <p class="mb-0" style="font-size: 13px;">20 years of culinary mastery</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="col">
                        <div class="guide-item">
                            <div class="guide-img">
                                <div class="guide-img-efects">
                                    <img src="Images/NameClient/Sokneang.jpg" class="img-fluid w-100 rounded-top"
                                        alt="Chef Marcus" style="height: 220px; object-fit: cover;">
                                </div>
                                <div class="guide-icon rounded-pill p-1">
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-twitter" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-instagram" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="guide-title text-center rounded-bottom p-4">
                                <div class="guide-title-inner">
                                    <span class="role-badge">Executive Chef</span>
                                    <h4 class="mt-2" style="font-size: 15px;">Chhoeunr Sokneang</h4>
                                    <div class="divider-line mx-auto my-2"></div>
                                    <p class="mb-0" style="font-size: 13px;">20 years of culinary mastery</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div class="col">
                        <div class="guide-item">
                            <div class="guide-img">
                                <div class="guide-img-efects">
                                    <img src="Images/NameClient/HongMeng.jpg" class="img-fluid w-100 rounded-top"
                                        alt="Chef Marcus" style="height: 220px; object-fit: cover;">
                                </div>
                                <div class="guide-icon rounded-pill p-1">
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-twitter" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-instagram" style="font-size: 10px;"></i>
                                    </a>
                                    <a class="btn btn-square btn-primary rounded-circle mx-0"
                                        style="height: 24px; width: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        href="">
                                        <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="guide-title text-center rounded-bottom p-4">
                                <div class="guide-title-inner">
                                    <span class="role-badge">Executive Chef</span>
                                    <h4 class="mt-2" style="font-size: 15px;">Chef Chhun HongMeng</h4>
                                    <div class="divider-line mx-auto my-2"></div>
                                    <p class="mb-0" style="font-size: 13px;">20 years of culinary mastery</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Travel Guide End -->

        <!-- Subscribe Start -->
        <div class="container-fluid subscribe bg-light py-5">
            <div class="container text-center py-5">
                <div class="mx-auto text-center" style="max-width: 900px;">
                    <h5 class="subscribe-title px-3">Stay Connected</h5>
                    <h1 class="text-white mb-4">Join Our Foodie Newsletter</h1>
                    <p class="text-white mb-5">Be the first to know about our seasonal menus, exclusive chef's table
                        events,
                        special offers, and behind-the-scenes stories from our kitchen.
                        Subscribe and let us bring the flavors straight to your inbox.
                    </p>
                    <div class="position-relative mx-auto">
                        <input class="form-control border-primary rounded-pill w-100 py-3 ps-4 pe-5" type="text"
                            placeholder="Your email">
                        <button type="button"
                            class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 px-4 mt-2 me-2">Subscribe
                            Now</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Subscribe End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i class="fa fa-arrow-up"></i></a>

        <!-- Footer Start -->
        <?php include 'include/footer.php'; ?>

        <!-- Copyright Start -->
        <?php include 'include/copyright.php'; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>


    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- JavaScript -->
    <script>
        // Load the cart from localStorage, or initialize an empty array
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem('cart')) || [];
        } catch (e) {
            cart = [];
        }

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        function addToCart(name, price) {
            const existing = cart.find(i => i.name === name);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({
                    name,
                    price,
                    qty: 1
                });
            }
            saveCart();
            showCartToast(name);
            updateCartUI();
        }

        function updateCartUI() {
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const cartTax = document.getElementById('cartTax');
            const grandTotal = document.getElementById('grandTotal');

            let subtotal = 0;

            if (cartItems) {
                cartItems.innerHTML = '';
                cart.forEach((item, i) => {
                    subtotal += item.price * item.qty;
                    cartItems.innerHTML += `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">
                            $${item.price.toFixed(2)} ×
                            <button class="btn btn-sm btn-outline-secondary px-1 py-0" onclick="changeQty(${i}, -1)">−</button>
                            ${item.qty}
                            <button class="btn btn-sm btn-outline-secondary px-1 py-0" onclick="changeQty(${i}, 1)">+</button>
                        </small>
                    </div>
                    <div>
                        <strong>$${(item.price * item.qty).toFixed(2)}</strong>
                        <button class="btn btn-sm btn-danger ms-2" onclick="removeItem(${i})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;
                });
            } else {
                subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
            }

            const tax = subtotal * 0.10;
            if (cartTotal) cartTotal.textContent = subtotal.toFixed(2);
            if (cartTax) cartTax.textContent = tax.toFixed(2);
            if (grandTotal) grandTotal.textContent = (subtotal + tax).toFixed(2);

            const totalQty = cart.reduce((a, i) => a + i.qty, 0);
            document.querySelectorAll('#cartCount').forEach(el => {
                el.textContent = totalQty;
            });
        }

        function changeQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            saveCart();
            updateCartUI();
            updateOrderItemsSummary();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            saveCart();
            updateCartUI();
            updateOrderItemsSummary();
        }

        // Fallback toggle function if it's missing elsewhere in your scripts
        function toggleBookingTable() {
            const orderTypeEl = document.getElementById('orderType');
            const bookingSection = document.getElementById('bookingSection'); // Adjust ID if needed
            if (orderTypeEl && bookingSection) {
                if (orderTypeEl.value === 'Dine In') {
                    bookingSection.classList.remove('d-none');
                } else {
                    bookingSection.classList.add('d-none');
                }
            }
        }

        function placeOrder() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }

            const customerName = document.getElementById('customerName').value.trim();
            const phone = document.getElementById('phoneNumber').value.trim();
            const email = document.getElementById('email').value.trim();
            const address = document.getElementById('address').value.trim();
            const orderType = document.getElementById('orderType').value;
            const customerType = document.getElementById('customerType').value;

            if (!customerName || !phone) {
                alert('Please fill in Customer Name and Phone Number.');
                return;
            }

            const payload = {
                customerName,
                phone,
                email,
                address,
                customerType,
                orderType,
                items: cart
            };

            fetch('place_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Order #' + data.orderID + ' placed successfully!');
                        cart = [];
                        saveCart();
                        updateCartUI();
                        bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => {
                    alert('❌ Network error: ' + err);
                });
        }

        function updateCustomerSummary() {
            // Use optional chaining or fallbacks to prevent errors if elements don't exist
            const name = document.getElementById('customerName')?.value || '';
            const phone = document.getElementById('phoneNumber')?.value || '';
            const email = document.getElementById('email')?.value || '';
            const orderType = document.getElementById('orderType')?.value || '';
            const customerType = document.getElementById('customerType')?.value || '';

            const tableNumber = document.getElementById('tableNumber')?.value || '';
            const reservationDate = document.getElementById('reservationDate')?.value || '';
            const reservationTime = document.getElementById('reservationTime')?.value || '';
            const tableRequests = document.getElementById('tableRequests')?.value || '';

            // Safely update DOM text
            if (document.getElementById('summaryName')) document.getElementById('summaryName').textContent = name || '-';
            if (document.getElementById('summaryPhone')) document.getElementById('summaryPhone').textContent = phone || '-';
            if (document.getElementById('summaryEmail')) document.getElementById('summaryEmail').textContent = email || '-';
            if (document.getElementById('summaryOrderType')) document.getElementById('summaryOrderType').textContent = orderType || '-';
            if (document.getElementById('summaryCustomerType')) document.getElementById('summaryCustomerType').textContent = customerType || '-';

            if (document.getElementById('summaryTable')) document.getElementById('summaryTable').textContent = tableNumber || '-';
            if (document.getElementById('summaryDate')) document.getElementById('summaryDate').textContent = reservationDate || '-';
            if (document.getElementById('summaryTime')) document.getElementById('summaryTime').textContent = reservationTime || '-';
            if (document.getElementById('summaryRequests')) document.getElementById('summaryRequests').textContent = tableRequests || '-';
        }

        function updateOrderItemsSummary() {
            const summaryOrderItems = document.getElementById('summaryOrderItems');
            if (!summaryOrderItems) return;

            if (cart.length === 0) {
                summaryOrderItems.innerHTML = '<p class="text-muted mb-0">No items in cart</p>';
                return;
            }

            let html = '';
            cart.forEach((item, i) => {
                const itemTotal = item.price * item.qty;
                html += `
            <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                <div class="flex-grow-1">
                    <span>${item.name}</span>
                    <small class="text-muted">($${item.price.toFixed(2)} × ${item.qty})</small>
                </div>
                <strong>$${itemTotal.toFixed(2)}</strong>
            </div>`;
            });

            summaryOrderItems.innerHTML = html;
        }

        // Single DOMContentLoaded handler
        document.addEventListener('DOMContentLoaded', function() {
            // Attach listener safely if elements exist
            if (document.getElementById('customerName')) document.getElementById('customerName').addEventListener('input', updateCustomerSummary);
            if (document.getElementById('phoneNumber')) document.getElementById('phoneNumber').addEventListener('input', updateCustomerSummary);
            if (document.getElementById('email')) document.getElementById('email').addEventListener('input', updateCustomerSummary);
            if (document.getElementById('customerType')) document.getElementById('customerType').addEventListener('change', updateCustomerSummary);

            if (document.getElementById('orderType')) {
                document.getElementById('orderType').addEventListener('change', function() {
                    toggleBookingTable();
                    updateCustomerSummary();
                });
            }

            if (document.getElementById('tableNumber')) document.getElementById('tableNumber').addEventListener('change', updateCustomerSummary);
            if (document.getElementById('reservationDate')) document.getElementById('reservationDate').addEventListener('input', updateCustomerSummary);
            if (document.getElementById('reservationTime')) document.getElementById('reservationTime').addEventListener('input', updateCustomerSummary);
            if (document.getElementById('tableRequests')) document.getElementById('tableRequests').addEventListener('input', updateCustomerSummary);

            // Run setup functions
            toggleBookingTable();
            updateCustomerSummary();
            updateOrderItemsSummary();
            updateCartUI();
        });

        function showPaymentSection() {
            document.getElementById("paymentSection").classList.remove("d-none");
            document.getElementById("paymentSection").scrollIntoView({
                behavior: "smooth"
            });
            document.getElementById("proceedBtn").classList.add("d-none");
            document.getElementById("confirmBtn").classList.remove("d-none");

            let total = document.getElementById("grandTotal").innerText;
            document.getElementById("payTotal").value = total;
        }

        if (document.getElementById('cartModal')) {
            document.getElementById('cartModal').addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                const id = btn?.getAttribute('data-orderid');
                const total = parseFloat(btn?.getAttribute('data-total'));
                if (!isNaN(total)) {
                    document.getElementById('payOrderID').value = id;
                    document.getElementById('payTotal').value = total;
                    document.getElementById('summaryTotal').textContent = '$' + total.toFixed(2);
                    document.getElementById('qrAmountDisplay').textContent = '$' + total.toFixed(2);
                    document.getElementById('bankAmtDisplay').textContent = '$' + total.toFixed(2);
                }
            });
        }

        function switchPayTab(method, el) {
            document.querySelectorAll('.pay-panel').forEach(p => p.classList.add('d-none'));
            document.querySelectorAll('.pay-tab-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
            document.getElementById('panel-' + method).classList.remove('d-none');
            if (el) el.classList.add('active');
            document.getElementById('payMethodInput').value = method;
        }

        function calcChange() {
            const total = parseFloat(document.getElementById('payTotal').value) || 0;
            const recv = parseFloat(document.getElementById('cashReceived').value) || 0;
            const ch = recv - total;
            const el = document.getElementById('changeAmt');
            el.textContent = '$' + Math.max(ch, 0).toFixed(2);
            document.getElementById('payAmountInput').value = recv;
        }

        function copyText(id, btn) {
            navigator.clipboard.writeText(document.getElementById(id).value)
                .then(() => {
                    btn.textContent = 'Copied!';
                    setTimeout(() => {
                        btn.textContent = 'Copy';
                    }, 1500);
                });
        }

        function fmtCard() {
            let v = document.getElementById('cardNum').value.replace(/\D/g, '').substring(0, 16);
            document.getElementById('cardNum').value = v.replace(/(.{4})/g, '$1 ').trim();
        }
    </script>
</body>

</html>