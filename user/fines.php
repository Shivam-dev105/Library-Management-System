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
$lateFineMoney = $settingRow['late_fine'];

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
    <title>My Fines | <?= $settingRow['system_name']; ?></title>
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

        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .book-thumb {
            width: 45px;
            height: 65px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }

        .payment-card {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border: 1px solid #e9ecef;
        }

        .fine-row {
            transition: background-color 0.2s;
        }

        .fine-row:hover {
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
                <h4 class="fw-bold mb-0">Fine Details</h4>
                <p class="text-muted small mb-0">View and manage your library penalties</p>
            </div>
            <a href="dashboard.php"
                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm d-none d-md-inline-block">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="custom-card p-4 h-100">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Penalty Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 border-top-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>BOOK</th>
                                    <th>DUE DATE</th>
                                    <th>DAYS LATE</th>
                                    <th>FINE AMOUNT</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentDate = date('Y-m-d');

                                // =========================================
                                // 1. CALCULATE TOTAL FINES (Direct from DB)
                                // =========================================
                                $total_sql = "SELECT fine_amount FROM `issued_books` 
                                              WHERE user_id = '$student_id' 
                                              AND payment_status = 0 
                                              AND fine_amount > 0";
                                $total_result = mysqli_query($con, $total_sql);

                                $total_fine = 0;
                                $total_books_with_fines = mysqli_num_rows($total_result);

                                while ($row = mysqli_fetch_assoc($total_result)) {
                                    $total_fine += $row['fine_amount']; // Fetching directly from DB
                                }

                                // =========================================
                                // 2. PAGINATION LOGIC
                                // =========================================
                                $limit = 10; // Number of records per page
                                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
                                $offset = ($page - 1) * $limit;
                                $total_pages = ceil($total_books_with_fines / $limit);

                                // =========================================
                                // 3. FETCH PAGINATED DATA
                                // =========================================
                                $fines_sql = "SELECT ib.*, b.title, b.author, b.book_cover FROM `issued_books` ib 
                                              JOIN `books` b ON ib.book_id = b.id WHERE ib.user_id = '$student_id' 
                                              AND ib.payment_status = 0 
                                              AND ib.fine_amount > 0 
                                              ORDER BY ib.due_date ASC 
                                              LIMIT $limit OFFSET $offset";
                                $fines_result = mysqli_query($con, $fines_sql);

                                if (mysqli_num_rows($fines_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($fines_result)) {
                                        $coverImage = !empty($row['book_cover']) && file_exists("../admin/uploads/book_cover/" . $row['book_cover'])
                                            ? "../admin/uploads/book_cover/" . $row['book_cover']
                                            : "../admin/uploads/book_cover/book_cover.png";

                                        $dueDate = date('d M Y', strtotime($row['due_date']));

                                        // Fetch exact fine and late days from DB
                                        $daysLate = $row['late_days'] ?? 0;
                                        $currentFine = $row['fine_amount'] ?? 0;

                                        // Badge purely for display based on return status
                                        if ($row['return_status'] == 1) {
                                            $badge = '<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill small">Unpaid (Returned)</span>';
                                        } else {
                                            $badge = '<span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill small"><i class="bi bi-clock-history me-1"></i> Unpaid (Not Returned)</span>';
                                        }
                                ?>
                                        <tr class="fine-row">
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $coverImage; ?>" class="book-thumb me-3" alt="Book">
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark text-truncate" style="max-width: 200px;" title="<?= $row['title']; ?>"><?= $row['title']; ?></h6>
                                                        <p class="text-muted small mb-0">By <?= $row['author']; ?></p>
                                                        <?php
                                                        $uniqueId = $row['book_unique_code'];
                                                        $bookId = $row['book_id'];
                                                        $bookBarcode = mysqli_query($con, "SELECT * FROM `book_copies` WHERE `id` ='$uniqueId' AND `book_id` = '$bookId'");
                                                        $bookBarcodeName = mysqli_fetch_assoc($bookBarcode);
                                                        $barcodeName = $bookBarcodeName['unique_code'] ?? 'N/A';
                                                        ?>
                                                        <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>
                                                            <?= $barcodeName; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="text-dark fw-medium"><?= $dueDate; ?></span></td>
                                            <td><span class="text-danger fw-bold"><?= $daysLate; ?> Days</span></td>
                                            <td><span class="fw-bold fs-6 text-dark">₹<?= number_format($currentFine, 2); ?></span></td>
                                            <td><?= $badge; ?></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 border-0">
                                            <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded mb-3">
                                                <i class="bi bi-emoji-smile text-success fs-1"></i>
                                            </div>
                                            <h5 class="fw-bold mt-2">All Clear!</h5>
                                            <p class="text-muted">You have no pending fines or overdue books.</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="custom-card payment-card p-4 text-center mb-4">
                    <div class="mb-4">
                        <i class="bi bi-cash-stack text-primary" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="text-muted mb-1">Total Outstanding Dues</h5>
                    <h1 class="display-4 fw-bold text-dark mb-4">₹<?= number_format($total_fine, 2); ?></h1>

                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2 text-start">
                        <span class="text-muted">Penalty Rate:</span>
                        <span class="fw-medium">₹<?= $lateFineMoney; ?> / day</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-4 text-start">
                        <span class="text-muted">Books Penalized:</span>
                        <span class="fw-medium"><?= $total_books_with_fines; ?> Books</span>
                    </div>

                    <?php if ($total_fine > 0): ?>
                        <button class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-semibold mb-2" onclick="alert('Visit the Librarian desk with fine amount')">
                        <!-- <button class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-semibold mb-2" onclick="alert('Payment Gateway Integration Pending')"> -->
                            <i class="bi bi-credit-card me-2"></i> Pay Now
                        </button>
                        <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i> Please clear dues before the semester end.</p>
                    <?php else: ?>
                        <button class="btn btn-success w-100 rounded-pill py-2 disabled opacity-75">
                            <i class="bi bi-check2-circle me-2"></i> No Dues Pending
                        </button>
                    <?php endif; ?>
                </div>

                <div class="custom-card p-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-shield-exclamation text-primary me-2"></i> Library Policy</h6>
                    <p class="text-muted small mb-0">
                        Fines continue to accumulate automatically daily for unreturned overdue books. If you believe there is an error in your penalty calculation, please visit the Librarian's desk with your ID card.
                    </p>
                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>