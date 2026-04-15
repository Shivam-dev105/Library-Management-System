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
    <title>Issue History | <?= $settingRow['system_name']; ?></title>
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
            --success-green: #2e7d32;
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

        .manage-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .badge-overdue {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--danger-red);
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--success-green);
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


        /* Responsive Table Labels for Mobile */
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
            <h4 class="fw-bold mb-4 custom-underline">Issue History Log</h4>

            <ul class="nav nav-tabs overdue-tabs" id="historyTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="returned-tab" data-bs-toggle="tab" data-bs-target="#returned" type="button" role="tab"><i class="bi bi-check-circle me-2"></i>Returned History</button>
                </li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="returned">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                            <h5 class="fw-bold mb-2 mb-md-0 text-success">
                                <i class="bi bi-check-circle me-2"></i>Returned Books History
                            </h5>
                            <div class="d-flex gap-2">
                                <input type="text" id="returnedSearchInput" class="form-control form-control-sm search-input" placeholder="Search Name, Reg No, Title...">
                                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table" id="returnedTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Student Details</th>
                                        <th>Book Details</th>
                                        <th>Return Date</th>
                                        <th>Late Days</th>
                                        <th>Fine Collected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $history_sql = "SELECT  ib.*, u.name, u.reg_no, b.title, b.isbn, bc.unique_code
                                        FROM issued_books ib
                                        JOIN users u ON ib.user_id = u.id
                                        JOIN books b ON ib.book_id = b.id
                                        LEFT JOIN book_copies bc ON ib.book_unique_code = bc.unique_code AND ib.book_id = bc.book_id
                                        WHERE ib.return_status = 1
                                        ORDER BY ib.return_date DESC";
                                    $history_query = mysqli_query($con, $history_sql);
                                    if (mysqli_num_rows($history_query) > 0) {
                                        $j = 1;
                                        while ($hRow = mysqli_fetch_assoc($history_query)) {
                                            $paymentBadge = ($hRow['payment_status'] == 1 && $hRow['fine_amount'] > 0)
                                                ? "<span class='badge bg-success ms-1'>Paid</span>"
                                                : (($hRow['fine_amount'] > 0) ? "<span class='badge bg-danger ms-1'>Unpaid</span>" : "");
                                    ?>
                                            <tr class="searchable-row">
                                                <td data-label="Qty -" class="qty-cell">
                                                    <span class="qty-badge"><?= $j++; ?></span>
                                                </td>
                                                <td data-label="Student -">
                                                    <div class="fw-bold"><?= $hRow['name']; ?></div>
                                                    <small class="text-muted">Reg: <?= $hRow['reg_no']; ?></small>
                                                </td>
                                                <td data-label="Book -">
                                                    <div class="fw-bold"><?= $hRow['title']; ?></div>
                                                    <?php
                                                    $hRow['book_unique_code'];
                                                    $hRow['book_id'];

                                                    $uniqueId = $hRow['book_unique_code'];
                                                    $bookId = $hRow['book_id'];
                                                    $bookBarcode = mysqli_query($con, "SELECT * FROM `book_copies` WHERE `id` ='$uniqueId' AND `book_id` = '$bookId'");
                                                    $bookBarcodeName = mysqli_fetch_assoc($bookBarcode);
                                                    $barcodeName = $bookBarcodeName['unique_code'];
                                                    ?>
                                                    <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>
                                                        <?= $barcodeName; ?>
                                                    </small>
                                                </td>

                                                <td data-label="Return Date -">
                                                    <span class="text-success fw-bold"><?= date("d-m-Y", strtotime($hRow['return_date'])); ?></span>
                                                </td>
                                                <td data-label="Late Days -">
                                                    <?= ($hRow['late_days'] > 0) ? "<span class='text-danger'>{$hRow['late_days']} Days</span>" : "<span class='text-muted'>On Time</span>"; ?>
                                                </td>
                                                <td data-label="Fine Paid -">
                                                    <span class="fw-bold">₹<?= number_format($hRow['fine_amount'], 2); ?></span>
                                                    <?= $paymentBadge; ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "
                                            <tr>
                                                <td colspan='6' class='p-0'>
                                                    <div class='d-flex align-items-center justify-content-center text-center' 
                                                        style='min-height: 220px;'>
                                                        
                                                        <div class='px-3'>
                                                            <i class='bi bi-clock-history text-secondary opacity-50 mb-3' style='font-size: 2.5rem;'></i>
                                                            
                                                            <h6 class='text-muted fw-bold mb-1'>No Book History Found</h6>
                                                            
                                                            <p class='text-muted small mb-0'>
                                                                There are no previous issue or return records available.
                                                            </p>
                                                        </div>
                                                        
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- searching  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function setupSearch(inputId, tableId) {
                const searchInput = document.getElementById(inputId);
                if (!searchInput) return;

                searchInput.addEventListener("input", function() {
                    const filter = searchInput.value.toLowerCase();
                    const rows = document.querySelectorAll(`#${tableId} .searchable-row`);

                    rows.forEach(row => {
                        let textContent = row.innerText || row.textContent;
                        if (textContent.toLowerCase().includes(filter)) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                });
            }

            // Setup search for both tabs
            setupSearch("activeSearchInput", "activeTable");
            setupSearch("returnedSearchInput", "returnedTable");

            // URL Search parameter Logic
            let queryString = window.location.search;
            if (!queryString && window.location.hash.includes('?')) {
                queryString = window.location.hash.split('?')[1];
            }
            const urlParams = new URLSearchParams(queryString);
            const userReg = urlParams.get('user_reg');

            if (userReg) {
                const activeSearch = document.getElementById("activeSearchInput");
                if (activeSearch) {
                    activeSearch.value = userReg;
                    activeSearch.dispatchEvent(new Event('input'));
                }
            }
        });
    </script>

    <!-- hash target  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let hash = window.location.hash.split('?')[0];
            if (hash) {
                let targetTab = document.querySelector('.nav-tabs .nav-link[data-bs-target="' + hash + '"]');
                if (targetTab) {
                    let tab = new bootstrap.Tab(targetTab);
                    tab.show();
                }
            }

            let tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    let target = e.target.getAttribute('data-bs-target');
                    if (target) {
                        history.replaceState(null, null, target);
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>