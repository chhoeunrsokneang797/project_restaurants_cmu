<?php
// $walkin comes from include/walkin.php, which menu.php calls before
// including this file. It's an array (TableID, BookingID, TableName,
// Location, CustomerName, Phone) if this visitor scanned a table's QR
// code and that table has an active CheckedIn booking -- or null for a
// normal online customer.
$walkin = $walkin ?? null;
$isCustomerLoggedIn = isset($_SESSION['customer_id']);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/menu.php', PHP_URL_PATH);
$loginReturnPage = basename(is_string($requestPath) ? $requestPath : '/menu.php');
$loginReturnPage = in_array($loginReturnPage, ['index.php', 'menu.php', 'table_order.php'], true)
    ? $loginReturnPage
    : 'menu.php';
$loginReturnQuery = [];
if (isset($_GET['tab']) && is_string($_GET['tab'])
    && in_array($_GET['tab'], ['tab-1', 'tab-2', 'tab-3', 'tab-4'], true)) {
    $loginReturnQuery['tab'] = $_GET['tab'];
}
if (isset($_GET['t'], $_GET['k'])
    && is_string($_GET['t'])
    && ctype_digit($_GET['t'])
    && is_string($_GET['k'])
    && preg_match('/^[a-f0-9]{16}$/i', $_GET['k'])) {
    $loginReturnQuery['t'] = (int) $_GET['t'];
    $loginReturnQuery['k'] = $_GET['k'];
}
$loginReturnUrl = $loginReturnPage . ($loginReturnQuery ? '?' . http_build_query($loginReturnQuery) : '');
?>
<!-- Toast container -->
<div id="toastContainer"></div>

<!-- Cart button -->
<button type="button" class="btn btn-primary btn-primary-outline-0 btn-md-square cart-btn"
    style="position:fixed; bottom: 30px; right: 30px; z-index: 99;"
    data-bs-toggle="modal" data-bs-target="#cartModal">
    <i class="fas fa-shopping-cart"></i>
    <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        0
    </span>
</button>

<?php if ($walkin): ?>
<!-- Small persistent banner so the guest always knows they're in walk-in / table mode -->
<div style="position:fixed; bottom: 100px; right: 30px; z-index: 98; max-width: 260px;"
     class="bg-white shadow-sm rounded-3 p-2 px-3 border">
    <div class="small font-kantumruy fw-bold text-primary" style="letter-spacing:.5px;">ការកម្មង់សម្រាប់តុ</div>
    <div class="fw-bold font-kantumruy"><?= htmlspecialchars($walkin['TableName'] . ' ' . $walkin['Location']); ?></div>
</div>
<?php endif; ?>


