<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

// Fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);

// ==========================================
// HANDLE FINE PAYMENT SUBMISSION
// ==========================================
if (isset($_POST['collect_fine'])) {
    $issue_id = mysqli_real_escape_string($con, $_POST['issue_id']);

    // Update payment_status to 1 (Paid)
    $update_sql = "UPDATE `issued_books` SET `payment_status` = 1, `payment_date` = CURDATE() WHERE `id` = '$issue_id'";
    if (mysqli_query($con, $update_sql)) {
        $_SESSION['msg'] = "Fine collected successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Something went wrong. Please try again.";
        $_SESSION['msg_type'] = "danger";
    }
    // Redirect to prevent form resubmission
    header("Location: fine_management.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fines | <?= $settingRow['system_name']; ?></title>
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


        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
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
    </style>
</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid">
            <h4 class="fw-bold mb-4 custom-underline">Fine Management</h4>

            <?php if (isset($_SESSION['msg'])) { ?>
                <div id="autoAlert" class="alert alert-<?= $_SESSION['msg_type']; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?= $_SESSION['msg']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
                unset($_SESSION['msg']);
                unset($_SESSION['msg_type']);
            } ?>

            <ul class="nav nav-tabs" id="fineTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="bi bi-exclamation-circle me-2"></i>Pending Fines
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid" type="button" role="tab">
                        <i class="bi bi-check-circle me-2"></i>Paid Fines
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                            <h5 class="fw-bold mb-0 text-danger">
                                <i class="bi bi-cash-coin me-2"></i>Unpaid Fines
                            </h5>
                            <div class="d-flex gap-2">
                                <input type="text" id="pendingSearch" class="form-control form-control-sm search-input" placeholder="Search Student, Reg No...">
                                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table" id="pendingTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Student Details</th>
                                        <th>Book Details</th>
                                        <th>Late Days</th>
                                        <th>Fine Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $pending_sql = "SELECT ib.*, u.name, u.reg_no, b.title, b.isbn, bc.unique_code
                                        FROM issued_books ib
                                        JOIN users u ON ib.user_id = u.id
                                        JOIN books b ON ib.book_id = b.id
                                        LEFT JOIN book_copies bc ON ib.book_unique_code = bc.id AND ib.book_id = bc.book_id
                                        WHERE ib.fine_amount > 0 AND ib.payment_status = 0
                                        ORDER BY ib.return_date DESC";
                                    $pending_query = mysqli_query($con, $pending_sql);

                                    if (mysqli_num_rows($pending_query) > 0) {
                                        $j = 1;
                                        while ($pRow = mysqli_fetch_assoc($pending_query)) {
                                    ?>
                                            <tr class="searchable-row">
                                                <!-- <td class="text-muted fw-medium"><?= $j++; ?></td> -->
                                                <td data-label="Qty -" class="qty-cell"><span class="qty-badge"><?= $j++; ?></span></td>

                                                <td data-label="Student Details -">
                                                    <div class="fw-bold text-dark"><?= $pRow['name']; ?></div>
                                                    <small class="text-muted">Reg: <?= $pRow['reg_no']; ?></small>
                                                </td>
                                                <td data-label="Book Details -">
                                                    <div class="fw-semibold text-dark mb-1"><?= $pRow['title']; ?></div>
                                                    <small class="text-muted"><i class="bi bi-upc-scan me-1"></i> <?= $pRow['unique_code'] ?></small>
                                                </td>
                                                <td data-label="Late Days -">
                                                    <span class='badge badge-overdue px-2 py-1'><?= $pRow['late_days']; ?> Days</span>
                                                </td>
                                                <td data-label="Fine Amount -">
                                                    <span class="text-danger fw-bold fs-6">₹<?= number_format($pRow['fine_amount'], 2); ?></span>
                                                </td>
                                                <td data-label="Action -">
                                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to collect ₹<?= $pRow['fine_amount']; ?> from <?= $pRow['name']; ?>?');">
                                                        <input type="hidden" name="issue_id" value="<?= $pRow['id']; ?>">
                                                        <button type="submit" name="collect_fine" class="btn btn-sm btn-success text-white ">
                                                            <i class="bi bi-cash me-1"></i> Collect Fine
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='p-0'><div class='d-flex align-items-center justify-content-center text-center'style='min-height: 220px;'><div class='px-3'><i class='bi bi-cash-coin text-success opacity-50 mb-3'style='font-size: 2.5rem;'></i><h6 class='text-muted fw-bold mb-1'>No Pending Fines</h6><p class='text-muted small mb-0'>All fines have been cleared successfully.</p></div></div></td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="paid" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                            <h5 class="fw-bold mb-0 text-success">
                                <i class="bi bi-check-circle me-2"></i>Collected Fines
                            </h5>
                            <div class="d-flex gap-2">
                                <input type="text" id="paidSearch" class="form-control form-control-sm search-input" placeholder="Search Student, Reg No...">
                                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table" id="paidTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Student Details</th>
                                        <th>Book Details</th>
                                        <th>Return Date</th>
                                        <th>Fine Paid</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $paid_sql = "SELECT ib.*, u.name, u.reg_no, b.title, b.isbn, bc.unique_code
                                        FROM issued_books ib
                                        JOIN users u ON ib.user_id = u.id
                                        JOIN books b ON ib.book_id = b.id
                                        LEFT JOIN book_copies bc ON ib.book_unique_code = bc.id AND ib.book_id = bc.book_id
                                        WHERE ib.fine_amount > 0 AND ib.payment_status = 1
                                        ORDER BY ib.return_date DESC";
                                    $paid_query = mysqli_query($con, $paid_sql);

                                    if (mysqli_num_rows($paid_query) > 0) {
                                        $k = 1;
                                        while ($pdRow = mysqli_fetch_assoc($paid_query)) {
                                    ?>
                                            <tr class="searchable-row">
                                                <!-- <td class="text-muted fw-medium"><?= $k++; ?></td> -->
                                                <td data-label="Qty -" class="qty-cell">
                                                    <span class="qty-badge"><?= $k++; ?></span>
                                                </td>
                                                <td data-label="Student Details -">
                                                    <div class="fw-bold text-dark"><?= $pdRow['name']; ?></div>
                                                    <small class="text-muted">Reg: <?= $pdRow['reg_no']; ?></small>
                                                </td>
                                                <td data-label="Book Details -">
                                                    <div class="fw-semibold text-dark mb-1"><?= $pdRow['title']; ?></div>
                                                    <small class="text-muted"><i class="bi bi-upc-scan me-1"></i> <?= $pdRow['unique_code'] ? $pdRow['unique_code'] : 'N/A'; ?></small>
                                                </td>
                                                <td data-label="Return Date -">
                                                    <span class="text-muted"><?= date("d M Y", strtotime($pdRow['return_date'])); ?></span>
                                                </td>
                                                <td data-label="Fine Paid -">
                                                    <span class="text-dark fw-bold">₹<?= number_format($pdRow['fine_amount'], 2); ?></span>
                                                </td>
                                                <td data-label="Status -">
                                                    <span class='badge bg-success-subtle text-success fw-semibold border border-success-subtle'>Paid</span>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='p-0'><div class='d-flex align-items-center justify-content-center text-center'style='min-height: 220px;'><div class='px-3'><i class='bi bi-wallet2 text-primary opacity-50 mb-3'style='font-size: 2.5rem;'></i><h6 class='text-muted fw-bold mb-1'>No Collected Fines</h6><p class='text-muted small mb-0'>No fine payments have been recorded yet.</p></div></div></td></tr>";
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

    <!-- 6sec remove  -->
    <script>
        setTimeout(function() {
            var alert = document.getElementById('autoAlert');
            if (alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 6000); // 6000ms = 6 seconds
    </script>

    <!-- searching -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Reusable Search Function
            function setupSearch(inputId, tableId) {
                const searchInput = document.getElementById(inputId);
                if (!searchInput) return;

                searchInput.addEventListener("input", function() {
                    const filter = searchInput.value.toLowerCase().trim();
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

            // Init Search for both tables
            setupSearch("pendingSearch", "pendingTable");
            setupSearch("paidSearch", "paidTable");

            // Hash Handling (To keep the same tab open after page reload)
            let hash = window.location.hash;
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