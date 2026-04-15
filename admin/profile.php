<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}
$adminData = $_SESSION['admin'];

// personal information
if (isset($_POST["update_profile"])) {
    $fname = $_POST["full_name"];
    $phone = $_POST["phone"];

    $update_profile = mysqli_query($con, "UPDATE `admin` SET `name`='$fname',`phone`='$phone',`updated_at`= NOW() WHERE `email` = '$adminData'");

    if ($update_profile) {
        echo "<script>alert('Profile Updated successfully.'); window.location.href='profile.php#edit-profile';</script>";
    } else {
        echo "<script>alert('Profile Updated failed. Please try again'); window.location.href='profile.php';</script>";
    }
}

// profile image 
if (isset($_POST["update_profile_img"])) {
    $old_img = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $tmp   = $_FILES['image']['tmp_name'];

    if (!empty($image)) {

        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed = array("jpg", "jpeg", "png", "webp");

        if (in_array($ext, $allowed)) {

            // 5MB limit
            if ($_FILES['image']['size'] > 5000000) {
                echo "<script>alert('Image must be under 2MB'); window.location.href='profile.php';();</script>";
                exit();
            }
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
            echo "<script>alert('Only JPG, PNG, WEBP allowed'); window.location.href='profile.php';();</script>";
            exit();
        }
    } else {
        $finalImage = $old_img;
    }

    $update_profile_img = (mysqli_query($con, "UPDATE admin SET postimage='$finalImage',updated_at=NOW() WHERE email='$adminData'"));

    if ($update_profile_img) {
        echo "<script>alert('Profile image Updated successfully.'); window.location.href='profile.php#edit-profile';</script>";
    } else {
        echo "<script>alert('Profile image Updated failed. Please try again'); window.location.href='profile.php';</script>";
    }
}

// change password 
if (isset($_POST['update_password'])) {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current password from DB
    $checkQuery = mysqli_query($con, "SELECT password FROM admin WHERE email='$adminData'");
    $row = mysqli_fetch_assoc($checkQuery);

    if (!$row) {
        echo "<script>alert('User not found'); window.location='profile.php';</script>";
        exit();
    }

    $dbPassword = $row['password'];

    // If you are using plain password (not recommended)
    // if ($current != $dbPassword)

    // If you are using password_hash (recommended)
    if (!password_verify($current, $dbPassword)) {
        echo "<script>alert('❌Current password is incorrect'); window.location='profile.php';</script>";
        exit();
    }

    if ($new !== $confirm) {
        echo "<script>alert('❌New passwords do not match'); window.location='profile.php';</script>";
        exit();
    }

    // Hash new password
    $hashedPassword = password_hash($new, PASSWORD_DEFAULT);

    $passUpdate = mysqli_query($con, "UPDATE admin SET password='$hashedPassword', pass_updated_at=NOW() WHERE email='$adminData'");

    if ($passUpdate) {
        echo "<script>alert('Password Updated Successfully'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Password Update Failed'); window.location='profile.php';</script>";
    }
}

