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

// Handle Search Query
$searchQuery = "";
$searchCondition = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = mysqli_real_escape_string($con, trim($_GET['search']));
    // Search by Title, Author, or ISBN (Using alias 'b' for the books table)
    $searchCondition = " AND (`b`.`title` LIKE '%$searchQuery%' OR `b`.`author` LIKE '%$searchQuery%' OR `b`.`isbn` LIKE '%$searchQuery%')";
}

// =========================================
// 1. PAGINATION SETUP (12 Books per page)
// =========================================
$limit = 12; // Maximum 12 books per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total books (with active status and matching search if any)
$count_sql = "SELECT COUNT(*) as total FROM `books` b WHERE `b`.`status` = 1 $searchCondition";
$count_result = mysqli_query($con, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_books = $count_row['total'];
$total_pages = ceil($total_books / $limit);

// =========================================
// 2. FETCH PAGINATED BOOKS WITH JOINS
// =========================================
$books_sql = "SELECT b.*, d.department_name, c.category_name, r.rack, r.section 
    FROM `books` b
    LEFT JOIN `department` d ON b.department_id = d.id
    LEFT JOIN `category` c ON b.category_id = c.id
    LEFT JOIN `rack_section` r ON b.rack_id = r.id
    WHERE b.status = 1 $searchCondition 
    ORDER BY b.id DESC 
    LIMIT $limit OFFSET $offset
";
$books_result = mysqli_query($con, $books_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books | <?= $settingRow['system_name']; ?></title>
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

        /* Book Card Specific Styles */
        .book-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 250px;
            object-fit: contain;
            background-color: #f8f9fa;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .search-bar-wrapper {
            border-radius: 50px;
            padding: 3px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .search-input {
            border: none;
            box-shadow: none !important;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <?php include("includes/sidebar.php"); ?>

    <div class="main-content">

        <?php include("includes/header.php"); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Search Library Collection</h4>
                <p class="text-muted small mb-0">Find books by title, author, or ISBN</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 d-none d-md-inline-block">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="row mb-5 justify-content-center">
            <div class="col-lg-8">
                <form action="search_books.php" method="GET">
                    <div class="search-bar-wrapper d-flex align-items-center">
                        <i class="bi bi-search text-muted ms-3 fs-5"></i>
                        <input type="text" name="search" class="form-control form-control-lg search-input bg-transparent" placeholder="Enter book title, author, or ISBN..." value="<?= htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 me-1">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($searchQuery)): ?>
            <div class="mb-1 d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0">Showing results for: <span class="text-dark fw-bold">"<?= htmlspecialchars($searchQuery); ?>"</span></h6>
                <span class="text-muted small">Found <?= $total_books; ?> matches</span>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <?php
            if (mysqli_num_rows($books_result) > 0) {
                while ($book = mysqli_fetch_assoc($books_result)) {
                    // Check cover image fallback
                    $uploadPath = "../admin/uploads/book_cover/";
                    $defaultImage = "../admin/uploads/book_cover/book_cover.png";
                    $coverImage = !empty($book['book_cover']) && file_exists($uploadPath . $book['book_cover']) ? $uploadPath . $book['book_cover'] : $defaultImage;

                    // Stock logic
                    $inStock = $book['quantity'] > 0;
                    $badgeClass = $inStock ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                    $badgeText = $inStock ? 'Available (' . $book['quantity'] . ')' : 'Out of Stock';
            ?>
                    <div class="col-sm-6 col-md-4 col-xl-3">
                        <div class="custom-card book-card">

                            <div class="position-relative">
                                <span class="badge <?= $badgeClass; ?> position-absolute top-0 end-0 m-3 shadow-sm z-2"><?= $badgeText; ?></span>
                                <img src="<?= $coverImage; ?>" class="book-cover w-100" alt="Book Cover">
                            </div>

                            <div class="card-body d-flex flex-column p-4">
                                <h6 class="fw-bold text-truncate mb-1" title="<?= $book['title']; ?>">
                                    <?= $book['title']; ?>
                                </h6>
                                <p class="text-muted small mb-2">By <?= $book['author']; ?></p>
                                <p class="text-muted small mb-3"><i class="bi bi-upc-scan me-1"></i> <?= $book['isbn']; ?></p>

                                <div class="mt-auto pt-3 border-top">
                                    <button type="button" class="btn btn-outline-primary w-100 rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#bookModal<?= $book['id']; ?>">
                                        <i class="bi bi-eye me-2"></i> View & Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="bookModal<?= $book['id']; ?>" tabindex="-1" aria-labelledby="bookModalLabel<?= $book['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 overflow-hidden" style="border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.15);">

                                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 position-absolute w-100 z-3" style="background: transparent;">
                                    <button type="button" class="btn-close bg-white rounded-circle shadow-sm p-2" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; transition: 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                                </div>

                                <div class="modal-body p-0">
                                    <div class="row g-0">

                                        <div class="col-md-5 d-flex flex-column align-items-center justify-content-center p-4 position-relative" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                                            <div class="position-absolute top-0 start-0 mt-4 ms-4 mb-1 z-2">
                                                <span class="badge <?= $badgeClass; ?> rounded-pill px-3 py-2 mb-3 shadow-sm border" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    <i class="bi <?= $inStock ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                                                    <?= $badgeText; ?>
                                                </span>
                                            </div>
                                            <div class="bg-white p-2 rounded-3 mt-4 mb-3" style="box-shadow: -10px 10px 20px rgba(0,0,0,0.08);">
                                                <img src="<?= $coverImage; ?>" class="img-fluid rounded" style="max-height: 350px; width: 100%; object-fit: contain; min-width: 200px;" alt="Book Cover">
                                            </div>
                                        </div>

                                        <div class="col-md-7 bg-white p-4 p-md-5 d-flex flex-column">

                                            <div class="mb-4">
                                                <!-- <span class="text-primary fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Library Catalog</span> -->
                                                <h3 class="fw-bold text-dark mb-1" style="line-height: 1.3; font-family: 'Inter', sans-serif;">
                                                    <?= $book['title']; ?>
                                                </h3>
                                                <p class="text-muted fs-6 mb-0" style="font-weight: 300;">
                                                    By <span class="fw-medium text-dark"><?= $book['author']; ?></span>
                                                </p>
                                            </div>

                                            <!-- <h6 class="fw-bold text-uppercase text-secondary mb-3 pb-2 border-bottom" style="font-size: 0.8rem; letter-spacing: 1px;">Book Details</h6> -->

                                            <div class="bg-light px-4 py-2 rounded-4 mb-4 border" style="font-size: 0.9rem;">

                                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color: rgba(0,0,0,0.06) !important;">
                                                    <span class="text-muted fw-medium"><i class="bi bi-upc-scan me-1 text-secondary"></i>ISBN Number</span>
                                                    <span class="fw-semibold text-dark text-end">
                                                        <?= $book['isbn']; ?>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color: rgba(0,0,0,0.06) !important;">
                                                    <span class="text-muted fw-medium">Price</span>
                                                    <span class="fw-semibold text-success text-end">
                                                        <?= !empty($book['price']) ? '₹' . number_format($book['price'], 2) : 'Free/NA'; ?>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color: rgba(0,0,0,0.06) !important;">
                                                    <span class="text-muted fw-medium"><i class="bi bi-building me-1 text-secondary"></i>Department</span>
                                                    <span class="fw-semibold text-dark text-end text-break" style="max-width: 65%;">
                                                        <?= !empty($book['department_name']) ? $book['department_name'] : 'N/A'; ?>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color: rgba(0,0,0,0.06) !important;">
                                                    <span class="text-muted fw-medium"><i class="bi bi-tags me-1 text-secondary"></i>Category</span>
                                                    <span class="fw-semibold text-dark text-end text-break" style="max-width: 65%;">
                                                        <?= !empty($book['category_name']) ? $book['category_name'] : 'N/A'; ?>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center py-3">
                                                    <span class="text-muted fw-medium"><i class="bi bi-bookshelf me-1 text-secondary"></i>Location</span>
                                                    <span class="fw-semibold text-dark text-end text-break" style="max-width: 65%;">
                                                        <?= !empty($book['rack']) ? $book['rack'] : 'N/A'; ?>
                                                        <?= !empty($book['section']) ? '<span class="text-muted fw-normal ms-1">(' . $book['section'] . ')</span>' : ''; ?>
                                                    </span>
                                                </div>

                                            </div>

                                            <!-- <div class="mb-4">
                                                <h6 class="fw-bold text-uppercase text-secondary mb-2 pb-1 border-bottom" style="font-size: 0.8rem; letter-spacing: 1px;">Overview</h6>
                                                <p class="text-muted mb-0" style="font-size: 0.85rem; text-align: justify; line-height: 1.6; max-height: 100px; overflow-y: auto;">
                                                    <?= !empty($book['description']) ? nl2br(htmlspecialchars($book['description'])) : '<span class="text-black-50 fst-italic">No overview available for this title.</span>'; ?>
                                                </p>
                                            </div> -->

                                            <div class="mt-auto pt-2 border-top">
                                                <form action="" method="POST" class="m-0 mt-3">
                                                    <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                                                    <button type="submit" name="request_book" class="btn <?= $inStock ? 'btn-primary' : 'btn-secondary disabled'; ?> w-100 rounded-pill py-2 shadow-sm fw-semibold" <?= !$inStock ? 'disabled' : ''; ?> style="transition: transform 0.2s;" onclick="alert('Visit the Library for issue book')">
                                                        <i class="bi <?= $inStock ? 'bi-bookmark-plus' : 'bi-slash-circle'; ?> me-2"></i>
                                                        <?= $inStock ? 'Request to Borrow' : 'Currently Unavailable' ?>
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                }
            } else {
                // No Books Found State
                ?>
                <div class="col-12 text-center py-5 custom-card">
                    <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mt-3">No books found</h5>
                    <p class="text-muted">We couldn't find any books matching your search. Try different keywords or check your spelling.</p>
                    <a href="search_books.php" class="btn btn-primary mt-2 rounded-pill px-4">View All Books</a>
                </div>
            <?php } ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Book Catalog Pagination" class="mb-5">
                <ul class="pagination justify-content-center">

                    <?php $searchParam = !empty($searchQuery) ? "&search=" . urlencode($searchQuery) : ""; ?>

                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-pill px-3 me-1" href="?page=<?= $page - 1; ?><?= $searchParam; ?>">Previous</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link rounded-circle mx-1" href="?page=<?= $i; ?><?= $searchParam; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-pill px-3 ms-1" href="?page=<?= $page + 1; ?><?= $searchParam; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <?php include("includes/footer.php"); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>