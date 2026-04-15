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
    <title>Borrowing History | <?= $settingRow['system_name']; ?></title>
    <!-- faviocn  -->
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
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }

        /* Timeline style left border for rows */
        .history-row {
            border-left: 4px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .history-row:hover {
            border-left-color: var(--primary-color);
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
                <h4 class="fw-bold mb-0">Borrowing History</h4>
                <p class="text-muted small mb-0">A complete log of all the books you have read and returned.</p>
            </div>
            <a href="my_issued_books.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm d-none d-md-inline-block">
                <i class="bi bi-book me-2"></i>View Active Books
            </a>
        </div>

        <div class="custom-card p-4 mb-4">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0 border-top-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>BOOK DETAILS</th>
                            <th>BORROWED ON</th>
                            <th>RETURNED ON</th>
                            <th>LATE DAYS</th>
                            <th>FINE CHARGED</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // =========================================
                        // 1. PAGINATION SETUP
                        // =========================================
                        $limit = 10; // Number of records per page
                        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
                        $offset = ($page - 1) * $limit;

                        // Count total records for pagination
                        $count_sql = "SELECT COUNT(*) as total FROM `issued_books` WHERE `user_id` = '$student_id' AND `return_status` = 1";
                        $count_result = mysqli_query($con, $count_sql);
                        $count_row = mysqli_fetch_assoc($count_result);
                        $total_records = $count_row['total'];
                        $total_pages = ceil($total_records / $limit);

                        // =========================================
                        // 2. FETCH PAGINATED DATA
                        // =========================================
                        $history_sql = "SELECT ib.*, b.title, b.author, b.isbn, b.book_cover 
                                FROM `issued_books` ib 
                                JOIN `books` b ON ib.book_id = b.id 
                                WHERE ib.user_id = '$student_id' AND ib.return_status = 1 
                                ORDER BY ib.return_date DESC, ib.issued_date DESC
                                LIMIT $limit OFFSET $offset";
                        $history_result = mysqli_query($con, $history_sql);

                        if (mysqli_num_rows($history_result) > 0) {
                            while ($row = mysqli_fetch_assoc($history_result)) {
                                // Image fallback
                                $coverImage = !empty($row['book_cover']) && file_exists("../admin/uploads/book_cover/" . $row['book_cover'])
                                    ? "../admin/uploads/book_cover/" . $row['book_cover']
                                    : "../admin/uploads/book_cover/book_cover.png";

                                // Date formatting
                                $issuedDate = date('d M Y', strtotime($row['issued_date']));
                                $returnDate = !empty($row['return_date']) ? date('d M Y', strtotime($row['return_date'])) : 'Unknown';

                                // Late days logic
                                $lateDays = !empty($row['late_days']) && $row['late_days'] > 0 ? $row['late_days'] : 0;
                                $lateBadge = $lateDays > 0 ? "<span class='text-danger fw-semibold'>$lateDays Days</span>" : "<span class='text-muted'>On Time</span>";

                                // Fine logic
                                $hasFine = !empty($row['fine_amount']) && $row['fine_amount'] > 0;
                                $fineAmount = $hasFine ? '₹' . $row['fine_amount'] : '₹0';
                                $fineClass = $hasFine ? 'text-danger fw-bold' : 'text-success fw-medium';

                                // Status Badge Logic (Based on payment_status and fine amount)
                                $paymentStatus = isset($row['payment_status']) ? $row['payment_status'] : 0;

                                if (!$hasFine) {
                                    $statusBadge = '<span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill"><i class="bi bi-archive me-1"></i> Returned</span>';
                                } elseif ($paymentStatus == 1) {
                                    $statusBadge = '<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Paid Return</span>';
                                } else {
                                    $statusBadge = '<span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i> Unpaid Return</span>';
                                }
                        ?>
                                <tr class="history-row">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $coverImage; ?>" class="book-thumb me-3" alt="Book" style="width: 45px; height: 65px; object-fit: cover; border-radius: 5px;">
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark"><?= $row['title']; ?></h6>
                                                <p class="text-muted small mb-0">By <?= $row['author']; ?></p>

                                                <?php
                                                $uniqueId = $row['book_unique_code'];
                                                $bookId = $row['book_id'];
                                                $bookBarcode = mysqli_query($con, "SELECT * FROM `book_copies` WHERE `id` ='$uniqueId' AND `book_id` = '$bookId'");
                                                $bookBarcodeName = mysqli_fetch_assoc($bookBarcode);
                                                $barcodeName = $bookBarcodeName['unique_code'];
                                                ?>
                                                <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>
                                                    <?= $barcodeName; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-muted fw-medium"><?= $issuedDate; ?></span></td>
                                    <td><span class="text-dark fw-bold"><?= $returnDate; ?></span></td>
                                    <td><?= $lateBadge; ?></td>
                                    <td><span class="<?= $fineClass; ?>"><?= $fineAmount; ?></span></td>
                                    <td>
                                        <?= $statusBadge; ?>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            // Empty State
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 border-0">
                                    <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="fw-bold mt-3">Your history is empty</h5>
                                    <p class="text-muted">You haven't completed any book returns yet.</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="History Pagination" class="mt-4 border-top pt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill px-3 me-1" href="?page=<?= $page - 1; ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link rounded-circle mx-1" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill px-3 ms-1" href="?page=<?= $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>