$admin = mysqli_query($con, "SELECT * FROM `admin` WHERE `email` = '$adminData'");
$adminRow = mysqli_fetch_assoc($admin);

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | <?= $settingRow['system_name']; ?></title>
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

        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .profile-img-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-tabs {
            gap: 10px;
        }

        .profile-tabs .nav-link {
            border: 1px grey solid;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .profile-tabs .nav-link:hover {
            background: #e2e6f5;
            transform: translateY(-2px);
        }

        .profile-tabs .nav-link.active {
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: #fff !important;
            box-shadow: 0 6px 15px rgba(26, 35, 126, 0.25);
        }

        @media (max-width: 576px) {
            .profile-tabs .nav-item {
                width: 100%;
            }

            .profile-tabs .nav-link {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
                display: block;
            }
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
            <h4 class="fw-bold mb-4 custom-underline">My Profile</h4>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card profile-card p-4 text-center">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="profile-img-wrapper position-relative d-inline-block">

                                <?php
                                $profileImage = !empty($adminRow['postimage'])
                                    ? "uploads/profile/" . $adminRow['postimage']
                                    : "https://ui-avatars.com/api/?name=" . urlencode($adminRow['name']) . "&background=1a237e&color=fff&size=128";
                                ?>

                                <img id="profilePreview"
                                    src="<?php echo $profileImage; ?>"
                                    class="profile-img rounded-circle"
                                    style="width:128px;height:128px;object-fit:cover;cursor:pointer;">

                                <!-- Hidden Old Image -->
                                <input type="hidden" name="old_image" value="<?php echo $adminRow['postimage']; ?>">

                                <!-- File Input -->
                                <input type="file" id="profileInput" name="image" accept="image/*" style="display:none;">

                                <!-- Camera Button -->
                                <button type="button"
                                    id="cameraBtn"
                                    class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle shadow">
                                    <i class="bi bi-camera"></i>
                                </button>

                            </div>

                            <!-- Other fields here -->

                            <button type="submit" name="update_profile_img" class="btn btn-primary mt-3">
                                Update Profile
                            </button>

                        </form>

                        <h5 class="fw-bold mb-1"><?= $adminRow['name']; ?></h5>
                        <p class="text-muted small mb-3">GP Bhojpur Library Management</p>

                        <?php
                        if ($adminRow['status'] == 1) {
                            echo '<div class="badge bg-success-subtle text-success px-3 py-2 mb-3">Active Account</div>';
                        } else {
                            echo '<div class="badge bg-danger-subtle text-danger px-3 py-2 mb-3">Inactive Account</div>';
                        }
                        ?>

                        <hr>
                        <div class="text-start">
                            <p class="mb-1 text-muted"><i class="bi bi-calendar-event me-2"></i>
                                <span class="fw-bold">Last Login:</span>
                                <span class="small">
                                    <?php
                                    if (!empty($adminRow['last_login'])) {
                                        echo date("d M Y, h:i A", strtotime($adminRow['last_login']));
                                    }
                                    ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card profile-card">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <ul class="nav nav-tabs profile-tabs border-0" id="profileTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#edit-profile" type="button">
                                        <i class="bi bi-person me-2"></i>Personal Info
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security" type="button">
                                        <i class="bi bi-shield-lock me-2"></i>Security
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4 pt-2">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="edit-profile">

                                    <form action="#" method="POST" enctype="multipart/form-data">
                                        <div id="alertPlaceholder" class="mt-3"></div>
                                        <div class="row g-3">

                                            <!-- Full Name -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Full Name</label>
                                                <input type="text" name="full_name"
                                                    class="form-control"
                                                    value="<?= $adminRow['name']; ?>">
                                            </div>

                                            <!-- Email -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Email ID</label>
                                                <input type="email" name="email"
                                                    class="form-control" onclick="showAlert(event)"
                                                    value="<?= $adminRow['email']; ?>" readonly>
                                            </div>

                                            <!-- Phone -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Phone Number</label>
                                                <input type="tel" name="phone"
                                                    class="form-control"
                                                    value="<?= $adminRow['phone']; ?>">
                                            </div>

                                            <!-- Role -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Role</label>
                                                <input type="text" name="role"
                                                    class="form-control"
                                                    value="Super Admin" disabled readonly>
                                            </div>

                                            <!-- Buttons -->
                                            <div class="col-12 mt-4">
                                                <button type="submit" name="update_profile" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i>Update Profile
                                                </button>
                                            </div>

                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="security">

                                    <form action="" method="POST">
                                        <div class="row g-3">

                                            <!-- Current Password -->
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold">Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                                                    <span class="input-group-text toggle-password" style="cursor:pointer;"><i class="bi bi-eye"></i></span>
                                                </div>
                                            </div>

                                            <!-- New Password -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="new_password" class="form-control" placeholder="New password" required>
                                                    <span class="input-group-text toggle-password" style="cursor:pointer;"><i class="bi bi-eye"></i></span>
                                                </div>
                                            </div>

                                            <!-- Confirm Password -->
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Confirm New Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                                                    <span class="input-group-text toggle-password" style="cursor:pointer;"><i class="bi bi-eye"></i></span>
                                                </div>
                                            </div>

                                            <!-- Buttons -->
                                            <div class="col-12 mt-3">
                                                <button type="submit" name="update_password" class="btn btn-danger">
                                                    <i class="bi bi-save me-1"></i>Update Password
                                                </button>

                                                <button type="reset" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle me-1"></i>Reset
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
        </div>

        <?php include("includes/footer.php"); ?>

    </div>
    <!--  Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- show or hide password  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

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

        });
    </script>

    <!-- camera button file open  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const cameraBtn = document.getElementById("cameraBtn");
            const profileInput = document.getElementById("profileInput");
            const profilePreview = document.getElementById("profilePreview");

            // Camera click → open file chooser
            cameraBtn.addEventListener("click", function() {
                profileInput.click();
            });

            // Image select → preview
            profileInput.addEventListener("change", function() {

                const file = this.files[0];

                if (file) {

                    // Only image validation
                    if (!file.type.startsWith("image/")) {
                        alert("Please select a valid image file.");
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    };

                    reader.readAsDataURL(file);
                }

            });

        });
    </script>

    <!-- Image Preview Overlay -->
    <div id="imagePreviewOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8);
            backdrop-filter:blur(5px); justify-content:center; align-items:center; z-index:9999;">

        <span id="closePreview" style="position:absolute; top:20px; right:30px; font-size:30px; color:#fff; cursor:pointer;">&times;</span>

        <img id="fullPreviewImg" style="max-width:90%; max-height:90%; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.5);">
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const profileImg = document.getElementById("profilePreview");
            const overlay = document.getElementById("imagePreviewOverlay");
            const fullImg = document.getElementById("fullPreviewImg");
            const closeBtn = document.getElementById("closePreview");

            // Click on profile image → open preview
            profileImg.addEventListener("click", function() {
                fullImg.src = this.src;
                overlay.style.display = "flex";
            });

            // Close button
            closeBtn.addEventListener("click", function() {
                overlay.style.display = "none";
            });

            // Click outside image → close
            overlay.addEventListener("click", function(e) {
                if (e.target === overlay) {
                    overlay.style.display = "none";
                }
            });

        });
    </script>

    <!-- email alert  -->
    <script>
        function showAlert(e) {
            e.preventDefault();

            const alertPlaceholder = document.getElementById('alertPlaceholder');

            alertPlaceholder.innerHTML = `<div id="autoAlert" class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Notice:</strong> Please contact your Developer to change email.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;

            // Auto dismiss after 3 seconds
            setTimeout(function() {
                const alertElement = document.getElementById('autoAlert');
                if (alertElement) {
                    const bsAlert = new bootstrap.Alert(alertElement);
                    bsAlert.close();
                }
            }, 1500);
        }
    </script>

    <!-- url update  -->
    <script>
        // Tab ko maintain rakhne ke liye JavaScript
        document.addEventListener("DOMContentLoaded", function() {
            // 1. URL se hash (e.g., #security) nikalna
            let hash = window.location.hash;
            if (hash) {
                // Update: data-bs-target aur href dono ko check karega
                let targetTab = document.querySelector('.nav-tabs .nav-link[data-bs-target="' + hash + '"], .nav-tabs .nav-link[href="' + hash + '"]');

                if (targetTab) {
                    // Bootstrap ki madad se us tab ko active karna
                    let tab = new bootstrap.Tab(targetTab);
                    tab.show();
                }
            }

            // 2. Jab user manually koi tab change kare, toh URL me bhi wo hash add kar dena
            let tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    let target = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
                    if (target) {
                        history.pushState(null, null, target);
                    }
                });
            });
        });
    </script>

</body>

</html>