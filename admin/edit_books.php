<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];

    $bookQuery = mysqli_query($con, "SELECT * FROM books WHERE id = '$book_id'");
    $bookRow = mysqli_fetch_assoc($bookQuery);
 } else {
    header("location: manage_books.php");
    exit();
}

if (isset($_POST['update_book'])) {

    $book_id = $_GET['book_id'];
    $title = $_POST['book_title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $department_id = $_POST['department_id'];
    $category_id = $_POST['category_id'];
    $rack_id = $_POST['rack_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $updateQuery = mysqli_query($con, "UPDATE books SET
            `title` = '$title',
            `author` = '$author',
            `isbn` = '$isbn',
            `department_id` = '$department_id',
            `category_id` = '$category_id',
            `rack_id` = '$rack_id',
            `price` = '$price',
            `quantity` = '$quantity',
            `description` = '$description',
            `updated_at` = NOW()
        WHERE `id` = '$book_id'
    ");

    if ($updateQuery) {
        echo "<script>alert('Book Updated Successfully'); window.location.href='manage_books.php'</script>";
    } else {
        echo "<script>alert('Something went wrong');</script>";
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
    <title>Edit Books | <?= $settingRow['system_name']; ?></title>
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
    </style>

</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid">
            <h4 class="fw-bold mb-4 custom-underline">Edit Book</h4>
            <ul class="nav nav-tabs" id="bookTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab"><i class="bi bi-plus-circle me-2"></i>Edit Book </button>
                </li>
            </ul>

            <div class="tab-content" id="bookTabsContent">

                <div class="tab-pane fade show active" id="add" role="tabpanel">
                    <div class="card manage-card p-4">
                        <h5 class="fw-bold mb-4 custom-underline">Edit Book Details of <span class="text-danger"><?= $bookRow['title']; ?></span></h5>

                        <form action="#" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">

                                <!-- Book Title -->
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Book Title</label>
                                    <input type="text" name="book_title" class="form-control" placeholder="Enter book title" value="<?= $bookRow['title']; ?>">
                                </div>

                                <!-- Author -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Author Name</label>
                                    <input type="text" name="author" class="form-control" placeholder="Enter author name" value="<?= $bookRow['author']; ?>">
                                </div>

                                <!-- ISBN -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">ISBN Number</label>
                                    <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Enter ISBN" value="<?= $bookRow['isbn']; ?>">
                                </div>

                                <!-- Department -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Department</label>
                                    <select name="department_id" class="form-select form-control">
                                        <option value="" disabled selected>-- Select Department --</option>
                                        <?php
                                        $departmentQuery = mysqli_query($con, "SELECT * FROM `department`");
                                        while ($departmentRow = mysqli_fetch_assoc($departmentQuery)) {
                                        ?>
                                            <option value="<?= $departmentRow['id']; ?>"
                                                <?= ($departmentRow['id'] == $bookRow['department_id']) ? 'selected' : ''; ?>>
                                                <?= $departmentRow['department_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="" disabled selected>-- Select Category --</option>
                                        <?php
                                        $categoryQuery = mysqli_query($con, "SELECT * FROM `category`");
                                        while ($categoryRow = mysqli_fetch_assoc($categoryQuery)) {
                                        ?>
                                            <option value="<?= $categoryRow['id']; ?>"
                                                <?= ($categoryRow['id'] == $bookRow['category_id']) ? 'selected' : ''; ?>>
                                                <?= $categoryRow['category_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Rack Section (Location) -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Rack Section (Location)</label>
                                    <select name="rack_id" class="form-select">
                                        <option value="" disabled selected>-- Select Rack Section --</option>
                                        <?php
                                        $rackQuery = mysqli_query($con, "SELECT * FROM `rack_section`");
                                        while ($rackRow = mysqli_fetch_assoc($rackQuery)) {
                                        ?>
                                            <option value="<?= $rackRow['id']; ?>"
                                                <?= ($rackRow['id'] == $bookRow['rack_id']) ? 'selected' : ''; ?>>
                                                <?= $rackRow['rack']; ?> (<?= $rackRow['section']; ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" min="1" value="<?= $bookRow['quantity']; ?>">
                                </div>

                                <!-- Price -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Price (₹)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $bookRow['price']; ?>">
                                </div>

                                <!-- Book Cover -->
                                <!-- <div class="col-md-6">
                                    <label class="form-label small fw-bold">Book Cover Image</label>
                                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                                </div> -->

                                <!-- Description -->
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Short description about book"><?= $bookRow['description']; ?></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Reset
                                    </button>
                                    <button type="submit" name="update_book" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Update Book
                                    </button>
                                </div>

                            </div>

                        </form>


                    </div>
                </div>

            </div>
        </div>

        <?php include("includes/footer.php"); ?>
    </div>

    <!-- javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>