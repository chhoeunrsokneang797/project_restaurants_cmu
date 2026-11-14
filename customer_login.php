<?php
include 'include/cn.php';

if (!isset($_SESSION['customer_csrf'])) {
    $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
}

function getCustomerLoginRedirect($target)
{
    $allowedPages = ['index.php', 'menu.php', 'table_order.php'];
    $parts = parse_url(is_string($target) ? $target : '');
    if (!is_array($parts)) {
        return 'menu.php';
    }

    $page = $parts['path'] ?? '';

    if (!in_array($page, $allowedPages, true)) {
        return 'menu.php';
    }

    $query = [];
    parse_str($parts['query'] ?? '', $query);
    $safeQuery = [];

    if (isset($query['tab']) && in_array($query['tab'], ['tab-1', 'tab-2', 'tab-3', 'tab-4'], true)) {
        $safeQuery['tab'] = $query['tab'];
    }

    if (isset($query['t'], $query['k'])
        && is_string($query['t'])
        && ctype_digit($query['t'])
        && is_string($query['k'])
        && preg_match('/^[a-f0-9]{16}$/i', $query['k'])) {
        $safeQuery['t'] = (int) $query['t'];
        $safeQuery['k'] = $query['k'];
    }

    return $page . ($safeQuery ? '?' . http_build_query($safeQuery) : '');
}

$redirectTo = getCustomerLoginRedirect($_POST['next'] ?? $_GET['next'] ?? 'menu.php');
$loginError = '';
$signupName = '';
$signupPhone = '';
$signupEmail = '';
$activeAccountForm = 'login';

