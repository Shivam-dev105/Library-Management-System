<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}
$adminData = $_SESSION['admin'];

// for new session 
if (isset($_POST['add_session'])) {
    $session = $_POST['session'];
    $semester = $_POST['semester'];

    $session_add = mysqli_query($con, "INSERT INTO `academic_session`(`session`, `semester`,`status`,`created_at`) VALUES ('$session','$semester',1,NOW())");

    if ($session_add) {
        echo "<script>alert('New Session added Successfully'); window.location.href='academic.php#session'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='academic.php'</script>";
    }
}

// for rack and section  
if (isset($_POST['add_rack'])) {
    $rack = $_POST['rack_location'];
    $section = $_POST['section_name'];

    $rackQuery = mysqli_query($con, "INSERT INTO `rack_section`(`rack`, `section`,`status`,`created_at`) 
    VALUES ('$rack','$section',1, NOW())");

    if ($rackQuery) {
        echo "<script>alert('New Rack Added Successfully'); window.location.href='academic.php#rack'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='academic.php'</script>";
    }
}

// for adding department 
if (isset($_POST['add_depart'])) {
    $department = $_POST['department_name'];

    $departmentQuery = mysqli_query($con, "INSERT INTO `department`(`department_name`,`status`, `created_at`) 
    VALUES ('$department',1, NOW())");

    if ($departmentQuery) {
        echo "<script>alert('New Department Added Successfully'); window.location.href='academic.php#dept'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='academic.php'</script>";
    }
}

// for adding category 
if (isset($_POST['add_category'])) {
    $departmentid = $_POST['department_id'];
    $category = $_POST['category_name'];

    $departmentfetch = mysqli_query($con, "SELECT * FROM `department` WHERE `id` = '$departmentid' AND `status`= 1");
    $departmentRow = mysqli_fetch_assoc($departmentfetch);
    $department = $departmentRow['department_name'];

    $categoryQuery = mysqli_query($con, "INSERT INTO `category`(`department_id` , `category_name`,`status`, `created_at`) 
    VALUES ('$departmentid','$category',1, NOW())");

    if ($categoryQuery) {
        echo "<script>alert('New Category Added Successfully for $department'); window.location.href='academic.php#category'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='academic.php'</script>";
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
    <title>Academic | <?= $settingRow['system_name']; ?></title>
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

        .academic-card {
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
        @media (max-width: 767px) {

            .academic-tabs {
                display: flex;
                flex-wrap: nowrap;
                /* wrap band */
                justify-content: space-between;
            }

            .academic-tabs .nav-item {
                flex: 1;
                /* sab equal width */
                text-align: center;
            }

            .academic-tabs .nav-link {
                padding: 10px 0;
                font-size: 20px;
            }

            /* table  */
            /* Mobile Table Card Layout */
            @media (max-width: 768px) {

                .custom-table thead {
                    display: none;
                }

                .custom-table,
                .custom-table tbody,
                .custom-table tr,
                .custom-table td {
                    display: block;
                    width: 100%;
                }

                .custom-table tr {
                    position: relative;
                    margin-bottom: 15px;
                    background: #fff;
                    padding: 15px;
                    border-radius: 12px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                }

                .custom-table td {
                    text-align: right;
                    padding-left: 50%;
                    position: relative;
                    border: none;
                    padding-top: 4px;
                    padding-bottom: 8px;
                }

                .custom-table td::before {
                    content: attr(data-label);
                    position: absolute;
                    left: 15px;
                    width: 45%;
                    text-align: left;
                    font-weight: 600;
                    color: #1a237e;
                }

                /* Center Floating Badge */
                .qty-cell {
                    text-align: center !important;
                    padding-left: 0 !important;
                    /* margin-top: 1px; */
                }

                .qty-cell::before {
                    display: none;
                    /* remove label for qty */
                }

                .qty-badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #4e73df, #224abe);
                    color: #fff;
                    font-size: 18px;
                    font-weight: bold;
                    border-radius: 50%;
                    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.25);
                    border: 4px solid #fff;
                }
            }

        }
    </style>

</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">

        <?php include("includes/header.php"); ?>

        <div class="container-fluid">
            <h4 class="fw-bold mb-4 custom-underline">Academic</h4>
            <ul class="nav nav-tabs academic-tabs" id="academicTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#session"><i class="bi bi-calendar3 mx-2"></i><span class="d-none d-md-inline ms-2">Session/Sem</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rack"><i class="bi bi-layers mx-2"></i><span class="d-none d-md-inline ms-2">Rack/Section</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dept"><i class="bi bi-building mx-2"></i><span class="d-none d-md-inline ms-2">Department</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#category"><i class="bi bi-tags mx-2"></i><span class="d-none d-md-inline ms-2">Category</span></button></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="session">
                    <div class="card academic-card p-4">
                        <h5 class="fw-bold mb-4  pb-2 custom-underline">Academic Configuration</h5>
                        <div class="row g-4 mb-4">
                            <?php
                            $session = mysqli_query($con, "SELECT * FROM `academic_session` ORDER BY id DESC");
                            $i = 1;
                            if (mysqli_num_rows($session) > 0) {
                                while ($sessionRow = mysqli_fetch_assoc($session)) {
                            ?>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="card shadow border-0 rounded-4 h-100">
                                            <!-- Card Body -->
                                            <div class="card-body text-center">
                                                <!-- Number Circle -->
                                                <div class="mb-4">
                                                    <span style=" display:inline-flex;align-items:center; justify-content:center; width:50px; height:50px; background:linear-gradient(135deg,#4e73df,#224abe); color:#fff; font-size:18px; font-weight:bold; border-radius:50%; box-shadow:0 6px 10px rgba(0,0,0,0.25); border:4px solid #fff;">
                                                        <?= $i++; ?>
                                                    </span>
                                                </div>
                                                <h6 class="fw-bold mb-2">
                                                    Session : <span class="text-muted"><?= $sessionRow['session']; ?></span>
                                                </h6>
                                                <p class="text-muted small">Semester : <?= $sessionRow['semester']; ?></p>

                                                <!-- Status Badge -->
                                                <?php if ($sessionRow['status'] == 1) { ?>
                                                    <span class="badge status-badge bg-success mb-2">Active</span>
                                                <?php } else { ?>
                                                    <span class="badge status-badge bg-danger mb-2">Inactive</span>
                                                <?php } ?>

                                                <!-- Toggle Switch -->
                                                <div class="form-check form-switch d-flex justify-content-center mt-2">
                                                    <input class="form-check-input toggle-status"
                                                        type="checkbox"
                                                        data-id="<?= $sessionRow['id']; ?>"
                                                        <?= ($sessionRow['status'] == 1) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                            <!-- Footer -->
                                            <div class="card-footer bg-white border-0 d-flex justify-content-center gap-3 pb-3">
                                                <a href="edit_session.php?session_id=<?= $sessionRow['id']; ?>" class="btn btn-sm btn-outline-primary fs-6">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="delete.php?session_id=<?= $sessionRow['id']; ?>" class="btn btn-sm btn-outline-danger fs-6"
                                                    onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                            <?php }
                            } else {
                                echo '<span class="text-muted small fst-italic">No Session found. Add one below.</span>';
                            } ?>
                        </div>

                        <hr class="text-muted opacity-25 mb-4">

                        <form action="#" method="POST">
                            <div class="row g-3">

                                <!-- Add Session -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Add Session</label>
                                    <input type="text" name="session" class="form-control" placeholder="e.g. 2023-26" required>
                                    <small class="text-muted">Note: 3 Years session and Semester I to VI</small>
                                </div>

                                <!-- Active Semester -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Active Semester</label>
                                    <select name="semester" class="form-select" required>
                                        <option value="" disabled selected>-- Select Semester --</option>
                                        <option value="I">I</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                        <option value="V">V</option>
                                        <option value="VI">VI</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 mt-3">
                                    <button type="submit" name="add_session" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Add
                                    </button>

                                    <button type="reset" class="btn btn-secondary">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="rack">
                    <div class="card academic-card p-4">
                        <h5 class="fw-bold mb-4  pb-2 custom-underline">Library Architecture</h5>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php
                            // Fetch all categories
                            $racksection = mysqli_query($con, "SELECT * FROM `rack_section`");

                            if (mysqli_num_rows($racksection) > 0) {
                                while ($rackRow = mysqli_fetch_assoc($racksection)) {
                            ?>
                                    <span class="badge bg-primary-subtle text-primary p-2 d-flex align-items-center gap-2 shadow-sm">

                                        <span class="fw-medium"><?= $rackRow['rack']; ?> - <?= $rackRow['section'] ?></span>

                                        <a href="delete.php?rack_id=<?= $rackRow['id']; ?>"
                                            class="text-primary text-decoration-none ms-1"
                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </a>
                                    </span>
                            <?php
                                }
                            } else {
                                echo '<span class="text-muted small fst-italic">No rack found. Add one below.</span>';
                            }
                            ?>
                        </div>

                        <hr class="text-muted opacity-25 mb-4">

                        <form action="#" method="POST">
                            <div class="row g-3">

                                <!-- Rack Location -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Rack Location</label>
                                    <input type="text" name="rack_location" class="form-control" placeholder="e.g. Rack A" required>
                                </div>

                                <!-- Section Name -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Section Name</label>
                                    <input type="text" name="section_name" class="form-control" placeholder="e.g. Reference Section" required>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 mt-3">
                                    <button type="submit" name="add_rack" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Add Rack
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="dept">
                    <div class="card academic-card p-4">
                        <h5 class="fw-bold mb-4 pb-2 custom-underline">Academic Departments</h5>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php
                            $departmentfetch = mysqli_query($con, "SELECT * FROM `department`");
                            if (mysqli_num_rows($departmentfetch) > 0) {

                            while ($departmentRow = mysqli_fetch_assoc($departmentfetch)) {
                                $dept_id = $departmentRow['id'];
                                $dept_name = $departmentRow['department_name'];
                            ?>
                                <span class="badge bg-primary-subtle text-primary p-2 d-flex align-items-center gap-2">
                                    <a href="#" class="text-primary text-decoration-none fw-medium" data-bs-toggle="modal" data-bs-target="#categoryModal_<?= $dept_id; ?>">
                                        <?= $dept_name; ?>
                                    </a>
                                    <a href="delete.php?depart_id=<?= $dept_id; ?>" class="text-primary text-decoration-none ms-1" onclick="return confirm('Are you sure you want to delete this department?')">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                </span>

                                <div class="modal fade" id="categoryModal_<?= $dept_id; ?>" tabindex="-1" aria-labelledby="modalLabel_<?= $dept_id; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow">

                                            <div class="modal-header bg-light border-bottom-0">
                                                <h6 class="modal-title text-dark fw-bold" id="modalLabel_<?= $dept_id; ?>">
                                                    <i class="bi bi-folder2-open me-2 text-primary"></i><?= $dept_name; ?> Categories
                                                </h6>
                                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-0">
                                                <?php
                                                $categoryQuery = "SELECT * FROM `category` WHERE `department_id` = '$dept_id'";
                                                $categoryFetch = mysqli_query($con, $categoryQuery);

                                                if (mysqli_num_rows($categoryFetch) > 0) {
                                                    echo '<ul class="list-group list-group-flush">';
                                                    while ($categoryRow = mysqli_fetch_assoc($categoryFetch)) {
                                                ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                                            <span class="text-secondary fw-medium">
                                                                <?= $categoryRow['category_name']; ?>
                                                            </span>
                                                            <a href="delete.php?category_id=<?= $categoryRow['id']; ?>" class="btn btn-sm btn-outline-danger border-0" title="Delete Category" onclick="return confirm('Are you sure you want to delete this category?')">
                                                                <i class="bi bi-trash3"></i>
                                                            </a>
                                                        </li>
                                                    <?php
                                                    }
                                                    echo '</ul>';
                                                } else {
                                                    ?>
                                                    <div class="text-center p-5 text-muted">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                                        <p class="mb-0 small">No categories found in this department.</p>
                                                    </div>
                                                <?php } ?>
                                            </div>

                                            <!-- <div class="modal-footer border-top-0 bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            </div> -->

                                        </div>
                                    </div>
                                </div>
                            <?php } } else {
                                echo '<span class="text-muted small fst-italic">No Department found. Add one below.</span>';
                            }?>
                        </div>
                        <hr class="text-muted opacity-25 mb-4">

                        <form action="#" method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">New Department Name</label>
                                <input type="text" name="department_name" class="form-control" placeholder="Enter Dept Name" required>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" name="add_depart" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle me-2"></i> Add Department
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="category">
                    <div class="card academic-card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4 pb-2 custom-underline">Manage Categories</h5>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php
                            // Fetch all categories
                            $categoryfetch = mysqli_query($con, "SELECT * FROM `category`");

                            if (mysqli_num_rows($categoryfetch) > 0) {
                                while ($categoryRow = mysqli_fetch_assoc($categoryfetch)) {
                            ?>
                                    <span class="badge bg-primary-subtle text-primary p-2 d-flex align-items-center gap-2 shadow-sm">
                                        <i class="bi bi-tags-fill opacity-75"></i>
                                        <span class="fw-medium"><?= $categoryRow['category_name']; ?></span>

                                        <a href="delete.php?category_id=<?= $categoryRow['id']; ?>"
                                            class="text-primary text-decoration-none ms-1"
                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </a>
                                    </span>
                            <?php
                                }
                            } else {
                                echo '<span class="text-muted small fst-italic">No categories found. Add one below.</span>';
                            }
                            ?>
                        </div>

                        <hr class="text-muted opacity-25 mb-4">

                        <form action="#" method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label for="department_id" class="form-label small fw-bold text-secondary">Select Department</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                    <option value="" disabled selected>-- Select Department --</option>
                                    <?php
                                    $department = mysqli_query($con, "SELECT * FROM `department` WHERE `status` = 1");
                                    while ($departmentRows = mysqli_fetch_assoc($department)) {
                                    ?>
                                        <option value="<?= $departmentRows['id'] ?>"><?= $departmentRows['department_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="category_name" class="form-label small fw-bold text-secondary">New Category Name</label>
                                <input type="text" name="category_name" id="category_name" class="form-control" placeholder="Enter Category Name" required>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" name="add_category" class="btn btn-primary px-4 shadow-sm">
                                    <i class="bi bi-plus-circle me-2"></i> Add Category
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

    <!-- session status js  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll('.toggle-status').forEach(function(toggle) {

                toggle.addEventListener('change', function() {

                    let sessionId = this.getAttribute('data-id');
                    let status = this.checked ? 1 : 0;

                    let card = this.closest('.card-body');
                    let badge = card.querySelector('.status-badge');

                    fetch('ajax/session_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'session_id=' + sessionId + '&status=' + status
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (status == 1) {
                                badge.classList.remove('bg-danger');
                                badge.classList.add('bg-success');
                                badge.innerText = "Active";
                            } else {
                                badge.classList.remove('bg-success');
                                badge.classList.add('bg-danger');
                                badge.innerText = "Inactive";
                            }

                        });

                });

            });

        });
    </script>

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