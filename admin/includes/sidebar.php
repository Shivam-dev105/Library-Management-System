<?php
$currentPage = basename($_SERVER['PHP_SELF']);
include("config.php");

include("includes/logout_confirmation.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>

<!-- sidebar  -->
<div class="sidebar">
    <div class="px-4 mb-4 sidebar-header">
        <div class="sidebar-logo d-none">
            <img src="uploads/logo/<?= $settingRow['logo']; ?>" width="70" class="rounded-circle" alt="logo">
        </div>

        <div class="sidebar-title">
            <h5 class="fw-bold mb-0 text-white"><?= $settingRow['system_name']; ?></h5>
            <small class="text-white-50">Admin Panel</small>
        </div>
    </div>

    <nav class="nav flex-column">
        <!-- dashboard  -->
        <a class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>

        <!-- manage student  -->
        <a class="nav-link <?php echo ($currentPage == 'manage_students.php') ? 'active' : ''; ?>" href="manage_students.php"><i class="bi bi-people"></i> <span>Manage Students</span></a>

        <!-- manage books  -->
        <a class="nav-link <?php echo ($currentPage == 'manage_books.php') ? 'active' : ''; ?>" href="manage_books.php"><i class="bi bi-book"></i> <span>Manage Books</span></a>

        <!-- issue and return  -->
        <a class="nav-link <?php echo ($currentPage == 'issue-return.php') ? 'active' : ''; ?>" href="issue-return.php"><i class="bi bi-arrow-left-right"></i> <span>Issue/Return</span></a>

        <!-- overdue list  -->
        <a class="nav-link <?php echo ($currentPage == 'overdue-list.php') ? 'active' : ''; ?>" href="overdue-list.php"><i class="bi bi-clock-history"></i> <span>Overdue List</span></a>

        <!-- fine  -->
        <a class="nav-link <?php echo ($currentPage == 'fine_management.php') ? 'active' : ''; ?>" href="fine_management.php"><i class="bi bi-cash-coin"></i> <span>Fine Management</span></a>

        <!-- history  -->
        <a class="nav-link <?php echo ($currentPage == 'history.php') ? 'active' : ''; ?>" href="history.php"><i class="bi bi-clock-history"></i> <span>Borrowing History</span></a>

        <!-- academic  -->
        <a class="nav-link <?php echo ($currentPage == 'academic.php') ? 'active' : ''; ?>" href="academic.php"><i class="bi bi-mortarboard"></i><span>Academic</span></a>

        <hr class="mx-3 opacity-25 text-white">

        <!-- profile  -->
        <a class="nav-link <?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>" href="profile.php"><i class="bi bi-person-circle"></i><span>Profile</span></a>

        <!-- settings  -->
        <a class="nav-link <?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?>" href="settings.php"><i class="bi bi-gear"></i><span>Settings</span></a>

        <!-- logout  -->
        <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-left"></i> <span>Logout</span></a>
    </nav>
</div>

<div class="sidebar-overlay" id="overlay"></div>

<!-- css of sidebar  -->
<style>
    :root {
        --sidebar-bg: #1a237e;
        --accent: #00c853;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 90px;
    }

    /* --- Sidebar Base --- */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        /* min-height ki jagah height use kare */
        background: var(--sidebar-bg);
        position: fixed;
        left: 0;
        top: 0;
        color: white;
        padding-top: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;

        overflow-y: auto;
        /* 👈 important */
        overflow-x: hidden;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.7);
        padding: 12px 22px;
        display: flex;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
        overflow: hidden;
        border-left: 4px solid transparent;
    }

    .sidebar .nav-link i {
        font-size: 1.2rem;
        min-width: 25px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.1);
        border-left-color: var(--accent);
    }

    /* --- Desktop Collapsed State --- */
    @media (min-width: 992px) {
        body.collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        /* Smooth text animation */
        .sidebar .nav-link span,
        .sidebar-title h5,
        .sidebar-title small {
            transition: all 0.3s ease;
            opacity: 1;
            transform: translateX(0);
            white-space: nowrap;
        }

        /* Optional delay for premium feel */
        body:not(.collapsed) .sidebar .nav-link span {
            transition-delay: 0.1s;
        }

        body.collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
    }

    /* --- Mobile Responsive State --- */
    @media (max-width: 991px) {
        .sidebar {
            left: calc(var(--sidebar-width) * -1);
        }

        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        body.show-sidebar .sidebar {
            left: 0;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        body.show-sidebar .sidebar-overlay {
            display: block;
        }
    }

    /* Mini sidebar logo show */
    body.collapsed .sidebar-title {
        display: none;
    }

    body.collapsed .sidebar-logo {
        display: block !important;
        margin-left: -16px;
    }

    /* Mini label under icon */
    body.collapsed .sidebar .nav-link {
        flex-direction: column;
        font-size: 11px;
        padding: 12px 5px;
        align-items: center;
        justify-content: center;
    }

    body.collapsed .sidebar .nav-link span {
        display: block !important;
        font-size: 8px;
        margin-top: -8px;
        opacity: 1;
        transform: none;
    }

    .sidebar .nav-link span {
        transition: all 0.3s ease;
    }

    /* Custom Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--sidebar-bg);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Custom Scrollbar */
    body::-webkit-scrollbar {
        width: 6px;
    }

    body::-webkit-scrollbar-track {
        background: transparent;
    }

    body::-webkit-scrollbar-thumb {
        background: var(--sidebar-bg);
        border-radius: 10px;
    }

    body::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>