<?php
session_start();
include('includes/config.php');

// check student login
if (!isset($_SESSION['user'])) {
    header('location:../index.php');
    exit();
}

$userEmail = $_SESSION['user'];

// fetch system settings 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);

// Fetch logged-in user ID
$userQuery = mysqli_query($con, "SELECT `id` FROM `users` WHERE `email`='$userEmail'");
$userRow = mysqli_fetch_assoc($userQuery);
$student_id = $userRow['id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | <?= $settingRow['system_name']; ?></title>
    <link rel="icon" href="../admin/uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --main-bg: #f4f7fe;
            --primary-color: #1a237e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            overflow-x: hidden;
        }

        .main-content {
            margin-left: var(--sidebar-width, 250px);
            padding: 30px;
            transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        /* Styling for Cards */
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .stat-card {
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Profile Card Styling */
        .profile-card {
            background: linear-gradient(135deg, var(--primary-color), #3949ab);
            color: white;
            border-radius: 15px;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">

        <?php include("includes/header.php"); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Welcome back, <?= explode(' ', $userRow['name'])[0] ?? 'Student'; ?>! 👋</h4>
                <p class="text-muted small mb-0">Here is what's happening with your library account today.</p>
            </div>
            <div class="mt-3 mt-md-0 d-none d-sm-block">
                <div class="bg-white px-4 py-2 rounded-pill shadow-sm text-primary fw-semibold border">
                    <i class="bi bi-calendar3 me-2"></i> <?= date('d M, Y (l)') ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <a href="my_issued_books.php" class="col-6 col-lg-3 text-decoration-none">
                <div class="custom-card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary me-2 me-lg-3"><i class="bi bi-book"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Currently Holding</p>
                            <?php
                            // Count books currently issued to this student that are NOT returned
                            $holding_sql = "SELECT COUNT(*) as total_holding FROM `issued_books` WHERE `user_id` = '$student_id' AND `return_status` != 1";
                            $holding_result = mysqli_query($con, $holding_sql);
                            $holding_row = mysqli_fetch_assoc($holding_result);
                            $holding_count = $holding_row['total_holding'];
                            ?>
                            <h5 class="fw-bold mb-0"><?= $holding_count; ?></h5>
                        </div>
                    </div>
                </div>
            </a>
            <a href="my_issued_books.php" class="col-6 col-lg-3 text-decoration-none">
                <div class="custom-card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger me-2 me-lg-3"><i class="bi bi-exclamation-octagon"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Overdue Books</p>
                            <?php
                            // Count books that are not returned AND the due date has passed today's date
                            $overdue_sql = "SELECT COUNT(*) as total_overdue FROM `issued_books` WHERE `user_id` = '$student_id' AND `return_status` != 1 AND `due_date` < CURDATE()";
                            $overdue_result = mysqli_query($con, $overdue_sql);
                            $overdue_row = mysqli_fetch_assoc($overdue_result);
                            $overdue_count = $overdue_row['total_overdue'];
                            ?>
                            <h5 class="fw-bold mb-0 text-danger"><?= $overdue_count; ?></h5>
                        </div>
                    </div>
                </div>
            </a>
            <a href="fines.php" class="col-6 col-lg-3 text-decoration-none">
                <div class="custom-card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning me-2 me-lg-3"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Pending Fines</p>
                            <?php
                            // Calculate total pending fines
                            $fine_per_day = 5; // ₹5 per day late
                            $currentDate = date('Y-m-d');
                            $total_fine = 0;

                            // Fetch books with unpaid fines or that are currently overdue
                            $fines_sql = "SELECT fine_amount, return_status, due_date 
                              FROM `issued_books` 
                              WHERE user_id = '$student_id' 
                              AND payment_status = 0 
                              AND (fine_amount > 0 OR (return_status = 0 AND due_date < CURDATE()))";
                            $fines_result = mysqli_query($con, $fines_sql);

                            if (mysqli_num_rows($fines_result) > 0) {
                                while ($row = mysqli_fetch_assoc($fines_result)) {
                                    if ($row['return_status'] == 1) {
                                        // Returned but unpaid
                                        $currentFine = $row['fine_amount'] ?? 0;
                                    } else {
                                        // Not returned, actively accumulating
                                        $daysLate = floor((strtotime($currentDate) - strtotime($row['due_date'])) / (60 * 60 * 24));
                                        $currentFine = max($row['fine_amount'], ($daysLate * $fine_per_day));
                                    }
                                    $total_fine += $currentFine;
                                }
                            }
                            ?>
                            <h5 class="fw-bold mb-0 text-success">₹<?= number_format($total_fine, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </a>
            <a href="search_books.php" class="col-6 col-lg-3 text-decoration-none">
                <div class="custom-card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-2 me-lg-3"><i class="bi bi-collection"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Library Collection</p>
                            <?php
                            // Count total active books in the library catalog
                            $collection_sql = "SELECT COUNT(*) as total_books FROM `books` WHERE `status` = 1";
                            $collection_result = mysqli_query($con, $collection_sql);
                            $collection_row = mysqli_fetch_assoc($collection_result);
                            $total_collection = $collection_row['total_books'];
                            ?>
                            <h5 class="fw-bold mb-0 text-dark"><?= number_format($total_collection); ?></h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="custom-card mb-4 p-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">My Active Books</h6>
                        <a href="my_issued_books.php" class="text-decoration-none small">View History</a>
                    </div>
                    <div class="table-responsive p-3 pt-0">
                        <table class="table align-middle table-hover">
                            <thead class="text-muted small table-light">
                                <tr>
                                    <th>BOOK TITLE</th>
                                    <th>AUTHOR</th>
                                    <th>ISSUE DATE</th>
                                    <th>DUE DATE</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentDate = date('Y-m-d');

                                // Fetch active books for this user, limited to 5 for the dashboard view
                                $active_sql = "SELECT ib.*, b.title, b.author, b.isbn 
                               FROM `issued_books` ib 
                               JOIN `books` b ON ib.book_id = b.id 
                               WHERE ib.user_id = '$student_id' AND ib.return_status != 1 
                               ORDER BY ib.due_date ASC 
                               LIMIT 5";
                                $active_result = mysqli_query($con, $active_sql);

                                if (mysqli_num_rows($active_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($active_result)) {

                                        $issueDate = date('d M Y', strtotime($row['issued_date']));
                                        $dueDate = date('d M Y', strtotime($row['due_date']));

                                        // Dynamic Status Logic
                                        if ($currentDate > $row['due_date']) {
                                            // Calculate late days
                                            $date_diff = strtotime($currentDate) - strtotime($row['due_date']);
                                            $daysLate = floor($date_diff / (60 * 60 * 24));
                                            $statusBadge = '<span class="badge bg-danger-subtle text-danger">Overdue (' . $daysLate . ' days)</span>';
                                        } else {
                                            // Still active
                                            $statusBadge = '<span class="badge bg-success-subtle text-success">Active</span>';
                                        }
                                ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;" title="<?= $row['title']; ?>">
                                                    <?= $row['title']; ?>
                                                </div>
                                                <small class="text-muted">ISBN: <?= $row['isbn']; ?></small>
                                            </td>
                                            <td><?= $row['author']; ?></td>
                                            <td><span class="text-muted"><?= $issueDate; ?></span></td>
                                            <td><span class="fw-medium text-dark"><?= $dueDate; ?></span></td>
                                            <td><?= $statusBadge; ?></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    // Empty State if student has no active books
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-journal-check fs-3 d-block mb-2"></i>
                                                You have no active books right now.
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="custom-card p-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">Library Notices & New Arrivals</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="alert alert-info border-0 d-flex align-items-center mb-3" role="alert">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Holiday Notice:</strong> The library will remain closed on March 1st. Please plan your returns accordingly.
                            </div>
                        </div>
                        <div class="d-flex align-items-center p-2 border rounded mb-2">
                            <div class="bg-light p-2 rounded me-3"><i class="bi bi-journal-plus fs-4 text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">New Books Added to CS Department</h6>
                                <small class="text-muted">5 new copies of "Clean Code" are now available.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 ">
                <div class="custom-card profile-card p-4 mb-4 text-center position-relative overflow-hidden">
                    <i class="bi bi-fingerprint position-absolute" style="font-size: 150px; right: -30px; top: -20px; opacity: 0.3;"></i>

                    <?php
                    // fetch logged-in user data 
                    $user = mysqli_query($con, "SELECT * FROM `users` WHERE `email`='$userEmail'");
                    $userRow = mysqli_fetch_assoc($user);
                    ?>

                    <?php
                    $profileImage = !empty($userRow['profile_image'])
                        ? "uploads/profile/" . $userRow['profile_image']
                        : "https://ui-avatars.com/api/?name=" . urlencode($userRow['name']) . "&background=1a237e&color=fff&size=128";
                    ?>
                    <a href="profile.php"><img src="<?= $profileImage; ?>" class="rounded-circle profile-img mb-3" alt="Profile"></a>

                    <h5 class="fw-bold mb-1"><?= $userRow['name'] ?></h5>
                    <!-- department query  -->
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

                    <div class="bg-white bg-opacity-25 rounded p-2 text-start">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-white-50">Reg No:</small>
                            <small class="fw-bold"><?= $userRow['reg_no'] ?></small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-white-50">Email:</small>
                            <small class="fw-bold"><?= $userRow['email'] ?></small>
                        </div>
                        <!-- session  -->
                        <?php
                        $id = $userRow['session_id'];
                        $session = mysqli_query($con, "SELECT * FROM `academic_session` WHERE `id`='$id'");
                        $sessionRow = mysqli_fetch_assoc($session);
                        ?>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-white-50">Semester:</small>
                            <small class="fw-bold"><?= $sessionRow['semester']; ?></small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-white-50">Session:</small>
                            <small class="fw-bold"><?= $sessionRow['session']; ?></small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-white-50">Account Status:</small>
                            <?php
                            if ($userRow['status'] == 1) {
                                echo "<small class='badge bg-success'>Active</small>";
                            } else {
                                echo "<small class='badge bg-danger'>Inactive</small>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="custom-card p-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">Quick Links</h6>
                    </div>
                    <div class="list-group list-group-flush rounded-bottom">
                        <a href="search_books.php" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0">
                            <i class="bi bi-bookmark-plus text-primary me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Request a Book</h6>
                                <small class="text-muted">Send a request to the librarian</small>
                            </div>
                        </a>
                        <a href="fines.php" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0">
                            <i class="bi bi-credit-card text-warning me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Pay Fines</h6>
                                <small class="text-muted">Clear your pending dues</small>
                            </div>
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0">
                            <i class="bi bi-gear text-secondary me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Account Settings</h6>
                                <small class="text-muted">Update password and details</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>