<?php
include 'include/cn.php';
include 'include/walkin.php';
include 'include/walkin_table.php';

$walkin = resolveTableOrderSession($myCon);

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
    <?php include 'include/head.php'; ?>
</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Bayon&family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
    .product-name { font-family: "Kantumruy Pro", sans-serif; }
    
    :root {
        --table-deep:  #042C53;
        --table-base:  #0C447C;
        --table-mid:   #185FA5;
        --table-light: #378ADD;
        --table-pale:  #E6F1FB;
    }

    .bg-breadcrumb-table {
        background: linear-gradient(rgba(19, 53, 123, 0.5), rgba(19, 53, 123, 0.5)), url(Banners/banner-package.jpg);
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        min-height: 45vh;
        display: flex;
        align-items: center;
        padding: 50px 0;
    }

    .table-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.4);
        color: #fff;
        padding: 8px 20px;
        border-radius: 100px;
        font-weight: 700;
        letter-spacing: .5px;
        margin-bottom: 14px;
    }

    .table-pill i { color: #FFF; }

    .booking-section {
        position: relative;
        overflow: hidden;
        padding: 30px 0;
        background-image:
            radial-gradient(ellipse 55% 60% at 5% 95%, rgba(224,122,46,0.30) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 60% 100%, rgba(196,85,28,0.12) 0%, transparent 60%);
    }

    .btn-outline-secondary { border: 1px solid var(--table-base); color: var(--table-base); }
    .btn-outline-secondary:hover { color: #FFF; background: var(--table-base); }
    .btn-primary { background-color: var(--table-base) !important; border-color: var(--table-base) !important; }
    .btn-primary:hover { background-color: var(--table-deep) !important; border-color: var(--table-deep) !important; }    

    .nav-pills.custom-tabs .nav-link.active { background-color: var(--table-base) !important; }
    .nav-pills.custom-tabs .nav-link { color: var(--table-deep); }

    .dest-body .dest-title { font-family: 'Battambang', system-ui; font-size: 18px; font-weight: 600; }

    #toastContainer { position: fixed; top: 90px; right: 20px; z-index: 9999; }
    .cart-toast {
        background: linear-gradient(135deg, var(--table-base), var(--table-mid));
        color: #fff; padding: 14px 18px; border-radius: 12px; margin-bottom: 10px;
        box-shadow: 0 8px 24px rgba(122,46,18,0.35);
        display: flex; align-items: center; gap: 12px; min-width: 250px;
        opacity: 0; transform: translateX(40px);
        animation: toastIn 0.35s ease forwards, toastOut 0.35s ease forwards 2.3s;
    }
    .cart-toast i { font-size: 18px; background: rgba(255,255,255,0.18); padding: 8px; border-radius: 50%; }
    @keyframes toastIn { to { opacity: 1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity: 0; transform: translateX(40px); } }

    .not-checked-in-card {
        max-width: 520px;
        margin: 60px auto;
        text-align: center;
        padding: 40px 30px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
</style>

<body>

    <?php include 'include/topbar.php' ?>

    <?php if (!$walkin): ?>
        <div class="container-fluid bg-breadcrumb-table">
            <div class="not-checked-in-card">
                <div style="font-size:48px; color:var(--table-base);">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="fw-bold mt-3 mb-2">This table isn't active yet</h3>
                <p class="text-muted mb-0">
                    Please scan the QR code on your table again, or ask a staff
                    member to check you in — once seated, this page will show
                    your table's menu automatically.
                </p>
            </div>
        </div>

    <?php else: ?>

    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb-table">
        <div class="container text-center py-4" style="max-width: 900px;">
            <div class="table-pill font-kantumruy">
                <i class="fas fa-chair"></i>
                <?= htmlspecialchars($walkin['TableName'] . ' ' . $walkin['Location']); ?>
            </div>
            <h1 class="text-white display-4 mb-2">Order For Your Table</h1>
            <p class="text-white mb-0 fs-6">
                Browse the menu below and tap "Add to Cart" — your order goes
                straight to the kitchen for <?= htmlspecialchars($walkin['TableName']); ?>.
            </p>
        </div>
    </div>
    <!-- Header End -->

    <?php
    $itemsPerPage = 12;
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'tab-1';

    function renderMenuGrid($queryResult)
    {
        $index = 0;
        $html = '';
        while ($row = $queryResult->fetch_assoc()) {
            $imagePath = 'images/trash/' . $row['Image'];
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
                <h5 class="section-title px-3">Today's Menu</h5>
                <h1 class="mb-0">DINE-IN ORDERING</h1>
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

    <?php endif; // $walkin check ?>

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
        function toggleMenu(button) {
            const tabPane = button.closest('.tab-pane');
            const extraItems = tabPane.querySelectorAll('.extra-item');
            const isExpanded = button.innerText === "Show Less";
            extraItems.forEach(item => {
                if (isExpanded) item.classList.add('d-none');
                else item.classList.remove('d-none');
            });
            button.innerText = isExpanded ? "Load More" : "Show Less";
            if (isExpanded) tabPane.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>

</html>