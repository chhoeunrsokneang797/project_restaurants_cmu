<?php
include 'include/cn.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php?next=menu.php');
    exit;
}

if (!isset($_SESSION['customer_csrf'])) {
    $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
}

$accountError = '';
$accountSuccess = '';
$customerID = (int) $_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !hash_equals($_SESSION['customer_csrf'], $csrfToken)) {
        $accountError = 'Your session expired. Please reload and try again.';
    } elseif (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $accountError = 'Choose a photo to upload.';
    } elseif ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $accountError = 'The photo upload failed. Please try again.';
    } elseif ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        $accountError = 'The photo must be 2 MB or smaller.';
    } elseif (!is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
        $accountError = 'The selected photo could not be verified. Please try again.';
    } else {
        $tmpPath = $_FILES['profile_photo']['tmp_name'];
        $imageInfo = getimagesize($tmpPath);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if ($imageInfo === false || !isset($allowedTypes[$mimeType])) {
            $accountError = 'Choose a valid JPG, PNG, or WEBP image.';
        } else {
            $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'customers';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                throw new RuntimeException('Could not create the customer photo upload directory.');
            }

            $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
            $relativePath = 'uploads/customers/' . $fileName;
            $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

            if (!move_uploaded_file($tmpPath, $destination)) {
                throw new RuntimeException('Could not save the uploaded customer photo.');
            }

            $stmt = $myCon->prepare('UPDATE Customers SET ProfilePhoto = ? WHERE CustomerID = ?');
            $stmt->bind_param('si', $relativePath, $customerID);
            $stmt->execute();
            $stmt->close();

            $oldPhoto = $_SESSION['customer_photo'] ?? '';
            $_SESSION['customer_photo'] = $relativePath;
            $accountSuccess = 'Profile photo updated.';

            if (is_string($oldPhoto) && preg_match('#^uploads/customers/[a-f0-9]{32}\.(jpg|png|webp)$#', $oldPhoto)) {
                $oldPhotoPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $oldPhoto);
                if (is_file($oldPhotoPath) && !unlink($oldPhotoPath)) {
                    error_log('Could not remove the previous customer profile photo: ' . $oldPhotoPath);
                }
            }
        }
    }
}

$stmt = $myCon->prepare(
    'SELECT CustomerName, Phone, CustomerType, RegisterDate, ProfilePhoto
     FROM Customers
     WHERE CustomerID = ?
     LIMIT 1'
);
$stmt->bind_param('i', $customerID);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_phone'], $_SESSION['customer_photo']);
    header('Location: customer_login.php');
    exit;
}

$_SESSION['customer_name'] = $customer['CustomerName'];
$_SESSION['customer_phone'] = $customer['Phone'];
$_SESSION['customer_photo'] = $customer['ProfilePhoto'] ?? '';
$profilePhoto = $_SESSION['customer_photo'] !== '' ? $_SESSION['customer_photo'] : '';
$memberSince = $customer['RegisterDate'] ? date('d M Y', strtotime($customer['RegisterDate'])) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/head.php'; ?>
    <style>
        body.customer-account-page { min-height: 100vh; background: #f1f3fa; }
        .account-hero { padding: 28px 0 90px; color: #fff; background: linear-gradient(115deg, #17327e, #2851bd); }
        .account-brand { color: #fff; font-weight: 700; text-decoration: none; }
        .account-back { color: #fff; text-decoration: none; }
        .account-content { margin-top: -66px; padding-bottom: 48px; }
        .account-card { height: 100%; border: 0; border-radius: 24px; box-shadow: 0 18px 44px rgba(34, 48, 91, .12); }
        .account-avatar { width: 132px; height: 132px; border-radius: 30px; object-fit: cover; background: #e8edfc; }
        .account-avatar-fallback { display: inline-flex; align-items: center; justify-content: center; color: #27479e; font-size: 58px; }
        .account-preview { width: 90px; height: 90px; border-radius: 24px; object-fit: cover; background: #e8edfc; }
        .account-upload { max-width: 470px; }
    </style>
</head>

<body class="customer-account-page">
    <header class="account-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="index.php" class="account-brand d-flex align-items-center gap-2">
                    <img src="Images/logo-ang.svg" alt="" width="42" height="42" class="rounded-circle bg-white p-1">
                    <span>Angkor Siemreap Restaurant</span>
                </a>
                <a href="menu.php" class="account-back btn btn-outline-light rounded-pill px-3">&larr; Back</a>
            </div>
            <h1 class="display-6 fw-bold mb-1">My account</h1>
            <p class="mb-0 opacity-75">View your profile and update your photo.</p>
        </div>
    </header>

    <main class="container account-content">
        <?php if ($accountError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($accountError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($accountSuccess !== ''): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($accountSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <section class="card account-card">
                    <div class="card-body d-flex flex-column align-items-center text-center p-4 p-lg-5">
                        <?php if ($profilePhoto !== ''): ?>
                            <img src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8'); ?> profile photo" class="account-avatar mb-3">
                        <?php else: ?>
                            <span class="account-avatar account-avatar-fallback mb-3" role="img" aria-label="Default profile photo"><i class="fas fa-user" aria-hidden="true"></i></span>
                        <?php endif; ?>
                        <h2 class="h3 fw-bold mb-1"><?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-secondary mb-4"><?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <hr class="w-100">
                        <?php if ($memberSince !== ''): ?>
                            <p class="text-secondary mb-2">Member since <?= htmlspecialchars($memberSince, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <span class="badge rounded-pill px-3 py-2" style="background:#e8edfc;color:#244497;"><?= htmlspecialchars($customer['CustomerType'] ?? 'Customer', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </section>
            </div>

            <div class="col-12 col-md-8">
                <section class="card account-card">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 fw-bold mb-4">Your profile</h2>
                        <h3 class="h5 fw-bold mb-1">Profile photo</h3>
                        <p class="text-secondary">JPG, PNG or WEBP, up to 2 MB. A square photo works best.</p>

                        <form method="post" enctype="multipart/form-data" class="account-upload">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['customer_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                                <?php if ($profilePhoto !== ''): ?>
                                    <img src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>" alt="Current profile photo" class="account-preview">
                                <?php else: ?>
                                    <span class="account-preview account-avatar-fallback" aria-hidden="true"><i class="fas fa-user"></i></span>
                                <?php endif; ?>
                                <input type="file" id="profile_photo" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Save photo</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>

</html>
