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


// Fetch Issued Books for this specific student
// Assuming your table is named `issued_books` with columns: id, book_id, student_id, issue_date, due_date, return_date, status

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Issued Books | <?= $settingRow['system_name']; ?></title>
    <!-- favicon  -->
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

        .issued-row {
            border-left: 4px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .issued-row:hover {
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
                <h4 class="fw-bold mb-0">My Active Issued Books</h4>
                <p class="text-muted small mb-0">Track your current borrowings and return history</p>
            </div>
            <a href="search_books.php" class="btn btn-primary rounded-pill px-4 shadow-sm d-none d-md-inline-block">
                <i class="bi bi-plus-lg me-2"></i>Request New Book
            </a>
        </div>

        <div class="custom-card p-4 mb-4">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>BOOK DETAILS</th>
                            <th>ISSUE DATE</th>
                            <th>DUE DATE</th>
                            <th>STATUS</th>
                            <th>FINE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch Issued Books for this specific student
                        // We filter out returned books (return_status != 1) so this only shows active/overdue books
                        $issue_sql = "SELECT ib.*, b.title, b.author, b.isbn, b.book_cover 
                              FROM `issued_books` ib 
                              JOIN `books` b ON ib.book_id = b.id 
                              WHERE ib.user_id = '$student_id' AND ib.return_status != 1 
                              ORDER BY ib.issued_date DESC";
                        $issue_result = mysqli_query($con, $issue_sql);

                        if (mysqli_num_rows($issue_result) > 0) {
                            while ($row = mysqli_fetch_assoc($issue_result)) {

                                // Image fallback
                                $coverImage = !empty($row['book_cover']) && file_exists("../admin/uploads/book_cover/" . $row['book_cover'])
                                    ? "../admin/uploads/book_cover/" . $row['book_cover']
                                    : "../admin/uploads/book_cover/book_cover.png";

                                // Date formatting
                                $issueDate = date('d M Y', strtotime($row['issued_date']));
                                $dueDate = date('d M Y', strtotime($row['due_date']));

                                // Dynamic Fine and Status Calculation
                                $fine_per_day = $lateFineMoney; // Penalty rate: ₹5 per day
                                $currentDate = date('Y-m-d');

                                $statusBadge = "";
                                $fineAmount = "-";
                                $textClass = "text-muted"; // Default text color for the fine column

                                // Since the SQL query filters OUT returned books, we only need to check if it's active or overdue
                                if ($currentDate > $row['due_date']) {
                                    // OVERDUE LOGIC
                                    // Calculate the difference in days
                                    $date_diff = strtotime($currentDate) - strtotime($row['due_date']);
                                    $daysLate = floor($date_diff / (60 * 60 * 24));

                                    // Calculate dynamic fine
                                    $calculatedFine = $daysLate * $fine_per_day;
                                    $fineAmount = '₹' . $calculatedFine;
                                    $textClass = "text-danger fw-bold"; // Make overdue fines red

                                    $statusBadge = '<span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i> Overdue (' . $daysLate . ' days)</span>';
                                } else {
                                    // ACTIVE LOGIC (Not late yet)
                                    $fineAmount = '-';
                                    $textClass = "text-muted";
                                    $statusBadge = '<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-book me-1"></i> Active</span>';
                                }
                        ?>
                                <tr class="issued-row">
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
                                    <td><span class="text-muted fw-medium"><?= $issueDate; ?></span></td>
                                    <td><span class="text-dark fw-bold"><?= $dueDate; ?></span></td>
                                    <td><?= $statusBadge; ?></td>
                                    <td>
                                        <span class="<?= $textClass; ?>">
                                            <?= $fineAmount; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            // Empty State
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="fw-bold mt-3">No active books</h5>
                                    <p class="text-muted">You don't have any books currently issued. Head over to the library collection to find your next read!</p>
                                    <a href="search_books.php" class="btn btn-outline-primary mt-2 rounded-pill px-4">Browse Books</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>