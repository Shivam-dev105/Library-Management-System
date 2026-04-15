<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

if (isset($_POST['issue_book'])) {
    $user_reg   = mysqli_real_escape_string($con, trim($_POST['reg_no']));
    $unique_code = mysqli_real_escape_string($con, trim($_POST['unique_code'])); // ISBN की जगह unique_code
    $issue_date = $_POST['issue_date'];
    $due_date   = $_POST['due_date'];

    // 🔎 1. Get User ID
    $user_query = mysqli_query($con, "SELECT `id` FROM `users` WHERE `reg_no`='$user_reg' AND `status`=1");

    $unique_code_id = mysqli_query($con, "SELECT `id` FROM `book_copies` WHERE `unique_code`='$unique_code'");

    // 🔎 2. Get Book ID and Check if Copy is Available
    // अब हम book_copies टेबल से चेक करेंगे कि ये physical copy available है या नहीं
    $copy_query = mysqli_query($con, "SELECT `id`, `book_id` FROM `book_copies` WHERE `unique_code`='$unique_code' AND `status`=1");

    if (mysqli_num_rows($user_query) > 0 && mysqli_num_rows($copy_query) > 0) {

        $user_row = mysqli_fetch_assoc($user_query);
        $copy_row = mysqli_fetch_assoc($copy_query);
        $unique_row = mysqli_fetch_assoc($unique_code_id);

        $user_id = $user_row['id'];
        $book_id = $copy_row['book_id'];
        $copy_id = $copy_row['id'];
        $unique_id = $unique_row['id'];

        // ✅ Insert into issued_books
        $issued_book = mysqli_query($con, "INSERT INTO `issued_books` (`user_id`, `book_id`,`book_unique_code`, `issued_date`, `due_date`)
        VALUES ('$user_id', '$book_id','$unique_id','$issue_date', '$due_date')");

        if ($issued_book) {
            // 📉 Reduce main book quantity by 1
            mysqli_query($con, "UPDATE `books` SET `quantity` = quantity - 1 WHERE `id`='$book_id'");
            // 🛑 Mark this specific barcode copy as 'Issued' so no one else can issue it
            mysqli_query($con, "UPDATE `book_copies` SET `status` = '0' WHERE `id`='$copy_id'");

            echo "<script>alert('New Book Issued Successfully');window.location.href='issue-return.php#issue';</script>";
        } else {
            echo "<script>alert('Something went wrong while issuing');window.location.href='issue-return.php#issue';</script>";
        }
    } else {
        echo "<script>alert('Invalid Student OR Book Copy is not available');window.location.href='issue-return.php#issue';</script>";
    }
}

// return books 
if (isset($_POST['confirm_return'])) {
    $issued_id = mysqli_real_escape_string($con, $_POST['issued_id']);
    $return_date = mysqli_real_escape_string($con, $_POST['return_date']);

    // 🔎 Get Book ID first from issued_books
    $getBook = mysqli_query($con, "SELECT book_id FROM issued_books WHERE id='$issued_id'");

    if (mysqli_num_rows($getBook) > 0) {
        $bookRow = mysqli_fetch_assoc($getBook);
        $book_id = $bookRow['book_id'];

        // ✅ 1. Update issued_books (मार्क एज़ रिटर्न)
        mysqli_query($con, "UPDATE issued_books SET return_date='$return_date', return_status=1 WHERE id='$issued_id'");

        // ✅ 2. Increase main book quantity (टोटल बची हुई किताबें बढ़ाएं)
        mysqli_query($con, "UPDATE books SET quantity = quantity + 1 WHERE id='$book_id'");

        // ✅ 3. Update book_copies status (उस किताब की एक कॉपी को वापस '1' कर दें)
        // LIMIT 1 लगाया गया है ताकि सिर्फ एक ही कॉपी का स्टेटस 1 हो (सारी इश्यू हुई कॉपीज का नहीं)
        mysqli_query($con, "UPDATE `book_copies` SET `status` = '1' WHERE `book_id`='$book_id' AND `status` != '1' LIMIT 1");

        echo "<script>alert('Book Returned Successfully');window.location.href='issue-return.php#return';</script>";
    } else {
        echo "<script>alert('Something went wrong while returning');window.location.href='issue-return.php#return';</script>";
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
    <title>Issue & Return | <?= $settingRow['system_name']; ?></title>
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

        /* Custom UI Components */
        .nav-tabs {
            border: none;
            margin-bottom: 20px;
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

        /* Card Styling */
        .manage-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
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

        /* Responsive Labels for Mobile Table */
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
            <h4 class="fw-bold mb-4 custom-underline">Circulation Desk</h4>

            <ul class="nav nav-tabs" id="circulationTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="issue-tab" data-bs-toggle="tab" data-bs-target="#issue" type="button" role="tab">
                        <i class="bi bi-journal-arrow-up me-2"></i>Issue Book
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="return-tab" data-bs-toggle="tab" data-bs-target="#return" type="button" role="tab">
                        <i class="bi bi-journal-arrow-down me-2"></i>Return Book
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="circulationTabsContent">

                <div class="tab-pane fade show active" id="issue" role="tabpanel">
                    <div class="card manage-card p-4">
                        <h5 class="fw-bold mb-4 custom-underline">New Issue Transaction</h5>

                        <form action="#" method="POST">

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Student Registration Number</label>
                                    <div class="input-group">
                                        <input type="text" name="reg_no" id="reg_no" class="form-control"placeholder="Enter Reg No (e.g. 152...)" maxlength="10" required>
                                        <button class="btn btn-outline-secondary" type="button" id="startScanBtn"><i class="bi bi-qr-code-scan"></i> Scan QR</button>
                                    </div>
                                    <div class="form-text text-muted" id="studentResult">Verify student ID before issuing.</div>
                                    <div id="qr-reader" class="mt-2" style="display: none; width: 100%; border-radius: 8px; overflow: hidden; border: 1px solid #ccc;"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Scan Book Barcode</label>
                                    <div class="input-group">
                                        <input type="text" name="unique_code" id="unique_code" class="form-control" placeholder="e.g., GPB-0001" required>
                                        <button class="btn btn-outline-secondary" type="button" id="startBarcodeScanBtn">
                                            <i class="bi bi-upc-scan"></i> Scan Book
                                        </button>
                                    </div>
                                    <div class="form-text text-muted" id="bookResult">Ensure book copy is currently "Available".</div>
                                    <div id="barcode-reader" class="mt-2" style="display: none; width: 100%; border-radius: 8px; overflow: hidden; border: 1px solid #ccc;"></div>
                                </div>

                                <!-- Issue Date -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Issue Date</label>
                                    <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Due Date -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Due Date</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                    <div class="form-text text-muted" id="bookResult">Maximum 10 days after issue date.</div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="issue_book" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Issue Book
                                    </button>

                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="return" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

                            <h5 class="fw-bold mb-2 mb-md-0 custom-underline">
                                <i class="bi bi-journal-check me-2"></i>Issued Books
                            </h5>

                            <div class="d-flex gap-2">
                                <input type="text" id="issueSearchInput" class="form-control form-control-sm" placeholder="Search Name, Reg No, Title, ISBN...">
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
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="issuedTableBody">
                                    <?php
                                    $issuedBook = mysqli_query($con, "SELECT * FROM `issued_books` WHERE `return_status` != 1");
                                    if (mysqli_num_rows($issuedBook) > 0) {
                                        $i = 1;
                                        while ($issuedBook_row = mysqli_fetch_assoc($issuedBook)) {
                                    ?>
                                            <tr>
                                                <td data-label="Qty -" class="qty-cell">
                                                    <span class="qty-badge"><?= $i++; ?></span>
                                                </td>
                                                <?php
                                                $id = $issuedBook_row['user_id'];
                                                $user = mysqli_query($con, "SELECT `name`,`reg_no` FROM `users` WHERE `id` = '$id'");
                                                $userRow = mysqli_fetch_assoc($user);

                                                ?>
                                                <td data-label="Student -">
                                                    <div class="fw-bold"><?= $userRow['name']; ?></div>
                                                    <small class="text-muted">Reg: <?= $userRow['reg_no']; ?></small>
                                                </td>

                                                <?php
                                                $id = $issuedBook_row['book_id'];
                                                $book = mysqli_query($con, "SELECT `title`,`isbn` FROM `books` WHERE `id` = '$id'");
                                                $bookRow = mysqli_fetch_assoc($book);
                                                ?>
                                                <td data-label="Book -">
                                                    <div><?= $bookRow['title']; ?></div>
                                                    <?php
                                                    $uniqueid = $issuedBook_row['book_unique_code'];
                                                    $unique = mysqli_query($con, "SELECT `unique_code` FROM `book_copies` WHERE `id` = '$uniqueid' AND `book_id` = '$id'");
                                                    $uniqueRow = mysqli_fetch_assoc($unique);
                                                    ?>
                                                    <small class="text-muted"><i class="bi bi-upc-scan me-1"></i> <?= $uniqueRow['unique_code']; ?></small>
                                                </td>

                                                <td data-label="Issued -"><?= date("d-m-Y", strtotime($issuedBook_row['issued_date'])); ?></td>

                                                <?php
                                                $today = date("Y-m-d");
                                                $due_date = $issuedBook_row['due_date'];
                                                $isOverdue = ($today > $due_date);
                                                ?>

                                                <td data-label="Due -">
                                                    <?php if ($isOverdue) { ?>
                                                        <a href="overdue-list.php?user_reg=<?= $userRow['reg_no']; ?>#pending"
                                                            class="text-danger fw-bold text-decoration-none">
                                                            <?= date("d-m-Y", strtotime($due_date)); ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        <span class="text-dark ">
                                                            <?= date("d-m-Y", strtotime($due_date)); ?>
                                                        </span>
                                                    <?php } ?>
                                                </td>

                                                <td data-label="Action -">
                                                    <?php if ($isOverdue) { ?>
                                                        <a href="overdue-list.php?user_reg=<?= $userRow['reg_no']; ?>&unique_book=<?= $uniqueRow['unique_code']; ?>#pending" class="btn btn-sm btn-danger text-white">
                                                            <i class="bi bi-exclamation-circle me-1"></i>Overdue
                                                        </a>
                                                    <?php } else { ?>
                                                        <button
                                                            class="btn btn-sm btn-success text-white return-btn"
                                                            data-id="<?= $issuedBook_row['id']; ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#returnModal">
                                                            <i class="bi bi-arrow-return-left me-1"></i>Return
                                                        </button>
                                                    <?php } ?>
                                                </td>

                                            </tr>
                                        <?php } } else {
                                        // ==========================================
                                        // EMPTY STATE UI (If no books are issued)
                                        // ==========================================
                                        ?>
                                        <tr>
                                            <td colspan="6" class="p-0">
                                                <div class="d-flex align-items-center justify-content-center text-center"
                                                    style="min-height: 250px;">

                                                    <div class="px-3">
                                                        <i class="bi bi-inbox fs-1 mb-3 text-secondary"></i>

                                                        <h6 class="text-muted fw-bold mb-1">
                                                            No Books Currently Issued
                                                        </h6>

                                                        <p class="text-muted small mb-0">
                                                            All books have been returned or none have been issued yet.
                                                        </p>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Return Book Modal -->
        <div class="modal fade" id="returnModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">

                    <div class="modal-header bg-success text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="bi bi-journal-check me-2"></i>Return Book
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="" method="POST">
                        <div class="modal-body p-4">

                            <!-- ✅ Only issued ID -->
                            <input type="hidden" name="issued_id" id="returnIssuedId">

                            <!-- Student Reg No -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Student Reg No</label>
                                <input type="text" class="form-control" id="displayReg" readonly>
                            </div>

                            <!-- Book Title -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Book Title</label>
                                <input type="text" class="form-control" id="displayBook" readonly>
                            </div>

                            <!-- Book Unique  -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Book Unique Code</label>
                                <input type="text" class="form-control" id="displayBookUniqueCode" readonly>
                            </div>

                            <!-- Return Date -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Return Date</label>
                                <input type="date"
                                    name="return_date"
                                    class="form-control"
                                    value="<?= date('Y-m-d'); ?>"
                                    required>
                            </div>

                            <!-- Fine -->
                            <!-- <div class="mb-3">
                                <label class="form-label small fw-bold">Fine (if any)</label>
                                <input type="number"
                                    name="fine"
                                    class="form-control"
                                    placeholder="Enter fine amount (₹)"
                                    value="0">
                            </div> -->

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" name="confirm_return" class="btn btn-success">
                                Confirm Return
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- modal show  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const returnButtons = document.querySelectorAll(".return-btn");
            returnButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    let issuedId = this.dataset.id;
                    document.getElementById("returnIssuedId").value = issuedId;
                    // AJAX fetch
                    fetch("ajax/get_issue_details.php?id=" + issuedId)
                        .then(response => response.json())
                        .then(data => {

                            document.getElementById("displayReg").value = data.reg_no;
                            document.getElementById("displayBook").value = data.book_title;
                            document.getElementById("displayBookUniqueCode").value = data.book_unique_code;

                        });
                });
            });
        });
    </script>

    <!-- search button  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("issueSearchInput");
            const tableBody = document.getElementById("issuedTableBody");
            const rows = tableBody.getElementsByTagName("tr");

            searchInput.addEventListener("input", function() {
                const filter = searchInput.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    // Get the Student Details (index 1) and Book Details (index 2) columns
                    let studentCol = rows[i].getElementsByTagName("td")[1];
                    let bookCol = rows[i].getElementsByTagName("td")[2];

                    if (studentCol || bookCol) {
                        // textContent gets all the text inside the div and small tags cleanly
                        let studentText = studentCol.textContent || studentCol.innerText;
                        let bookText = bookCol.textContent || bookCol.innerText;

                        // Check if the search query matches any part of the student details OR book details
                        if (studentText.toLowerCase().indexOf(filter) > -1 || bookText.toLowerCase().indexOf(filter) > -1) {
                            rows[i].style.display = ""; // Show row
                        } else {
                            rows[i].style.display = "none"; // Hide row
                        }
                    }
                }
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

    <!-- scanning student   -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // --- 1. AJAX CHECK LOGIC (Keyup & Blur) ---
            let regInput = document.getElementById("reg_no");
            let resultBox = document.getElementById("studentResult");
            let isValidStudent = false;

            regInput.addEventListener("keyup", function() {
                let regNo = this.value.trim();

                if (regNo.length > 9) {
                    resultBox.innerHTML = "<span class='text-primary small'>Checking...</span>";

                    fetch("ajax/check_student.php?reg_no=" + regNo)
                        .then(response => response.text())
                        .then(data => {
                            resultBox.innerHTML = data;
                            // Agar success message aata hai to valid mark kare
                            if (data.includes("Student Name")) {
                                isValidStudent = true;
                            } else {
                                isValidStudent = false;
                            }
                        });
                } else {
                    resultBox.innerHTML = "Verify student ID before issuing.";
                    isValidStudent = false;
                }
            });

            regInput.addEventListener("blur", function() {
                setTimeout(function() {
                    if (isValidStudent) return;
                    resultBox.innerHTML = "Verify student ID before issuing.";
                }, 200);
            });


            // --- 2. QR SCANNER LOGIC ---
            const startScanBtn = document.getElementById("startScanBtn");
            const readerDiv = document.getElementById("qr-reader");
            let html5QrCode = null;

            startScanBtn.addEventListener("click", function() {
                if (readerDiv.style.display === "none") {
                    readerDiv.style.display = "block";
                    startScanBtn.innerHTML = '<i class="bi bi-stop-circle"></i> Stop';
                    startScanBtn.classList.replace('btn-outline-secondary', 'btn-outline-danger');

                    html5QrCode = new Html5Qrcode("qr-reader");

                    html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        (decodedText, decodedResult) => {
                            // 1. Extract Reg No using Regex
                            let regNo = "";
                            const match = decodedText.match(/Registration No:\s*([A-Za-z0-9_-]+)/i);

                            if (match && match[1]) {
                                regNo = match[1];
                            } else {
                                regNo = decodedText.trim();
                            }

                            // 2. Put the number in the input box
                            regInput.value = regNo;

                            // 👇 3. YAHAN HAI MAGIC: Manually trigger the 'keyup' event
                            regInput.dispatchEvent(new Event('keyup'));

                            // 4. Stop the scanner
                            stopScanner();

                            // 5. Play success sound
                            let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
                            audio.play().catch(e => console.log("Audio play blocked by browser"));
                        },
                        (errorMessage) => {
                            // Ignore background scan errors
                        }
                    ).catch((err) => {
                        alert("Error starting camera. Please ensure you have granted camera permissions.");
                        stopScanner();
                    });

                } else {
                    stopScanner();
                }
            });

            function stopScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        readerDiv.style.display = "none";
                        startScanBtn.innerHTML = '<i class="bi bi-qr-code-scan"></i> Scan QR';
                        startScanBtn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
                    }).catch(err => {
                        console.error("Failed to stop scanner", err);
                    });
                }
            }
        });
    </script>

    <!-- scaniing books  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. AJAX Check Logic ---
            let codeInput = document.getElementById("unique_code");
            let bookResult = document.getElementById("bookResult");
            let isValidBook = false;

            codeInput.addEventListener("keyup", function() {
                let code = this.value.trim();

                if (code.length >= 4) {
                    fetch("ajax/check_book.php?code=" + encodeURIComponent(code))
                        .then(response => response.text())
                        .then(data => {
                            bookResult.innerHTML = data;
                            if (data.includes("(Available)")) {
                                isValidBook = true;
                            } else {
                                isValidBook = false;
                            }
                        });
                } else {
                    bookResult.innerHTML = "Ensure book is currently 'Available'.";
                    isValidBook = false;
                }
            });

            codeInput.addEventListener("blur", function() {
                setTimeout(function() {
                    if (isValidBook) return;
                    if (codeInput.value === "") bookResult.innerHTML = "Ensure book is currently 'Available'.";
                }, 200);
            });

            // --- 2. Camera Barcode Scanner Logic ---
            let barcodeScanner;
            let isBarcodeScanning = false;
            const barcodeReaderDiv = document.getElementById('barcode-reader');
            const startBarcodeScanBtn = document.getElementById('startBarcodeScanBtn');

            startBarcodeScanBtn.addEventListener('click', function() {
                if (isBarcodeScanning) {
                    // If already scanning, stop it
                    barcodeScanner.stop().then(() => {
                        barcodeReaderDiv.style.display = 'none';
                        startBarcodeScanBtn.innerHTML = '<i class="bi bi-upc-scan"></i> Scan Book';
                        startBarcodeScanBtn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
                        isBarcodeScanning = false;
                    }).catch(err => console.log(err));
                } else {
                    // Start the scanner
                    barcodeReaderDiv.style.display = 'block';
                    startBarcodeScanBtn.innerHTML = '<i class="bi bi-stop-circle"></i> Stop Camera';
                    startBarcodeScanBtn.classList.replace('btn-outline-secondary', 'btn-outline-danger');

                    barcodeScanner = new Html5Qrcode("barcode-reader");

                    barcodeScanner.start({
                            facingMode: "environment"
                        }, {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 150
                            }
                        },
                        (decodedText, decodedResult) => {
                            // Success! Barcode found.
                            codeInput.value = decodedText;

                            // Stop the camera automatically
                            barcodeScanner.stop().then(() => {
                                barcodeReaderDiv.style.display = 'none';
                                startBarcodeScanBtn.innerHTML = '<i class="bi bi-upc-scan"></i> Scan Book';
                                startBarcodeScanBtn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
                                isBarcodeScanning = false;
                            });

                            // Instantly trigger check
                            codeInput.dispatchEvent(new Event('keyup'));

                            // Play sound
                            let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
                            audio.play().catch(e => console.log("Audio play blocked"));
                        },
                        (errorMessage) => {
                            // Ignore scanning errors
                        }
                    ).then(() => {
                        isBarcodeScanning = true;
                    }).catch((err) => {
                        console.error("Camera access error", err);
                        alert("Please allow camera permissions in your browser.");
                        barcodeReaderDiv.style.display = 'none';
                        startBarcodeScanBtn.innerHTML = '<i class="bi bi-upc-scan"></i> Scan Book';
                        startBarcodeScanBtn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
                    });
                }
            });
        });
    </script>

</body>

</html>