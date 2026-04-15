<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}
$adminData = $_SESSION['admin'];

// Get Session ID
if (isset($_GET['session_id'])) {
    $id = $_GET['session_id'];

    $editQuery = mysqli_query($con, "SELECT * FROM `academic_session` WHERE id='$id'");
    $editRow = mysqli_fetch_assoc($editQuery);
} else {
    header("location: academic.php");
    exit();
}

// Update Session
if (isset($_POST['update_session'])) {

    $session = $_POST['session'];
    $semester = $_POST['semester'];

    $update = mysqli_query(
        $con,
        "UPDATE `academic_session` 
         SET `session`='$session',
          `semester`='$semester',
           `updated_at`=NOW()
         WHERE id='$id'"
    );

    if ($update) {
        echo "<script>alert('Session Updated Successfully'); window.location.href='academic.php'</script>";
    } else {
        echo "<script>alert('Something went wrong');window.location.href='edit_session.php?session_id= $id'</script>";
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
    <title>Edit Session | <?= $settingRow['system_name']; ?></title>
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
            <h4 class="fw-bold mb-4 custom-underline">Edit Session</h4>

            <ul class="nav nav-tabs academic-tabs" id="academicTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#session"><i class="bi bi-calendar3 mx-2"></i><span class="d-none d-md-inline ms-2">Edit Session/Sem</span></button></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="session">
                    <div class="card academic-card p-4">
                        <h5 class="fw-bold mb-4  pb-2 custom-underline">Edit Session of <span class="text-danger"><?= $editRow['session']; ?></span></h5>

                        <form action="#" method="POST">
                            <div class="row g-3">

                                <!-- Add Session -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Update Session</label>

                                    <input type="text" name="session" class="form-control"
                                        value="<?= $editRow['session']; ?>">
                                    <small class="text-muted">Note: 3 Years session and Semester I to VI</small>
                                </div>

                                <!-- Active Semester -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Update Active Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="I" <?= ($editRow['semester'] == 'I') ? 'selected' : ''; ?>>I</option>
                                        <option value="II" <?= ($editRow['semester'] == 'II') ? 'selected' : ''; ?>>II</option>
                                        <option value="III" <?= ($editRow['semester'] == 'III') ? 'selected' : ''; ?>>III</option>
                                        <option value="IV" <?= ($editRow['semester'] == 'IV') ? 'selected' : ''; ?>>IV</option>
                                        <option value="V" <?= ($editRow['semester'] == 'V') ? 'selected' : ''; ?>>V</option>
                                        <option value="VI" <?= ($editRow['semester'] == 'VI') ? 'selected' : ''; ?>>VI</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 mt-3">
                                    <button type="submit" name="update_session" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Update Session
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>