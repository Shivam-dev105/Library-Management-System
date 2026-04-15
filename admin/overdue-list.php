<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}


// for overdue returning
if (isset($_POST['overdue_return'])) {

    $reg = $_POST['reg'];
    $isbn = $_POST['isbn'];
    $return_date = $_POST['return_date'];
    $lateDays = $_POST['lateDays'];
    $fine = $_POST['fine'];
    $payment_status = $_POST['payment_status'];

    $BookUnique = $_POST['BookUniqueCode'];
    $unique = mysqli_query($con, "SELECT * FROM `book_copies` WHERE `unique_code`='$BookUnique'");
    $uniqueId = mysqli_fetch_assoc($unique);
    $BookUniqueId = $uniqueId['id'];

    // Payment date ka logic set karna
    // Agar status 1 hai toh CURDATE() warna NULL (Bina quotes ke taaki SQL isko function/keyword samjhe)
    $payment_date_sql = ($payment_status == 1) ? "CURDATE()" : "NULL";

    // Get issued id (Added book_unique_code to WHERE clause)
    $getIssue = mysqli_query($con, "SELECT issued_books.id, issued_books.due_date, books.id AS book_id, users.name, users.reg_no, books.title, books.isbn
        FROM issued_books
        JOIN users ON issued_books.user_id = users.id
        JOIN books ON issued_books.book_id = books.id
        WHERE users.reg_no='$reg'
        AND books.isbn='$isbn'
        AND issued_books.book_unique_code='$BookUniqueId' 
        AND issued_books.return_status=0
    ");

    if (mysqli_num_rows($getIssue) > 0) {
        $row = mysqli_fetch_assoc($getIssue);
        $issued_id = $row['id'];
        $book_id   = $row['book_id'];

        // Yahan payment_date=$payment_date_sql add kiya gaya hai (bina single quotes ke)
        mysqli_query($con, "UPDATE issued_books
        SET return_date='$return_date',
            late_days='$lateDays',
            fine_amount='$fine',
            return_status=1,
            payment_status='$payment_status',
            payment_date=$payment_date_sql
        WHERE id='$issued_id'
        ");

        // Increase main book quantity
        mysqli_query($con, "UPDATE books
        SET quantity = quantity + 1
        WHERE id='$book_id'
        ");

        // ✅ 3. Update book_copies status (Update the EXACT copy using unique_code)
        mysqli_query($con, "UPDATE `book_copies` 
        SET `status` = '1' 
        WHERE `unique_code`='$BookUnique'");

        echo "<script>alert('Book Returned Successfully');window.location.href='overdue-list.php#pending';</script>";
    } else {
        echo "<script>alert('Something went wrong or this specific book copy is not issued to this user.');window.location.href='overdue-list.php';</script>";
    }
}

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
$lateFineMoney = $settingRow['late_fine'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue List | <?= $settingRow['system_name']; ?></title>
    <link rel="icon" href="uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --main-bg: #f4f7fe;
            --primary-indigo: #1a237e;
            --danger-red: #d32f2f;
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
            /* gap: 10px; */
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

        @media (max-width: 576px) {
            .nav-tabs .nav-item {
                width: 100%;
            }

            .nav-tabs .nav-link {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
                display: block;
            }
        }

        .manage-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        /* Red Alert for Overdue badge */
        .badge-overdue {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--danger-red);
            font-weight: 600;
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

        /* Mobile View Fix */
        /* @media (max-width: 767px) {

            .overdue-tabs {
                display: flex;
                flex-wrap: nowrap;
                /* wrap band */
        /* justify-content: space-between; */
        /* } */

        /* .overdue-tabs .nav-item { */
        /* flex: 1; */
        /* sab equal width */
        /* text-align: center;
            }

            .overdue-tabs .nav-link {
                padding: 10px 0;
                font-size: 20px;
            }
        } */

        @media (max-width: 768px) {
            .custom-table thead {
                display: none;
            }

            .custom-table tr {
                display: block;
                position: relative;
                margin-bottom: 15px;
                background: #fff;
                padding: 15px;
                border-radius: 12px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .custom-table td {
                display: block;
                text-align: right;
                padding-left: 50%;
                position: relative;
                border: none;
                padding-top: 8px;
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
            <h4 class="fw-bold mb-4 custom-underline">Overdue Monitor</h4>

            <ul class="nav nav-tabs overdue-tabs" id="overdueTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab"><i class="bi bi-exclamation-octagon me-2 mb-md-2"></i>Pending Returns</button>
                </li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="pending">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                            <h5 class="fw-bold mb-2 mb-md-0 text-danger">
                                <i class="bi bi-exclamation-octagon me-2"></i>Pending Returns
                            </h5>

                            <div class="d-flex gap-2">
                                <input type="text" id="pendingSearchInput" class="form-control form-control-sm" placeholder="Search Name, Reg No, Title, ISBN...">
                                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Student Details</th>
                                        <th>Book Details</th>
                                        <th>Due Date</th>
                                        <th>Days Late</th>
                                        <th>Fine (₹)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingTableBody">
                                    <?php
                                    $today = date("Y-m-d");
                                    $issuedBook = mysqli_query($con, "SELECT issued_books.*, users.name, users.reg_no, books.title, books.isbn
                                        FROM issued_books
                                        JOIN users ON issued_books.user_id = users.id
                                        JOIN books ON issued_books.book_id = books.id
                                        WHERE issued_books.return_status = 0
                                        AND issued_books.due_date < '$today'
                                    ");
                                    if (mysqli_num_rows($issuedBook) > 0) {
                                        $i = 1;
                                        while ($row = mysqli_fetch_assoc($issuedBook)) {
                                            $book_id = $row['book_id'];
                                            $book_unique_code_id = $row['book_unique_code'];
                                            $due_date = $row['due_date'];
                                            $late_days = (strtotime($today) - strtotime($due_date)) / (60 * 60 * 24);
                                            $fine = $late_days * $lateFineMoney; // ₹5 per day example
                                    ?>
                                            <tr>
                                                <td data-label="Qty -" class="qty-cell">
                                                    <span class="qty-badge"><?= $i++; ?></span>
                                                </td>
                                                <td data-label="Student -">
                                                    <div class="fw-bold"><?= $row['name']; ?></div>
                                                    <small class="text-muted">Reg: <?= $row['reg_no']; ?></small>
                                                </td>
                                                <td data-label="Book -">
                                                    <div><?= $row['title']; ?></div>

                                                    <?php
                                                    $unique = mysqli_query($con, "SELECT `unique_code` FROM `book_copies` WHERE `id` = '$book_unique_code_id' AND `book_id` = '$book_id'");
                                                    $uniqueRow = mysqli_fetch_assoc($unique);
                                                    ?>
                                                    <small class="text-muted"><i class="bi bi-upc-scan me-1"></i> <?= $uniqueRow['unique_code'];  ?></small>

                                                </td>
                                                <td data-label="Due -"><?= date("d-m-Y", strtotime($due_date)); ?></td>
                                                <td data-label="Late -">
                                                    <span class="badge badge-overdue"><?= $late_days; ?> Days * ₹ <?= $lateFineMoney; ?></span>
                                                </td>
                                                <td data-label="Fine -">
                                                    <span class="text-danger fw-bold"> ₹ <?= number_format($fine, 2); ?></span>
                                                </td>
                                                <td data-label="Action -">
                                                    <button class="btn btn-sm btn-danger text-white return-btn" title="Process Return" data-id="<?= $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#returnModal">
                                                        <i class="bi bi-check2-square me-2"></i>Return
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php }
                                    } else {
                                        // ==========================================
                                        // EMPTY STATE UI (If no books are overdue)
                                        // ==========================================
                                        ?>
                                        <tr>
                                            <td colspan="7" class="p-0">
                                                <div class="d-flex align-items-center justify-content-center text-center"
                                                    style="min-height: 250px;">

                                                    <div class="px-3">
                                                        <i class="bi bi-check-circle text-success opacity-50 mb-3"
                                                            style="font-size: 3rem;"></i>

                                                        <h6 class="text-muted fw-bold mb-1">
                                                            No Overdue Books!
                                                        </h6>

                                                        <p class="text-muted small mb-0">
                                                            All issued books are currently within their due dates.
                                                        </p>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="small text-muted">
                                <i class="bi bi-info-circle me-1"></i> Fine is calculated at ₹<?= $lateFineMoney; ?> per day after the due date.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Return Modal -->
        <div class="modal fade" id="returnModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow-lg border-0">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-arrow-return-left me-2"></i>Process Book Return
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="#" method="POST">
                        <div class="modal-body p-4">

                            <!-- Hidden Fields -->
                            <input type="hidden" name="reg" id="retReg">
                            <input type="hidden" name="isbn" id="retIsbn">

                            <!-- <div class="mb-3">
                                <label class="form-label small fw-bold">Student Name</label>
                                <input type="text" class="form-control" id="retName" readonly>
                            </div> -->

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Registration No</label>
                                <input type="text" class="form-control" id="retRegDisplay" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Book Title</label>
                                <input type="text" class="form-control" id="retBook" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Book Unique Code</label>
                                <input type="text" class="form-control" name="BookUniqueCode" id="retUniqueCode" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Return Date</label>
                                <input type="date" name="return_date"
                                    class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Late Days</label>
                                <input type="text" name="lateDays" class="form-control" id="retLate" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Fine Amount (₹)</label>
                                <input type="number" name="fine" class="form-control" id="retFine">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Fine Payment Status</label>

                                <select name="payment_status" id="retPayment" class="form-select">
                                    <option value="" selected disabled>-- Select Payment Done</option>
                                    <option value="1">Paid</option>
                                    <option value="0">Unpaid</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" name="overdue_return" class="btn btn-success">
                                Confirm Return
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- search box id redirect -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("pendingSearchInput");
            const tableBody = document.getElementById("pendingTableBody");
            const rows = tableBody.getElementsByTagName("tr");

            // 1. Search Logic
            searchInput.addEventListener("input", function() {
                const filter = searchInput.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    let studentCol = rows[i].getElementsByTagName("td")[1];
                    let bookCol = rows[i].getElementsByTagName("td")[2];

                    if (studentCol || bookCol) {
                        let studentText = studentCol.textContent || studentCol.innerText;
                        let bookText = bookCol.textContent || bookCol.innerText;

                        if (studentText.toLowerCase().indexOf(filter) > -1 || bookText.toLowerCase().indexOf(filter) > -1) {
                            rows[i].style.display = "";
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                }
            });

            // 2. URL Parameter ko read karna aur auto-search karna (UPDATED)
            let queryString = window.location.search;

            // Agar ? hash (#) ke baad aaya hai, toh usko wahan se nikaalo
            if (!queryString && window.location.hash.includes('?')) {
                queryString = window.location.hash.split('?')[1];
            }

            const urlParams = new URLSearchParams(queryString);
            const userReg = urlParams.get('user_reg'); // URL se user_reg ki value lena

            if (userReg && searchInput) {
                // Search box me reg_no daal do
                searchInput.value = userReg;

                // Input event ko manually trigger karo taaki list turant filter ho jaye
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    </script>

    <!-- url # using  -->

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

    <!-- return modal  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const returnButtons = document.querySelectorAll(".return-btn");

            returnButtons.forEach(btn => {
                btn.addEventListener("click", function() {

                    let issuedId = this.dataset.id;

                    // AJAX Call
                    fetch("ajax/get_return_details.php?id=" + issuedId)
                        .then(response => response.json())
                        .then(data => {

                            // Hidden fields
                            document.getElementById("retReg").value = data.reg_no;
                            document.getElementById("retIsbn").value = data.isbn;
                            document.getElementById("retUniqueCode").value = data.uniqueCode;

                            // Display fields
                            // document.getElementById("retName").value = data.name;
                            document.getElementById("retRegDisplay").value = data.reg_no;
                            document.getElementById("retBook").value = data.title;
                            document.getElementById("retLate").value = data.late_days;
                            document.getElementById("retFine").value = data.fine;
                        });

                });
            });

        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>