<?php
session_start();
include("includes/config.php");

$error = "";

if (!isset($_SESSION['otp_email'])) {
    header("Location: register.php");
    exit;
}
$email = $_SESSION['otp_email'];

/* ===== OTP VERIFY ===== */
if (isset($_POST['verify_otp'])) {

    // Combine 4 boxes
    $otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'];

    $check = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) == 1) {
        $row = mysqli_fetch_assoc($check);
        if ($row['otp'] == $otp) {
            // Verify account
            mysqli_query($con, "UPDATE users SET is_verified='1', status='1', otp=NULL WHERE email='$email'");

            unset($_SESSION['otp_email']);
            echo "<script>alert('Otp Verified! Login Now');window.location.href='student_login.php'</script>";
            exit;
        } else {
            $error = "Wrong OTP!";
        }
    } else {
        $error = "User not found!";
    }
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
    <title>OTP Verification | <?= $settingRow['system_name']; ?></title>
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

        .otp_section {
            padding: 10px 0;
            background: linear-gradient(to bottom, #ffffff, #dbeafe);
        }

        /* Desktop / Laptop View */
        @media (min-width: 992px) {
            .otp_section {
                padding: 67px 0;
            }
        }

        /* register  */
        .otp_card {
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

        /* otp  */
        .otp-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .otp-input {
            width: 50px;
            height: 60px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            margin: 0 5px;
        }

        .otp-input:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .btn-verify {
            background-color: #1a237e;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>

</head>

<body>

    <?php include("includes/header.php") ?>

    <main>
        <section class="otp_section">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="text-center mb-4">
                            <div class="bg-light d-inline-block p-3 rounded-circle mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#1a237e" class="bi bi-shield-lock" viewBox="0 0 16 16">
                                    <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.481 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM8 0c.662 0 1.77.249 2.813.525a61.11 61.11 0 0 1 3.333.98c.778.256 1.326.93 1.326 1.748 0 5.056-2.256 8.301-4.441 10.418a11.3 11.3 0 0 1-2.286 1.728l-.099.059a.5.5 0 0 1-.628 0l-.099-.059a11.303 11.303 0 0 1-2.286-1.728C1.256 11.1 0 7.856 0 2.8c0-.818.548-1.492 1.326-1.748.966-.318 2.314-.693 3.333-.98C5.701.248 6.81 0 8 0z" />
                                    <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z" />
                                </svg>
                            </div>
                            <h2 class="fw-bold" id="title">OTP Verification</h2>
                            <p class="text-muted" id="subtitle">We've sent a 4-digit code to your registered email address.</p>
                        </div>

                        <div class="card otp_card p-4 p-md-5 bg-white">

                            <form action="#" method="POST" id="otp">

                                <div class="d-flex justify-content-center mb-4">
                                    <input type="text" name="otp1" class="otp-input otp" maxlength="1" required>
                                    <input type="text" name="otp2" class="otp-input otp" maxlength="1" required>
                                    <input type="text" name="otp3" class="otp-input otp" maxlength="1" required>
                                    <input type="text" name="otp4" class="otp-input otp" maxlength="1" required>
                                </div>

                                <button type="submit" name="verify_otp" class="btn btn-primary btn-verify w-100 shadow mb-3">Verify Account</button>

                                <div class="text-center">
                                    <p class="small text-muted mb-0">Didn't receive the code?</p>
                                    <a href="#" class="small fw-bold text-decoration-none" style="color: #1a237e;">Resend OTP</a>
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

    <script>
        const otpInputs = document.querySelectorAll(".otp");

        otpInputs.forEach((input, index) => {

            input.addEventListener("input", () => {

                // Allow only digits
                input.value = input.value.replace(/[^0-9]/g, '');

                // Move to next box
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener("keydown", (e) => {

                // Backspace → move back
                if (e.key === "Backspace" && input.value === "" && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

        });
    </script>

</body>

</html>