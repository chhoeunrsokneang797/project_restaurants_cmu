<?php include 'include/cn.php';

if (isset($_POST['btnReserve'])) {
    $customerName = trim($_POST['CustomerName']);
    $phone        = trim($_POST['Phone']);
    $email        = trim($_POST['Email']);
    $address      = trim($_POST['Address']);

    $bookingDateTime = $_POST['BookingDate'];
    $bookingDate      = date('Y-m-d', strtotime($bookingDateTime));
    $bookingTime      = date('H:i:s', strtotime($bookingDateTime));

    $tableID = (int) $_POST['TableID'];
    $guests  = (int) $_POST['NumberOfGuests'];
    $note    = trim($_POST['Note']);

    $myCon->begin_transaction();

    $stmtTableCheck = $myCon->prepare("
        SELECT Status
        FROM RestaurantTables
        WHERE TableID = ?
        FOR UPDATE
    ");
    $stmtTableCheck->bind_param("i", $tableID);
    $stmtTableCheck->execute();
    $tableRow = $stmtTableCheck->get_result()->fetch_assoc();
    $stmtTableCheck->close();

    if (!$tableRow || $tableRow['Status'] !== 'Available') {
        $myCon->rollback();
        echo "
        <script>
            alert('This table is no longer available. Please choose another table.');
            window.location='booking.php';
        </script>";
        exit;
    }

    /* ---------------------------------------------------------
       Find existing customer by phone, or create one
    --------------------------------------------------------- */
    $stmtCust = $myCon->prepare("SELECT CustomerID FROM Customers WHERE Phone = ?");
    $stmtCust->bind_param("s", $phone);
    $stmtCust->execute();
    $custRow = $stmtCust->get_result()->fetch_assoc();
    $stmtCust->close();

    if ($custRow) {
        $customerID = $custRow['CustomerID'];
    } else {
        $stmtNewCust = $myCon->prepare("
            INSERT INTO Customers (CustomerName, Phone, Email, Address)
            VALUES (?, ?, ?, ?)
        ");
        $stmtNewCust->bind_param("ssss", $customerName, $phone, $email, $address);
        $stmtNewCust->execute();
        $customerID = $myCon->insert_id;
        $stmtNewCust->close();
    }

    /* ---------------------------------------------------------
       Check if this table is already booked for that date/time
       (only active bookings block a new reservation)
    --------------------------------------------------------- */
    $stmtCheck = $myCon->prepare("
        SELECT BookingID
        FROM TableBookings
        WHERE TableID = ? AND BookingDate = ? AND BookingTime = ?
          AND BookingStatus IN ('Reserved', 'CheckedIn')
    ");
    $stmtCheck->bind_param("iss", $tableID, $bookingDate, $bookingTime);
    $stmtCheck->execute();
    $alreadyBooked = $stmtCheck->get_result()->num_rows > 0;
    $stmtCheck->close();

    if ($alreadyBooked) {
        $myCon->rollback();
        echo "
        <script>
            alert('This table is already booked for the selected date and time.');
        </script>";

    } else {

        /* ---------------------------------------------------------
           Insert booking with BookingStatus = 'Reserved' so it
           shows up correctly on the admin side (tblbooking.php)
        --------------------------------------------------------- */
        $bookingStatus = 'Reserved';

        $stmtInsert = $myCon->prepare("
            INSERT INTO TableBookings
                (CustomerID, TableID, BookingDate, BookingTime, NumberOfGuests, ContactPhone, BookingStatus, Note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->bind_param(
            "iississs",
            $customerID,
            $tableID,
            $bookingDate,
            $bookingTime,
            $guests,
            $phone,
            $bookingStatus,
            $note
        );
        $insertOk = $stmtInsert->execute();
        $stmtInsert->close();

        if ($insertOk) {

            $stmtTable = $myCon->prepare("UPDATE RestaurantTables SET Status = 'Reserved' WHERE TableID = ? AND Status = 'Available'");
            $stmtTable->bind_param("i", $tableID);
            $tableUpdated = $stmtTable->execute() && $stmtTable->affected_rows === 1;
            $stmtTable->close();

            if ($tableUpdated) {
                $myCon->commit();
                echo "
                <script>
                    alert('Table booked successfully.');
                    window.location='booking.php';
                </script>";
            } else {
                $myCon->rollback();
                echo "
                <script>
                    alert('This table is no longer available. Please choose another table.');
                </script>";
            }
        } else {
            $myCon->rollback();
            echo "
            <script>
                alert('Booking failed: " . htmlspecialchars($myCon->error) . "');
            </script>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>
    <link rel="stylesheet" href="css/booking.css">

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

        .bg-breadcrumb {
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
</head>



<body>
    <?php include 'include/topbar.php' ?>

    <div class="container-fluid position-relative p-0">
        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-3 mb-4">Online Booking</h1>
            </div>
        </div>
        <!-- Header End -->

        <!-- Reservation Start -->
        <div class="contact container-fluid booking-section py-5">
            <div class="mx-auto text-center mt-0" style="max-width: 900px;">
                <h5 class="section-title px-3">Booking Now</h5>
            </div>
            <div class="lattice-overlay"></div>
            <div class="fog-circle one"></div>
            <div class="fog-circle two"></div>
            <div class="container py-3">
                <div class="row g-5 align-items-center">

                    <!-- Left Content -->
                    <div class="col-lg-6">
                        <h5 class="text-warning">Reservation</h5>

                        <h1 class="mb-4">
                            Reserve Your Table Online
                        </h1>

                        <p class="mb-4">
                            Enjoy an unforgettable dining experience at our restaurant.
                            Reserve your table in advance and indulge in authentic Khmer
                            and international cuisine prepared by our talented chefs.
                        </p>

                        <p class="mb-4">
                            Whether you're planning a family dinner, a romantic date,
                            or a special celebration, we are ready to provide exceptional
                            service in a warm and welcoming atmosphere.
                        </p>

                        <a href="menu.php"
                            class="btn btn-light text-primary rounded-pill py-3 px-5 mt-2">
                            View Our Menu
                        </a>
                    </div>

                    <!-- Right Reservation Form -->
                    <div class="col-lg-6">
                        <div class="booking-card">
                            <h2 class="mb-3 text-shadow-blue-900">
                                Book Your Table Today
                            </h2>

                            <p class="mb-4">
                                Reserve your table online and enjoy delicious dishes,
                                outstanding service, and a memorable dining experience.
                            </p>

                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" placeholder="Your Name" name="CustomerName" required>
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" placeholder="Phone Number" name="Phone" required>
                                            <label for="phone">Phone Number</label>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email"
                                                class="form-control"
                                                name="Email"
                                                placeholder="Email Address">
                                            <label>Email Address</label>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text"
                                                class="form-control"
                                                name="Address"
                                                placeholder="Address">
                                            <label>Address</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Reservation Date & Time
                                        </label>
                                        <input type="datetime-local" class="form-control" name="BookingDate" id="datetime" required>
                                    </div>

                                    <!-- Table Selection -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Select Table
                                        </label>

                                        <select class="form-select"
                                            name="TableID"
                                            required>

                                            <option value="">
                                                Choose Table
                                            </option>

                                            <?php
                                            $tables = mysqli_query($myCon, "
                                                    SELECT TableID,TableName,Capacity,Location,Status
                                                    FROM restauranttables
                                                    ORDER BY TableName
                                                ");

                                            while ($row = mysqli_fetch_assoc($tables)) {
                                                $available = $row['Status'] === 'Available';
                                                $tableStatus = $available ? 'Available' : $row['Status'];
                                            ?>
                                                <option value="<?= (int) $row['TableID']; ?>" <?= $available ? '' : 'disabled'; ?>>
                                                    <?= htmlspecialchars($row['TableName'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?= htmlspecialchars($row['Location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    (<?= (int) $row['Capacity']; ?> Persons) - <?= htmlspecialchars($tableStatus, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php } ?>

                                        </select>
                                    </div>

                                    <!-- Number of Guests -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Number of Guests
                                        </label>

                                        <input type="number"
                                            class="form-control"
                                            name="NumberOfGuests"
                                            min="1"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Occasion
                                        </label>

                                        <select class="form-select"
                                            id="CategoriesSelect">

                                            <option>Birthday Celebration</option>
                                            <option>Family Dinner</option>
                                            <option>Business Meeting</option>
                                            <option>Romantic Dinner</option>

                                        </select>
                                    </div>

                                    <!-- Special Request -->
                                    <div class="col-12">
                                        <div class="form-floating">

                                            <textarea class="form-control"
                                                name="Note"
                                                placeholder="Special Request" style="height:100px"></textarea>

                                            <label>
                                                Special Request
                                            </label>

                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" name="btnReserve" class="col-12 btn btn-primary rounded-pill py-3">
                                            Reserve Now
                                        </button>
                                    </div>

                                </div>
                            </form>

                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!-- Reservation End -->

        <!-- Subscribe Start -->
        <div class="container-fluid subscribe py-5">
            <div class="container text-center py-5">
                <div class="mx-auto text-center" style="max-width: 900px;">
                    <h5 class="subscribe-title px-3">Subscribe</h5>
                    <h1 class="text-white mb-4">Our Newsletter</h1>
                    <p class="text-white mb-5">Lorem ipsum dolor sit amet consectetur adipisicing elit. Laborum tempore nam, architecto doloremque velit explicabo? Voluptate sunt eveniet fuga eligendi! Expedita laudantium fugiat corrupti eum cum repellat a laborum quasi.
                    </p>
                    <div class="position-relative mx-auto">
                        <input class="form-control border-primary rounded-pill w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                        <button type="button" class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 px-4 mt-2 me-2">Subscribe</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Subscribe End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i class="fa fa-arrow-up"></i></a>

        <!-- Footer -->
        <?php include 'include/footer.php'; ?>

        <!-- Copyright -->
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
</body>

</html>