<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div id="cartModalDialog" class="modal-dialog modal-fullscreen-md-down modal-xl modal-dialog-scrollable checkout-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 checkout-modal">

            <!-- Header -->
            <div id="cartModalHeader" class="modal-header checkout-header">
                <h4 class="modal-title fw-bold mb-0">
                    <i class="fas fa-shopping-cart me-3"></i>
                    Checkout Order
                </h4>

                <button type="button" class="btn-close btn-close-blue" aria-label="Close checkout" onclick="handleCheckoutClose()"></button>
            </div>

            <!-- Body -->
            <div class="modal-body checkout-body">

                <!-- STEP 1: ORDER FORM -->
                <div id="stepOrderForm">
                    <div class="row g-3">
                        <div class="col-12 col-lg-7">

                            <?php if ($walkin): ?>
                                <div class="card border-0 shadow-sm checkout-card">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-kantumruy small fw-bold text-primary" style="letter-spacing:.5px;">
                                                ការកម្មង់សម្រាប់តុ
                                            </div>
                                            <div class="fs-5 fw-bold font-kantumruy">
                                                <?= htmlspecialchars($walkin['TableName'] . ' ' . $walkin['Location']); ?>
                                            </div>
                                        </div>
                                        <a href="?clear=1" class="btn btn-sm btn-outline-secondary">Not your table?</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card border-0 shadow-sm checkout-card">
                                    <div class="card-header checkout-card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-circle me-2 text-primary"></i>
                                            Customer Information
                                        </h5>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                                <input type="text" id="customerName" class="form-control" value="<?= htmlspecialchars($_SESSION['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" id="phoneNumber" class="form-control" value="<?= htmlspecialchars($_SESSION['customer_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>
                                        <input type="hidden" id="orderType" value="Dine In">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Order Type <span class="text-danger">*</span></label>
                                                <div class="checkout-order-types" role="group" aria-label="Order type">
                                                    <button type="button" class="checkout-order-type selected" data-order-type="Dine In" aria-pressed="true">Dine In</button>
                                                    <button type="button" class="checkout-order-type" data-order-type="Delivery" aria-pressed="false">Delivery</button>
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="dineInSection">
                                                <label class="form-label">Choose Your Table <span class="text-danger">*</span></label>
                                                <select class="form-select checkout-input" name="TableID" id="tableID" required>
                                                    <option value="">Choose table</option>
                                                    <?php
                                                    $reservedByTable = [];
                                                    $reservedRes = mysqli_query($myCon, "
                                                        SELECT b1.TableID,
                                                               COALESCE(c.CustomerName, CONCAT('Walk-in #', b1.BookingID)) AS CustomerName,
                                                               COALESCE(c.Phone, b1.ContactPhone) AS ContactPhone
                                                        FROM tablebookings b1
                                                        INNER JOIN (
                                                            SELECT TableID, MAX(BookingID) AS maxID
                                                            FROM tablebookings
                                                            WHERE BookingStatus IN ('Reserved', 'CheckedIn')
                                                            GROUP BY TableID
                                                        ) b2 ON b1.TableID = b2.TableID AND b1.BookingID = b2.maxID
                                                        LEFT JOIN Customers c ON b1.CustomerID = c.CustomerID
                                                    ");
                                                    while ($r = mysqli_fetch_assoc($reservedRes)) {
                                                        $reservedByTable[$r['TableID']] = [
                                                            'name'  => $r['CustomerName'],
                                                            'phone' => $r['ContactPhone'],
                                                        ];
                                                    }

                                                    $tables = mysqli_query($myCon, "
                                                        SELECT TableID, TableName, Capacity, Location, Status
                                                        FROM RestaurantTables
                                                        ORDER BY TableName
                                                    ");

                                                    while ($row = mysqli_fetch_assoc($tables)) {
                                                        $reservedLabel = '';
                                                        if (isset($reservedByTable[$row['TableID']])) {
                                                            $res = $reservedByTable[$row['TableID']];
                                                            $reservedLabel = ' : ' . $res['name'];
                                                            if (!empty($res['phone'])) {
                                                                $reservedLabel .= ' (' . $res['phone'] . ')';
                                                            }
                                                        }
                                                        $tableAvailable = $row['Status'] === 'Available';
                                                    ?>
                                                        <option class="font-kantumruy" value="<?= (int) $row['TableID']; ?>" <?= $tableAvailable ? '' : 'disabled'; ?>>
                                                            <?= htmlspecialchars($row['TableName'], ENT_QUOTES, 'UTF-8'); ?>
                                                            <?= htmlspecialchars($row['Location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            - <?= htmlspecialchars($row['Status'], ENT_QUOTES, 'UTF-8'); ?><?= htmlspecialchars($reservedLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-12" id="addressCol" style="display:none;">
                                                <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                                <textarea id="address" class="form-control checkout-input" rows="2" placeholder="Enter your delivery address"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card border-0 shadow-sm mt-3 checkout-card">
                                <div class="card-header checkout-card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-utensils me-2 text-success"></i>
                                        Order Items
                                    </h5>
                                </div>
                                <div class="card-body" id="cartItems">
                                    <p class="text-muted text-center py-3">No items yet</p>
                                </div>
                            </div>

                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm sticky-top checkout-card checkout-summary">
                                <div class="card-header checkout-card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt text-primary me-2"></i>
                                        Order Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <button class="form-select shadow-sm text-start d-flex align-items-center justify-content-between"
                                            type="button" id="currencyDropdown" data-bs-toggle="dropdown">
                                            <span id="selectedCurrency">
                                                <img src="https://flagcdn.com/w20/us.png" class="me-2" />
                                                USD ($)
                                            </span>
                                        </button>
                                        <input type="hidden" id="currency" value="USD">
                                        <ul class="dropdown-menu w-100">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                    onclick="selectCurrency('USD', 'US', '$')">
                                                    <img src="https://flagcdn.com/w20/us.png" class="me-2" />
                                                    USD ($)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                    onclick="selectCurrency('KHR', 'KH', '៛')">
                                                    <img src="https://flagcdn.com/w20/kh.png" class="me-2" />
                                                    KHR (៛)
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 checkout-total-row">
                                        <span>Delivery:</span>
                                        <span><span class="currencySymbol">$</span><span id="deliveryFee">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span><span class="currencySymbol">$</span><span id="cartTotal">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (10%) :</span>
                                        <span><span class="currencySymbol">$</span><span id="cartTax">0.00</span></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center checkout-grand-total mb-3">
                                        <h5 class="mb-0">Total Amount:</h5>
                                        <h4 class="mb-0 fw-bold">
                                            <span class="currencySymbol">$</span>
                                            <span id="grandTotal">0.00</span>
                                        </h4>
                                    </div>
                                    <button class="btn btn-success rounded-pill w-100 py-2 px-4 mt-2" onclick="placeOrder()">
                                        <i class="fas fa-lock me-2"></i>
                                        Place Order
                                    </button>
                                    <button class="btn btn-outline-secondary rounded-pill w-100 py-2 px-4 mt-2" data-bs-dismiss="modal" onclick="location.reload()">
                                        Continue Shopping
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: PAYMENT METHOD -->
                <div id="stepPayment" class="d-none">
                    <section class="card border-0 shadow-sm mx-auto" style="max-width: 520px;">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                            <h4 class="modal-title fw-bold mb-0">
                                <i class="fas fa-wallet text-primary me-2"></i>Payment
                            </h4>
                            <button type="button" class="btn-close" aria-label="Close" onclick="handleCheckoutClose()"></button>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="text-center fw-bold mb-2">How would you like to pay?</h5>
                            <p class="text-center text-muted mb-4">Total: <strong id="paymentTotal">$0.00</strong></p>
                            <p id="paymentDoneNotice" class="alert alert-success text-center py-2 d-none" role="status"></p>

                            <div class="d-grid gap-3 mb-3">
                                <button type="button" class="payment-method-option selected" data-payment-method="Cash" aria-pressed="true">
                                    <i class="fas fa-money-bill-wave payment-method-icon" aria-hidden="true"></i>
                                    <span class="payment-method-copy">
                                        <strong>Cash</strong>
                                        <small>Pay when you receive your order / at the restaurant</small>
                                    </span>
                                    <i class="fas fa-check-circle payment-method-check" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="payment-method-option" data-payment-method="KHQR" aria-pressed="false">
                                    <i class="fas fa-qrcode payment-method-icon" aria-hidden="true"></i>
                                    <span class="payment-method-copy">
                                        <strong>KHQR</strong>
                                        <small>Confirm your order to display the payment QR</small>
                                    </span>
                                    <i class="fas fa-check-circle payment-method-check" aria-hidden="true"></i>
                                </button>
                            </div>

                            <p id="paymentError" class="alert alert-danger py-2 d-none" role="alert"></p>
                            <button type="button" id="confirmPaymentButton" class="btn btn-success rounded-pill w-100 py-2" onclick="confirmPayment()">
                                <i class="fas fa-check me-2"></i>Confirm
                            </button>
                            <button type="button" id="backToOrderButton" class="btn btn-link text-secondary w-100 mt-3 text-decoration-none" onclick="backToOrder()">
                                &larr; Back to order
                            </button>
                        </div>
                    </section>
                </div>

                <div id="stepKhqrPayment" class="d-none">
                    <section class="card border-0 shadow-sm mx-auto" style="max-width: 520px;">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                            <h4 class="modal-title fw-bold mb-0">
                                <i class="fas fa-wallet text-primary me-2"></i>KHQR Payment
                            </h4>
                            <button type="button" class="btn-close" aria-label="Close" onclick="closeAndReset()"></button>
                        </div>
                        <div class="card-body p-4 text-center">
                            <p class="text-muted mb-1">Order <strong id="khqrOrderReference"></strong></p>
                            <p class="mb-3">Order total: <strong id="khqrOrderTotal">$0.00</strong></p>
                            <img
                                src="images/payment/khqr-reference.png"
                                class="khqr-payment-image"
                                alt="Supplied KHQR image for AngKor Siemreap Restaurant, fixed at 1.10 USD">
                            <p class="small text-danger fw-semibold mb-2">
                                This supplied QR has a fixed amount of $1.10 and does not update to match your order total.
                            </p>
                            <p class="text-muted mb-3">
                                <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>
                                Waiting for payment verification
                            </p>
                            <p class="small text-muted mb-3">
                                Your order remains unpaid until restaurant staff verify the payment.
                            </p>
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="closeAndReset()">
                                Done
                            </button>
                        </div>
                    </section>
                </div>

                <!-- STEP 3: CHECK-IN RECEIPT -->
                <div id="stepSuccess" style="display:none;">
                    <div class="text-center mb-4">
                        <div class="mb-2" style="font-size:48px; color:#1D9E75;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 id="successTitle" class="fw-bold mb-1">Order confirmed!</h4>
                        <p id="successDesc" class="text-muted mb-1">Your order is confirmed.</p>
                        <p class="mb-0"><strong id="successRef"></strong></p>
                    </div>

                    <div class="card border-0 shadow-sm mx-auto" style="max-width:520px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-bold" style="color:#042C53;">AngKor Siem Reap</div>
                                    <div class="text-muted small">Siem Reap, Cambodia</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold" style="color:#042C53;">Order #<span id="inv-orderID"></span></div>
                                    <div class="text-muted small" id="inv-date"></div>
                                </div>
                            </div>

                            <div class="p-3 rounded mb-3 text-center" style="background:#E6F1FB; border:1px solid #185FA5;">
                                <div class="text-uppercase small fw-bold" style="color:#0C447C; letter-spacing:1px;" id="inv-table-label">Requested table — show at check-in</div>
                                <div class="fw-bold" style="color:#042C53; font-size:22px;" id="inv-table">—</div>
                                <div class="small text-muted mt-1" id="inv-table-note">Pending confirmation by restaurant staff</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Customer</div>
                                    <div id="inv-name"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Phone</div>
                                    <div id="inv-phone"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Email</div>
                                    <div id="inv-email"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Order type</div>
                                    <div id="inv-orderType"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Payment</div>
                                    <div id="inv-payment"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-uppercase small fw-bold" style="color:#042C53;">Status</div>
                                    <div id="inv-payStatus-wrap"><span id="inv-payStatus" class="badge"></span></div>
                                </div>
                            </div>

                            <hr>

                            <table class="table table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="inv-items"></tbody>
                            </table>

                            <div class="ms-auto" style="max-width:240px;">
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Subtotal</span><span>$<span id="inv-subtotal">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Tax (10%)</span><span>$<span id="inv-tax">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold" style="color:#042C53; border-top:2px solid #042C53; margin-top:6px; padding-top:6px;">
                                    <span>Grand total</span><span>$<span id="inv-grand">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between small" style="color:#1D9E75;">
                                    <span>Paid now</span><span>$<span id="inv-paidNow">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Balance due</span><span>$<span id="inv-balance">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-primary rounded-pill px-4 me-2" onclick="printInvoice()">
                            <i class="fas fa-print me-2"></i>Save Receipt
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-4" onclick="returnToPaymentMethods()">
                            Done
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&display=swap');
    .product-name { font-family: "Kantumruy Pro", sans-serif; }
    .checkout-dialog { max-width: 960px; }
    .checkout-modal { border-radius: 18px !important; overflow: hidden; }
    .checkout-header {
        min-height: 68px;
        padding: 14px 20px;
        border-bottom: 1px solid #e6e8ed;
        background: #fff;
        color: #171923;
    }
    .checkout-header h4 { font-size: 25px; }
    .checkout-header h4 i { color: #193d7d; }
    .checkout-header .btn-close { opacity: .55; }
    .checkout-body { padding: 18px; background: #f3f4f6; }
    .checkout-card {
        overflow: visible;
        border-radius: 15px;
        background: #fff;
    }
    .checkout-card-header {
        padding: 12px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }
    .checkout-card-header h5 { font-size: 20px; font-weight: 600; }
    .checkout-card-header h5 i { color: #193d7d !important; }
    .checkout-card .card-body { padding: 18px; }
    .checkout-card .form-label { margin-bottom: 7px; color: #777; }
    .checkout-input,
    #customerName,
    #phoneNumber {
        min-height: 44px;
        border-color: #d3d9e3;
        border-radius: 11px;
        background-color: #eaf1ff;
    }
    .checkout-order-types { display: flex; gap: 7px; }
    .checkout-order-type {
        min-height: 44px;
        flex: 1;
        padding: 7px 10px;
        border: 1px solid #214a7a;
        border-radius: 11px;
        background: #fff;
        color: #214a7a;
        font-weight: 600;
        white-space: nowrap;
    }
    .checkout-order-type.selected {
        border-color: #163f78;
        background: #163f78;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(139, 112, 255, .45);
    }
    .checkout-summary { top: 12px; }
    .checkout-summary .card-body { padding: 18px; color: #777; }
    .checkout-grand-total {
        min-height: 72px;
        padding: 14px 18px;
        border-radius: 11px;
        background: #193d7d;
        color: #fff;
    }
    .checkout-grand-total h4 { font-size: 29px; }
    .checkout-summary .btn-success {
        min-height: 50px;
        background: #20a653;
        border-color: #20a653;
        font-weight: 700;
    }
    .checkout-summary .btn-outline-secondary {
        min-height: 48px;
        border: 1px solid #214a7a;
        color: #214a7a;
        font-weight: 600;
    }
    .checkout-items-list { display: flex; flex-direction: column; gap: 10px; }
    .checkout-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 8px 0 12px;
        border-bottom: 1px solid #e0e3e8;
    }
    .checkout-item-icon {
        display: grid;
        width: 70px;
        height: 70px;
        flex: 0 0 70px;
        place-items: center;
        border-radius: 12px;
        background: #e8f1fb;
        color: #214a7a;
        font-size: 24px;
        object-fit: cover;
    }
    .checkout-item-info { min-width: 0; flex: 1; }
    .checkout-item-name { display: block; font-weight: 600; overflow-wrap: anywhere; }
    .checkout-item-controls { display: flex; align-items: center; gap: 7px; color: #777; }
    .checkout-qty-button {
        width: 25px;
        height: 25px;
        padding: 0;
        border: 1px solid #214a7a;
        border-radius: 50%;
        background: #fff;
        color: #214a7a;
        line-height: 1;
    }
    .checkout-item-price { color: #777; font-weight: 600; white-space: nowrap; }
    .checkout-remove-button {
        width: 36px;
        height: 38px;
        border: 0;
        border-radius: 10px;
        background: #d83a4b;
        color: #fff;
    }
    .btn-outline-secondary { border: 1px solid #0C447C; color: #0C447C; }
    .btn-outline-secondary:hover { color: #fff; background: #0C447C; }
    #toastContainer { position: fixed; top: 90px; right: 20px; z-index: 9999; }
    .cart-toast {
        background: linear-gradient(135deg, #0C447C, #185FA5);
        color: #fff; padding: 14px 18px; border-radius: 12px; margin-bottom: 10px;
        box-shadow: 0 8px 24px rgba(12, 68, 124, .35);
        display: flex; align-items: center; gap: 12px; min-width: 250px;
        opacity: 0; transform: translateX(40px);
        animation: toastIn .35s ease forwards, toastOut .35s ease forwards 2.3s;
    }
    .payment-method-option {
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        min-height: 92px;
        padding: 14px 18px;
        border: 2px solid #dfe3e5;
        border-radius: 16px;
        background: #fff;
        color: #20252b;
        text-align: left;
        transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
    }
    .payment-method-option:hover { border-color: #9eb4aa; }
    .payment-method-option.selected {
        border-color: #198754;
        background: #f3fbf7;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, .1);
    }
    .payment-method-icon { width: 34px; color: #164a78; font-size: 24px; text-align: center; }
    .payment-method-copy { display: flex; flex: 1; flex-direction: column; gap: 4px; }
    .payment-method-copy strong { font-size: 17px; }
    .payment-method-copy small { color: #70777d; font-size: 14px; }
    .payment-method-check { color: #198754; opacity: 0; }
    .payment-method-option.selected .payment-method-check { opacity: 1; }
    .payment-modal { max-width: 560px; }
    .khqr-payment-image {
        display: block;
        width: 100%;
        max-width: 344px;
        height: auto;
        margin: 0 auto 12px;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    }
    .cart-toast i { font-size: 18px; background: rgba(255, 255, 255, .18); padding: 8px; border-radius: 50%; }
    @keyframes toastIn { to { opacity: 1; transform: translateX(0); } }
    @keyframes toastOut { to { opacity: 0; transform: translateX(40px); } }
    @media (max-width: 575.98px) {
        .checkout-body { padding: 12px; }
        .checkout-card .card-body { padding: 14px; }
        .checkout-header h4 { font-size: 21px; }
        .checkout-order-type { padding-inline: 6px; font-size: 13px; }
        .checkout-item { gap: 9px; }
        .checkout-item-icon { width: 54px; height: 58px; flex-basis: 54px; }
        .checkout-item-price { font-size: 14px; }
    }
</style>

<script>
    // ── Walk-in session data (from QR scan), injected by PHP ──────
    const isWalkIn         = <?= $walkin ? 'true' : 'false'; ?>;
    const isCustomerLoggedIn = <?= $isCustomerLoggedIn ? 'true' : 'false'; ?>;
    const customerLoginUrl = <?= json_encode('customer_login.php?next=' . rawurlencode($loginReturnUrl)); ?>;
    const walkinTableId    = <?= $walkin ? (int) $walkin['TableID'] : 'null'; ?>;
    const walkinBookingId  = <?= $walkin ? (int) $walkin['BookingID'] : 'null'; ?>;
    const walkinTableLabel = <?= $walkin ? json_encode($walkin['TableName'] . ' ' . $walkin['Location']) : 'null'; ?>;

    // ── Cart storage key ───────────────────────────────────────
    // FIX: each table gets its OWN localStorage key (cart_table_5,
    // cart_table_2, ...) instead of everyone sharing one 'cart' key.
    // This is what stops Table 1 and Table 2 orders from merging when
    // both links are open in the same browser. Online (non walk-in)
    // customers keep using the plain 'cart' key as before.
    const cartKey = isWalkIn && walkinTableId ? ('cart_table_' + walkinTableId) : 'cart';

    let cart = [];
    let selectedPaymentMethod = 'Cash';
    let pendingOrder = null;
    let paymentCompleted = false;
    let completedPaymentMethod = '';
    try {
        const stored = JSON.parse(localStorage.getItem(cartKey)) || [];
        cart = stored
            .filter(i => i && typeof i.name === 'string' && !isNaN(parseFloat(i.price)))
            .map(i => ({
                name: i.name,
                price: parseFloat(i.price),
                qty: Math.max(1, parseInt(i.qty, 10) || 1),
                image: typeof i.image === 'string' ? i.image : ''
            }));
    } catch (e) {
        cart = [];
    }

    function saveCart() {
        localStorage.setItem(cartKey, JSON.stringify(cart));
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[character]);
    }

    function addToCart(name, price, image = '') {
        if (!isCustomerLoggedIn) {
            window.location.href = customerLoginUrl;
            return;
        }

        const existing = cart.find(i => i.name === name);
        if (existing) {
            existing.qty++;
            if (image) existing.image = image;
        } else {
            cart.push({ name, price, qty: 1, image });
        }
        saveCart();
        showCartToast(name);
        updateCartUI();
    }

    function updateCartUI() {
        const cartItems = document.getElementById('cartItems');
        let subtotal = 0;

        if (cartItems) {
            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-muted text-center py-3">No items yet — add from the menu above.</p>';
            } else {
                cartItems.innerHTML = '<div class="checkout-items-list"></div>';
                const itemList = cartItems.querySelector('.checkout-items-list');
                cart.forEach((item, i) => {
                    subtotal += item.price * item.qty;
                    itemList.innerHTML += `
                <div class="checkout-item">
                    ${item.image
                        ? `<img class="checkout-item-icon" src="${escapeHtml(item.image)}" alt="">`
                        : '<div class="checkout-item-icon" aria-hidden="true"><i class="fas fa-utensils"></i></div>'}
                    <div class="checkout-item-info">
                        <strong class="product-name checkout-item-name">${escapeHtml(item.name)}</strong>
                        <div class="checkout-item-controls">
                            <span>$${item.price.toFixed(2)} ×</span>
                            <button class="checkout-qty-button" aria-label="Decrease quantity" onclick="changeQty(${i},-1)">−</button>
                            <span>${item.qty}</span>
                            <button class="checkout-qty-button" aria-label="Increase quantity" onclick="changeQty(${i},1)">+</button>
                        </div>
                    </div>
                    <strong class="checkout-item-price">$${(item.price * item.qty).toFixed(2)}</strong>
                    <button class="checkout-remove-button" aria-label="Remove item" onclick="removeItem(${i})">
                        <i class="fas fa-trash"></i>
                        </button>
                </div>`;
                });
            }
        } else {
            subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
        }

        const tax = subtotal * 0.10;
        const grand = subtotal + tax;

        if (document.getElementById('cartTotal')) document.getElementById('cartTotal').textContent = subtotal.toFixed(2);
        if (document.getElementById('cartTax')) document.getElementById('cartTax').textContent = tax.toFixed(2);
        if (document.getElementById('grandTotal')) document.getElementById('grandTotal').textContent = grand.toFixed(2);

        const totalQty = cart.reduce((a, i) => a + i.qty, 0);
        document.querySelectorAll('#cartCount').forEach(el => el.textContent = totalQty);
    }

    function changeQty(index, delta) {
        cart[index].qty += delta;
        if (cart[index].qty <= 0) cart.splice(index, 1);
        saveCart();
        updateCartUI();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        saveCart();
        updateCartUI();
    }

    function placeOrder() {
        if (cart.length === 0) {
            alert('Your cart is empty.');
            return;
        }

        paymentCompleted = false;
        completedPaymentMethod = '';
        document.getElementById('paymentDoneNotice').classList.add('d-none');
        document.getElementById('confirmPaymentButton').disabled = false;
        document.getElementById('backToOrderButton').classList.remove('d-none');
        document.querySelectorAll('.payment-method-option').forEach(button => {
            button.disabled = false;
        });

        let customerName, phone, email, address, orderType, tableId, tableLabel;

        if (isWalkIn) {
            customerName = 'Walk-in Guest';
            phone = '';
            email = '';
            address = '';
            orderType = 'Dine In';
            tableId = walkinTableId;
            tableLabel = walkinTableLabel;
        } else {
            customerName = document.getElementById('customerName').value.trim();
            phone = document.getElementById('phoneNumber').value.trim();
            email = document.getElementById('email')?.value.trim() || '';
            address = document.getElementById('address')?.value.trim() || '';
            orderType = document.getElementById('orderType').value;

            const tableSelect = document.getElementById('tableID');
            tableId = orderType === 'Dine In' && tableSelect ? tableSelect.value : '';
            tableLabel = (tableSelect && tableSelect.selectedIndex > 0) ?
                tableSelect.options[tableSelect.selectedIndex].text.trim() :
                '';
            if (orderType !== 'Dine In') tableLabel = '';

            if (!customerName || !phone) {
                alert('Please enter customer name and phone number.');
                return;
            }
            if (orderType === 'Dine In' && !tableId) {
                alert('Please choose a table.');
                return;
            }
            if (orderType === 'Delivery' && !address) {
                alert('Please enter your delivery address.');
                return;
            }
        }

        const cartSnapshot = cart.map(i => ({ ...i }));
        const subtotal = cartSnapshot.reduce((s, i) => s + i.price * i.qty, 0);
        const grand = subtotal * 1.10;

        pendingOrder = {
            customerName, phone, email, address, orderType, tableId, tableLabel,
            cartSnapshot, grand
        };
        selectedPaymentMethod = 'Cash';
        document.querySelectorAll('.payment-method-option').forEach(button => {
            const selected = button.dataset.paymentMethod === selectedPaymentMethod;
            button.classList.toggle('selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
        const currencySymbol = document.querySelector('.currencySymbol')?.textContent || '$';
        document.getElementById('paymentTotal').textContent = currencySymbol + grand.toFixed(2);
        document.getElementById('paymentError').classList.add('d-none');
        document.getElementById('confirmPaymentButton').innerHTML = '<i class="fas fa-check me-2"></i>Confirm';
        document.getElementById('stepOrderForm').classList.add('d-none');
        document.getElementById('stepPayment').classList.remove('d-none');
        document.getElementById('cartModalHeader').classList.add('d-none');
        document.getElementById('cartModalDialog').classList.remove('modal-xl');
        document.getElementById('cartModalDialog').classList.add('payment-modal');
    }

    function backToOrder() {
        if (paymentCompleted) {
            closeCheckoutModal();
            return;
        }
        document.getElementById('stepPayment').classList.add('d-none');
        document.getElementById('stepOrderForm').classList.remove('d-none');
        document.getElementById('cartModalHeader').classList.remove('d-none');
        document.getElementById('cartModalDialog').classList.remove('payment-modal');
        document.getElementById('cartModalDialog').classList.add('modal-xl');
    }

    function returnToPaymentMethods() {
        if (!paymentCompleted) return;

        document.getElementById('stepSuccess').style.display = 'none';
        document.getElementById('stepKhqrPayment').classList.add('d-none');
        document.getElementById('cartModalHeader').classList.add('d-none');
        document.getElementById('stepOrderForm').classList.add('d-none');
        document.getElementById('stepPayment').classList.remove('d-none');
        document.getElementById('cartModalDialog').classList.remove('modal-xl');
        document.getElementById('cartModalDialog').classList.add('payment-modal');
        document.getElementById('paymentDoneNotice').textContent =
            'Order submitted with ' + completedPaymentMethod + '. You cannot change the payment method for this order.';
        document.getElementById('paymentDoneNotice').classList.remove('d-none');
        document.getElementById('confirmPaymentButton').disabled = true;
        document.getElementById('confirmPaymentButton').innerHTML = '<i class="fas fa-check me-2"></i>Order submitted';
        document.getElementById('backToOrderButton').textContent = 'Close';
        document.querySelectorAll('.payment-method-option').forEach(button => {
            button.disabled = true;
            const selected = button.dataset.paymentMethod === completedPaymentMethod;
            button.classList.toggle('selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
    }

    function closeCheckoutModal() {
        const modalEl = document.getElementById('cartModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    }

    function handleCheckoutClose() {
        if (paymentCompleted) {
            returnToPaymentMethods();
            return;
        }

        closeCheckoutModal();
    }

    function confirmPayment() {
        if (!pendingOrder) {
            backToOrder();
            return;
        }

        const { customerName, phone, email, address, orderType, tableId, tableLabel, cartSnapshot, grand } = pendingOrder;
        const paymentMethod = selectedPaymentMethod;
        const payload = {
            customerName, phone, email, address, orderType, paymentMethod,
            tableId, tableLabel, isWalkIn,
            bookingId: isWalkIn ? walkinBookingId : null,
            items: cartSnapshot.map(i => ({ name: i.name, price: i.price, qty: i.qty }))
        };

        const confirmButton = document.getElementById('confirmPaymentButton');
        const paymentError = document.getElementById('paymentError');
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting order...';
        paymentError.classList.add('d-none');

        fetch('place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Order failed.');
                }
                return result;
            })
            .then(result => {
                if (result.success) {
                    cart = [];
                    pendingOrder = null;
                    paymentCompleted = true;
                    completedPaymentMethod = paymentMethod;
                    saveCart();
                    document.getElementById('stepPayment').classList.add('d-none');
                    if (paymentMethod === 'KHQR') {
                        document.getElementById('khqrOrderReference').textContent = '#' + result.orderID;
                        document.getElementById('khqrOrderTotal').textContent =
                            (document.querySelector('.currencySymbol')?.textContent || '$') + grand.toFixed(2);
                        document.getElementById('stepKhqrPayment').classList.remove('d-none');
                    } else {
                        document.getElementById('cartModalHeader').classList.remove('d-none');
                        document.getElementById('cartModalDialog').classList.remove('payment-modal');
                        document.getElementById('cartModalDialog').classList.add('modal-xl');
                        showSuccess(
                            result.orderID, customerName, phone, email, orderType,
                            paymentMethod, 0, grand, cartSnapshot, tableLabel || 'Take Away', address
                        );
                    }
                } else {
                    throw new Error(result.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error(error);
                paymentError.textContent = error.message || 'Something went wrong placing the order.';
                paymentError.classList.remove('d-none');
            })
            .finally(() => {
                confirmButton.disabled = false;
                confirmButton.innerHTML = selectedPaymentMethod === 'KHQR'
                    ? '<i class="fas fa-check me-2"></i>Confirm and show QR'
                    : '<i class="fas fa-check me-2"></i>Confirm';
            });
    }

    document.querySelectorAll('.payment-method-option').forEach(button => {
        button.addEventListener('click', function () {
            selectedPaymentMethod = this.dataset.paymentMethod;
            document.querySelectorAll('.payment-method-option').forEach(option => {
                const selected = option === this;
                option.classList.toggle('selected', selected);
                option.setAttribute('aria-pressed', String(selected));
            });
            document.getElementById('confirmPaymentButton').innerHTML = selectedPaymentMethod === 'KHQR'
                ? '<i class="fas fa-check me-2"></i>Confirm and show QR'
                : '<i class="fas fa-check me-2"></i>Confirm';
        });
    });

    function handleOrderTypeChange() {
        const typeEl = document.getElementById('orderType');
        if (!typeEl) return;
        const type = typeEl.value;
        const dineSection = document.getElementById('dineInSection');
        const addressCol = document.getElementById('addressCol');
        if (dineSection) dineSection.style.display = (type === 'Dine In') ? '' : 'none';
        if (addressCol) addressCol.style.display = (type === 'Delivery') ? '' : 'none';
    }

    document.querySelectorAll('.checkout-order-type').forEach(button => {
        button.addEventListener('click', function () {
            const orderType = document.getElementById('orderType');
            if (!orderType) return;
            orderType.value = this.dataset.orderType;
            document.querySelectorAll('.checkout-order-type').forEach(option => {
                const selected = option === this;
                option.classList.toggle('selected', selected);
                option.setAttribute('aria-pressed', String(selected));
            });
            handleOrderTypeChange();
        });
    });

    function showSuccess(orderID, name, phone, email, orderType, method, paidNow, grand, cartSnapshot, tableLabel, address) {
        const balance = parseFloat((grand - paidNow).toFixed(2));
        const isDine = orderType === 'Dine In';
        const isDelivery = orderType === 'Delivery';

        document.getElementById('stepOrderForm').style.display = 'none';
        document.getElementById('stepSuccess').style.display = '';

        document.getElementById('successTitle').textContent = 'Order received!';
        if (isWalkIn) {
            document.getElementById('successDesc').textContent = 'Your order has been sent straight to the kitchen for your table.';
        } else {
            document.getElementById('successDesc').textContent = isDine ?
                'Show this receipt at the door — staff will confirm your table when you check in.' :
                'Your order is confirmed and being prepared.';
        }
        document.getElementById('successRef').textContent = 'Order #' + orderID;

        document.getElementById('inv-orderID').textContent = orderID;
        document.getElementById('inv-date').textContent = new Date().toLocaleString();
        document.getElementById('inv-name').textContent = name;
        document.getElementById('inv-phone').textContent = phone || '—';
        document.getElementById('inv-email').textContent = email || '—';
        document.getElementById('inv-orderType').textContent = orderType;
        document.getElementById('inv-payment').textContent = method;
        document.getElementById('inv-table').textContent = isDine
            ? (tableLabel || '—')
            : isDelivery ? (address || '—') : 'Take Away (no table)';

        document.getElementById('inv-table-label').textContent = isWalkIn ?
            'Seated at' : isDelivery ? 'Delivery address' : 'Requested table — show at check-in';
        document.getElementById('inv-table-note').textContent = isWalkIn ?
            'Sent to the kitchen' : isDelivery ? 'Address provided for delivery' : 'Pending confirmation by restaurant staff';

        const statusEl = document.getElementById('inv-payStatus');
        if (method === 'Cash') {
            statusEl.textContent = 'Pay on arrival';
            statusEl.className = 'badge bg-warning text-dark';
        } else if (method === 'KHQR') {
            statusEl.textContent = 'Awaiting payment';
            statusEl.className = 'badge bg-info text-dark';
        } else {
            statusEl.textContent = 'Paid';
            statusEl.className = 'badge bg-success';
        }

        let subtotal = 0, rows = '';
        cartSnapshot.forEach((item, i) => {
            const lt = item.price * item.qty;
            subtotal += lt;
            rows += `<tr>
            <td>${i + 1}</td>
            <td class="product-name">${item.name}</td>
            <td class="text-center">${item.qty}</td>
            <td class="text-end">$${item.price.toFixed(2)}</td>
            <td class="text-end">$${lt.toFixed(2)}</td>
        </tr>`;
        });
        document.getElementById('inv-items').innerHTML = rows || '<tr><td colspan="5" class="text-muted text-center">No items</td></tr>';

        const tax = subtotal * 0.10;
        document.getElementById('inv-subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('inv-tax').textContent = tax.toFixed(2);
        document.getElementById('inv-grand').textContent = grand.toFixed(2);
        document.getElementById('inv-paidNow').textContent = paidNow.toFixed(2);
        document.getElementById('inv-balance').textContent = balance.toFixed(2);

        updateCartUI();
    }

    function printInvoice() {
        const orderID = document.getElementById('inv-orderID').textContent;
        const date = document.getElementById('inv-date').textContent;
        const name = document.getElementById('inv-name').textContent;
        const phone = document.getElementById('inv-phone').textContent;
        const email = document.getElementById('inv-email').textContent;
        const orderType = document.getElementById('inv-orderType').textContent;
        const payment = document.getElementById('inv-payment').textContent;
        const table = document.getElementById('inv-table').textContent;
        const tableLabelText = document.getElementById('inv-table-label').textContent;
        const tableNoteText = document.getElementById('inv-table-note').textContent;
        const statusEl = document.getElementById('inv-payStatus');
        const statusHTML = statusEl.outerHTML;
        const items = document.getElementById('inv-items').innerHTML;
        const subtotal = document.getElementById('inv-subtotal').textContent;
        const tax = document.getElementById('inv-tax').textContent;
        const grand = document.getElementById('inv-grand').textContent;
        const paidNow = document.getElementById('inv-paidNow').textContent;
        const balance = document.getElementById('inv-balance').textContent;

        const isPaid = statusEl.classList.contains('bg-success');
        const isDeposit = statusEl.classList.contains('bg-info');
        const stripeColor = isPaid ? '#1D9E75' : isDeposit ? '#185FA5' : '#BA7517';
        const stripeText = isPaid ? '✓ &nbsp;Payment received' :
            isDeposit ? '✓ &nbsp;Deposit paid — balance due at restaurant' :
            '⏳ &nbsp;Pay on arrival — order held 15 min';

        const html = `
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');
            body { font-family: 'Open Sans', sans-serif; background: #e8ecf0; margin: 0; padding: 30px 16px; }
            .sheet { background: #fff; width: 520px; margin: 0 auto; border-radius: 12px; overflow: hidden; }
            .r-head { background: #042C53; color: #fff; padding: 22px 24px 16px; display: flex; justify-content: space-between; align-items: flex-start; }
            .r-brand { color: #fff; font-size: 17px; font-weight: 700; }
            .r-brand-sub { color: #fff; font-size: 11px; opacity: .55; margin-top: 2px; }
            .r-num { color: #fff; font-size: 19px; font-weight: 700; text-align: right; }
            .r-date { color: #fff; font-size: 11px; opacity: .55; text-align: right; margin-top: 2px; }
            .r-stripe { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; text-align: center; padding: 6px; color: #000; background: ${stripeColor}; }
            .r-table-callout { text-align:center; background:#E6F1FB; border-bottom:1px solid #185FA5; padding: 12px; }
            .r-table-callout .lbl { font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#0C447C; }
            .r-table-callout .val { font-size:22px; font-weight:700; color:#042C53; }
            .r-body { padding: 20px 24px; }
            .r-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; margin-bottom: 18px; }
            .r-lbl { color: #042C53; font-size: 10px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; margin-bottom: 2px; }
            .r-val { font-size: 13px; font-weight: 600; color: #000; }
            hr { border: none; border-top: 0.5px solid #e8e8e8; margin: 14px 0; }
            table { width: 100%; border-collapse: collapse; table-layout: fixed; }
            thead tr { border-bottom: 1.5px solid #042C53; }
            thead th { font-size: 10px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: #042C53; padding: 5px 0; text-align: left; }
            thead th:not(:first-child) { text-align: right; }
            tbody td { padding: 8px 0; border-bottom: 0.5px solid #f2f2f2; font-size: 13px; vertical-align: top; }
            tbody td:not(:first-child) { text-align: right; }
            tbody tr:last-child td { border-bottom: none; }
            .totals { margin: 14px 0 0 auto; width: 210px; }
            .trow { display: flex; justify-content: space-between; font-size: 12px; color: #888; padding: 3px 0; }
            .grand { display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; color: #042C53; border-top: 2px solid #042C53; margin-top: 8px; padding-top: 8px; }
            .paid  { display: flex; justify-content: space-between; font-size: 12px; color: #1D9E75; font-weight: 700; padding: 3px 0; }
            .bal   { display: flex; justify-content: space-between; font-size: 12px; color: #bbb; padding: 3px 0; }
            .r-foot { background: #f8f9fb; border-top: 0.5px solid #e8e8e8; padding: 13px 24px; display: flex; justify-content: space-between; align-items: center; }
            .r-foot-txt { font-size: 11px; color: #bbb; line-height: 1.6; }
            .pill { background: #042C53; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: .7px; text-transform: uppercase; padding: 4px 11px; border-radius: 100px; }
            .badge { font-size: 11px; padding: 2px 8px; border-radius: 4px; }
            .bg-success { background: #1D9E75; color: #fff; }
            .bg-info    { background: #185FA5; color: #fff; }
            .bg-warning { background: #BA7517; color: #fff; }
            @media print {
                body { background: #fff; padding: 0; }
                .sheet { border-radius: 0; width: 100%; }
            }
        </style>
        <div class="sheet">
            <div class="r-head">
                <div>
                    <div class="r-brand">AngKor Siem Reap</div>
                    <div class="r-brand-sub">Siem Reap, Cambodia</div>
                </div>
                <div>
                    <div class="r-num">#${orderID}</div>
                    <div class="r-date">${date}</div>
                </div>
            </div>
            <div class="r-stripe">${stripeText}</div>
            <div class="r-table-callout">
                <div class="lbl">${tableLabelText}</div>
                <div class="val">${table}</div>
                <div style="font-size:11px; color:#5F5E5A; margin-top:2px;">${tableNoteText}</div>
            </div>
            <div class="r-body">
                <div class="r-meta">
                    <div><div class="r-lbl">Customer</div><div class="r-val">${name}</div></div>
                    <div><div class="r-lbl">Phone</div><div class="r-val">${phone}</div></div>
                    <div><div class="r-lbl">Email</div><div class="r-val">${email}</div></div>
                    <div><div class="r-lbl">Order type</div><div class="r-val">${orderType}</div></div>
                    <div><div class="r-lbl">Payment</div><div class="r-val">${payment}</div></div>
                    <div><div class="r-lbl">Status</div><div class="r-val">${statusHTML}</div></div>
                </div>
                <hr>
                <table>
                    <thead><tr>
                        <th style="width:10%">#</th>
                        <th style="width:30%">Item</th>
                        <th style="width:26%">Qty</th>
                        <th style="width:22%">Unit</th>
                        <th style="width:22%">Total</th>
                    </tr></thead>
                    <tbody>${items}</tbody>
                </table>
                <div class="totals">
                    <div class="trow"><span>Subtotal</span><span>$${subtotal}</span></div>
                    <div class="trow"><span>Tax (10%)</span><span>$${tax}</span></div>
                    <div class="grand"><span>Grand total</span><span>$${grand}</span></div>
                    <div class="paid"><span>Paid now</span><span>$${paidNow}</span></div>
                    <div class="bal"><span>Balance due</span><span>$${balance}</span></div>
                </div>
            </div>
            <div class="r-foot">
                <div class="r-foot-txt">Thank you for dining with us!<br>We look forward to seeing you.</div>
                <div class="pill">AngKor Siem Reap</div>
            </div>
        </div>`;

        let frame = document.getElementById('_printFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = '_printFrame';
            frame.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
            document.body.appendChild(frame);
        }

        const doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' + html + '</body></html>');
        doc.close();

        frame.onload = function() {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        };
    }

    function closeAndReset() {
        const modalEl = document.getElementById('cartModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
        setTimeout(() => {
            document.getElementById('stepSuccess').style.display = 'none';
            document.getElementById('stepKhqrPayment').classList.add('d-none');
            document.getElementById('stepOrderForm').style.display = '';
            document.getElementById('stepPayment').classList.add('d-none');
            document.getElementById('paymentDoneNotice').classList.add('d-none');
            document.getElementById('cartModalDialog').classList.remove('payment-modal');
            document.getElementById('cartModalDialog').classList.add('modal-xl');
            document.getElementById('cartModalHeader').classList.remove('d-none');
            const placeBtn = document.querySelector('.btn-success[onclick="placeOrder()"]');
            if (placeBtn) {
                placeBtn.disabled = false;
                placeBtn.innerHTML = '<i class="fas fa-shopping-bag me-2"></i>Place Order';
            }
            pendingOrder = null;
            paymentCompleted = false;
            completedPaymentMethod = '';
        }, 300);
    }

    function showCartToast(name) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'cart-toast';
        toast.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>Added to cart</strong><br>
            <small class="product-name">${name}</small>
        </div>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 2700);
    }

    function selectCurrency(code, flag, symbol) {
        document.getElementById('currency').value = code;
        document.querySelectorAll('.currencySymbol').forEach(el => el.textContent = symbol);
        const label = document.getElementById('selectedCurrency');
        if (label) label.innerHTML = `<img src="https://flagcdn.com/w20/${flag.toLowerCase()}.png" class="me-2" />${code} (${symbol})`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleOrderTypeChange();
        updateCartUI();
    });
</script>