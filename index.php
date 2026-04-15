<?php
include("includes/config.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $settingRow['company_name']; ?> | <?= $settingRow['system_name']; ?></title>

    <link rel="icon" href="admin/uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #4f46e5;
            /* Modern Indigo */
            --primary-hover: #4338ca;
            --bg-body: #f8fafc;
            /* Soft Slate */
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
        }

        /* --- Premium Background Effects --- */
        .hero-section {
            width: 100%;
            padding: 80px 0;
            position: relative;
            background: radial-gradient(circle at top center, rgba(79, 70, 229, 0.08) 0%, rgba(248, 250, 252, 0) 70%);
            overflow: hidden;
        }

        /* Subtle background glow for depth */
        .hero-section::before {
            content: '';
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 400px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, transparent 70%);
            filter: blur(40px);
            z-index: -1;
        }

        /* --- Image Styling --- */
        .college-logo {
            height: 110px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
            transition: transform 0.3s ease;
        }

        .college-logo:hover {
            transform: scale(1.02);
        }

        /* --- Badge Styling --- */
        .badge-soft {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            border: 1px solid rgba(79, 70, 229, 0.2);
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* --- Typography --- */
        .hero-title {
            letter-spacing: -0.03em;
            line-height: 1.2;
            color: var(--text-main);
        }

        /* --- Buttons --- */
        .btn-modern {
            font-weight: 500;
            padding: 0.85rem 2.25rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary-modern {
            background-color: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-primary-modern:hover {
            background-color: var(--primary-hover);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        }

        .btn-outline-modern {
            background-color: white;
            color: var(--text-main);
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .btn-outline-modern:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        }

        /* --- Admin Link --- */
        .admin-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .admin-link:hover {
            color: var(--primary);
        }

        /* Desktop Adjustments */
        @media (min-width: 992px) {
            .hero-section {
                padding: 120px 0;
            }
        }
    </style>
    
</head>

<body>

    <?php include("includes/header.php") ?>

    <main>
        <section class="hero-section">
            <div class="container text-center">

                <img class="college-logo mb-4" src="admin/uploads/logo/<?= $settingRow['logo'] ?>" alt="<?= $settingRow['company_name']; ?> Logo">

                <div class="mb-4">
                    <span class="badge rounded-pill badge-soft px-3 py-2 shadow-sm">
                        <i class="bi bi-stars me-1"></i> Welcome to Digital <?= $settingRow['system_name']; ?>
                    </span>
                </div>

                <h1 class="display-4 fw-bold hero-title mb-3">Explore our Digital Collection</h1>
                <p class="lead mx-auto mb-5" style="max-width: 650px; color: var(--text-muted);">
                    Access thousands of books, journals, and research papers curated exclusively for the <?= $settingRow['company_name']; ?> repository.
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 mb-5">
                    <a href="student_login.php" class="btn btn-modern btn-primary-modern w-100 w-sm-auto" style="max-width: 240px;">
                        <i class="bi bi-person-fill"></i> Student Login
                    </a>
                    <a href="register.php" class="btn btn-modern btn-outline-modern w-100 w-sm-auto" style="max-width: 240px;">
                        Create Account
                    </a>
                </div>

                <div class="mt-2">
                    <a href="admin_login.php" class="admin-link">
                        Admin Access <i class="bi bi-arrow-right-short fs-5 ms-1"></i>
                    </a>
                </div>

            </div>
        </section>
    </main>

    <?php include("includes/footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>