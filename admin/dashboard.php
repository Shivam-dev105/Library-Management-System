<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
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
    <title>Admin Dashboard | <?= htmlspecialchars($settingRow['system_name']); ?></title>
    <link rel="icon" href="uploads/favicon/<?= htmlspecialchars($settingRow['favicon']); ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->

    <style>
        :root {
            --main-bg: #f4f7fe;
            --primary-blue: #4318FF;
            --success-green: #05CD99;
            --warning-orange: #FFCE20;
            --danger-red: #EE5D50;
            /* --sidebar-width: 250px; */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            overflow-x: hidden;
        }

        /* --- Main Content Base --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        /* Custom Colors for Icons */
        .icon-blue {
            background: rgba(67, 24, 255, 0.1);
            color: var(--primary-blue);
        }

        .icon-green {
            background: rgba(5, 205, 153, 0.1);
            color: var(--success-green);
        }

        .icon-orange {
            background: rgba(255, 206, 32, 0.15);
            color: #d9a400;
        }

        .icon-red {
            background: rgba(238, 93, 80, 0.1);
            color: var(--danger-red);
        }

        .icon-purple {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .icon-info {
            background: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        /* Custom Scrollbar */
        /* .dept-scroll-list {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .dept-scroll-list::-webkit-scrollbar {
            width: 4px;
        }

        .dept-scroll-list::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 10px;
        }

        .dept-scroll-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .dept-scroll-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        } */

        /* Widget Styling */
        .session-widget {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .session-widget:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .widget-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #edf2f7;
            border-top: 4px solid var(--primary-blue);
        }

        .stat-box {
            background-color: #fff;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .stat-box:hover {
            border-color: #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        }
    </style>
</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid p-0">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-1">
                <div>
                    <h4 class="fw-bold mb-1 text-dark fs-4">Dashboard Overview</h4>
                    <p class="text-muted small mb-0">Here's what's happening in your library today.</p>
                </div>
                <div class="mt-3 mt-md-0 d-none d-sm-block">
                    <div class="bg-white px-4 py-2 rounded-pill shadow-sm text-primary fw-semibold border small">
                        <i class="bi bi-calendar3 me-2"></i> <?= date('d M, Y (l)') ?>
                    </div>
                </div>
            </div>

            <!-- <div class="d-flex align-items-center justify-content-between px-3 py-2 mb-3 rounded shadow-sm" style="background: linear-gradient(90deg, rgba(67,24,255,0.1) 0%, rgba(67,24,255,0.02) 100%); border-left: 4px solid var(--primary-blue);">
                <div class="d-flex align-items-center">
                    <i class="bi bi-lightning-charge-fill text-primary me-2 fs-5"></i>
                    <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px; color: var(--primary-blue);">Today's Quick Stats</h6>
                </div>
                <div class="badge bg-white text-dark border shadow-sm">
                    <i class="bi bi-clock me-1 text-muted"></i> Live
                </div>
            </div> -->

            <div class="row g-3 mb-4">
                <?php
                // Ek hi query me Aaj ka Issue, Return, Fine count aur Fine sum nikalna
                $today = date('Y-m-d');

                $todayStatsQuery = mysqli_query($con, "SELECT 
                    SUM(CASE WHEN DATE(issued_date) = '$today' THEN 1 ELSE 0 END) AS today_issued,
                    SUM(CASE WHEN return_status = 1 AND DATE(return_date) = '$today' THEN 1 ELSE 0 END) AS today_returned,
                    SUM(CASE WHEN payment_status = 1 AND fine_amount > 0 AND DATE(payment_date) = '$today' THEN 1 ELSE 0 END) AS today_fine_payers,
                    SUM(CASE WHEN payment_status = 1 AND DATE(payment_date) = '$today' THEN fine_amount ELSE 0 END) AS today_fine_collected
                FROM `issued_books`");

                $todayIssued = $todayStats['today_issued'] ?? 0;
                $todayReturned = $todayStats['today_returned'] ?? 0;
                $todayFinePayers = $todayStats['today_fine_payers'] ?? 0;
                $todayFineCollected = $todayStats['today_fine_collected'] ?? 0;
                ?>

                <div class="col-6 col-lg-3">
                    <div class="session-widget p-3 d-flex align-items-center h-100" style="border-left: 4px solid var(--primary-blue);">
                        <div class="icon-box icon-blue me-3 d-flex align-items-center justify-content-center" style="width:45px; height:45px; font-size:1.3rem;">
                            <i class="bi bi-journal-arrow-up"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">ISSUED TODAY</small>
                            <h3 class="fw-bold text-dark mb-0"><?= number_format($todayIssued); ?> <span class="fs-6 text-muted">Books</span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="session-widget p-3 d-flex align-items-center h-100" style="border-left: 4px solid var(--success-green);">
                        <div class="icon-box icon-green me-3 d-flex align-items-center justify-content-center" style="width:45px; height:45px; font-size:1.3rem;">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">RETURNED TODAY</small>
                            <h3 class="fw-bold text-dark mb-0"><?= number_format($todayReturned); ?> <span class="fs-6 text-muted">Books</span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="session-widget p-3 d-flex align-items-center h-100" style="border-left: 4px solid #d9a400;">
                        <div class="icon-box icon-orange me-3 d-flex align-items-center justify-content-center" style="width:45px; height:45px; font-size:1.3rem;">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">FINE PAYERS TODAY</small>
                            <h3 class="fw-bold text-dark mb-0"><?= number_format($todayFinePayers); ?> <span class="fs-6 text-muted">Students</span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="session-widget p-3 d-flex align-items-center h-100" style="border-left: 4px solid #6b21a8;">
                        <div class="icon-box icon-purple me-3 d-flex align-items-center justify-content-center" style="width:45px; height:45px; font-size:1.3rem;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">COLLECTED TODAY</small>
                            <h3 class="fw-bold text-dark mb-0">₹<?= number_format($todayFineCollected); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-3 rounded shadow-sm" style="background: linear-gradient(90deg, rgba(67,24,255,0.1) 0%, rgba(67,24,255,0.02) 100%); border-left: 4px solid var(--primary-blue);">
                <div class="d-flex align-items-center">
                    <i class="bi bi-lightning-charge-fill text-primary me-2 fs-5"></i>
                    <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px; color: var(--primary-blue);">Overall's Quick Stats</h6>
                </div>
                <div class="badge bg-white text-dark border shadow-sm">
                    <i class="bi bi-clock me-1 text-muted"></i> Live
                </div>
            </div> -->

            <div class="row g-4 mb-4">
                <?php
                // Step 1: Sabse pehle saare ACTIVE SESSIONS nikalenge
                $sessionQuery = mysqli_query($con, "SELECT id, session, semester FROM `academic_session` WHERE status = 1 ORDER BY id DESC");

                while ($sessionRow = mysqli_fetch_assoc($sessionQuery)) {
                    $sessionId = $sessionRow['id'];
                    $sessionName = $sessionRow['session'];
                    $SemesterName = $sessionRow['semester'];

                    // Step 2: Is specific session ke TOTAL Active aur Inactive students count karenge
                    $totalsQuery = mysqli_query($con, "SELECT 
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as total_active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as total_inactive
                    FROM `users` WHERE session_id = '$sessionId'");

                    $totalsRow = mysqli_fetch_assoc($totalsQuery);
                    $totalActive = $totalsRow['total_active'] ?? 0;
                    $totalInactive = $totalsRow['total_inactive'] ?? 0;
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="session-widget h-100 d-flex flex-column">

                            <div class="widget-header p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="fw-bold mb-0 fs-6">
                                        <i class="bi bi-calendar-range text-primary me-2"></i><a href="academic.php#session" class="text-decoration-none  text-dark">Session - <?= htmlspecialchars($sessionName); ?> (<?= htmlspecialchars($SemesterName); ?>)</a>
                                    </h5>
                                </div>

                                <div class="d-flex gap-3 mt-3">
                                    <a href="manage_students.php#view" class="flex-fill stat-box px-2 py-2 text-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-purple mx-auto mb-1" style="width: 35px; height: 35px; font-size: 1.5rem; border-radius: 20px;">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <small class="text-muted d-block fw-bold" style="font-size: 0.80rem; letter-spacing: 0.5px;">Total Active</small>
                                        <span class="fw-bold text-success fs-4"><?= number_format($totalActive); ?></span>
                                    </a>
                                    <a href="manage_students.php#view" class="flex-fill stat-box px-2 py-2 text-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-red mx-auto mb-1" style="width: 35px; height: 35px; font-size: 1.5rem; border-radius: 20px;">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <small class="text-muted d-block fw-bold" style="font-size: 0.80rem; letter-spacing: 0.5px;">Total Inactive</small>
                                        <span class="fw-bold text-danger fs-4"><?= number_format($totalInactive); ?></span>
                                    </a>
                                </div>
                            </div>

                            <div class="p-3 flex-grow-1">
                                <h6 class="text-muted fw-bold" style="font-size: 0.90rem; letter-spacing: 0.5px;">Department Breakdown</h6>
                                <hr class="mb-2">
                                <div class="dept-scroll-list">
                                    <?php
                                    $deptQuery = mysqli_query($con, "SELECT d.department_name, 
                                        SUM(CASE WHEN u.status = 1 THEN 1 ELSE 0 END) as dept_active,
                                        SUM(CASE WHEN u.status = 0 THEN 1 ELSE 0 END) as dept_inactive
                                        FROM `department` d
                                        LEFT JOIN `users` u ON d.id = u.department_id AND u.session_id = '$sessionId'
                                        WHERE d.status = 1
                                        GROUP BY d.id
                                    ");

                                    while ($deptRow = mysqli_fetch_assoc($deptQuery)) {
                                        $deptActive = $deptRow['dept_active'] ?? 0;
                                        $deptInactive = $deptRow['dept_inactive'] ?? 0;
                                        $deptName = $deptRow['department_name'];
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                            <span class="fw-medium text-dark text-truncate pe-2" style="max-width: 65%; font-size: 0.85rem;" title="<?= htmlspecialchars($deptName); ?>">
                                                <i class="bi bi-building text-secondary me-2" style="font-size: 0.8rem;"></i><?= htmlspecialchars($deptName); ?>
                                            </span>
                                            <div class="d-flex gap-1">
                                                <span class="badge bg-success text-white rounded-pill d-flex align-items-center justify-content-center" title="Active Students" style="width: 32px; font-weight: 600;"><?= $deptActive; ?></span>
                                                <span class="badge bg-danger text-white rounded-pill d-flex align-items-center justify-content-center" title="Inactive Students" style="width: 32px; font-weight: 600;"><?= $deptInactive; ?></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row g-4 mb-4">

                <div class="col-12 col-md-6 col-xl-4">
                    <?php
                    $overallBookQuery = mysqli_query($con, "SELECT COUNT(id) AS total_titles, SUM(quantity) AS total_volume FROM `books`");
                    $overallBookRow = mysqli_fetch_assoc($overallBookQuery);
                    $totalTitles = $overallBookRow['total_titles'] ?? 0;
                    $totalVolume = $overallBookRow['total_volume'] ?? 0;
                    ?>
                    <div class="session-widget h-100 d-flex flex-column">
                        <div class="widget-header p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold text-dark mb-0 fs-6"><i class="bi bi-bookshelf text-primary me-2"></i>Library Inventory</h5>
                            </div>

                            <div class="d-flex gap-3 mt-3">
                                <a href="manage_books.php#manage" class="flex-fill stat-box px-2 py-2 text-center shadow-sm text-decoration-none">
                                    <div class="icon-box icon-purple mx-auto mb-1" style="width: 35px; height: 35px; font-size: 1.5rem; border-radius: 20px;">
                                        <i class="bi bi-journal-richtext"></i>
                                    </div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.80rem; letter-spacing: 0.5px;">Unique Title</small>
                                    <span class="fw-bold text-dark fs-4"><?= number_format($totalTitles); ?></span>
                                </a>
                                <a href="manage_books.php#manage" class="flex-fill stat-box px-2 py-2 text-center shadow-sm text-decoration-none">
                                    <div class="icon-box icon-info mx-auto mb-1" style="width: 35px; height: 35px; font-size: 1.5rem; border-radius: 20px;">
                                        <i class="bi bi-collection"></i>
                                    </div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.80rem; letter-spacing: 0.5px;">Total Volume</small>
                                    <span class="fw-bold text-info fs-4"><?= number_format($totalVolume); ?></span>
                                </a>
                            </div>
                        </div>

                        <div class="p-3 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-muted fw-bold mb-0" style="font-size: 0.90rem; letter-spacing: 0.5px;">Branch Wise Breakdown</h6>
                                <div class="d-flex gap-2">
                                    <small class="text-muted" style="font-size: 0.65rem; font-weight: 700;">TITLES</small>
                                    <small class="text-muted" style="font-size: 0.65rem; font-weight: 700;">VOL.</small>
                                </div>
                            </div>
                            <hr class="mb-2">
                            <div class="dept-scroll-list">
                                <?php
                                $branchBookQuery = mysqli_query($con, "SELECT d.department_name, COUNT(b.id) AS branch_titles, SUM(b.quantity) AS branch_volume FROM `department` d LEFT JOIN `books` b ON d.id = b.department_id WHERE d.status = 1 GROUP BY d.id");
                                while ($branchRow = mysqli_fetch_assoc($branchBookQuery)) {
                                    $branchTitles = $branchRow['branch_titles'] ?? 0;
                                    $branchVolume = $branchRow['branch_volume'] ?? 0;
                                    $branchName = $branchRow['department_name'];
                                ?>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                        <div class="d-flex align-items-center gap-2" style="max-width: 60%;">
                                            <i class="bi bi-building text-secondary" style="font-size: 0.8rem;"></i>
                                            <span class="fw-medium text-dark text-truncate" style="font-size: 0.85rem;" title="<?= htmlspecialchars($branchName); ?>"><?= htmlspecialchars($branchName); ?></span>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <span class="badge rounded-pill d-flex align-items-center justify-content-center" title="Unique Titles" style="background-color: #f3e8ff; color: #6b21a8; width: 35px; font-weight: 600;"><?= number_format($branchTitles); ?></span>
                                            <span class="badge bg-info-subtle text-info rounded-pill d-flex align-items-center justify-content-center" title="Total Volume" style="width: 35px; font-weight: 600;"><?= number_format($branchVolume); ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <?php
                    $issueStatsQuery = mysqli_query($con, "SELECT 
                    COUNT(id) AS total_all_issued, 
                    SUM(CASE WHEN return_status = 1 THEN 1 ELSE 0 END) AS total_returned, 
                    SUM(CASE WHEN return_status = 0 AND due_date >= CURDATE() THEN 1 ELSE 0 END) AS active_issued, 
                    SUM(CASE WHEN return_status = 0 AND due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue_issued,
                    SUM(fine_amount) AS total_generated_fine,
                    SUM(CASE WHEN payment_status = 1 THEN fine_amount ELSE 0 END) AS total_paid_fine,
                    SUM(CASE WHEN payment_status = 0 THEN fine_amount ELSE 0 END) AS total_pending_fine
                    FROM `issued_books`");

                    $issueStats = mysqli_fetch_assoc($issueStatsQuery);

                    $totalAll = $issueStats['total_all_issued'] ?? 0;
                    $totalReturned = $issueStats['total_returned'] ?? 0;
                    $activeIssued = $issueStats['active_issued'] ?? 0;
                    $overdueIssued = $issueStats['overdue_issued'] ?? 0;
                    $totalFine = $issueStats['total_generated_fine'] ?? 0;
                    $paidFine = $issueStats['total_paid_fine'] ?? 0;
                    $pendingFine = $issueStats['total_pending_fine'] ?? 0;
                    ?>
                    <div class="session-widget h-100 d-flex flex-column">
                        <div class="widget-header p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-dark mb-0 fs-6"><i class="bi bi-arrow-left-right text-primary me-2"></i>Issue, Return & Fines</h5>
                            </div>
                        </div>

                        <div class="p-3 flex-grow-1 d-flex flex-column justify-content-center">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm">
                                        <div class="icon-box icon-purple d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-collection-fill"></i>
                                        </div>
                                        <h3 class="fw-bold text-dark mb-0 fs-4"><?= number_format($totalAll); ?></h3>
                                        <small class="text-muted fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">ALL ISSUED</small>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <a href="history.php#returned" class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-green d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                        <h3 class="fw-bold text-success mb-0 fs-4"><?= number_format($totalReturned); ?></h3>
                                        <small class="text-muted fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">RETURNED</small>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <div class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-blue d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-bookmark-star-fill"></i>
                                        </div>
                                        <h3 class="fw-bold text-primary mb-0 fs-4"><?= number_format($activeIssued); ?></h3>
                                        <small class="text-muted fw-bold mt-1 text-center" style="font-size: 0.65rem; letter-spacing: 0.5px;">ACTIVE ISSUED</small>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <a href="overdue-list.php#pending" class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm text-decoration-none" style="border-color: #fbd5d5 !important; background-color: #fffafb;">
                                        <div class="icon-box icon-red d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-exclamation-octagon-fill"></i>
                                        </div>
                                        <h3 class="fw-bold text-danger mb-0 fs-4"><?= number_format($overdueIssued); ?></h3>
                                        <small class="text-danger fw-bold mt-1 text-center" style="font-size: 0.65rem; letter-spacing: 0.5px;">OVERDUE</small>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <a href="fine_management.php#pending" class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm bg-white border text-decoration-none">
                                        <div class="icon-box icon-red d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        </div>
                                        <h3 class="fw-bold text-danger mb-0 fs-4">₹<?= number_format($pendingFine); ?></h3>
                                        <small class="text-danger fw-bold mt-1 text-center" style="font-size: 0.65rem; letter-spacing: 0.5px;">PENDING FINE</small>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <a href="fine_management.php#paid" class="stat-box p-1 h-100 d-flex flex-column align-items-center justify-content-center shadow-sm text-decoration-none" style="border-color: #bbf7d0 !important; background-color: #f0fdf4;">
                                        <div class="icon-box icon-green d-flex align-items-center justify-content-center mb-2" style="width:40px; height:40px; border-radius:50%; font-size:18px;">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <h3 class="fw-bold text-success mb-0 fs-4">₹<?= number_format($paidFine); ?></h3>
                                        <small class="text-success fw-bold mt-1 text-center" style="font-size: 0.65rem; letter-spacing: 0.5px;">PAID FINES</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12 col-xl-4">
                    <?php
                    $setupStatsQuery = mysqli_query($con, "SELECT (SELECT COUNT(id) FROM `academic_session` WHERE `status` = 1) AS total_sessions, (SELECT COUNT(id) FROM `department` WHERE `status` = 1) AS total_branches, (SELECT COUNT(id) FROM `rack_section` WHERE `status` = 1) AS total_racks, (SELECT COUNT(id) FROM `category` WHERE `status` = 1) AS total_categories");
                    $setupStats = mysqli_fetch_assoc($setupStatsQuery);
                    $totalSessions = $setupStats['total_sessions'] ?? 0;
                    $totalBranches = $setupStats['total_branches'] ?? 0;
                    $totalRacks = $setupStats['total_racks'] ?? 0;
                    $totalCategories = $setupStats['total_categories'] ?? 0;
                    ?>
                    <div class="session-widget h-100 d-flex flex-column">
                        <div class="widget-header p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0 fs-6"><i class="bi bi-gear-fill text-primary me-2"></i>Academic & Setup</h5>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="academic.php#session" class="stat-box px-2 py-2 d-flex align-items-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-green me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 1rem; border-radius: 8px;">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0 fs-6"><?= number_format($totalSessions); ?></h4>
                                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Sessions</small>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <a href="academic.php#dept" class="stat-box px-2 py-2 d-flex align-items-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-orange me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 1rem; border-radius: 8px;">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0 fs-6"><?= number_format($totalBranches); ?></h4>
                                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Departments</small>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <a href="academic.php#rack" class="stat-box px-2 py-2 d-flex align-items-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-info me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 1rem; border-radius: 8px;">
                                            <i class="bi bi-hdd-rack"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0 fs-6"><?= number_format($totalRacks); ?></h4>
                                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Racks</small>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-6">
                                    <a href="academic.php#category" class="stat-box px-2 py-2 d-flex align-items-center shadow-sm text-decoration-none">
                                        <div class="icon-box icon-purple me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 1rem; border-radius: 8px;">
                                            <i class="bi bi-tags"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0 fs-6"><?= number_format($totalCategories); ?></h4>
                                            <small class="text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Categories</small>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-muted fw-bold mb-0" style="font-size: 0.90rem; letter-spacing: 0.5px;">Branch-wise Categories</h6>
                                <small class="text-muted" style="font-size: 0.65rem; font-weight: 700;">COUNT</small>
                            </div>
                            <hr class="mb-2">

                            <div class="dept-scroll-list">
                                <?php
                                $branchCatQuery = mysqli_query($con, "SELECT d.department_name, COUNT(c.id) AS category_count FROM `department` d LEFT JOIN `category` c ON d.id = c.department_id AND c.status = 1 WHERE d.status = 1 GROUP BY d.id");
                                while ($branchCatRow = mysqli_fetch_assoc($branchCatQuery)) {
                                    $catCount = $branchCatRow['category_count'] ?? 0;
                                    $branchName = $branchCatRow['department_name'];
                                ?>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                        <div class="d-flex align-items-center gap-2" style="max-width: 75%;">
                                            <i class="bi bi-tag-fill text-secondary" style="font-size: 0.8rem;"></i>
                                            <span class="fw-medium text-dark text-truncate" style="font-size: 0.85rem;" title="<?= htmlspecialchars($branchName); ?>"><?= htmlspecialchars($branchName); ?></span>
                                        </div>
                                        <span class="badge bg-purple-subtle text-purple rounded-pill d-flex align-items-center justify-content-center" title="Total Categories" style="background-color: #f3e8ff; color: #6b21a8; width: 35px; font-weight: 600;"><?= number_format($catCount); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>