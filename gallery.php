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
    /* ── Pale Fog Palette ──────────────────────────────────── */
    :root {
        --fog-bg: #F1EFE8;
        --fog-mid: #D3D1C7;
        --fog-deep: #B4B2A9;
        --fog-text: #2C2C2A;
        --fog-text-soft: #5F5E5A;
        --sr-blue-deep: #042C53;
        --sr-blue-base: #0C447C;
        --sr-blue-mid: #185FA5;
        --sr-blue-light: #378ADD;
        --sr-blue-pale: #E6F1FB;
    }

    .booking-section {
        position: relative;
        overflow: hidden;
        padding: 30px 0;
        background-image:
            radial-gradient(ellipse 55% 60% at 5% 95%, rgba(133, 183, 235, 0.45) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 60% 100%, rgba(55, 138, 221, 0.10) 0%, transparent 60%);
    }

    /* Lattice texture overlay (Khmer-pattern feel) */
    .lattice-overlay {
        position: absolute;
        inset: 0;
        opacity: 0.06;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg width='50' height='50' viewBox='0 0 84 84' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%230C447C' stroke-width='1'%3E%3Cpath d='M42 0 L84 42 L42 84 L0 42 Z'/%3E%3Ccircle cx='42' cy='42' r='18'/%3E%3C/g%3E%3C/svg%3E");
    }

    /* Soft circle accents */
    .fog-circle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .fog-circle.one {
        width: 320px;
        height: 320px;
        background: var(--fog-deep);
        opacity: 0.18;
        top: -120px;
        left: -100px;
    }

    .fog-circle.two {
        width: 220px;
        height: 220px;
        background: var(--sr-blue-light);
        opacity: 0.10;
        bottom: -80px;
        right: 8%;
    }

    .font-kantumruy {
        font-family: 'Kantumruy Pro', sans-serif;
    }

    .bg-breadcrumb-gellery {
        background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/banner.jpg);
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: scroll;
        min-height: 70vh;
        display: flex;
        align-items: center;
        padding: 80px 0;
    }

    .bg-breadcrumb-service p {
        font-size: 20px;
        line-height: 1.8;
        max-width: 750px;
        margin: 0 auto;
    }

    /* ── Scroll Animation ── */
    .dest-item {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .dest-item.animate-in {
        opacity: 1;
        transform: translateY(0);
    }

    /* ── Custom Pagination Styles matching your image ── */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 40px;
        margin-bottom: 20px;
    }

    .custom-pagination a,
    .custom-pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background-color: #F8F9FA;
        color: #495057;
        font-weight: 600;
        text-decoration: none;
        font-size: 16px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .custom-pagination a:hover {
        background-color: #E9ECEF;
        color: #212529;
    }

    .custom-pagination .active-page {
        background-color: #FFFFFF;
        border: 2px solid #FFC107;
        /* Orange/Gold Accent outline from image */
        color: #FFC107;
    }

    .custom-pagination .disabled-nav {
        color: #CED4DA;
        pointer-events: none;
        background-color: #F8F9FA;
    }
</style>

