<?php include 'include/cn.php';
if (isset($_REQUEST['out'])) {
    session_destroy();
    header("Location: login.php");
}

if (isset($_POST['login'])) {
    $username = trim($_POST['Username']);
    $password = $_POST['Password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $result = mysqli_query($myCon, "SELECT * FROM Employees WHERE Username='$username'");

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['PasswordHash'])) {
                $_SESSION['user_id'] = $row['EmployeeID'];
                $_SESSION['FullName'] = $row['FullName'];
                $_SESSION['Username'] = $row['Username'];
                $_SESSION['RoleID'] = $row['RoleID'];

                if ($row['RoleID'] == '2') {
                    header("Location: index.php");
                } elseif ($row['RoleID'] == '1') {
                    header("Location: administrator/employee.php");
                } 
                exit;
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="icon" type="image/png" href="Images/logo-ang.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a, #1e1b4b, #312e81, #7c3aed);
            background-size: 400% 400%;
            animation: bgMove 15s ease infinite;
            position: relative;
            padding: 20px;
        }

        /* Animated Background */
        @keyframes bgMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating Blur Circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .5;
            animation: float 8s ease-in-out infinite;
        }

        .circle1 {
            width: 300px;
            height: 300px;
            background: #8b5cf6;
            top: -100px;
            left: -100px;
        }

        .circle2 {
            width: 280px;
            height: 280px;
            background: #06b6d4;
            bottom: -100px;
            right: -100px;
            animation-delay: 2s;
        }

        .circle3 {
            width: 200px;
            height: 200px;
            background: #ec4899;
            bottom: 30%;
            left: 10%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-30px);
            }
        }

        /* Login Card */
        .login-container {
            width: 100%;
            max-width: 430px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 35px;
            box-shadow:
                0 10px 40px rgba(0, 0, 0, .35),
                inset 0 1px 1px rgba(255, 255, 255, .15);
            position: relative;
            overflow: hidden;
            transition: .4s;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow:
                0 20px 50px rgba(0, 0, 0, .4),
                inset 0 1px 1px rgba(255, 255, 255, .2);
        }

        /* Glow Border */
        .login-card::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 30px;
            padding: 2px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4, #ec4899);
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: .6;
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        /* .logo{
    width:55px;
    height:55px;
    margin:auto;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#8b5cf6,#06b6d4);
    box-shadow:0 10px 30px rgba(139,92,246,.4);
    margin-bottom:20px;
    animation:pulse 3s infinite;
} */

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .logo img {
            width: 300px;
            height: 160px;
            color: #fff;
        }

        .login-header h1 {
            color: #fff;
            font-size: 1.3rem;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .login-header p {
            color: rgba(255, 255, 255, .7);
            font-size: 13px;
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 9px 15px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: .3s;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, .45);
        }

        .form-control:focus {
            border-color: #8b5cf6;
            background: rgba(255, 255, 255, .12);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, .2);
        }

        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, .7);
            font-size: 18px;
        }

        /* Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, .75);
            font-size: 14px;
        }

        .remember input {
            accent-color: #8b5cf6;
        }

        .forgot {
            color: #c4b5fd;
            text-decoration: none;
            font-size: 14px;
            transition: .3s;
        }

        .forgot:hover {
            color: #fff;
        }

        /* Button */
        .login-btn {
            width: 100%;
            border: none;
            height: 45px;
            border-radius: 18px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            position: relative;
            overflow: hidden;
            transition: .4s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, .45);
        }

        .login-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, .35),
                    transparent);
            transition: .6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 15px;
            gap: 10px;
        }

        .divider span {
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, .15);
        }

        /* Social Buttons */
        .social-login {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            border: 1px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .3s;
        }

        .social-btn:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, .15);
        }

        .social-btn svg {
            width: 18px;
            height: 18px;
            fill: #fff;
        }

        /* Footer */
        .signup {
            margin-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .signup a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        /* =========================
   RESPONSIVE DESIGN
========================= */

        /* Laptop */
        @media (max-width: 1024px) {

            .login-container {
                max-width: 380px;
            }

            .login-card {
                padding: 28px;
            }

            .circle1 {
                width: 240px;
                height: 240px;
            }

            .circle2 {
                width: 220px;
                height: 220px;
            }

            .circle3 {
                width: 160px;
                height: 160px;
            }

        }

        /* Tablet */
        @media (max-width: 768px) {

            body {
                padding: 16px;
                overflow-y: auto;
            }

            .login-container {
                max-width: 340px;
            }

            .login-card {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .logo img {
                width: 100px;
                height: 60px;
            }

            .login-header {
                margin-bottom: 20px;
            }

            .login-header h1 {
                font-size: 1.1rem;
            }

            .login-header p {
                font-size: 12px;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .form-control {
                padding: 12px 14px;
                font-size: 12px;
                border-radius: 14px;
            }

            .remember,
            .forgot {
                font-size: 12px;
            }

            .login-btn {
                height: 42px;
                font-size: 13px;
                border-radius: 14px;
            }

            .social-btn {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }

            .social-btn svg {
                width: 16px;
                height: 16px;
            }

            .signup {
                margin-top: 20px;
                font-size: 12px;
            }

            .divider {
                margin: 14px 0;
            }

            .divider span {
                font-size: 11px;
            }

            .circle {
                filter: blur(70px);
                opacity: .35;
            }

        }

        /* Mobile */
        @media (max-width: 480px) {

            body {
                padding: 12px;
                align-items: center;
            }

            .login-container {
                max-width: 80%;
            }

            .login-card {
                padding: 20px 16px;
                border-radius: 20px;
            }

            .logo img {
                width: 85px;
                height: 50px;
            }

            .login-header {
                margin-bottom: 10px;
            }

            .login-header h1 {
                font-size: 1rem;
                margin-bottom: 3px;
            }

            .login-header p {
                font-size: 11px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            .form-control {
                padding: 10px 12px;
                font-size: 11px;
                border-radius: 12px;
            }

            .toggle-password {
                right: 12px;
                font-size: 15px;
            }

            .form-options {
                gap: 8px;
                margin-bottom: 12px;
            }

            .remember,
            .forgot {
                font-size: 11px;
            }

            .login-btn {
                height: 38px;
                font-size: 12px;
                border-radius: 12px;
            }

            .divider {
                gap: 8px;
                margin: 12px 0;
            }

            .divider span {
                font-size: 10px;
            }

            .social-login {
                gap: 8px;
            }

            .social-btn {
                width: 36px;
                height: 36px;
                border-radius: 10px;
            }

            .social-btn svg {
                width: 14px;
                height: 14px;
            }

            .signup {
                margin-top: 16px;
                font-size: 11px;
            }

            .circle1 {
                width: 180px;
                height: 180px;
            }

            .circle2 {
                width: 160px;
                height: 160px;
            }

            .circle3 {
                width: 120px;
                height: 120px;
            }

        }

        /* Extra Small Mobile */
        @media (max-width: 360px) {

            .login-card {
                padding: 16px 14px;
            }

            .logo img {
                width: 75px;
                height: 45px;
            }

            .login-header h1 {
                font-size: .9rem;
            }

            .form-control {
                padding: 9px 10px;
                font-size: 10px;
            }

            .login-btn {
                height: 36px;
                font-size: 11px;
            }

            .social-btn {
                width: 32px;
                height: 32px;
            }

        }
    </style>
</head>

<body>

    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
    <div class="circle circle3"></div>

    <div class="login-container">

        <div class="login-card">

            <div class="login-header">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="logo">
                    <!-- <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg> -->
                    <img src="../img/logo-ang.png" alt="">
                </div>

                <h1>Welcome To Angkor Siem Reap</h1>
                <p>Login to continue your journey</p>

            </div>

            <form method="POST">
                <div class="form-group">
                    <input type="username" name="Username" class="form-control" placeholder="Enter your username" required>
                </div>

                <div class="form-group password-wrapper">
                    <input type="password" name="Password" class="form-control" id="Password" placeholder="Enter your password" required>

                    <span class="toggle-password" onclick="togglePassword()">
                        👁
                    </span>
                </div>

                <div class="form-options">
                    <!-- <label class="remember">
                        <input type="checkbox">
                        Remember me
                    </label> -->

                    <!-- <a href="#" class="forgot">Forgot Password?</a> -->
                </div>

                <button type="submit" name="login" class="login-btn">
                    <!-- <a href="#" name="login" type="submit">Sign In</a> -->
                    Sign In

                </button>

            </form>

            <!-- <div class="divider">
                <span>OR CONTINUE WITH</span>
            </div> -->

            <!-- <div class="social-login">

                <button class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M21.35 11.1H12v2.98h5.39c-.23 1.5-1.8 4.4-5.39 4.4-3.24 0-5.88-2.68-5.88-5.98s2.64-5.98 5.88-5.98c1.84 0 3.07.79 3.77 1.46l2.57-2.49C16.72 3.98 14.6 3 12 3 6.92 3 2.8 7.03 2.8 12s4.12 9 9.2 9c5.3 0 8.8-3.72 8.8-8.97 0-.6-.06-1.05-.15-1.43z" />
                    </svg>
                </button>

                <button class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.88 3.77-3.88 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.62.77-1.62 1.56V12h2.76l-.44 2.89h-2.32v6.99A10 10 0 0 0 22 12z" />
                    </svg>
                </button>

                <button class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M16.37 1.43c0 1.14-.45 2.23-1.22 3.04-.84.89-2.2 1.57-3.39 1.48-.15-1.11.44-2.3 1.19-3.08.83-.88 2.25-1.5 3.42-1.44zM20.91 17.2c-.59 1.36-.88 1.97-1.64 3.19-1.06 1.69-2.55 3.79-4.39 3.81-1.63.02-2.05-1.05-4.27-1.04-2.22.01-2.68 1.06-4.31 1.03-1.84-.02-3.25-1.91-4.31-3.6C-1.2 15.55.06 8.77 3.96 6.55c1.4-.8 3.04-1.28 4.59-1.3 1.76-.03 3.42 1.19 4.49 1.19 1.07 0 3.07-1.47 5.17-1.25.88.04 3.34.36 4.92 2.67-.13.08-2.94 1.72-2.91 5.12.03 4.06 3.57 5.41 3.61 5.43z" />
                    </svg>
                </button>

            </div> -->

            <!-- <div class="signup">
                Don’t have an account?
                <a href="#">Create Account</a>
            </div> -->

        </div>

    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById("Password");

            if (password.type === "Password") {
                password.type = "text";
            } else {
                password.type = "ផassword";
            }
        }
    </script>

</body>

</html>