if (isset($_SESSION['customer_id'])) {
    header('Location: ' . $redirectTo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!is_string($csrfToken) || !hash_equals($_SESSION['customer_csrf'], $csrfToken)) {
        $loginError = 'Your login session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'create_account') {
        $activeAccountForm = 'create';
        $nameInput = $_POST['customer_name'] ?? '';
        $phoneInput = $_POST['signup_phone'] ?? '';
        $emailInput = $_POST['signup_email'] ?? '';
        $passwordInput = $_POST['signup_password'] ?? '';
        $confirmPasswordInput = $_POST['confirm_password'] ?? '';

        $signupName = is_string($nameInput) ? trim($nameInput) : '';
        $signupPhone = is_string($phoneInput) ? trim($phoneInput) : '';
        $signupEmail = is_string($emailInput) ? trim($emailInput) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        $confirmPassword = is_string($confirmPasswordInput) ? $confirmPasswordInput : '';

        if ($signupName === '' || strlen($signupName) > 100) {
            $loginError = 'Enter your name (up to 100 characters).';
        } elseif ($signupPhone === '' || strlen($signupPhone) > 20) {
            $loginError = 'Enter a valid phone number (up to 20 characters).';
        } elseif ($signupEmail !== '' && (strlen($signupEmail) > 100 || !filter_var($signupEmail, FILTER_VALIDATE_EMAIL))) {
            $loginError = 'Enter a valid email address.';
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            $loginError = 'Choose a password between 8 and 72 characters.';
        } elseif ($password !== $confirmPassword) {
            $loginError = 'The passwords do not match.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $myCon->prepare(
                'INSERT INTO Customers (CustomerName, Phone, Email, PasswordHash, CustomerType, Role)
                 VALUES (?, ?, ?, ?, \'Regular\', \'Customer\')'
            );
            $stmt->bind_param('ssss', $signupName, $signupPhone, $signupEmail, $passwordHash);

            try {
                $stmt->execute();
                $customerID = $myCon->insert_id;
                $stmt->close();

                session_regenerate_id(true);
                $_SESSION['customer_id'] = (int) $customerID;
                $_SESSION['customer_name'] = $signupName;
                $_SESSION['customer_phone'] = $signupPhone;
                $_SESSION['customer_photo'] = '';
                $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));

                header('Location: ' . $redirectTo);
                exit;
            } catch (mysqli_sql_exception $exception) {
                $stmt->close();
                if ($exception->getCode() === 1062) {
                    $loginError = 'An account with that phone number already exists. Please sign in instead.';
                } else {
                    throw $exception;
                }
            }
        }
    } elseif (($_POST['action'] ?? '') === 'login') {
        $phoneInput = $_POST['phone'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $phone = is_string($phoneInput) ? trim($phoneInput) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';

        if ($phone === '' || $password === '') {
            $loginError = 'Enter your phone number and password.';
        } else {
            $stmt = $myCon->prepare(
                'SELECT CustomerID, CustomerName, Phone, PasswordHash, ProfilePhoto
                 FROM Customers
                 WHERE Phone = ?
                 LIMIT 1'
            );
            $stmt->bind_param('s', $phone);
            $stmt->execute();
            $customer = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($customer && !empty($customer['PasswordHash']) && password_verify($password, $customer['PasswordHash'])) {
                session_regenerate_id(true);
                $_SESSION['customer_id'] = (int) $customer['CustomerID'];
                $_SESSION['customer_name'] = $customer['CustomerName'];
                $_SESSION['customer_phone'] = $customer['Phone'];
                $_SESSION['customer_photo'] = $customer['ProfilePhoto'] ?? '';
                $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));

                header('Location: ' . $redirectTo);
                exit;
            }

            $loginError = 'Phone number or password is incorrect.';
        }
    } else {
        $loginError = 'Choose sign in or create an account.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>
    <style>
        body.customer-auth-page {
            min-height: 100vh;
            margin: 0;
            color: #242a28;
            font-family: "Roboto", Arial, sans-serif;
            background: #171412;
        }

        .customer-auth-scene {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 20px;
            background:
                linear-gradient(rgba(17, 15, 14, .58), rgba(17, 15, 14, .68)),
                url("Banners/about-restaurant.png") center / cover no-repeat;
        }

        .customer-auth-card {
            width: min(100%, 710px);
            padding: 28px 34px 30px;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .28);
        }

        .customer-auth-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: #626866;
            font-size: 15px;
            text-decoration: none;
        }

        .customer-auth-back:hover {
            color: #1d3d91;
        }

        .customer-auth-brand {
            margin-bottom: 8px;
            color: #203d8c;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .customer-auth-title {
            margin-bottom: 4px;
            font-size: clamp(28px, 5vw, 36px);
            font-weight: 700;
            letter-spacing: -.7px;
        }

        .customer-auth-subtitle {
            margin-bottom: 16px;
            color: #686e6c;
        }

        .customer-auth-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 15px;
            padding: 4px;
            border: 1px solid #dedfdd;
            border-radius: 15px;
            background: #f5f5f3;
        }

        .customer-auth-tab {
            flex: 1;
            min-height: 44px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: #555d5a;
            font-weight: 600;
        }

        .customer-auth-tab.active {
            background: #fff;
            color: #203d8c;
            box-shadow: 0 1px 5px rgba(0, 0, 0, .12);
        }

        .customer-auth-form .form-label {
            margin-bottom: 6px;
            font-weight: 500;
        }

        .customer-auth-form .form-control {
            min-height: 48px;
            border-color: #dedfdd;
            border-radius: 12px;
            background-color: #f8f8f7;
            box-shadow: none;
        }

        .customer-auth-form .form-control:focus {
            border-color: #9eafe0;
            background-color: #eaf0ff;
            box-shadow: 0 0 0 3px rgba(32, 61, 140, .1);
        }

        .customer-auth-form .input-group .form-control {
            border-radius: 12px 0 0 12px;
        }

        .customer-auth-form .input-group .toggle-password {
            min-width: 52px;
            border-color: #dedfdd;
            border-radius: 0 12px 12px 0;
            background: #f8f8f7;
            color: #59615e;
        }

        .customer-auth-submit {
            min-height: 52px;
            border: 0;
            border-radius: 13px;
            background: #203f9a;
            font-size: 16px;
        }

        .customer-auth-submit:hover,
        .customer-auth-submit:focus {
            background: #183580;
        }

        @media (max-width: 575.98px) {
            .customer-auth-scene {
                padding: 16px 12px;
            }

            .customer-auth-card {
                padding: 23px 20px;
                border-radius: 19px;
            }
        }
    </style>
