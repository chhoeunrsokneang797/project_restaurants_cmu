<?php include 'include/cn.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>
</head>
<style>
    /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
        
    .bg-breadcrumb-service {
    background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/service.png);
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: scroll;
    /* padding: 150px 0 100px 0; */
    min-height: 70vh;
    display: flex;
    align-items: center;
    padding: 80px 0;
}
</style>
<body>

    <?php include 'include/topbar.php' ?>

    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb-service ">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h1 class="text-white display-3 mb-3">Our Service</h1>
            <p class="text-white mb-3 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                1500s,
            </p>
        </div>
    </div>
    <!-- Header End -->

    <!-- Service Start -->
    <section class="services-section py-3">
        <div class="services-container">

            <!-- Section header -->
            <div class="services-header">
                <div class="services-eyebrow">
                    <span class="eyebrow-line"></span>
                    <span class="eyebrow-text">Our Services</span>
                    <span class="eyebrow-line"></span>
                </div>
                <h2 class="services-title">What We <em>Offer You</em></h2>
                <p class="services-subtitle">Premium experiences crafted for every occasion — from intimate dinners to
                    grand celebrations at ANG KOR SIEM REAP.</p>
            </div>

            <!-- Main grid: dark panel + cards -->
            <div class="services-grid">

                <!-- LEFT: Dark panel -->
                <div class="services-panel">
                    <div class="panel-top">
                        <span class="panel-tag">Why Choose Us</span>
                        <h3 class="panel-title">Crafting <em>Unforgettable</em> Moments</h3>
                        <p class="panel-desc">We bring passion, flavor, and elegance to your table — combining authentic
                            Khmer hospitality with modern convenience.</p>
                    </div>

                    <!-- Stats -->
                    <div class="panel-stats">
                        <div class="panel-stat">
                            <span class="panel-stat-num">500+</span>
                            <div class="panel-stat-label">
                                <strong>Restaurant Partners</strong>
                                Top venues across Siem Reap
                            </div>
                        </div>
                        <div class="panel-stat">
                            <span class="panel-stat-num">20K+</span>
                            <div class="panel-stat-label">
                                <strong>Happy Guests</strong>
                                Served with excellence
                            </div>
                        </div>
                        <div class="panel-stat">
                            <span class="panel-stat-num">98%</span>
                            <div class="panel-stat-label">
                                <strong>Satisfaction Rate</strong>
                                Across all bookings
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="panel-cta">
                        <a href="services.php" class="btn-gold">
                            View All Services <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- RIGHT: 2×2 Feature cards -->
                <div class="services-cards">

                    <!-- Card 1 — Quality Foods -->
                    <div class="service-card">
                        <span class="card-number">01</span>
                        <div class="card-icon">
                            <i class="fa fa-utensils"></i>
                        </div>
                        <h4 class="card-title">Quality Foods</h4>
                        <p class="card-desc">Farm-fresh ingredients sourced daily. Authentic Khmer recipes perfected
                            over generations, served with modern elegance.</p>
                        <a href="#" class="card-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <!-- Card 2 — Hotel Reservation (filled/navy) -->
                    <div class="service-card filled">
                        <span class="card-number">02</span>
                        <div class="card-icon">
                            <i class="fa fa-hotel"></i>
                        </div>
                        <h4 class="card-title">Hotel Reservation</h4>
                        <p class="card-desc">Handpicked accommodations from boutique guesthouses to 5-star resorts — all
                            bookable in seconds from our platform.</p>
                        <a href="#" class="card-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <!-- Card 3 — Event Management -->
                    <div class="service-card">
                        <span class="card-number">03</span>
                        <div class="card-icon">
                            <i class="fa fa-calendar-alt"></i>
                        </div>
                        <h4 class="card-title">Event Management</h4>
                        <p class="card-desc">Weddings, gala dinners, corporate events, and private celebrations —
                            expertly planned and flawlessly executed.</p>
                        <a href="#" class="card-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <!-- Card 4 — Online Orders -->
                    <div class="service-card">
                        <span class="card-number">04</span>
                        <div class="card-icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <h4 class="card-title">Online Orders</h4>
                        <p class="card-desc">Order your favourite dishes from top restaurants and have them delivered
                            fresh to your door — fast, reliable, delicious.</p>
                        <a href="#" class="card-link">Learn more <i class="fa fa-arrow-right"></i></a>
                    </div>

                </div>
                <!-- end cards -->

            </div>
            <!-- end main grid -->

            <!-- Bottom CTA strip -->
            <div class="services-cta-strip">
                <div class="strip-text">
                    <p>Ready to experience the best?</p>
                    <h3>Book your table or order online today</h3>
                </div>
                <div class="strip-btns">
                    <a href="booking.php" class="btn-strip-primary">
                        <i class="fa fa-calendar-check" style="font-size:12px;"></i> Book a Table
                    </a>
                    <a href="contact.php" class="btn-strip-ghost">
                        <i class="fa fa-phone" style="font-size:12px;"></i> Contact Us
                    </a>
                </div>
            </div>

        </div>
    </section>
    <!-- Service End -->

    <!-- Testimonial Start -->
    <div class="container-fluid testimonial py-3">
        <div class="container">
            <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                <h5 class="section-title px-3">Customer Reviews</h5>
                <h1 class="mb-0">What Our Guests Say!!!</h1>
            </div>
            <div class="testimonial-carousel owl-carousel">
                <div class="testimonial-item text-center rounded pb-4">
                    <div class="testimonial-comment bg-light rounded p-4">
                        <p class="text-center mb-5">Booking a table was fast and simple. The food was amazing and the service was excellent. I will definitely come back again!
                        </p>
                    </div>
                    <div class="testimonial-img p-1">
                        <img src="Images/NameClient/Rith.png" class="img-fluid rounded-circle" alt="Image">
                    </div>
                    <div style="margin-top: -35px;">
                        <h5 class="mb-0">San Sovannarith</h5>
                        <p class="mb-0">Phnom Penh, Cambodian</p>
                        <div class="d-flex justify-content-center">
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item text-center rounded pb-4">
                    <div class="testimonial-comment bg-light rounded p-4">
                        <p class="text-center mb-5">I love how easy it is to reserve a table online. Everything was smooth, and the restaurant atmosphere was perfect.
                        </p>
                    </div>
                    <div class="testimonial-img p-1">
                        <img src="Images/NameClient/HongMeng.jpg" class="img-fluid rounded-circle" alt="Image">
                    </div>
                    <div style="margin-top: -35px;">
                        <h5 class="mb-0">Chhun HongMeng</h5>
                        <p class="mb-0">Phnom Penh, Cambodian</p>
                        <div class="d-flex justify-content-center">
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item text-center rounded pb-4">
                    <div class="testimonial-comment bg-light rounded p-4">
                        <p class="text-center mb-5">Booking a table was fast and simple. The food was amazing and the service was excellent. I will definitely come back again!
                        </p>
                    </div>
                    <div class="testimonial-img p-1">
                        <img src="Images/NameClient/Sokneang.jpg" class="img-fluid rounded-circle" alt="Image">
                    </div>
                    <div style="margin-top: -35px;">
                        <h5 class="mb-0">Chhoeunr SokNeang</h5>
                        <p class="mb-0">Phnom Penh, Cambodian</p>
                        <div class="d-flex justify-content-center">
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="testimonial-item text-center rounded pb-4">
                    <div class="testimonial-comment bg-light rounded p-4">
                        <p class="text-center mb-5">Booking a table was fast and simple. The food was amazing and the service was excellent. I will definitely come back again!
                        </p>
                    </div>
                    <div class="testimonial-img p-1">
                        <img src="Images/NameClient/Lay.jpg" class="img-fluid rounded-circle" alt="Image">
                    </div>
                    <div style="margin-top: -35px;">
                        <h5 class="mb-0">Tang KimLay</h5>
                        <p class="mb-0">Phnom Penh, Cambodian</p>
                        <div class="d-flex justify-content-center">
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="testimonial-item text-center rounded pb-4">
                    <div class="testimonial-comment bg-light rounded p-4">
                        <p class="text-center mb-5">Booking a table was fast and simple. The food was amazing and the service was excellent. I will definitely come back again!
                        </p>
                    </div>
                    <div class="testimonial-img p-1">
                        <img src="Images/NameClient/Mey.jpg" class="img-fluid rounded-circle" alt="Image">
                    </div>
                    <div style="margin-top: -35px;">
                        <h5 class="mb-0">Yong LeapMey</h5>
                        <p class="mb-0">Phnom Penh, Cambodain</p>
                        <div class="d-flex justify-content-center">
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                            <i class="fas fa-star text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->

    <!-- Subscribe Start -->
    <div class="container-fluid subscribe py-5">
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

    <?php include 'include/footer.php';?>

    <?php include 'include/copyright.php'; ?>

    </div>
    <!-- Navbar & Hero End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i
            class="fa fa-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>


    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>