<?php
session_start();
include("includes/config.php");

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    /* ================= USER LOGIN ================= */
    $userQuery = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($userQuery) > 0) {
        $userData = mysqli_fetch_assoc($userQuery);

        // ✅ password_verify for user
        if (password_verify($password, $userData['password'])) {

            // 🔐 email verified / active check
            if ($userData['status'] == 1 && $userData['is_verified'] == 1 && $userData['role'] == 'student') {
                $_SESSION['user'] = $email;
                // ✅ Update last login time
                mysqli_query($con, "UPDATE users SET last_login = NOW() WHERE email = '".$userData['email']."'");
                echo "<script>alert('User Login Successful');window.location.href='user/dashboard.php'</script>";
                exit;
            } else {
                echo "<script>alert('⛔ Account inactive. Contact admin');window.location.href='index.php'</script>";
                exit;
            }
        } else {
            echo "<script>alert('❌ Invalid email or password');window.location.href='index.php'</script>";
            exit;
        }
    }

    /* ================= NO ACCOUNT FOUND ================= */

    echo "<script>alert('❌ Invalid email or password');window.location.href='index.php'</script>";
}

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | <?= $settingRow['system_name']; ?></title>
    <!-- Favicon For .ico file -->
    <link rel="icon" href="admin/uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #1a237e;
            /* Deep Indigo */
            --accent-color: #00c853;
            /* Fresh Green */
            --text-dark: #2c3e50;
            --bg-light: #f8f9fc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        .login-section {
            padding: 40px 0 130px 0;
            background: linear-gradient(to bottom, #ffffff, #dbeafe);
        }

        /* Desktop / Laptop View */
        @media (min-width: 992px) {
            .login-section {
                padding: 23px  0;
            }
        }

        /* login  */
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .btn-primary {
            background-color: #1a237e;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #0d1440;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }
        .custom-border {
            border-left: 4px solid #1a237e !important;
        }
    </style>

</head>

<body>

    <?php include("includes/header.php") ?>

    <main>
        <section class="login-section">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="text-center mb-4">
                            <img src="admin/uploads/logo/<?= $settingRow['logo'] ?>" alt="Logo" height="60" class="mb-3">
                            <h2 class="fw-bold" id="title">Students Login</h2>
                            <p class="text-muted" id="subtitle">Welcome back to the <?= $settingRow['system_name']; ?> Portal</p>
                        </div>

                        <div class="card login-card p-4 p-md-5 bg-white custom-border">

                            <form action="#" method="POST" id="login">

                                <div class="mb-3">
                                    <label class="form-label">Email ID</label>
                                    <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                        <span class="input-group-text" style="cursor:pointer;"
                                            onclick="togglePassword()">
                                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                        </span>
                                    </div>
                                </div>

                                <button type="submit" name="login" class="btn btn-primary w-100 mb-3 shadow">Login</button>

                                <div class="text-center">
                                    <span class="text-muted small">No account yet?</span>
                                    <a href="register.php" class="text-decoration-none small fw-bold ms-1" style="color: #1a237e;">Create Account</a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include("includes/footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- show and hide password  -->
    <script>
        function togglePassword() {

            let password = document.getElementById("password");
            let eye = document.getElementById("eyeIcon");

            if (password.type === "password") {
                password.type = "text";
                eye.classList.remove("bi-eye-slash");
                eye.classList.add("bi-eye");
            } else {
                password.type = "password";
                eye.classList.remove("bi-eye");
                eye.classList.add("bi-eye-slash");
            }
        }
    </script>

</body>

</html>