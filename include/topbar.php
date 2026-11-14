<!-- Spinner Start -->
<div id="spinner"
    class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<!-- Spinner End -->

<?php
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['customer_csrf'])) {
    $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
}
?>
<div class="container-fluid position-relative p-0">

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
        <a href="" class="navbar-brand p-0">
            <h3 class="m-0 logo-title">
                <img src="Images/logo-ang.svg" class=" me-2 logo-img" alt="Logo">
                ANGKOR SIEM REAP
            </h3>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0">
                <a href="index.php" class="nav-item nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
                <a href="about.php" class="nav-item nav-link <?= ($current_page == 'about.php') ? 'active' : ''; ?>">About</a>
                <a href="services.php" class="nav-item nav-link <?= ($current_page == 'services.php') ? 'active' : ''; ?>">Services</a>
                <a href="menu.php" class="nav-item nav-link <?= ($current_page == 'menu.php') ? 'active' : ''; ?>">Menu</a>
                <a href="gallery.php" class="nav-item nav-link <?= ($current_page == 'gallery.php') ? 'active' : ''; ?>">Gallery</a>            
                <a href="contact.php" class="nav-item nav-link <?= ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact Us</a>                                                               
            </div>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer_account.php" class="d-flex align-items-center gap-2 ms-lg-4 text-decoration-none text-dark">
                    <?php if (!empty($_SESSION['customer_photo'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['customer_photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                    <?php else: ?>
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-user" aria-hidden="true"></i>
                        </span>
                    <?php endif; ?>
                    <span class="fw-semibold"><?= htmlspecialchars($_SESSION['customer_name'] ?? 'My account', ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <form action="customer_logout.php" method="post" class="ms-2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['customer_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-primary rounded-pill py-2 px-4">Sign out</button>
                </form>
            <?php else: ?>
                <a href="customer_login.php" class="btn btn-outline-primary rounded-pill py-2 px-4 ms-lg-4">Customer Login</a>
            <?php endif; ?>
            <a href="booking.php" class="btn btn-primary rounded-pill py-2 px-4 ms-lg-2">Book Now</a>
        </div>
    </nav>
</div>
<!-- Navbar End -->