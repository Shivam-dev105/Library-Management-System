<?php 
include("includes/logout_confirmation.php");
include("config.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>

<!-- header section -->
<div class="d-flex align-items-center mb-4 bg-white p-3 rounded shadow sticky-top">

    <button class="sidebar-toggle-btn me-3" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <h4 class="fw-bold mb-0 d-none d-sm-block">Welcome, Librarian</h4>

    <div class="dropdown ms-auto">
        <button class="btn bg-light border rounded-pill px-3 shadow-sm dropdown-toggle"
            type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle text-primary me-1"></i> Admin
        </button>

        <ul class="dropdown-menu dropdown-menu-end premium-dropdown p-2">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>

        </ul>
    </div>

</div>

<!-- css for header  -->
<style>
    .sidebar-toggle-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: none;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .sidebar-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .sidebar-toggle-btn:active {
        transform: scale(0.95);
    }

    /* Premium Dropdown */
    .premium-dropdown {
        width: 220px;
        border-radius: 18px;

        /* Glass Effect */
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);

        border: 1px solid rgba(255, 255, 255, 0.25);

        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);

        padding: 10px;
    }

    .premium-dropdown .dropdown-item {
        padding: 10px 14px;
        font-weight: 400;
        transition: all 0.2s ease;
    }

    .premium-dropdown .dropdown-item:hover {
        width: 100%;
        background: #f4f7fe;
        font-weight: 800;
        transform: translateX(5px);
    }
</style>

<!-- javascript for header  -->
<script>
    const toggleBtn = document.getElementById('sidebarToggle');
    const icon = toggleBtn.querySelector("i");
    const body = document.body;
    const overlay = document.getElementById('overlay');

    toggleBtn.addEventListener("click", () => {

        // Agar screen width 992px se chhoti hai (mobile)
        if (window.innerWidth < 992) {
            // Sirf bi-list hi rahe
            icon.classList.remove("bi-x-lg");
            icon.classList.add("bi-list");
        } else {
            // Laptop/Desktop me toggle kare
            icon.classList.toggle("bi-list");
            icon.classList.toggle("bi-x-lg");
        }
    });

    toggleBtn.addEventListener('click', () => {
        if (window.innerWidth >= 992) {
            // Desktop: Toggle mini-sidebar
            body.classList.toggle('collapsed');
        } else {
            // Mobile: Toggle full overlay sidebar
            body.classList.toggle('show-sidebar');
        }
    });

    overlay.addEventListener('click', () => {
        body.classList.remove('show-sidebar');
    });

    // Optional: Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            body.classList.remove('show-sidebar');
        }
    });
</script>