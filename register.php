<?php
session_start();
include("includes/config.php");

if (isset($_POST['create_account'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $session = $_POST['session'];
    $department_id = $_POST['department'];
    $reg = $_POST['reg_no'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $otp = rand(1000, 9999);

    // Check email
    $check = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        // Agar active + verified hai
        if ($row['status'] == 1 && $row['is_verified'] == 1) {
            echo "<script>alert('Email already registered and verified!');window.location.href='register.php'</script>";
            exit;
        } else {
            // Purana record delete karo
            mysqli_query($con, "DELETE FROM users WHERE email='$email'");
        }
    }

    $query = mysqli_query($con, "INSERT INTO `users`(`role`, `name`, `email`, `phone`, `session_id`,`department_id`, `reg_no`, `password`, `otp`,`created_at`) 
    VALUES ('student','$name','$email','$phone','$session','$department_id','$reg','$password','$otp',NOW())");

    if ($query) {
        $_SESSION['otp_email'] = $email;
        echo "<script>alert('Otp Send to your Gmail! Verify Now');window.location.href='otp_verify.php'</script>";
    } else {
        echo "<script>alert('Account Created Failed! Register again');window.location.href='register.php'</script>";
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
    <title>Student Registration | <?= $settingRow['system_name']; ?></title>
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

        .register-section {
            padding: 10px 0;
            background: linear-gradient(to bottom, #ffffff, #dbeafe);
        }

        /* Desktop / Laptop View */
        @media (min-width: 992px) {
            .register-section {
                padding: 0 0;
            }
        }

        /* register  */
        .reg-card {
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

        .input-group-text:hover {
            background: #e9ecef;
        }

        .password-rules {
            display: none;
        }

        .custom-border {
            border-left: 4px solid #1a237e !important;
        }
    </style>

</head>

<body>

    <?php include("includes/header.php") ?>

    <main>
        <section class="register-section">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="text-center mb-4">
                            <img src="admin/uploads/logo/<?= $settingRow['logo'] ?>" alt="Logo" height="60" class="mb-3">
                            <h2 class="fw-bold" id="title">Students Registration</h2>
                            <p class="text-muted" id="subtitle">Join the Digital <?= $settingRow['system_name'] ?></p>
                        </div>

                        <div class="card reg-card p-4 p-md-5 bg-white shadow custom-border">

                            <form action="#" method="POST" id="register">

                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Name" required>
                                    <small class="text-danger" id="nameError"></small>
                                </div>

                                <div class="row">

                                    <!-- Email -->
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Email ID</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="name@example.com" required>
                                        <small class="text-danger" id="emailError"></small>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="10-digit mobile number"
                                            maxlength="10" required>
                                        <small class="text-danger" id="phoneError"></small>
                                    </div>

                                </div>

                                <div class="row">

                                    <!-- Session -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Session</label>
                                        <select class="form-select form-control" id="session" name="session" required>
                                            <option value="" disabled selected>-- Select Session --</option>

                                            <?php
                                            $sessionQuery = mysqli_query($con, "SELECT * FROM academic_session WHERE status=1 ORDER BY id DESC");
                                            while ($sessionRow = mysqli_fetch_assoc($sessionQuery)) {
                                            ?>
                                                <option value="<?= $sessionRow['id']; ?>"><?= $sessionRow['session']; ?> - (<?= $sessionRow['semester']; ?>)</option>
                                            <?php } ?>
                                        </select>
                                        <small class="text-danger" id="sessionError"></small>
                                    </div>

                                    <!-- Reg No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Registration No.</label>
                                        <input type="text" class="form-control" id="reg_no" name="reg_no"
                                            placeholder="<?= $settingRow['company_code']; ?>xxxxxxx" maxlength="10" required>
                                        <small class="text-danger" id="regError"></small>
                                    </div>
                                </div>

                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select class="form-select form-control" id="department" name="department" required>
                                        <option value="" disabled selected>-- Select Department --</option>
                                        <?php 
                                        $department = mysqli_query($con, "SELECT * FROM `department` WHERE `status`=1");
                                        while($departmentRow = mysqli_fetch_assoc($department)) {
                                        ?> 
                                        <option value="<?= $departmentRow['id']; ?>"><?= $departmentRow['department_name']; ?></option>
                                        <?Php } ?>
                                    </select>
                                </div>

                                <!-- Password -->
                                <div class="mb-4">
                                    <label class="form-label">Create Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password"
                                            name="password" placeholder="Min 8 characters" required>

                                        <span class="input-group-text" style="cursor:pointer;"
                                            onclick="togglePassword()">
                                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                        </span>
                                    </div>
                                    <div class="mt-2 small password-rules" id="passwordRules">
                                        <div id="lenErr" class="text-danger">❌ Minimum 8 characters</div>
                                        <div id="upperErr" class="text-danger">❌ One uppercase letter (A-Z)</div>
                                        <div id="lowerErr" class="text-danger">❌ One lowercase letter (a-z)</div>
                                        <div id="numErr" class="text-danger">❌ One number (0-9)</div>
                                        <div id="spErr" class="text-danger">❌ One special character (@$!%*?&)</div>
                                    </div>
                                </div>

                                <button type="submit" name="create_account" id="createBtn" class="btn btn-primary w-100 mb-3 shadow">Create Account</button>

                                <div class="text-center">
                                    <span class="text-muted small">Already have an account?</span>
                                    <a href="student_login.php" class="text-decoration-none small fw-bold ms-1" style="color: #1a237e;">Log in here</a>
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

    <!-- validations  -->
    <script>
        const nameInput = document.getElementById("name");
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");
        const sessionSel = document.getElementById("session");
        const regInput = document.getElementById("reg_no");

        const passInput = document.getElementById("password");
        const passwordRules = document.getElementById("passwordRules");

        const lenErr = document.getElementById("lenErr");
        const upperErr = document.getElementById("upperErr");
        const lowerErr = document.getElementById("lowerErr");
        const numErr = document.getElementById("numErr");
        const spErr = document.getElementById("spErr");

        // Show rules on focus
        passInput.addEventListener("focus", () => {
            passwordRules.style.display = "block";
        });

        // Hide rules if empty on blur
        passInput.addEventListener("blur", () => {
            if (passInput.value === "") {
                passwordRules.style.display = "none";
            }
        });

        /* ================= NAME ================= */
        const nameError = document.getElementById("nameError");

        // Show validation only when user types
        nameInput.addEventListener("input", () => {

            // Allow only letters & space
            nameInput.value = nameInput.value.replace(/[^a-zA-Z\s]/g, '');

            if (nameInput.value.length === 0) {
                nameError.innerText = ""; // empty field → no error
                return;
            }

            if (nameInput.value.length < 3) {
                nameError.innerText = "Minimum 3 letters required";
            } else {
                nameError.innerText = "";
            }
        });

        // Hide error when user leaves field empty
        nameInput.addEventListener("blur", () => {
            if (nameInput.value.length === 0) {
                nameError.innerText = "";
            }
        });


        /* ================= EMAIL ================= */
        emailInput.addEventListener("input", () => {

            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(emailInput.value)) {
                emailError.innerText = "Invalid email format";
            } else {
                emailError.innerText = "";
            }
        });

        // Hide error when user leaves field empty
        emailInput.addEventListener("blur", () => {
            if (emailInput.value.length === 0) {
                emailError.innerText = "";
            }
        });

        /* ================= PHONE ================= */
        phoneInput.addEventListener("input", () => {

            phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '');

            if (!/^[6-9][0-9]{9}$/.test(phoneInput.value)) {
                phoneError.innerText = "Enter valid 10-digit Indian number";
            } else {
                phoneError.innerText = "";
            }
        });
        // Hide error when user leaves field empty
        phoneInput.addEventListener("blur", () => {
            if (phoneInput.value.length === 0) {
                phoneError.innerText = "";
            }
        });

        /* ================= SESSION ================= */
        sessionSel.addEventListener("change", () => {

            if (sessionSel.value == "") {
                sessionError.innerText = "Select session";
            } else {
                sessionError.innerText = "";
            }
        });
        // Hide error when user leaves field empty
        sessionSel.addEventListener("blur", () => {
            if (sessionSel.value.length === 0) {
                sessionError.innerText = "";
            }
        });

        /* ================= REG NO ================= */
        const regError = document.getElementById("regError");

        // Auto add 152 on focus (only if empty)
        regInput.addEventListener("focus", () => {
            if (regInput.value === "") {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }
        });

        regInput.addEventListener("input", () => {

            // Allow only numbers
            regInput.value = regInput.value.replace(/[^0-9]/g, '');

            // Force start with 152
            if (!regInput.value.startsWith("<?= $settingRow['company_code']; ?>")) {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }

            // If only default 152 is there, don't show error
            if (regInput.value === "<?= $settingRow['company_code']; ?>") {
                regError.innerText = "";
                return;
            }

            // Length validation
            if (regInput.value.length != 10) {
                regError.innerText = "Registration must be 10 digits";
            } else if (regInput.value.length === 10) {
                regError.innerText = "";
            }

        });

        // Hide error if user clears field
        regInput.addEventListener("blur", () => {
            if (regInput.value === "" || regInput.value === "<?= $settingRow['company_code']; ?>") {
                regError.innerText = "";
            }
        });

        /* ================= PASSWORD ================= */
        passInput.addEventListener("input", () => {

            passwordRules.style.display = "block";

            const pass = passInput.value;

            // Length
            if (pass.length >= 8) {
                lenErr.className = "text-success";
                lenErr.innerHTML = "✅ Minimum 8 characters";
            } else {
                lenErr.className = "text-danger";
                lenErr.innerHTML = "❌ Minimum 8 characters";
            }

            // Uppercase
            if (/[A-Z]/.test(pass)) {
                upperErr.className = "text-success";
                upperErr.innerHTML = "✅ One uppercase letter (A-Z)";
            } else {
                upperErr.className = "text-danger";
                upperErr.innerHTML = "❌ One uppercase letter (A-Z)";
            }

            // Lowercase
            if (/[a-z]/.test(pass)) {
                lowerErr.className = "text-success";
                lowerErr.innerHTML = "✅ One lowercase letter (a-z)";
            } else {
                lowerErr.className = "text-danger";
                lowerErr.innerHTML = "❌ One lowercase letter (a-z)";
            }

            // Number
            if (/[0-9]/.test(pass)) {
                numErr.className = "text-success";
                numErr.innerHTML = "✅ One number (0-9)";
            } else {
                numErr.className = "text-danger";
                numErr.innerHTML = "❌ One number (0-9)";
            }

            // Special Char
            if (/[@$!%*?&]/.test(pass)) {
                spErr.className = "text-success";
                spErr.innerHTML = "✅ One special character (@$!%*?&)";
            } else {
                spErr.className = "text-danger";
                spErr.innerHTML = "❌ One special character (@$!%*?&)";
            }

        });
    </script>

    <!-- registration number checeking  -->
    <script>
        let regTimer = null;

        /* ================= REG NO ================= */

        regInput.addEventListener("focus", () => {
            if (regInput.value === "") {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }
        });

        regInput.addEventListener("input", function() {

            clearTimeout(regTimer);

            // Allow only numbers
            regInput.value = regInput.value.replace(/[^0-9]/g, '');

            // Must start with 152
            if (!regInput.value.startsWith("<?= $settingRow['company_code']; ?>")) {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }

            // Length validation
            if (regInput.value.length !== 10) {
                regError.innerText = "Registration must be 10 digits";
                regError.className = "text-danger";
                return;
            }

            regError.innerText = "Checking...";
            regError.className = "text-warning";

            // Delay 500ms before DB check
            regTimer = setTimeout(() => {

                fetch("ajax/check_reg.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "reg_no=" + encodeURIComponent(regInput.value)
                    })
                    .then(res => res.text())
                    .then(data => {

                        if (data.trim() === "exists") {
                            regError.innerText = "Registration already active";
                            regError.className = "text-danger";
                        } else if (data.trim() === "available") {
                            regError.innerText = "";
                            regError.className = "text-success";
                        }

                    });

            }, 500);

        });
    </script>

    <!-- email checking  -->
    <script>
        const emailError = document.getElementById("emailError");

        let emailTimer = null;

        emailInput.addEventListener("input", function() {

            clearTimeout(emailTimer);

            let email = this.value;

            // Basic format check first
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(email)) {
                emailError.innerText = "Invalid email format";
                emailError.className = "text-danger";
                return;
            }

            emailError.innerText = "Checking...";
            emailError.className = "text-warning";

            // Delay 500ms (avoid too many requests)
            emailTimer = setTimeout(() => {

                fetch("ajax/check_email.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "email=" + encodeURIComponent(email)
                    })

                    .then(res => res.text())
                    .then(data => {

                        if (data === "exists") {
                            emailError.innerText = "Email already registered";
                            emailError.className = "text-danger";
                        } else if (data === "available") {
                            emailError.innerText = "";
                            emailError.className = "text-success";
                        }

                    });

            }, 500); // wait before checking

        });
    </script>

    <!-- phone checking  -->
    <script>
        let phoneTimer = null;

        phoneInput.addEventListener("input", function() {

            clearTimeout(phoneTimer);

            let phone = this.value.replace(/[^0-9]/g, '');

            if (!/^[6-9][0-9]{9}$/.test(phone)) {
                phoneError.innerText = "Enter valid 10-digit Indian number";
                return;
            }

            phoneError.innerText = "Checking...";
            phoneError.className = "text-warning";

            phoneTimer = setTimeout(() => {

                fetch("ajax/check_phone.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "phone=" + encodeURIComponent(phone)
                    })
                    .then(res => res.text())
                    .then(data => {

                        if (data === "exists") {
                            phoneError.innerText = "Phone already registered";
                            phoneError.className = "text-danger";
                        } else {
                            phoneError.innerText = "";
                        }

                    });

            }, 500);

        });
    </script>

    <!-- stop submission when error -->
    <script>
        document.getElementById("register").addEventListener("submit", function(e) {

            if (emailError.innerText.includes("already")) {

                e.preventDefault();
                alert("This email is already registered!");
            }

            if (
                lenErr.classList.contains("text-danger") ||
                upperErr.classList.contains("text-danger") ||
                lowerErr.classList.contains("text-danger") ||
                numErr.classList.contains("text-danger") ||
                spErr.classList.contains("text-danger")
            ) {
                e.preventDefault();
                alert("Please create strong password!");
            }
            if (
                nameError.innerText !== "" ||
                emailError.innerText !== "" ||
                phoneError.innerText !== "" ||
                sessionError.innerText !== "" ||
                regError.innerText !== ""
            ) {
                e.preventDefault();
                alert("Please fix errors before submitting!");
            }

        });
    </script>

</body>

</html>