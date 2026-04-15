<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $userQuery = mysqli_query($con, "SELECT * FROM users WHERE id='$user_id' AND role='student'");
    $userRow = mysqli_fetch_assoc($userQuery);
}

if (isset($_POST['update_student'])) {
    $user_id = $_GET['user_id'];

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $session = $_POST['session'];
    $reg = $_POST['reg_no'];

    $update = mysqli_query($con, "
        UPDATE users SET
            name = '$name',
            email = '$email',
            phone = '$phone',
            department_id = '$department',
            session_id = '$session',
            reg_no = '$reg',
            updated_at = NOW()
        WHERE id = '$user_id' AND role='student'
    ");

    if ($update) {
        echo "<script>alert('Student Updated Successfully'); window.location.href='manage_students.php'</script>";
    } else {
        echo "<script>alert('Something went wrong');</script>";
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
    <title>Edit Students | <?= $settingRow['system_name']; ?></title>
    <!-- Favicon For .ico file -->
    <link rel="icon" href="uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --main-bg: #f4f7fe;
            --primary-indigo: #1a237e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            overflow-x: hidden;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s ease;
        }

        /* Tab Custom Styling */
        .nav-tabs {
            border: none;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: 1px grey solid;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: #e2e6f5;
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link.active {
            /* background-color: var(--primary-indigo); */
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: white !important;
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        @media (max-width: 576px) {
            .nav-tabs .nav-item {
                width: 100%;
            }

            .nav-tabs .nav-link {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
                display: block;
            }
        }

        /* Card Styling */
        .manage-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #4a5568;
            margin-top: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-indigo);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0);
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>

</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid">
            <h4 class="fw-bold mb-4 custom-underline">Edit Student</h4>

            <ul class="nav nav-tabs" id="studentTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab"><i class="bi bi-person-plus me-2"></i>Edit Student</button>
                </li>
            </ul>

            <div class="tab-content" id="studentTabsContent">

                <div class="tab-pane fade show active" id="register" role="tabpanel">
                    <div class="card manage-card p-4">
                        <h5 class="fw-bold mb-4 custom-underline">Edit of <span class="text-danger"><?= $userRow['name']; ?></span></h5>

                        <form action="#" method="POST" id="studentForm">

                            <div class="row g-3">

                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" value="<?= $userRow['name']; ?>">
                                    <small class="text-danger" id="nameError"></small>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" value="<?= $userRow['email']; ?>">
                                    <small class="text-danger" id="emailError"></small>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="10-digit mobile number"
                                        maxlength="10" pattern="[0-9]{10}" value="<?= $userRow['phone']; ?>">
                                    <small class="text-danger" id="phoneError"></small>
                                </div>

                                <!-- Session -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Department</label>
                                    <select class="form-select form-control" id="department" name="department">
                                        <option value="" disabled selected>-- Select Department --</option>
                                        <?php
                                        $departmentQuery = mysqli_query($con, "SELECT * FROM department WHERE status=1 ");
                                        while ($departmentRow = mysqli_fetch_assoc($departmentQuery)) {
                                        ?>
                                            <option value="<?= $departmentRow['id']; ?>"
                                                <?= ($departmentRow['id'] == $userRow['department_id']) ? 'selected' : ''; ?>>
                                                <?= $departmentRow['department_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Session -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Session</label>
                                    <select class="form-select form-control" id="session" name="session">
                                        <option value="" disabled selected>-- Select Session --</option>

                                        <?php
                                        $sessionQuery = mysqli_query($con, "SELECT * FROM academic_session WHERE status=1 ORDER BY id DESC");
                                        while ($sessionRow = mysqli_fetch_assoc($sessionQuery)) {
                                        ?>
                                            <option value="<?= $sessionRow['id']; ?>"
                                                <?= ($sessionRow['id'] == $userRow['session_id']) ? 'selected' : ''; ?>>
                                                <?= $sessionRow['session']; ?> - (<?= $sessionRow['semester']; ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-danger" id="sessionError"></small>
                                </div>

                                <!-- Registration Number -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Registration Number</label>
                                    <input type="text" class="form-control" id="reg_no" name="reg_no"
                                        placeholder="<?= $settingRow['company_code']; ?>xxxxxxx" maxlength="10" value="<?= $userRow['reg_no']; ?>">
                                    <small class="text-danger" id="regError"></small>
                                </div>

                                <!-- Submit -->
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="update_student" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Update Student
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- validations  -->
    <script>
        const nameInput = document.getElementById("name");
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");
        const sessionSel = document.getElementById("session");

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
                nameError.innerText = "Minimum 3 letters ";
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
    </script>

    <!-- registration number checeking  -->
    <script>
        const regInput = document.getElementById("reg_no");
        const regError = document.getElementById("regError");
        let regTimer = null;

        regInput.addEventListener("focus", function() {
            if (regInput.value === "") {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }
        });

        regInput.addEventListener("input", function() {

            clearTimeout(regTimer);

            // Allow only numbers
            regInput.value = regInput.value.replace(/[^0-9]/g, '');

            // Force start with company code
            if (!regInput.value.startsWith("<?= $settingRow['company_code']; ?>")) {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }

            // Hide error if user clears field
            regInput.addEventListener("blur", () => {
                if (regInput.value === "" || regInput.value === "<?= $settingRow['company_code']; ?>") {
                    regError.innerText = "";
                }
            });

            // Length check
            if (regInput.value.length !== 10) {
                regError.innerText = "Registration must be 10 digits";
                regError.className = "text-danger";
                return;
            }

            regError.innerText = "Checking...";
            regError.className = "text-warning";

            regTimer = setTimeout(function() {

                fetch("ajax/check_reg.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "reg_no=" + encodeURIComponent(regInput.value)
                    })
                    .then(response => response.text())
                    .then(data => {

                        if (data.trim() === "exists") {
                            regError.innerText = "Registration already registered";
                            regError.className = "text-danger";
                        } else {
                            regError.innerText = "";
                        }

                    })
                    .catch(() => {
                        regError.innerText = "Server error";
                        regError.className = "text-danger";
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
                phoneError.className = "text-danger";
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
                            phoneError.className = "text-success";
                        }

                    });

            }, 500);

        });
    </script>

    <!-- stop submission when error -->
    <script>
        document.getElementById("studentForm").addEventListener("submit", function(e) {

            if (emailError.innerText.includes("already")) {

                e.preventDefault();
                alert("This email is already registered!");
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