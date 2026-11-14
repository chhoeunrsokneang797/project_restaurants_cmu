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
    .bg-breadcrumb-contact {
    background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/contact-booking.png);
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
    <div class="container-fluid bg-breadcrumb-contact">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h1 class="text-white display-3 mb-3">Contact Us</h1>
            <p class="mb-3 fs-5 text-white">Lorem Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                1500s,
            </p>
            <!-- <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Pages</a></li>
                        <li class="breadcrumb-item active text-white">Contact</li>
                    </ol>     -->
        </div>
    </div>
    <!-- Header End -->

    <!-- Contact Start -->
    <div class="container-fluid contact bg-light py-3">
        <div class="container py-3">
            <div class="mx-auto text-center mb-3" style="max-width: 900px;">
                <h5 class="section-title px-3">Contact Us</h5>
                <h1 class="mb-0">Contact For Any Query</h1>
            </div>
            <div class="row g-5 align-items-center">
                <div class="col-lg-4">
                    <div class="bg-white rounded p-4">
                        <div class="text-center mb-4">
                            <i class="fa fa-map-marker-alt fa-3x text-primary"></i>
                            <h4 class="text-primary">
                                <Address></Address>
                            </h4>
                            <p class="mb-0">123 ranking Street, <br> New York, USA</p>
                        </div>
                        <div class="text-center mb-4">
                            <i class="fa fa-phone-alt fa-3x text-primary mb-3"></i>
                            <h4 class="text-primary">Mobile</h4>
                            <p class="mb-0">+012 345 67890</p>
                            <p class="mb-0">+012 345 67890</p>
                        </div>

                        <div class="text-center">
                            <i class="fa fa-envelope-open fa-3x text-primary mb-3"></i>
                            <h4 class="text-primary">Email</h4>
                            <p class="mb-0">info@example.com</p>
                            <p class="mb-0">info@example.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <h3 class="mb-2">Send us a message</h3>
                    <p class="mb-4">The contact form is currently inactive. Get a functional and working contact form with Ajax & PHP in a few minutes. Just copy and paste the files, add a little code and you're done. <a href="https://htmlcodex.com/contact-form">Download Now</a>.</p>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0" id="name" placeholder="Your Name">
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control border-0" id="email" placeholder="Your Email">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0" id="subject" placeholder="Subject">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control border-0" placeholder="Leave a message here" id="message" style="height: 160px"></textarea>
                                    <label for="message">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12">
                    <div class="rounded">
                        <iframe class="rounded w-100"
                            style="height: 450px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.33750346623!2d-73.97968099999999!3d40.6974881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1694259649153!5m2!1sen!2sbd"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

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

    <!-- REGISTER MODAL -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-md-down modal-md modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title fw-bold text-white">
                        <i class="fas fa-clipboard-list me-2"></i>Register Form
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="" method="POST">
                    <div class="modal-body bg-light">
                        <div class="row g-3">
                            <div class="col-12 col-lg-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-user-circle me-2 text-primary"></i>Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- MATCH WITH DATABASE COLUMNS -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="FullName" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Password</label>
                                                <input type="password" name="Password" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="Phone" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="Email" class="form-control" required>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Address</label>
                                                <textarea name="Address" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="submit" name="btnSaveRegister" class="btn btn-primary w-100 mt-2">
                                                    Register
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- LOGIN MODAL -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-md-down modal-md modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title fw-bold text-white">
                        <i class="fas fa-clipboard-list me-2"></i>Login Form
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="" method="POST">
                    <div class="modal-body bg-light">
                        <div class="row g-3">
                            <div class="col-12 col-lg-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-user-circle me-2 text-primary"></i>Customer Login</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Changed to Email -->
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="Email" name="Email" class="form-control" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Password</label>
                                                <input type="Password" name="Password" class="form-control" required>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="submit" name="btnLogin" class="btn btn-primary w-100 mt-2">
                                                    Login
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>

    <?php include 'include/copyright.php'; ?>
    </div>
    <!-- Navbar & Hero End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i class="fa fa-arrow-up"></i></a>


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