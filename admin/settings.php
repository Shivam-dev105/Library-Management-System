<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}
$adminData = $_SESSION['admin'];

// for general setting 
if (isset($_POST['general_setting'])) {

    $system_name    = $_POST['system_name'];
    $college_name    = $_POST['college_name'];
    $college_code    = $_POST['college_code'];
    $college_email   = $_POST['college_email'];
    $college_phone   = $_POST['college_phone'];
    $college_address = $_POST['college_address'];
    $library_address = $_POST['library_address'];
    $late_fine = $_POST['late_fine'];

    // for logo 
    $old_logo = $_POST['old_logo'];
    $logo = $_FILES['logo']['name'];
    $tmp   = $_FILES['logo']['tmp_name'];

    if (!empty($logo)) {

        $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
        $allowed = array("jpg", "jpeg", "png", "webp");

        if (in_array($ext, $allowed)) {

            // 2MB limit
            if ($_FILES['logo']['size'] > 2097152) {
                echo "<script>alert('logo must be under 2MB');</script>";
                exit();
            }
            $newName = time() . "." . $ext;
            $path = "uploads/logo/" . $newName;

            if (move_uploaded_file($tmp, $path)) {
                $finallogo = $newName;
                // Delete old logo
                if (!empty($old_logo) && file_exists("uploads/logo/" . $old_logo)) {
                    unlink("uploads/logo/" . $old_logo);
                }
            } else {
                $finallogo = $old_logo;
            }
        } else {
            echo "<script>alert('Only JPG, PNG, WEBP allowed');</script>";
            exit();
        }
    } else {
        $finallogo = $old_logo;
    }

    // for favicon 
    $old_favicon = $_POST['old_favicon'];
    $favicon = $_FILES['favicon']['name'];
    $tmp   = $_FILES['favicon']['tmp_name'];

    if (!empty($favicon)) {

        $ext = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
        $allowed = array("jpg", "jpeg", "png", "webp");

        if (in_array($ext, $allowed)) {

            // 2MB limit
            if ($_FILES['favicon']['size'] > 2097152) {
                echo "<script>alert('favicon must be under 2MB');</script>";
                exit();
            }
            $newName = time() . "." . $ext;
            $path = "uploads/favicon/" . $newName;

            if (move_uploaded_file($tmp, $path)) {
                $finalfavicon = $newName;
                // Delete old favicon
                if (!empty($old_favicon) && file_exists("uploads/favicon/" . $old_favicon)) {
                    unlink("uploads/favicon/" . $old_favicon);
                }
            } else {
                $finalfavicon = $old_favicon;
            }
        } else {
            echo "<script>alert('Only JPG, PNG, WEBP allowed');</script>";
            exit();
        }
    } else {
        $finalfavicon = $old_favicon;
    }

    // Update Query
    $general_setting = mysqli_query($con, "UPDATE `setting` SET 
    `system_name`   ='$system_name',
    `company_name`  ='$college_name',
    `company_code`  ='$college_code',
    `company_email` ='$college_email',
    `company_phone` ='$college_phone',
    `company_address`='$college_address',
    `system_address` ='$library_address',
    `late_fine`     ='$late_fine',
    `logo`          ='$finallogo',
    `favicon`       ='$finalfavicon',
    `updated_at`    = NOW() 
    WHERE 1");

    if ($general_setting) {
        echo "<script>alert('Settings Updated Successfully'); window.location.href='settings.php#general'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='settings.php'</script>";
    }
}

// for social media link 
if (isset($_POST['update_social'])) {
    $whatsapp = $_POST['whatsapp'];
    $facebook = $_POST['facebook'];
    $instagram = $_POST['instagram'];
    $linkedin = $_POST['linkedin'];
    $youtube = $_POST['youtube'];
    $twitter = $_POST['twitter'];

    $update_social = mysqli_query($con, "UPDATE `setting` SET 
    `whatsapp_link`='$whatsapp',
    `facebook_url`='$facebook',
    `instagram_url`='$instagram',
    `linkedin_url`='$linkedin',
    `youtube_link`='$youtube',
    `twitter_url`='$twitter',
    `updated_at`=NOW()
     WHERE 1");

    if ($update_social) {
        echo "<script>alert('Social media Link Updated Successfully'); window.location.href='settings.php#social'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='settings.php'</script>";
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
    <title>System Settings | <?= $settingRow['system_name']; ?></title>
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

        /* Elegant Tab Styling */
        .nav-tabs {
            border: none;
            margin-bottom: 20px;
            gap: 10px;
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
            /* border-color: var(--primary-indigo); */
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        .settings-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px hsla(0, 0%, 0%, 0.05);
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

        /* Mobile View Fix */
        /* @media (max-width: 767px) {

            .settings-tabs {
                display: flex;
                flex-wrap: nowrap;
                /* wrap band */
        /* justify-content: space-between; */
        /* } */

        /* .settings-tabs .nav-item {
                flex: 1; */
        /* sab equal width */
        /* text-align: center;
            }

            .settings-tabs .nav-link {
                padding: 10px 0;
                font-size: 20px;
            }
        } */
    </style>

</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid">
            <h4 class="fw-bold mb-4 custom-underline">System Settings</h4>

            <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general"><i class="bi bi-gear mx-2"></i><span class="d-none d-md-inline ms-2">General</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#social"><i class="bi bi-share mx-2"></i><span class="d-none d-md-inline ms-2">Social Media</span></button></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="general">
                    <div class="card settings-card p-4">
                        <h5 class="fw-bold mb-4  pb-2 custom-underline">Institution Details</h5>

                        <form action="#" method="POST" enctype="multipart/form-data">

                            <div class="row g-3">

                                <!-- System Name -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">System Name</label>
                                    <input type="text" name="system_name" class="form-control" placeholder="Enter System Name" value="<?= $settingRow['system_name']; ?>">
                                </div>

                                <!-- College Name -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">College Name</label>
                                    <input type="text" name="college_name" class="form-control" placeholder="Enter College Name" value="<?= $settingRow['company_name']; ?>">
                                </div>

                                <!-- College code  -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">College code</label>
                                    <input type="text" name="college_code" class="form-control" placeholder="Enter College code" value="<?= $settingRow['company_code']; ?>">
                                </div>

                                <!-- College Email -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">College Email</label>
                                    <input type="email" name="college_email" class="form-control" placeholder="Enter College Email" value="<?= $settingRow['company_email']; ?>">
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="tel" name="college_phone" class="form-control" placeholder="Enter College Phone" value="<?= $settingRow['company_phone']; ?>">
                                </div>

                                <!-- Address -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Address</label>
                                    <input type="text" name="college_address" class="form-control" placeholder="Enter College Address" value="<?= $settingRow['company_address']; ?>">
                                </div>

                                <!-- System Address -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Library Address</label>
                                    <input type="text" name="library_address" class="form-control" placeholder="Enter Library Address" value="<?= $settingRow['system_address']; ?>">
                                </div>

                                <!-- Late fine per days -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Library Late fine per Days</label>
                                    <input type="text" name="late_fine" class="form-control" placeholder="Enter Late fine per Days" value="<?= $settingRow['late_fine']; ?>">
                                </div>

                                <!-- Logo -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Logo</label>
                                    <!-- Hidden Old logo -->
                                    <input type="hidden" name="old_logo" value="<?php echo $settingRow['logo']; ?>">
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                </div>

                                <!-- Favicon -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Favicon</label>
                                    <!-- Hidden Old logo -->
                                    <input type="hidden" name="old_favicon" value="<?php echo $settingRow['favicon']; ?>">
                                    <input type="file" name="favicon" class="form-control" accept="image/*">
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 mt-3">
                                    <button type="submit" name="general_setting" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="social">
                    <div class="card settings-card p-4">
                        <h5 class="fw-bold mb-4  pb-2 custom-underline">Connect Channels</h5>

                        <form action="" method="post" class="row g-3">

                            <!-- WhatsApp -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-success"><i class="bi bi-whatsapp me-2"></i>Whatsapp Link</label>
                                <input type="url" name="whatsapp" class="form-control" placeholder="https://chat.whatsapp.com/..." value="<?= $settingRow['whatsapp_link'] ?>">
                            </div>

                            <!-- Facebook -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-primary"><i class="bi bi-facebook me-2"></i>Facebook Link</label>
                                <input type="url" name="facebook" class="form-control" placeholder="https://facebook.com/..." value="<?= $settingRow['facebook_url'] ?>">
                            </div>

                            <!-- Instagram -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" style="color:#E1306C;"><i class="bi bi-instagram me-2"></i>Instagram Link</label>
                                <input type="url" name="instagram" class="form-control" placeholder="https://instagram.com/..." value="<?= $settingRow['instagram_url'] ?>">
                            </div>

                            <!-- LinkedIn -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" style="color:#0A66C2;"><i class="bi bi-linkedin me-2"></i>LinkedIn Link</label>
                                <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/..." value="<?= $settingRow['linkedin_url'] ?>">
                            </div>

                            <!-- YouTube -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-danger"><i class="bi bi-youtube me-2"></i>YouTube Link</label>
                                <input type="url" name="youtube" class="form-control"placeholder="https://youtube.com/..." value="<?= $settingRow['youtube_link'] ?>">
                            </div>

                            <!-- Twitter / X -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark"><i class="bi bi-twitter-x me-2"></i>X (Twitter) Link</label>
                                <input type="url" name="twitter" class="form-control" placeholder="https://x.com/..." value="<?= $settingRow['twitter_url'] ?>">
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12 mt-4">
                                <button type="submit" name="update_social" class="btn btn-primary px-4 shadow">
                                    <i class="bi bi-link-45deg me-2"></i> Update Social Links
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- url update  -->
    <script>
        // Tab ko maintain rakhne ke liye JavaScript
        document.addEventListener("DOMContentLoaded", function() {
            // 1. URL se hash (e.g., #rack, #dept) nikalna
            let hash = window.location.hash;
            if (hash) {
                // Us tab ke button ko find karna jiska target ye hash hai
                let targetTab = document.querySelector('.nav-tabs .nav-link[data-bs-target="' + hash + '"]');
                if (targetTab) {
                    // Bootstrap ki madad se us tab ko active karna
                    let tab = new bootstrap.Tab(targetTab);
                    tab.show();
                }
            }

            // 2. Jab user manually koi tab change kare, toh URL me bhi wo hash add kar dena
            // (Taki normal page reload par bhi tab wahi rahe)
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
    </script>

</body>

</html>