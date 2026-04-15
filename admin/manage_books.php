<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

// add book 
if (isset($_POST['add_books'])) {

    // 1. Sanitize inputs to prevent SQL Injection
    $title = mysqli_real_escape_string($con, trim($_POST['book_title']));
    $author = mysqli_real_escape_string($con, trim($_POST['author']));
    $isbn = mysqli_real_escape_string($con, trim($_POST['isbn']));
    $department_id = mysqli_real_escape_string($con, $_POST['department_id']);
    $category_id = !empty($_POST['category_id'])
        ? mysqli_real_escape_string($con, $_POST['category_id'])
        : NULL;
    $rack_id    = mysqli_real_escape_string($con, $_POST['rack_id']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $quantity = (int)$_POST['quantity']; // Cast to integer
    $description = mysqli_real_escape_string($con, trim($_POST['description']));

    // 2. Insert into main `books` table
    $booksAddQuery = "INSERT INTO `books`(`title`, `author`, `isbn`, `department_id`, `category_id`, `rack_id`, `price`, `quantity`, `description`, `status`, `created_at`) 
                      VALUES ('$title', '$author', '$isbn', '$department_id', '$category_id', '$rack_id', '$price', '$quantity', '$description', 1, NOW())";

    $booksAdd = mysqli_query($con, $booksAddQuery);

    if ($booksAdd) {
        // 3. Get the ID of the book we just inserted
        $book_id = mysqli_insert_id($con);

        // 4. Logic to generate unique GPB codes
        // We find the highest existing ID in book_copies to ensure no duplicates
        $maxIdQuery = mysqli_query($con, "SELECT MAX(id) AS max_id FROM `book_copies`");
        $maxIdRow = mysqli_fetch_assoc($maxIdQuery);

        // If the table is empty, start from 1. Otherwise, take the max ID and add 1.
        $next_number = ($maxIdRow['max_id'] != null) ? $maxIdRow['max_id'] + 1 : 1;

        // 5. Loop based on the 'quantity' entered by the user
        for ($i = 0; $i < $quantity; $i++) {

            // Format the number to always have leading zeros (e.g., GPB-0001, GPB-0002)
            // You can change %04d to %02d if you only want 2 digits (e.g., GPB-01)
            $unique_code = "GPB-" . sprintf("%04d", $next_number);

            // The barcode can store the same unique string value. 
            // (Later, you can use a barcode plugin to render this text as a scannable image)
            // $barcode = $unique_code;

            // 6. Insert individual copy into `book_copies` table
            $insertCopyQuery = "INSERT INTO `book_copies` (`book_id`, `unique_code`,`status`, `created_at`) 
                                VALUES ('$book_id', '$unique_code',1, NOW())";

            mysqli_query($con, $insertCopyQuery);

            // Increment the number for the next book copy in the loop
            $next_number++;
        }

        echo "<script>alert('New Book added! Successfully generated $quantity unique copies.'); window.location.href='manage_books.php#add';</script>";
    } else {
        echo "<script>alert('Something went wrong while adding the main book.'); window.location.href='manage_books.php#add';</script>";
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
    <title>Manage Books | <?= $settingRow['system_name']; ?></title>
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

        /* table  */
        /* Mobile Table Card Layout */
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
            <h4 class="fw-bold mb-4 custom-underline">Books Repository</h4>

            <ul class="nav nav-tabs" id="bookTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button" role="tab"><i class="bi bi-collection me-2 mb-md-2"></i>Manage Books</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="copies-tab" data-bs-toggle="tab" data-bs-target="#copies" type="button" role="tab"><i class="bi bi-journals me-2"></i>Book Copies</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab"><i class="bi bi-plus-circle me-2"></i>Add New Book</button>
                </li>
            </ul>

            <div class="tab-content" id="bookTabsContent">

                <div class="tab-pane fade show active" id="manage" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

                            <h5 class="fw-bold mb-2 mb-md-0 custom-underline">
                                <i class="bi bi-collection me-2"></i>Book Inventory
                            </h5>

                            <div class="d-flex gap-2">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search by ISBN or Title...">
                                <button class="btn btn-primary btn-sm"> <i class="bi bi-search"></i> </button>
                            </div>

                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>ISBN</th>
                                        <th>Book Title</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Qty</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <?php
                                    $books = mysqli_query($con, "SELECT * FROM `books`");
                                    $i = 1;
                                    while ($booksRow = mysqli_fetch_assoc($books)) {
                                    ?>
                                        <tr>
                                            <td data-label="Qty -" class="qty-cell">
                                                <span class="qty-badge"><?= $i++; ?></span>
                                            </td>
                                            <td data-label="ISBN -"><?= $booksRow['isbn']; ?></td>
                                            <td data-label="Book Title -"><strong><?= $booksRow['title']; ?></strong></td>

                                            <td data-label="Department -">
                                                <?php
                                                $department_id = $booksRow['department_id'];
                                                $book_department = mysqli_query($con, "SELECT * FROM `department` WHERE `id` = '$department_id'");
                                                $book_departmentRow = mysqli_fetch_assoc($book_department);
                                                ?>
                                                <div class="fw-bold">
                                                    <?= $book_departmentRow['department_name']; ?>
                                                </div>
                                                <?php
                                                $category_id = $booksRow['category_id'];
                                                if (!empty($category_id)) {
                                                    $book_category = mysqli_query($con, "SELECT * FROM `category` WHERE `id` = '$category_id' AND `department_id` = '$department_id'");
                                                    $book_categoryRow = mysqli_fetch_assoc($book_category);
                                                    if ($book_categoryRow) {
                                                ?>
                                                        <small class="text-muted"><i class="bi bi-tags-fill me-2"></i> <?= $book_categoryRow['category_name']; ?>
                                                        </small>
                                                <?php
                                                    }
                                                }
                                                ?>

                                            </td>
                                            <td data-label="Status -">
                                                <?php if ($booksRow['status'] == 1) { ?>
                                                    <span class="badge status-badge bg-success mb-2">Active</span>
                                                <?php } else { ?>
                                                    <span class="badge status-badge bg-danger mb-2">Inactive</span>
                                                <?php } ?>
                                            </td>
                                            <td data-label="Qty -"><?= $booksRow['quantity']; ?></td>
                                            <td data-label="Action -">
                                                <button
                                                    class="btn btn-sm btn-outline-primary me-1 view-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewBookModal"
                                                    data-id="<?= $booksRow['id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <a href="edit_books.php?book_id=<?= $booksRow['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                                <a href="generate_barcode.php?book_id=<?= $booksRow['id']; ?>" class="btn btn-sm  btn-outline-success me-1"> <i class="bi bi-upc"></i> </a>
                                                <a href="delete.php?book_id=<?= $booksRow['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="copies" role="tabpanel">
                    <div class="card manage-card p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                            <h5 class="fw-bold mb-2 mb-md-0 custom-underline">
                                <i class="bi bi-journals me-2"></i>Book Copies Inventory
                            </h5>

                            <div class="d-flex gap-2">
                                <input type="text" id="searchCopiesInput" class="form-control form-control-sm" placeholder="Search by Unique Code or Title...">
                                <button class="btn btn-primary btn-sm"> <i class="bi bi-search"></i> </button>
                            </div>
                        </div>

                        <div>
                            <table class="table align-middle table-hover custom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sno</th>
                                        <th>Book Title</th>
                                        <th>Unique Code</th>
                                        <th>Status</th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                                <tbody id="copiesTableBody">
                                    <?php
                                    // JOIN query lagayi hai taaki book_copies ke sath book ka title bhi mil jaye
                                    $query = "SELECT bc.id AS copy_id, bc.unique_code, bc.status, b.title 
                                        FROM `book_copies` bc 
                                        LEFT JOIN `books` b ON bc.book_id = b.id  ";

                                    $copies = mysqli_query($con, $query);
                                    $i = 1;
                                    while ($copyRow = mysqli_fetch_assoc($copies)) {
                                    ?>
                                        <tr>
                                            <td data-label="Sno -" class="qty-cell">
                                                <span class="qty-badge"><?= $i++; ?></span>
                                            </td>

                                            <td data-label="Book Title -"><strong><?= !empty($copyRow['title']) ? $copyRow['title'] : '<span class="text-danger">Book Not Found</span>'; ?></strong></td>

                                            <td data-label="Unique Code -">
                                                <span class="badge bg-secondary"><?= $copyRow['unique_code']; ?></span>
                                            </td>

                                            <td data-label="Status -">
                                                <?php if ($copyRow['status'] == 1) { ?>
                                                    <span class="badge status-badge bg-success mb-2">Available</span>
                                                <?php } else { ?>
                                                    <span class="badge status-badge bg-danger mb-2">Issued</span>
                                                <?php } ?>
                                            </td>

                                            <!-- <td data-label="Action -">
                                                <a href="delete_copy.php?copy_id=<?= $copyRow['copy_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this copy?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td> -->
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="add" role="tabpanel">
                    <div class="card manage-card p-4">
                        <h5 class="fw-bold mb-4 custom-underline">Enter Book Details</h5>

                        <form action="#" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">

                                <!-- Book Title -->
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Book Title</label>
                                    <input type="text" name="book_title" class="form-control" placeholder="Enter book title" required>
                                </div>

                                <!-- Author -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Author Name</label>
                                    <input type="text" name="author" class="form-control" placeholder="Enter author name" required>
                                </div>

                                <!-- ISBN -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">ISBN Number</label>
                                    <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Enter ISBN">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Department</label>
                                    <select name="department_id" id="department_select" class="form-select" required>
                                        <option value="" disabled selected>-- Select Department --</option>
                                        <?php
                                        $departmentQuery = mysqli_query($con, "SELECT * FROM `department`");
                                        while ($departmentRow = mysqli_fetch_assoc($departmentQuery)) {
                                        ?>
                                            <option value="<?= $departmentRow['id']; ?>"><?= $departmentRow['department_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Category</label>
                                    <select name="category_id" id="category_select" class="form-select">
                                        <option value="" selected>-- Select Category --</option>
                                    </select>
                                </div>

                                <!-- Rack-Section -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Rack Section (Location)</label>
                                    <select name="rack_id" class="form-select" required>
                                        <option value="" disabled selected>-- Select Rack Section --</option>
                                        <?php
                                        $rackQuery = mysqli_query($con, "SELECT * FROM `rack_section`");
                                        while ($rackRow = mysqli_fetch_assoc($rackQuery)) {
                                        ?>
                                            <option value="<?= $rackRow['id']; ?>"><?= $rackRow['rack']; ?> - <?= $rackRow['section'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <small class="small text-muted">Enter here books location in library</small>
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" min="1" required>
                                </div>

                                <!-- Price -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Price (₹)</label>
                                    <input type="number" name="price" class="form-control" step="0.01">
                                </div>

                                <!-- Book Cover -->
                                <!-- <div class="col-md-6">
                                    <label class="form-label small fw-bold">Book Cover Image</label>
                                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                                </div> -->

                                <!-- Description -->
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Short description about book"></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <button type="submit" name="add_books" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Add Book
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>
                                </div>

                            </div>

                        </form>


                    </div>
                </div>

            </div>
        </div>

        <!-- View Book Modal -->
        <div class="modal fade" id="viewBookModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="bi bi-book me-2"></i><span id="modalTitle1"></span> - Book Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left Side -->
                            <div class="col-md-6">
                                <!-- <p><strong>ID:</strong> <span id="modalSno"></span></p> -->
                                <p><strong>ISBN:</strong> <span id="modalIsbn"></span></p>
                                <p><strong>Title:</strong> <span id="modalTitle2"></span></p>
                                <p><strong>Author:</strong> <span id="modalAuthor"></span></p>
                                <p id="departmentRow">
                                    <strong>Department:</strong>
                                    <span id="modalDepartment"></span>
                                </p>
                                <p id="categoryRow">
                                    <strong>Category:</strong>
                                    <span id="modalCategory"></span>
                                </p>
                            </div>
                            <!-- Right Side -->
                            <div class="col-md-6">
                                <p><strong>Location:</strong> <span id="modalRack"></span> (<span id="modalSection"></span>)</p>
                                <p>
                                    <strong>Available Qty:</strong>
                                    <span id="modalQty"></span>
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    <span class="badge bg-success fs-6" id="modalStatus"></span>
                                </p>
                                <strong>Action:</strong>
                                <a href="#" id="modaledit" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="#" id="modaldelete" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash" onclick="return confirm('Are you sure?')"></i></a>
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

    <!-- Search box  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("searchInput");
            const tableBody = document.getElementById("tableBody");
            const rows = tableBody.getElementsByTagName("tr");

            searchInput.addEventListener("input", function() {
                const filter = searchInput.value.toLowerCase().trim();

                for (let i = 0; i < rows.length; i++) {
                    // Get the text from ISBN (index 1), Title (index 2), and Dept/Category (index 3)
                    let isbnCol = rows[i].getElementsByTagName("td")[1];
                    let titleCol = rows[i].getElementsByTagName("td")[2];
                    let deptCategoryCol = rows[i].getElementsByTagName("td")[3]; // Added this line

                    if (isbnCol || titleCol || deptCategoryCol) {
                        let isbnText = isbnCol.textContent || isbnCol.innerText;
                        let titleText = titleCol.textContent || titleCol.innerText;
                        let deptCategoryText = deptCategoryCol.textContent || deptCategoryCol.innerText; // Added this line

                        // Check if the search query matches ISBN, Title, Department, OR Category
                        if (
                            isbnText.toLowerCase().includes(filter) ||
                            titleText.toLowerCase().includes(filter) ||
                            deptCategoryText.toLowerCase().includes(filter)
                        ) {
                            rows[i].style.display = ""; // Show row
                        } else {
                            rows[i].style.display = "none"; // Hide row
                        }
                    }
                }
            });
        });
    </script>

    <!-- for modal books  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const viewButtons = document.querySelectorAll(".view-btn");

            viewButtons.forEach(button => {
                button.addEventListener("click", function() {

                    var book_id = this.dataset.id;

                    fetch("ajax/fetch_book.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id=" + book_id
                        })
                        .then(response => response.json())
                        .then(data => {

                            document.getElementById("modaledit").href = "edit_books.php?book_id=" + data.id;
                            document.getElementById("modaldelete").href = "delete.php?book_id=" + data.id;
                            document.getElementById("modalIsbn").innerText = data.isbn;
                            document.getElementById("modalTitle1").innerText = data.title;
                            document.getElementById("modalTitle2").innerText = data.title;
                            document.getElementById("modalAuthor").innerText = data.author;
                            document.getElementById("modalCategory").innerText = data.category;
                            document.getElementById("modalDepartment").innerText = data.department;
                            document.getElementById("modalRack").innerText = data.rack;
                            document.getElementById("modalSection").innerText = data.section;
                            document.getElementById("modalQty").innerText = data.quantity;
                            document.getElementById("modalStatus").innerText = data.status;

                            let category = data.category; // jo bhi aapka category variable hai

                            if (category && category.trim() !== "") {
                                document.getElementById("modalCategory").innerText = category;
                                document.getElementById("categoryRow").style.display = "block";
                            } else {
                                document.getElementById("categoryRow").style.display = "none";
                            }

                            let department = data.department; // jo bhi aapka variable hai

                            if (department && department.trim() !== "") {
                                document.getElementById("modalDepartment").innerText = department;
                                document.getElementById("departmentRow").style.display = "block";
                            } else {
                                document.getElementById("departmentRow").style.display = "none";
                            }

                            if (data.status == 0) {
                                document.getElementById("modalStatus").className = "badge bg-danger fs-6";
                                document.getElementById("modalStatus").innerText = "Inactive";
                            } else {
                                document.getElementById("modalStatus").className = "badge bg-success fs-6";
                                document.getElementById("modalStatus").innerText = "Active";
                            }

                        });

                });
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

    <!-- for categorie of department  -->
    <script>
        document.getElementById('department_select').addEventListener('change', function() {
            var deptId = this.value;
            var categorySelect = document.getElementById('category_select');

            // Pehle se existing options ko clear karein
            categorySelect.innerHTML = '<option value="" selected>-- Select Category --</option>';

            if (deptId) {
                // AJAX request bhejein naye PHP file par
                fetch('ajax/get_categories.php?dept_id=' + deptId)
                    .then(response => response.json())
                    .then(data => {
                        // Har category ko dropdown mein add karein
                        data.forEach(function(category) {
                            var option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.category_name;
                            categorySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>

</body>

</html>