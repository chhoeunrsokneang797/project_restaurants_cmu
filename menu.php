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

if (isset($_POST['btnBooking'])) {
    $name = $_POST['customer_name'];
    $phone = $_POST['customer_phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $persons = $_POST['persons'];
    $table_type = $_POST['tableType'];
    $message = $_POST['message'];

    $sql = "INSERT INTO Orders (customer_name, customer_phone, date, time, persons, tableType, message) 
            VALUES ('$name','$phone','$date','$time','$persons','$table_type','$message')";

    $result = mysqli_query($myCon, $sql);
    if ($result) {
        echo "
            <script>
                alert('Booking Successfully');
                window.location='menu.php';
            </script>";
    } else {
        echo mysqli_error($myCon);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>

</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Bayon&family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
    .product-name {
        font-family: "Kantumruy Pro", sans-serif;
    }

    .bg-breadcrumb-packages {
        background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/banner-package.jpg);
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: scroll;
        min-height: 70vh;
        display: flex;
        align-items: center;
        padding: 80px 0;
    }

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
        /* font-family: 'Inter', sans-serif; */
        position: relative;
        overflow: hidden;
        padding: 30px 0;
        /* background-color: var(--fog-bg); */
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

    .btn-outline-secondary {
        border: 1px solid #0C447C;
        color: #0C447C;
    }

    .btn-outline-secondary:hover {
        color: #FFF;
        background: #0C447C;
    }

    .dest-body .dest-title {
        font-family: 'Battambang', system-ui;
        font-size: 18px;
        font-weight: 600;
    }

    #toastContainer {
        position: fixed;
        top: 90px;
        right: 20px;
        z-index: 9999;
    }

    .cart-toast {
        background: linear-gradient(135deg, var(--sr-blue-base), var(--sr-blue-mid));
        color: #fff;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 10px;
        box-shadow: 0 8px 24px rgba(12, 68, 124, 0.35);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 250px;
        opacity: 0;
        transform: translateX(40px);
        animation: toastIn 0.35s ease forwards, toastOut 0.35s ease forwards 2.3s;
    }

    .cart-toast i {
        font-size: 18px;
        background: rgba(255, 255, 255, 0.18);
        padding: 8px;
        border-radius: 50%;
    }

    @keyframes toastIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes toastOut {
        to {
            opacity: 0;
            transform: translateX(40px);
        }
    }


    /* Styling to match provided Pagination image design */
    .pagination-custom .page-link {
        background-color: #f4f6f8;
        color: #495057;
        border: 2px solid transparent;
        padding: 10px 16px;
        font-size: 1rem;
        transition: all 0.2s ease-in-out;
    }

    .pagination-custom .page-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }

    .pagination-custom .page-item.active .page-link {
        border-color: #ffc107 !important;
        /* Gold border outline for selected page */
        background-color: #ffffff !important;
        color: #ffc107 !important;
    }

    .pagination-custom .page-item.disabled .page-link {
        pointer-events: none;
        background-color: #f8f9fa;
    }
</style>

