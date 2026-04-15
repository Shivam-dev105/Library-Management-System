<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

// create account 
if (isset($_POST['create_account'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $session = $_POST['session'];
    $reg = $_POST['reg_no'];
    $password = password_hash($_POST['reg_no'], PASSWORD_DEFAULT);


    // Check email
    $check = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        // Agar active + verified hai
        if ($row['status'] == 1 && $row['is_verified'] == 1) {
            echo "<script>alert('Email already registered and verified!');window.location.href='manage_students.php'</script>";
            exit;
        } else {
            // Purana record delete karo
            mysqli_query($con, "DELETE FROM users WHERE email='$email'");
        }
    }

    $query = mysqli_query($con, "INSERT INTO `users`(`role`, `name`, `email`, `phone`, `session_id`,`department_id`,`reg_no`, `password`,`is_verified`,`status`,`created_at`) 
    VALUES ('student','$name','$email','$phone','$session','$department','$reg','$password',1,1,NOW())");

    if ($query) {
        echo "<script>alert('New Students Register Successfully'); window.location.href='manage_students.php#register'</script>";
    } else {
        echo "<script>alert('Something went wrong'); window.location.href='manage_students.php'</script>";
    }
}

// bulk of excel csv 

// 2. Check if the form is submitted
if (isset($_POST['submit_bulk'])) {

    // Check if file was uploaded
    if (!empty($_FILES['bulk_file']['name'])) {
        $fileName = $_FILES['bulk_file']['name'];
        $fileTmpName = $_FILES['bulk_file']['tmp_name'];

        // Verify it's a CSV file
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

        if (strtolower($fileExt) == "csv") {

            // Open the CSV file in read mode
            $fileHandle = fopen($fileTmpName, "r");

            // Skip the first row (headers)
            fgetcsv($fileHandle, 1000, ",");

            $successCount = 0;
            $errorCount = 0;
            $duplicateCount = 0;

            // 3. Loop through the CSV rows
            while (($data = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {

                // Escape special characters to prevent SQL errors and injection
                $name          = mysqli_real_escape_string($con, trim($data[0]));
                $email         = mysqli_real_escape_string($con, trim($data[1]));
                $phone         = mysqli_real_escape_string($con, trim($data[2]));
                $department_id = mysqli_real_escape_string($con, trim($data[3]));
                $session_id    = mysqli_real_escape_string($con, trim($data[4]));
                $reg_no        = mysqli_real_escape_string($con, trim($data[5]));

                // Hash the password securely (Fixed to $data[6])
                $raw_password  = trim($data[6]);
                $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

                // Default values for other required columns
                $role = "student";
                $is_verified = 1;
                $status = 1;

                // --- DUPLICATE CHECK START ---
                // चेक करें कि क्या यह email या reg_no पहले से डेटाबेस में है
                $checkQuery = "SELECT id FROM `users` WHERE `email`='$email' OR `reg_no`='$reg_no'";
                $checkResult = mysqli_query($con, $checkQuery);

                if (mysqli_num_rows($checkResult) > 0) {
                    // अगर रिकॉर्ड पहले से है, तो इसे स्किप करें और डुप्लीकेट काउंट बढ़ाएं
                    $duplicateCount++;
                    continue; // यह कमांड लूप को यहीं रोककर अगली लाइन (next row) पर ले जाएगी
                }
                // --- DUPLICATE CHECK END ---

                // 4. Build the raw SQL query string (सिर्फ तभी चलेगा जब डुप्लीकेट ना हो)
                $sql = "INSERT INTO `users` (
                            `role`, `name`, `email`, `phone`, `session_id`, 
                            `reg_no`, `department_id`, `password`, `is_verified`, 
                            `status`, `created_at`
                        ) VALUES (
                            '$role', '$name', '$email', '$phone', '$session_id', 
                            '$reg_no', '$department_id', '$hashed_password', '$is_verified', 
                            '$status', NOW()
                        )";

                // 5. Execute the query
                if (mysqli_query($con, $sql)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } // --- END OF WHILE LOOP ---

            // Close the file to free up server memory
            fclose($fileHandle);

            // 6. Show the final alert AFTER the loop is completely finished
            // अलर्ट मैसेज में डुप्लीकेट काउंट भी दिखाएं
            echo "<script>
                    alert('Bulk admission complete.\\n\\nSuccessfully Added: $successCount\\nSkipped (Already Exists): $duplicateCount\\nFailed Errors: $errorCount');
                    window.location.href='manage_students.php#bulk';
                  </script>";
        } else {
            // Handle wrong file extension
            echo "<script>
                    alert('Invalid file format. Please upload a CSV file.');
                    window.location.href='manage_students.php#bulk';
                  </script>";
        }
    } else {
        // Handle no file selected
        echo "<script>alert('Please select a file to upload.');window.location.href='manage_students.php#bulk';</script>";
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
    <title>Manage Students | <?= $settingRow['system_name']; ?></title>
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

        /* Tab Custom Styling */
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
            <h4 class="fw-bold mb-4 custom-underline">Student Management</h4>

            <ul class="nav nav-tabs" id="studentTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view" type="button" role="tab"><i class="bi bi-people me-2 mb-md-2"></i>Registered Students</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab"><i class="bi bi-person-plus me-2"></i>Direct Registration</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="bulkqr-tab" data-bs-toggle="tab" data-bs-target="#bulkqr" type="button" role="tab"><i class="bi bi-qr-code me-2"></i>Bulk Qr Generate</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab"><i class="bi bi-people-fill me-2"></i>Bulk Admission</button>
                </li>
            </ul>

            <div class="tab-content" id="studentTabsContent">

                <div class="tab-pane fade show active" id="view" role="tabpanel">
                    <div class="card manage-card p-4">
                        <form method="GET" action="">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

                                <h5 class="fw-bold mb-0 custom-underline text-nowrap">
                                    <i class="bi bi-people me-2"></i>All Students
                                </h5>

                                <div class="d-flex flex-wrap gap-2 w-100 justify-content-md-end">
                                    <select name="session_id" class="form-select form-select-sm w-auto">
                                        <option value="">Select Session</option>
                                        <?php
                                        // Fetch sessions for dropdown dynamically
                                        $sess_query = mysqli_query($con, "SELECT id, session, semester FROM `academic_session`");
                                        if ($sess_query) {
                                            while ($s = mysqli_fetch_assoc($sess_query)) {
                                                $selected = (isset($_GET['session_id']) && $_GET['session_id'] == $s['id']) ? 'selected' : '';
                                                echo "<option value='{$s['id']}' $selected>{$s['session']} - {$s['semester']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>

                                    <select name="department_id" class="form-select form-select-sm w-auto">
                                        <option value="">Select Department</option>
                                        <?php
                                        // Assuming you have a departments table, fetch them here
                                        // Replace 'departments' and 'dept_name' with your actual table/column names if different
                                        $dept_query = mysqli_query($con, "SELECT id, department_name FROM `department`");
                                        if ($dept_query) {
                                            while ($d = mysqli_fetch_assoc($dept_query)) {
                                                $selected = (isset($_GET['department_id']) && $_GET['department_id'] == $d['id']) ? 'selected' : '';
                                                echo "<option value='{$d['id']}' $selected>{$d['department_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>

                                    <div class="d-flex gap-2">
                                        <!-- full data search with reload  -->
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Reg No or Name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                                        <!-- full data search by ajax  -->
                                        <!-- <input type="text" id="studentSearchInput" class="form-control form-control-sm" placeholder="Search by Reg No or Name..."> -->
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                                        <a href="?" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
                                    </div>
                                </div>

                            </div>
                        </form>

                        <div>
                            <table class="table align-middle table-hover custom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Reg No</th>
                                        <th>Full Name</th>
                                        <th>Session/sem</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">
                                    <?php
                                    // --- FILTER & PAGINATION LOGIC START ---
                                    $limit = 25;
                                    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                                    $page = max($page, 1);
                                    $offset = ($page - 1) * $limit;

                                    $show_results = false;
                                    $where_clauses = [];
                                    $where_sql = "";
                                    $error_message = "";

                                    // 1. Text Search Logic
                                    if (!empty($_GET['search'])) {
                                        $search = mysqli_real_escape_string($con, $_GET['search']);
                                        $where_clauses[] = "(reg_no LIKE '%$search%' OR name LIKE '%$search%')";
                                        $show_results = true;
                                    }

                                    // 2. Session & Department Logic
                                    $session_id = !empty($_GET['session_id']) ? mysqli_real_escape_string($con, $_GET['session_id']) : '';
                                    $department_id = !empty($_GET['department_id']) ? mysqli_real_escape_string($con, $_GET['department_id']) : '';

                                    if (!empty($session_id)) {
                                        $where_clauses[] = "session_id = '$session_id'";
                                        $show_results = true; // Show results if session is selected

                                        // Apply department filter ONLY if session is also selected
                                        if (!empty($department_id)) {
                                            $where_clauses[] = "department_id = '$department_id'"; // Update 'department_id' to your actual column name if different
                                        }
                                    } elseif (empty($session_id) && !empty($department_id)) {
                                        // Department selected but NO session selected
                                        $error_message = "Please select a Session to filter by Department.";
                                    }

                                    if (!empty($where_clauses)) {
                                        $where_sql = " WHERE " . implode(" AND ", $where_clauses);
                                    }

                                    $total_pages = 0;

                                    // Execute queries ONLY if user searched or filtered correctly
                                    if ($show_results) {
                                        $countQuery = mysqli_query($con, "SELECT COUNT(id) AS total FROM `users` $where_sql");
                                        $countRow = mysqli_fetch_assoc($countQuery);
                                        $total_records = $countRow['total'];
                                        $total_pages = ceil($total_records / $limit);

                                        $users = mysqli_query($con, "SELECT * FROM `users` $where_sql ORDER BY reg_no ASC LIMIT $offset, $limit");
                                        $i = $offset + 1;

                                        if (mysqli_num_rows($users) > 0) {
                                            while ($usersRow = mysqli_fetch_assoc($users)) {
                                    ?>
                                                <tr>
                                                    <td data-label="Qty -" class="qty-cell"><span class="qty-badge"><?= $i++; ?></span></td>
                                                    <td data-label="Reg No -"><?= $usersRow['reg_no']; ?></td>
                                                    <td data-label="Name -"><strong><?= $usersRow['name']; ?></strong></td>
                                                    <?php
                                                    $id = $usersRow['session_id'];
                                                    $session = mysqli_query($con, "SELECT * FROM `academic_session` WHERE `id`='$id'");
                                                    $sessionRow = mysqli_fetch_assoc($session);
                                                    ?>
                                                    <td data-label="Session/Sem -"><?= $sessionRow['session'] ?? 'N/A'; ?> - <?= $sessionRow['semester'] ?? 'N/A'; ?></td>

                                                    <td data-label="Status -" class="status-cell">
                                                        <?php if ($usersRow['status'] == 1): ?>
                                                            <span class='badge bg-success status-badge'>Active</span>
                                                        <?php else: ?>
                                                            <span class='badge bg-danger status-badge'>Inactive</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td data-label="Action -">
                                                        <button class="btn btn-sm btn-outline-primary toggle-status me-1" data-id="<?= $usersRow['id']; ?>" data-status="<?= $usersRow['status']; ?>"><i class="bi <?= ($usersRow['status'] == 1) ? 'bi-toggle-on text-success' : 'bi-toggle-off text-danger'; ?>"></i></button>
                                                        <button class="btn btn-sm btn-outline-primary me-1 view-student" data-bs-toggle="modal" data-bs-target="#viewStudentModal" data-id="<?= $usersRow['id']; ?>"><i class="bi bi-eye"></i></button>
                                                        <a href="edit_students.php?user_id=<?= $usersRow['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                                        <a href="delete.php?user_id=<?= $usersRow['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                                                        <?php if (empty($usersRow['qr_code'])): ?>
                                                            <a href="generate_qr.php?user_id=<?= $usersRow['id']; ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-qr-code"></i></a>
                                                        <?php else: ?>
                                                            <a href="download_qr.php?user_id=<?= $usersRow['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                    <?php
                                            }
                                        } else {
                                            // Valid search/filter applied, but NO data found
                                            echo "
                                            <tr>
                                                <td colspan='6' class='p-0'>
                                                    <div class='d-flex align-items-center justify-content-center text-center' style='min-height: 220px;'>
                                                        <div class='px-3'>
                                                            <i class='bi bi-people text-secondary opacity-50 mb-3' style='font-size: 2.5rem;'></i>
                                                            <h6 class='text-muted fw-bold mb-1'>No Students Found</h6>
                                                            <p class='text-muted small mb-0'>No records match your selected filters.</p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        // INITIAL LOAD OR INVALID FILTER: Show "Please search" message
                                        $display_msg = !empty($error_message) ? $error_message : "Please search or apply filters to see student records.";
                                        $icon = !empty($error_message) ? "bi-exclamation-triangle" : "bi-search";
                                        $text_color = !empty($error_message) ? "text-danger" : "text-muted";

                                        echo "
                                        <tr>
                                            <td colspan='6' class='p-0'>
                                                <div class='d-flex align-items-center justify-content-center text-center' style='min-height: 220px;'>
                                                    <div class='px-3'>
                                                        <i class='bi $icon $text_color opacity-50 mb-3' style='font-size: 2.5rem;'></i>
                                                        <h6 class='$text_color fw-bold mb-1'>Search to View Data</h6>
                                                        <p class='text-muted small mb-0'>$display_msg</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div id="paginationContainer">
                            <?php if (isset($total_pages) && $total_pages > 1): ?>
                                <?php
                                // Build the base query string for pagination links so filters don't clear on page 2
                                $qs_array = $_GET;
                                unset($qs_array['page']); // Remove old page parameter
                                $base_qs = http_build_query($qs_array);
                                $base_url = "?" . ($base_qs ? $base_qs . "&" : "");
                                ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url ?>page=<?= $page - 1; ?>">Previous</a></li>
                                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                            <li class="page-item <?= ($page == $p) ? 'active' : ''; ?>"><a class="page-link" href="<?= $base_url ?>page=<?= $p; ?>"><?= $p; ?></a></li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url ?>page=<?= $page + 1; ?>">Next</a></li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="register" role="tabpanel">
                    <div class="card manage-card p-4">
                        <h5 class="fw-bold mb-4 custom-underline">Add Student Manually</h5>

                        <form action="#" method="POST" id="studentForm">

                            <div class="row g-3">

                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" required>
                                    <small class="text-danger" id="nameError"></small>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" required>
                                    <small class="text-danger" id="emailError"></small>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="10-digit mobile number"
                                        maxlength="10" pattern="[0-9]{10}" required>
                                    <small class="text-danger" id="phoneError"></small>
                                </div>

                                <!-- Session -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Department</label>
                                    <select class="form-select form-control" id="department" name="department" required>
                                        <option value="" disabled selected>-- Select department --</option>

                                        <?php
                                        $departmentQuery = mysqli_query($con, "SELECT * FROM department WHERE status=1");
                                        while ($departmentRow = mysqli_fetch_assoc($departmentQuery)) {
                                        ?>
                                            <option value="<?= $departmentRow['id']; ?>"><?= $departmentRow['department_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Session -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Session</label>
                                    <select class="form-select form-control" id="session" name="session" required>
                                        <option value="" disabled selected>-- Select Session --</option>

                                        <?php
                                        $sessionQuery = mysqli_query($con, "SELECT * FROM academic_session WHERE status=1 ORDER BY id DESC");
                                        while ($sessionRow = mysqli_fetch_assoc($sessionQuery)) {
                                        ?>
                                            <option value="<?= $sessionRow['id']; ?>"><?= $sessionRow['session']; ?> - (<?= $sessionRow['semester']; ?>)</option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-danger" id="sessionError"></small>
                                </div>

                                <!-- Registration Number -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Registration Number</label>
                                    <input type="text" class="form-control" id="reg_no" name="reg_no"
                                        placeholder="<?= $settingRow['company_code']; ?>xxxxxxx" maxlength="10" required>
                                    <small class="text-danger" id="regError"></small>
                                </div>
                                <hr class="mb-0">
                                <div class="col-md-5">
                                    <div class="alert alert-info d-flex align-items-center p-2" role="alert">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        <span class="mb-0">Default Password: <strong>Your Registration Number</strong></span>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="col-md-12 mt-3">

                                    <button type="submit" name="create_account" id="createBtn" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Register Student
                                    </button>

                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="bulkqr" role="tabpanel">

                    <!-- Header -->
                    <div class="card manage-card p-4">
                        <div class="mb-1">
                            <h5 class="fw-bold mb-0 custom-underline">Bulk QR Generate</h5>
                            <p class="text-muted small mt-2">Select Department and Session to generate QR codes.</p>
                        </div>

                        <hr class="my-3">

                        <!-- Form -->
                        <form action="generate_bulk_qr.php" method="POST">
                            <div class="row g-3">

                                <!-- Department -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">
                                        Select Department <span class="text-danger">*</span>
                                    </label>
                                    <select name="department_id" class="form-select form-control rounded-3" required>
                                        <option value="" selected disabled>-- Select Department --</option>
                                        <?php
                                        $department = mysqli_query($con, "SELECT * FROM `department` WHERE status = 1");
                                        while ($departmentRow = mysqli_fetch_assoc($department)) {
                                        ?>
                                            <option value="<?= $departmentRow['id'] ?>">
                                                <?= $departmentRow['department_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Session -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">
                                        Select Session <span class="text-danger">*</span>
                                    </label>
                                    <select name="session_id" class="form-select form-control rounded-3" required>
                                        <option value="" selected disabled>-- Select Session --</option>
                                        <?php
                                        $session = mysqli_query($con, "SELECT * FROM `academic_session` WHERE status = 1");
                                        while ($sessionRow = mysqli_fetch_assoc($session)) {
                                        ?>
                                            <option value="<?= $sessionRow['id'] ?>">
                                                <?= $sessionRow['session']; ?> - <?= $sessionRow['semester']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Button -->
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="generate_bulk_qr" class="btn btn-success rounded-3">
                                        <i class="bi bi-qr-code me-1"></i> Generate
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>

                <div class="tab-pane fade" id="bulk" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="mb-1">
                            <h5 class="fw-bold mb-0 custom-underline">Bulk Admission</h5>
                            <p class="text-muted small mt-2">Upload a CSV file to admit multiple students at once. Please ensure the file matches the required format.</p>
                        </div>
                        <hr class="text-muted my-3">

                        <form action="#" method="POST" enctype="multipart/form-data">
                            <div class="row align-items-center mb-2">
                                <div class="col-md-6">
                                    <label for="bulkFile" class="form-label fw-semibold">Upload Student Data <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" id="bulkFile" name="bulk_file" accept=".csv" required>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" name="submit_bulk" class="btn btn-primary">
                                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload Students
                                </button>
                                <button type="reset" class="btn btn-light border">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- View Student Modal -->
        <div class="modal fade" id="viewStudentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">

                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="bi bi-person-badge me-2"></i>Student Details (<span id="modalReg1"></span>)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        <!-- <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                    </div>

                    <div class="modal-body p-4">

                        <div class="row g-4">

                            <div class="col-md-6">
                                <!-- <p><strong>Sno:</strong> <span id="modalSno"></span></p> -->
                                <p><strong>Registration No:</strong> <span id="modalReg2"></span></p>
                                <p><strong>Full Name:</strong> <span id="modalName"></span></p>
                                <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                                <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
                                <p><strong>Session/Sem:</strong> <span id="modalSession"></span></p>

                            </div>

                            <div class="col-md-6">
                                <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
                                <p><strong>Last login:</strong> <span id="modallastLogin"></span></p>
                                <p>
                                    <strong>Verification:</strong>
                                    <span id="modalVerifyBadge"></span>
                                </p>

                                <p>
                                    <strong>Status:</strong>
                                    <span id="modalStatusBadge"></span>
                                </p>
                                <strong>Action:</strong>
                                <a href="#" id="modaledit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="#" id="modaldelete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>

                            </div>

                        </div>

                    </div>

                    <!-- <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div> -->

                </div>
            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- toggle button  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll(".toggle-status").forEach(function(button) {

                button.addEventListener("click", function() {

                    let userId = this.getAttribute("data-id");
                    let currentStatus = this.getAttribute("data-status");
                    let newStatus = (currentStatus == 1) ? 0 : 1;

                    let row = this.closest("tr");
                    let badge = row.querySelector(".status-badge");
                    let icon = this.querySelector("i");

                    fetch("ajax/user_status.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: "user_id=" + userId + "&status=" + newStatus
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() === "success") {

                                // Update data-status attribute
                                this.setAttribute("data-status", newStatus);

                                if (newStatus == 1) {
                                    badge.classList.remove("bg-danger");
                                    badge.classList.add("bg-success");
                                    badge.innerText = "Active";

                                    icon.classList.remove("bi-toggle-off", "text-danger");
                                    icon.classList.add("bi-toggle-on", "text-success");
                                } else {
                                    badge.classList.remove("bg-success");
                                    badge.classList.add("bg-danger");
                                    badge.innerText = "Inactive";

                                    icon.classList.remove("bi-toggle-on", "text-success");
                                    icon.classList.add("bi-toggle-off", "text-danger");
                                }
                            }

                        });

                });

            });

        });
    </script>

    <!-- searching data  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("studentSearchInput");
            const tableBody = document.getElementById("studentTableBody");
            const pagination = document.getElementById("paginationContainer");
            let timeout = null;

            searchInput.addEventListener("keyup", function() {
                clearTimeout(timeout); // Type karte samay purana timer cancel karein
                const query = this.value.trim();

                // Server par load kam karne ke liye 300ms ka delay (Debounce)
                timeout = setTimeout(() => {
                    fetch("ajax/search_students_ajax.php?query=" + encodeURIComponent(query))
                        .then(response => response.text())
                        .then(data => {
                            tableBody.innerHTML = data; // Naye result table me daalein

                            // Agar search me kuch likha hai to pagination hide kar dein
                            if (query.length > 0) {
                                pagination.style.display = "none";
                            } else {
                                pagination.style.display = "block";
                            }
                        })
                        .catch(error => console.error("Error fetching data:", error));
                }, 300);
            });
        });
    </script>

    <!-- view modal  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll(".view-student").forEach(function(btn) {

                btn.addEventListener("click", function() {

                    let studentId = this.getAttribute("data-id");

                    fetch("ajax/get_student.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: "student_id=" + studentId
                        })
                        .then(response => response.json())
                        .then(data => {

                            document.getElementById("modaledit").href = "edit_students.php?user_id=" + data.id;
                            document.getElementById("modaldelete").href = "delete.php?user_id=" + data.id;
                            document.getElementById("modalReg1").textContent = data.reg_no;
                            document.getElementById("modalReg2").textContent = data.reg_no;
                            document.getElementById("modalName").textContent = data.name;
                            document.getElementById("modalSession").textContent =
                                data.session + " (" + data.semester + ")";
                            document.getElementById("modalDepartment").textContent = data.department_name;
                            document.getElementById("modalEmail").textContent = data.email;
                            document.getElementById("modalPhone").textContent = data.phone;

                            let loginDate = data.last_login;
                            if (loginDate && !isNaN(new Date(loginDate))) {
                                let formattedDate = new Date(loginDate)
                                    .toLocaleDateString('en-GB')
                                    .replace(/\//g, '-');
                                document.getElementById("modallastLogin").textContent = formattedDate;
                            } else {
                                document.getElementById("modallastLogin").textContent = "N/A";
                            }

                            // Verification Badge
                            let verifyBadge = (data.is_verified == 1) ?
                                '<span class="badge bg-success">Verified</span>' :
                                '<span class="badge bg-warning text-dark">Not Verified</span>';

                            document.getElementById("modalVerifyBadge").innerHTML = verifyBadge;

                            // Status Badge
                            let statusBadge = (data.status == 1) ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">Inactive</span>';

                            document.getElementById("modalStatusBadge").innerHTML = statusBadge;

                        });

                });

            });

        });
    </script>

    <!-- validations  -->
    <script>
        const nameInput = document.getElementById("name");
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");
        const sessionSel = document.getElementById("session");

        /* ================= NAME ================= */
        const nameError = document.getElementById("nameError");

        // Show validation only when user types
        nameInput.addEventListener("input", () => {

            // Allow only letters & space
            nameInput.value = nameInput.value.replace(/[^a-zA-Z\s]/g, '');

            if (nameInput.value.length === 0) {
                nameError.innerText = ""; // empty field → no error
                return;
            }

            if (nameInput.value.length < 3) {
                nameError.innerText = "Minimum 3 letters required";
            } else {
                nameError.innerText = "";
            }
        });

        // Hide error when user leaves field empty
        nameInput.addEventListener("blur", () => {
            if (nameInput.value.length === 0) {
                nameError.innerText = "";
            }
        });

        /* ================= EMAIL ================= */
        emailInput.addEventListener("input", () => {

            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(emailInput.value)) {
                emailError.innerText = "Invalid email format";
            } else {
                emailError.innerText = "";
            }
        });

        // Hide error when user leaves field empty
        emailInput.addEventListener("blur", () => {
            if (emailInput.value.length === 0) {
                emailError.innerText = "";
            }
        });

        /* ================= PHONE ================= */
        phoneInput.addEventListener("input", () => {

            phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '');

            if (!/^[6-9][0-9]{9}$/.test(phoneInput.value)) {
                phoneError.innerText = "Enter valid 10-digit Indian number";
            } else {
                phoneError.innerText = "";
            }
        });
        // Hide error when user leaves field empty
        phoneInput.addEventListener("blur", () => {
            if (phoneInput.value.length === 0) {
                phoneError.innerText = "";
            }
        });

        /* ================= SESSION ================= */
        sessionSel.addEventListener("change", () => {

            if (sessionSel.value == "") {
                sessionError.innerText = "Select session";
            } else {
                sessionError.innerText = "";
            }
        });
        // Hide error when user leaves field empty
        sessionSel.addEventListener("blur", () => {
            if (sessionSel.value.length === 0) {
                sessionError.innerText = "";
            }
        });
    </script>

    <!-- registration number checeking  -->
    <script>
        const regInput = document.getElementById("reg_no");
        const regError = document.getElementById("regError");
        let regTimer = null;

        regInput.addEventListener("focus", function() {
            if (regInput.value === "") {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }
        });

        regInput.addEventListener("input", function() {

            clearTimeout(regTimer);

            // Allow only numbers
            regInput.value = regInput.value.replace(/[^0-9]/g, '');

            // Force start with company code
            if (!regInput.value.startsWith("<?= $settingRow['company_code']; ?>")) {
                regInput.value = "<?= $settingRow['company_code']; ?>";
            }

            // Hide error if user clears field
            regInput.addEventListener("blur", () => {
                if (regInput.value === "" || regInput.value === "<?= $settingRow['company_code']; ?>") {
                    regError.innerText = "";
                }
            });

            // Length check
            if (regInput.value.length !== 10) {
                regError.innerText = "Registration must be 10 digits";
                regError.className = "text-danger";
                return;
            }

            regError.innerText = "Checking...";
            regError.className = "text-warning";

            regTimer = setTimeout(function() {

                fetch("ajax/check_reg.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "reg_no=" + encodeURIComponent(regInput.value)
                    })
                    .then(response => response.text())
                    .then(data => {

                        if (data.trim() === "exists") {
                            regError.innerText = "Registration already registered";
                            regError.className = "text-danger";
                        } else {
                            regError.innerText = "";
                        }

                    })
                    .catch(() => {
                        regError.innerText = "Server error";
                        regError.className = "text-danger";
                    });

            }, 500);

        });
    </script>

    <!-- email checking  -->
    <script>
        const emailError = document.getElementById("emailError");
        let emailTimer = null;
        emailInput.addEventListener("input", function() {

            clearTimeout(emailTimer);
            let email = this.value;

            // Basic format check first
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(email)) {
                emailError.innerText = "Invalid email format";
                emailError.className = "text-danger";
                return;
            }

            emailError.innerText = "Checking...";
            emailError.className = "text-warning";

            // Delay 500ms (avoid too many requests)
            emailTimer = setTimeout(() => {

                fetch("ajax/check_email.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "email=" + encodeURIComponent(email)
                    })

                    .then(res => res.text())
                    .then(data => {

                        if (data === "exists") {
                            emailError.innerText = "Email already registered";
                            emailError.className = "text-danger";
                        } else if (data === "available") {
                            emailError.innerText = "";
                            emailError.className = "text-success";
                        }

                    });

            }, 500); // wait before checking

        });
    </script>

    <!-- phone checking  -->
    <script>
        let phoneTimer = null;

        phoneInput.addEventListener("input", function() {

            clearTimeout(phoneTimer);

            let phone = this.value.replace(/[^0-9]/g, '');

            if (!/^[6-9][0-9]{9}$/.test(phone)) {
                phoneError.innerText = "Enter valid 10-digit Indian number";
                phoneError.className = "text-danger";
                return;
            }

            phoneError.innerText = "Checking...";
            phoneError.className = "text-warning";

            phoneTimer = setTimeout(() => {

                fetch("ajax/check_phone.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "phone=" + encodeURIComponent(phone)
                    })
                    .then(res => res.text())
                    .then(data => {

                        if (data === "exists") {
                            phoneError.innerText = "Phone already registered";
                            phoneError.className = "text-danger";
                        } else {
                            phoneError.innerText = "";
                            phoneError.className = "text-success";
                        }

                    });

            }, 500);

        });
    </script>

    <!-- stop submission when error -->
    <script>
        document.getElementById("studentForm").addEventListener("submit", function(e) {

            if (emailError.innerText.includes("already")) {

                e.preventDefault();
                alert("This email is already registered!");
            }

            if (
                nameError.innerText !== "" ||
                emailError.innerText !== "" ||
                phoneError.innerText !== "" ||
                sessionError.innerText !== "" ||
                regError.innerText !== ""
            ) {
                e.preventDefault();
                alert("Please fix errors before submitting!");
            }

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

    <!-- search  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("studentSearchInput");

            // If the search box has text in it on page load, trigger the search
            if (searchInput.value.trim() !== "") {
                // Option A: If your search triggers on 'keyup' or 'input'
                searchInput.dispatchEvent(new Event('input'));

                // Option B: If your search requires clicking the button
                // document.getElementById("searchBtn").click();
            }
        });
    </script>

</body>

</html>