<body>

    <?php include 'include/topbar.php' ?>

    <div class="container-fluid bg-breadcrumb-gellery">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h1 class="text-white display-3 mb-3">Travel Gallery</h1>
            <p class="text-white mb-3 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p>
        </div>
    </div>

    <div class="container-fluid destination py-3">
        <div class="container py-3">
            <div class="mx-auto text-center mb-3" style="max-width: 900px;">
                <h5 class="section-title px-3">Photos Gallery</h5>
                <h1 class="mb-0">In Angkor Siem Reap</h1>
            </div>
            <div class="tab-class text-center">
                <div class="tab-content">
                    <!-- ALL -->
                    <div id="tab-all" class="tab-pane fade show active p-2">
                        <div class="destination-grid">
                            <?php
                            // 1. Force connection to render Khmer characters properly
                            mysqli_set_charset($myCon, "utf8mb4");

                            // 2. Setup pagination configurations
                            $limit = 12; // Display exactly 12 items per page
                            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                            if ($page < 1) $page = 1;
                            $offset = ($page - 1) * $limit;

                            // 3. Count total active records for calculating page counts
                            $total_sql = "SELECT COUNT(*) FROM MenuItems WHERE Status='Available'";
                            $total_result = $myCon->query($total_sql);
                            $total_rows = $total_result->fetch_row()[0];
                            $total_pages = ceil($total_rows / $limit);

                            // 4. Query with dynamic LIMIT and OFFSET controls
                            $sql = "SELECT * FROM MenuItems WHERE Status='Available' ORDER BY MenuItemID ASC LIMIT $limit OFFSET $offset";
                            $qr = $myCon->query($sql);

                            while ($row = $qr->fetch_assoc()) {
                                $imagePath = 'images/trash/' . $row['Image'];
                                echo '                                                                            
            <div class="dest-item">
                <div class="dest-img position-relative overflow-hidden rounded"
                    style="background-image: url(\'' . htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') . '\'); background-size: cover; background-position: center; min-height: 300px;">
                </div>
                <div class="dest-body">
                    <h4 class="dest-title mt-2">
                        ' . htmlspecialchars($row['ItemName'], ENT_QUOTES, 'UTF-8') . '
                    </h4>
                </div>
            </div>';
                            }
                            ?>
                        </div>

                        <!-- Pagination Controls matching image_3a9d2a.png -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-5 d-flex justify-content-center">
                                <ul class="pagination align-items-center gap-2 border-0">

                                    <!-- Arrow Left Button -->
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded bg-light border-0 text-secondary px-3 py-2" href="?page=<?= $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&lt;</span>
                                        </a>
                                    </li>

                                    <!-- Dynamic Page Numbers -->
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded px-3 py-2 fw-bold text-center border-0 <?= ($page == $i) ? 'bg-white border border-warning text-warning' : 'bg-light text-dark'; ?>"
                                                style="<?= ($page == $i) ? 'outline: 2px solid #FFC107; outline-offset: -2px;' : ''; ?>"
                                                href="?page=<?= $i; ?>">
                                                <?= $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Arrow Right Button -->
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded bg-light border-0 text-secondary px-3 py-2" href="?page=<?= $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&gt;</span>
                                        </a>
                                    </li>

                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="contact booking-section mb-0">
        <div class="lattice-overlay"></div>
        <div class="fog-circle one"></div>
        <div class="fog-circle two"></div>
        <div class="container position-relative" style="z-index:1;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h5 class="booking-subtitle py-2" style="color: #0C447C;">Reserve Table</h5>
                    <h1 class="booking-title">Enjoy Dining in Siem Reap 🍽️</h1>
                    <p class="booking-desc" style="color: #9CA3AF;">
                        Book your table in seconds. Experience the best Khmer and international cuisine with a cozy atmosphere.
                    </p>
                    <div class="booking-features">
                        <div>✔ Instant Confirmation</div>
                        <div>✔ Best Price Guarantee</div>
                        <div>✔ Free Cancellation</div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="booking-card">
                        <h3 class="mb-4 text-center" style="color:#0C447C;">Book Your Table</h3>
                        <form>
                            <div class="row g-3">
                                <div class="col-6"><input type="text" class="form-control custom-input" placeholder="Your Name"></div>
                                <div class="col-6"><input type="tel" class="form-control custom-input" placeholder="Phone"></div>
                                <div class="col-6"><input type="date" class="form-control custom-input"></div>
                                <div class="col-6"><input type="time" class="form-control custom-input"></div>
                                <div class="col-6">
                                    <select class="form-select custom-input">
                                        <option>Guests</option>
                                        <option>1 Person</option>
                                        <option>2 People</option>
                                        <option>4+ People</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select custom-input">
                                        <option>Table Type</option>
                                        <option>Indoor</option>
                                        <option>Outdoor</option>
                                    </select>
                                </div>
                                <div class="col-12"><textarea class="form-control custom-input" rows="3" placeholder="Special Request"></textarea></div>
                                <div class="col-12"><button class="btn btn-primary w-100">Reserve Now</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid subscribe py-5">
        <div class="container text-center py-5">
            <div class="mx-auto text-center" style="max-width: 900px;">
                <h5 class="subscribe-title px-3">Stay Connected</h5>
                <h1 class="text-white mb-4">Join Our Foodie Newsletter</h1>
                <p class="text-white mb-5">Be the first to know about our seasonal menus, exclusive chef's table events, special offers, and behind-the-scenes stories from our kitchen. Subscribe and let us bring the flavors straight to your inbox.</p>
                <div class="position-relative mx-auto">
                    <input class="form-control border-primary rounded-pill w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                    <button type="button" class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 px-4 mt-2 me-2">Subscribe Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i class="fa fa-arrow-up"></i></a>
    
    <?php include 'include/footer.php'; ?>
    <?php include 'include/copyright.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        setTimeout(() => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        entry.target.style.transitionDelay = (i * 0.08) + 's';
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });

            document.querySelectorAll('.dest-item').forEach(item => {
                observer.observe(item);
            });
        }, 300);
    </script>
</body>

</html>