<body>

    <?php include 'include/topbar.php' ?>

    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb-packages">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h1 class="text-white display-3 mb-3">AngKor Siemreap Menu</h1>
            <p class="text-white mb-3 fs-5">Lorem Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard dummy text ever since the
                1500s,
            </p>
        </div>
    </div>
    <!-- Header End -->

    <!-- Destination Start -->
    <?php
    // 1. SET CONFIGURATION
    // We will output all items into the HTML, but hide everything beyond the first 12,
    // allowing JavaScript to instantly show/hide them.
    $itemsPerPage = 12;

    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-1';

    // Helper function to render the grid items to eliminate massive code repetition
    function renderMenuGrid($queryResult)
    {
        $index = 0;
        $html = '';
        while ($row = $queryResult->fetch_assoc()) {
            $imagePath = 'images/trash/' . $row['Image'];
            // Items beyond the first 12 get the 'd-none' class to start hidden
            $hiddenClass = ($index >= 12) ? 'd-none extra-item' : '';

            $html .= '<div class="dest-item ' . $hiddenClass . '" data-cart-image="' . htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') . '">';
            $html .= '  <div class="dest-img position-relative overflow-hidden rounded" style="background-image: url(\'' . $imagePath . '\'); background-size: cover; background-position: center; min-height: 300px;">';
            $html .= '      <div class="dest-price">$ ' . $row['Price'] . '</div>';
            $html .= '  </div>';
            $html .= '  <div class="dest-body">';
            $html .= '      <h4 class="dest-title text-center">' . htmlspecialchars($row['ItemName'], ENT_QUOTES) . '</h4>';
            $html .= '      <p class="dest-meta font-kantumruy">' . htmlspecialchars($row['Description'], ENT_QUOTES) . '</p>';
            $html .= '      <div class="text-center mt-3">';
            $html .= '          <button class="btn btn-primary w-100 add-to-cart" onclick="addToCart(\'' . addslashes($row['ItemName']) . '\', ' . $row['Price'] . ', this.closest(\'.dest-item\').dataset.cartImage); return false;">';
            $html .= '              <i class="fas fa-shopping-cart me-2"></i> Add to Cart';
            $html .= '          </button>';
            $html .= '      </div>';
            $html .= '  </div>';
            $html .= '</div>';
            $index++;
        }
        return ['html' => $html, 'count' => $index];
    }
    ?>

    <div class="container-fluid destination py-3">
        <div class="container py-3">
            <div class="mx-auto text-center mb-3" style="max-width: 900px;">
                <h5 class="section-title px-3">Our Menus</h5>
                <h1 class="mb-0">IN ANGKOR SIEM REAP</h1>
            </div>
            <div class="tab-class text-center">
                <ul class="nav nav-pills custom-tabs justify-content-center mb-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activeTab == 'tab-1') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab-1"><span>All</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activeTab == 'tab-2') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab-2"><span>Foods</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activeTab == 'tab-3') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab-3"><span>Dessert</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($activeTab == 'tab-4') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#tab-4"><span>Drinks</span></a>
                    </li>
                </ul>

                <div class="tab-content text-start">

                    <div id="tab-1" class="tab-pane fade <?php echo ($activeTab == 'tab-1') ? 'show active' : ''; ?> p-2">
                        <div class="destination-grid">
                            <?php
                            $sql = "SELECT * FROM MenuItems WHERE Status='Available' ORDER BY MenuItemID ASC";
                            $res = renderMenuGrid($myCon->query($sql));
                            echo $res['html'];
                            ?>
                        </div>
                        <?php if ($res['count'] > $itemsPerPage): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-warning px-4 toggle-more-btn" onclick="toggleMenu(this)">Load More</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="tab-2" class="tab-pane fade <?php echo ($activeTab == 'tab-2') ? 'show active' : ''; ?> p-2">
                        <div class="destination-grid">
                            <?php
                            $sql = "SELECT * FROM MenuItems WHERE CategoryID = $foodCatID AND Status='Available' ORDER BY MenuItemID ASC";
                            $res = renderMenuGrid($myCon->query($sql));
                            echo $res['html'];
                            ?>
                        </div>
                        <?php if ($res['count'] > $itemsPerPage): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-warning px-4 toggle-more-btn" onclick="toggleMenu(this)">Load More</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="tab-3" class="tab-pane fade <?php echo ($activeTab == 'tab-3') ? 'show active' : ''; ?> p-2">
                        <div class="destination-grid">
                            <?php
                            $sql = "SELECT * FROM MenuItems WHERE CategoryID = $dessertCatID AND Status='Available' ORDER BY MenuItemID ASC";
                            $res = renderMenuGrid($myCon->query($sql));
                            echo $res['html'];
                            ?>
                        </div>
                        <?php if ($res['count'] > $itemsPerPage): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-warning px-4 toggle-more-btn" onclick="toggleMenu(this)">Load More</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="tab-4" class="tab-pane fade <?php echo ($activeTab == 'tab-4') ? 'show active' : ''; ?> p-2">
                        <div class="destination-grid">
                            <?php
                            $sql = "SELECT * FROM MenuItems WHERE CategoryID = $drinkCatID AND Status='Available' ORDER BY MenuItemID ASC";
                            $res = renderMenuGrid($myCon->query($sql));
                            echo $res['html'];
                            ?>
                        </div>
                        <?php if ($res['count'] > $itemsPerPage): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-warning px-4 toggle-more-btn" onclick="toggleMenu(this)">Load More</button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php include 'include/cart_btn.php'; ?>
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


    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>


    <!-- Template Javascript -->
    <script src="js/main.js"></script>   
    <script>
        function toggleMenu(button) {
            // Find the grid related to this specific clicked button
            const tabPane = button.closest('.tab-pane');
            const extraItems = tabPane.querySelectorAll('.extra-item');

            // Check if we are currently showing or hiding
            const isExpanded = button.innerText === "Show Less";

            extraItems.forEach(item => {
                if (isExpanded) {
                    item.classList.add('d-none'); // Hide them again
                } else {
                    item.classList.remove('d-none'); // Reveal them
                }
            });

            // Toggle the button text contextually
            button.innerText = isExpanded ? "Load More" : "Show Less";

            // Optional smooth scroll back up to the tab if collapse behavior happens
            if (isExpanded) {
                tabPane.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    </script> 
</body>

</html>