</head>

<body class="customer-auth-page">
    <main class="customer-auth-scene">
        <section class="customer-auth-card" aria-labelledby="customer-auth-title">
            <a class="customer-auth-back" href="index.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to home</a>
            <div class="customer-auth-brand">Angkor Siemreap Restaurant</div>
            <h1 id="customer-auth-title" class="customer-auth-title"><?= $activeAccountForm === 'create' ? 'Create your account' : 'Welcome back'; ?></h1>
            <p class="customer-auth-subtitle">Sign in to manage reservations and see your table history.</p>

            <div class="customer-auth-tabs" role="tablist" aria-label="Customer account options">
                <button type="button" class="customer-auth-tab <?= $activeAccountForm === 'login' ? 'active' : ''; ?>" id="loginTab" role="tab" aria-controls="loginPanel" aria-selected="<?= $activeAccountForm === 'login' ? 'true' : 'false'; ?>">Sign in</button>
                <button type="button" class="customer-auth-tab <?= $activeAccountForm === 'create' ? 'active' : ''; ?>" id="createTab" role="tab" aria-controls="createPanel" aria-selected="<?= $activeAccountForm === 'create' ? 'true' : 'false'; ?>">Create account</button>
            </div>

            <?php if ($loginError !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div id="loginPanel" role="tabpanel" aria-labelledby="loginTab" <?= $activeAccountForm === 'login' ? '' : 'hidden'; ?>>
                <form method="post" action="customer_login.php" class="customer-auth-form">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($redirectTo, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['customer_csrf'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" autocomplete="tel" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
                            <button type="button" class="btn toggle-password" data-password-target="password" aria-controls="password" aria-label="Show password" aria-pressed="false"><i class="far fa-eye" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary customer-auth-submit w-100">Sign in</button>
                </form>
            </div>

            <div id="createPanel" role="tabpanel" aria-labelledby="createTab" <?= $activeAccountForm === 'create' ? '' : 'hidden'; ?>>
                <form method="post" action="customer_login.php" class="customer-auth-form">
                    <input type="hidden" name="action" value="create_account">
                    <input type="hidden" name="next" value="<?= htmlspecialchars($redirectTo, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['customer_csrf'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Full name</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($signupName, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup_phone" class="form-label">Phone number</label>
                        <input type="tel" id="signup_phone" name="signup_phone" class="form-control" maxlength="20" autocomplete="tel" value="<?= htmlspecialchars($signupPhone, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup_email" class="form-label">Email <span class="text-muted">(optional)</span></label>
                        <input type="email" id="signup_email" name="signup_email" class="form-control" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($signupEmail, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="signup_password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="signup_password" name="signup_password" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                            <button type="button" class="btn toggle-password" data-password-target="signup_password" aria-controls="signup_password" aria-label="Show password" aria-pressed="false"><i class="far fa-eye" aria-hidden="true"></i></button>
                        </div>
                        <div class="form-text">Use at least 8 characters.</div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary customer-auth-submit w-100">Create account</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        const accountTabs = [
            { button: document.getElementById('loginTab'), panel: document.getElementById('loginPanel'), title: 'Welcome back' },
            { button: document.getElementById('createTab'), panel: document.getElementById('createPanel'), title: 'Create your account' }
        ];

        accountTabs.forEach(function (tab) {
            tab.button.addEventListener('click', function () {
                accountTabs.forEach(function (item) {
                    const selected = item === tab;
                    item.button.classList.toggle('active', selected);
                    item.button.setAttribute('aria-selected', String(selected));
                    item.panel.hidden = !selected;
                });
                document.getElementById('customer-auth-title').textContent = tab.title;
            });
        });

        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const password = document.getElementById(this.dataset.passwordTarget);
                const isVisible = password.type === 'text';
                password.type = isVisible ? 'password' : 'text';
                this.innerHTML = isVisible
                    ? '<i class="far fa-eye" aria-hidden="true"></i>'
                    : '<i class="far fa-eye-slash" aria-hidden="true"></i>';
                this.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                this.setAttribute('aria-pressed', String(!isVisible));
            });
        });
    </script>
</body>

</html>
