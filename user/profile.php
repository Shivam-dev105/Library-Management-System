<?php
session_start();
include('includes/config.php');

// check student login
if (!isset($_SESSION['user'])) {
    header('location:../index.php');
    exit();
}

$userEmail = $_SESSION['user'];

if (isset($_POST['update_profile'])) {
    $userEmail = $_SESSION['user'];
    $name = $_POST['name'];

    $old_img = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $tmp   = $_FILES['image']['tmp_name'];

    if (!empty($image)) {

        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed = array("jpg", "jpeg", "png", "webp");

        if (in_array($ext, $allowed)) {

            $newName = time() . "." . $ext;
            $path = "uploads/profile/" . $newName;

            if (move_uploaded_file($tmp, $path)) {
                $finalImage = $newName;
                // Delete old image
                if (!empty($old_img) && file_exists("uploads/profile/" . $old_img)) {
                    unlink("uploads/profile/" . $old_img);
                }
            } else {
                $finalImage = $old_img;
            }
        } else {
            echo "<script>alert('Only JPG, PNG, WEBP allowed'); window.location.href='profile.php#edit-profile';</script>";
            exit();
        }
    } else {
        $finalImage = $old_img;
    }

    $profile_data = mysqli_query($con, "UPDATE `users` SET 
    `name`='$name',
    `profile_image`='$finalImage',
    `updated_at`=NOW()
    WHERE `email`='$userEmail'");

    if ($profile_data) {
        echo "<script>alert('Profile Updated successfully.'); window.location.href='profile.php#edit-profile';</script>";
    } else {
        echo "<script>alert('Profile Updated failed. Please try again'); window.location.href='profile.php#edit-profile';</script>";
    }
}

// change password 
if (isset($_POST['update_password'])) {
    $userEmail = $_SESSION['user'];
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current password from DB
    $checkQuery = mysqli_query($con, "SELECT password FROM users WHERE email='$userEmail'");
    $row = mysqli_fetch_assoc($checkQuery);

    if (!$row) {
        echo "<script>alert('User not found'); window.location='profile.php';</script>";
        exit();
    }

    $dbPassword = $row['password'];

    // If you are using password_hash (recommended)
    if (!password_verify($current, $dbPassword)) {
        echo "<script>alert('Current password is incorrect'); window.location='profile.php#security';</script>";
        exit();
    }

    if ($new !== $confirm) {
        echo "<script>alert('New passwords do not match'); window.location='profile.php#security';</script>";
        exit();
    }

    // Hash new password
    $hashedPassword = password_hash($new, PASSWORD_DEFAULT);

    $passUpdate = mysqli_query($con, "UPDATE users SET password='$hashedPassword', updated_at=NOW() WHERE email='$userEmail'");

    if ($passUpdate) {
        echo "<script>alert('Password Updated Successfully'); window.location='profile.php#security';</script>";
    } else {
        echo "<script>alert('Password Update Failed'); window.location='profile.php#security';</script>";
    }
}

// fetch system settings 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | <?= $settingRow['system_name']; ?></title>
    <link rel="icon" href="../admin/uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --main-bg: #f4f7fe;
            --primary-color: #1a237e;
            --sidebar-width: 250px;
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
            position: relative;
            z-index: 1;
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
            overflow: hidden;
        }

        .profile-header-card {
            background: linear-gradient(135deg, var(--primary-color), #3949ab);
            color: white;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .profile-img-large {
            width: 120px;
            height: 120px;
            border: 4px solid rgba(255, 255, 255, 0.4);
            object-fit: cover;
            background-color: #fff;
        }

        /* Nav Tabs Custom Styling */
        .profile-tabs {
            border-bottom: 2px solid #edf2f7;
            gap: 15px;
            padding: 0 15px;
        }

        .profile-tabs .nav-link {
            color: #718096;
            font-weight: 600;
            padding: 15px 5px;
            border: none;
            border-bottom: 3px solid transparent;
            background: transparent;
            transition: all 0.3s ease;
        }

        .profile-tabs .nav-link:hover {
            color: var(--primary-color);
        }

        .profile-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #4a5568;
            margin-bottom: 6px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
            border-color: var(--primary-color);
        }

        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">

        <?php include("includes/header.php"); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Account Settings</h4>
                <p class="text-muted small mb-0">Manage your profile information and security</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="custom-card profile-header-card p-4 text-center mb-4">
                    <i class="bi bi-person-bounding-box position-absolute" style="font-size: 150px; right: -30px; top: -20px; opacity: 0.1;"></i>

                    <?php
                    $user = mysqli_query($con, "SELECT * FROM `users` WHERE `email`='$userEmail'");
                    $userRow = mysqli_fetch_assoc($user);

                    $profileImage = !empty($userRow['profile_image'])
                        ? "uploads/profile/" . $userRow['profile_image']
                        : "https://ui-avatars.com/api/?name=" . urlencode($userRow['name']) . "&background=1a237e&color=fff&size=128";
                    ?>

                    <img id="profilePreview"
                        src="<?php echo $profileImage; ?>"
                        class="profile-img-large rounded-circle mb-3 shadow-sm"
                        style="cursor:pointer;">

                    <h4 class="fw-bold mb-1"><?= $userRow['name'] ?></h4>

                    <?php
                    $id = $userRow['department_id'];
                    if (!empty($id)) {
                        $department = mysqli_query($con, "SELECT * FROM `department` WHERE `id`='$id'");
                        if (mysqli_num_rows($department) > 0) {
                            $departmentRow = mysqli_fetch_assoc($department);
                            echo '<p class="text-white-50 mb-3">' . $departmentRow['department_name'] . '</p>';
                        } else {
                            echo '<p class="text-warning small mb-3">Update your department</p>';
                        }
                    } else {
                        echo '<p class="text-warning small mb-3">Update your department</p>';
                    }
                    ?>
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Academic Details</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex justify-content-between">
                            <span class="text-white-50 small"><i class="bi bi-card-text me-2"></i>Registration No.</span>
                            <span class="fw-semibold white-50"><?= $userRow['reg_no'] ?></span>
                        </li>
                        <?php
                        $id = $userRow['session_id'];
                        $session = mysqli_query($con, "SELECT * FROM `academic_session` WHERE `id`='$id'");
                        $sessionRow = mysqli_fetch_assoc($session);
                        ?>
                        <li class="mb-3 d-flex justify-content-between">
                            <span class="text-white-50 small"><i class="bi bi-mortarboard me-2"></i>Semester</span>
                            <span class="fw-semibold white-50"><?= $sessionRow['semester'] ?? 'N/A'; ?></span>
                        </li>
                        <li class="mb-0 d-flex justify-content-between">
                            <span class="text-white-50 small"><i class="bi bi-calendar3 me-2"></i>Session</span>
                            <span class="fw-semibold white-50"><?= $sessionRow['session'] ?? 'N/A'; ?></span>
                        </li>
                    </ul>
                    <hr class="border-light opacity-25">
                    <div class="text-start">
                        <p class="mb-1 text-white"><i class="bi bi-calendar-event me-2"></i>
                            <span class="fw-bold">Last Login:</span>
                            <span class="small">
                                <?php
                                if (!empty($userRow['last_login'])) {
                                    echo date("d M Y, h:i A", strtotime($userRow['last_login']));
                                }
                                ?>
                            </span>
                        </p>
                    </div>
                </div>

                <!-- <div class="custom-card p-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Academic Details</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex justify-content-between">
                            <span class="text-muted small"><i class="bi bi-card-text me-2"></i>Registration No.</span>
                            <span class="fw-semibold text-dark"><?= $userRow['reg_no'] ?></span>
                        </li>
                        <?php
                        $id = $userRow['session_id'];
                        $session = mysqli_query($con, "SELECT * FROM `academic_session` WHERE `id`='$id'");
                        $sessionRow = mysqli_fetch_assoc($session);
                        ?>
                        <li class="mb-3 d-flex justify-content-between">
                            <span class="text-muted small"><i class="bi bi-mortarboard me-2"></i>Semester</span>
                            <span class="fw-semibold text-dark"><?= $sessionRow['semester'] ?? 'N/A'; ?></span>
                        </li>
                        <li class="mb-0 d-flex justify-content-between">
                            <span class="text-muted small"><i class="bi bi-calendar3 me-2"></i>Session</span>
                            <span class="fw-semibold text-dark"><?= $sessionRow['session'] ?? 'N/A'; ?></span>
                        </li>
                    </ul>
                </div> -->
            </div>

            <div class="col-lg-8">
                <div class="custom-card">
                    <div class="bg-white pt-3 px-3">
                        <ul class="nav nav-tabs profile-tabs" id="accountTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" type="button" role="tab">
                                    <i class="bi bi-person-lines-fill me-2"></i>Personal Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                    <i class="bi bi-shield-lock me-2"></i>Security
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4 pt-4">
                        <div class="tab-content" id="accountTabsContent">

                            <div class="tab-pane fade show active" id="edit-profile" role="tabpanel">
                                <div id="alertPlaceholder" class="mb-3"></div>
                                <form action="#" method="POST" enctype="multipart/form-data">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= $userRow['name']; ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" name="email" class="form-control bg-light"
                                                value="<?= $userRow['email']; ?>"
                                                readonly
                                                style="cursor: not-allowed;"
                                                onclick="showAlert(event, 'Email Address')">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control bg-light"
                                                value="<?= $userRow['phone']; ?>"
                                                readonly
                                                style="cursor: not-allowed;"
                                                onclick="showAlert(event, 'Phone Number')">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Update Profile Picture</label>
                                            <input type="hidden" name="old_image" value="<?php echo $userRow['profile_image']; ?>">
                                            <input class="form-control" type="file" name="image" accept=".jpg, .jpeg, .png, .webp">
                                            <small class="text-muted mt-1 d-block">Max file size: 5MB</small>
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <button type="submit" name="update_profile" class="btn btn-primary px-4 rounded-pill">
                                                <i class="bi bi-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                    <div>
                                        <strong>Password Requirements:</strong> Ensure your new password is at least 8 characters long for better security.
                                    </div>
                                </div>

                                <form action="profile.php" method="POST">
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" name="current_password" class="form-control border-end-0" placeholder="Enter current password" required>
                                                <span class="input-group-text toggle-password bg-white" style="cursor:pointer;">
                                                    <i class="bi bi-eye text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" class="form-control border-end-0" placeholder="New password" required>
                                                <span class="input-group-text toggle-password bg-white" style="cursor:pointer;">
                                                    <i class="bi bi-eye text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="confirm_password" class="form-control border-end-0" placeholder="Confirm password" required>
                                                <span class="input-group-text toggle-password bg-white" style="cursor:pointer;">
                                                    <i class="bi bi-eye text-muted"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <button type="reset" class="btn btn-light rounded-pill me-2 border">
                                                <i class="bi bi-x-circle me-1"></i>Reset
                                            </button>
                                            <button type="submit" name="update_password" class="btn btn-warning px-4 rounded-pill text-dark fw-semibold">
                                                <i class="bi bi-shield-check me-1"></i> Update Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <div id="imagePreviewOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8);
            backdrop-filter:blur(5px); justify-content:center; align-items:center; z-index:9999;">
        <span id="closePreview" style="position:absolute; top:20px; right:30px; font-size:30px; color:#fff; cursor:pointer;">&times;</span>
        <img id="fullPreviewImg" style="max-width:90%; max-height:90%; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.5);">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Password Toggle logic
            const toggles = document.querySelectorAll(".toggle-password");
            toggles.forEach(toggle => {
                toggle.addEventListener("click", function() {
                    const input = this.previousElementSibling;
                    const icon = this.querySelector("i");
                    if (input.type === "password") {
                        input.type = "text";
                        icon.classList.remove("bi-eye");
                        icon.classList.add("bi-eye-slash");
                    } else {
                        input.type = "password";
                        icon.classList.remove("bi-eye-slash");
                        icon.classList.add("bi-eye");
                    }
                });
            });

            // Image Preview Overlay Logic
            const profileImg = document.getElementById("profilePreview");
            const overlay = document.getElementById("imagePreviewOverlay");
            const fullImg = document.getElementById("fullPreviewImg");
            const closeBtn = document.getElementById("closePreview");

            if (profileImg) {
                profileImg.addEventListener("click", function() {
                    fullImg.src = this.src;
                    overlay.style.display = "flex";
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener("click", function() {
                    overlay.style.display = "none";
                });
            }

            if (overlay) {
                overlay.addEventListener("click", function(e) {
                    if (e.target === overlay) {
                        overlay.style.display = "none";
                    }
                });
            }

            // Tab Hash logic (URL maintain karne ke liye)
            let hash = window.location.hash;
            if (hash) {
                let targetTab = document.querySelector('.nav-tabs .nav-link[data-bs-target="' + hash + '"]');
                if (targetTab) {
                    let tab = new bootstrap.Tab(targetTab);
                    tab.show();
                }
            }

            let tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    let target = e.target.getAttribute('data-bs-target');
                    if (target) {
                        history.pushState(null, null, target);
                    }
                });
            });
        });

        // Email/Phone readonly alert
        function showAlert(e, fieldName) {
            e.preventDefault();
            const alertPlaceholder = document.getElementById('alertPlaceholder');
            alertPlaceholder.innerHTML = `
            <div id="autoAlert" class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Notice:</strong> Please contact your Librarian or Admin to change your <strong>${fieldName}</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

            setTimeout(function() {
                const alertElement = document.getElementById('autoAlert');
                if (alertElement) {
                    const bsAlert = new bootstrap.Alert(alertElement);
                    bsAlert.close();
                }
            }, 3000);
        }
    </script>
</body>

</html>