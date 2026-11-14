<?php include 'include/cn.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>
    <link rel="stylesheet" href="about.css">

    <style>
        .bg-breadcrumb {
            background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/about-restaurant.png);
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
        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
    </style>
</head>

<body>

    <?php include 'include/topbar.php' ?>

    <!-- Navbar & Hero Start -->
    <?php require 'about_navbar.php' ?>
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