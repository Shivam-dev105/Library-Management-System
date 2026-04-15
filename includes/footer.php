<?php
include("config.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>
<footer class="shadow">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-1 text-center text-md-start">
                <a href="index.php"><img src="assets/img/logo.png" alt="Footer Logo" height="40" class="mb-3"></a>
                <h5 class="fw-bold"><?= $settingRow['system_name']; ?></h5>
                <p class="text-muted pe-lg-4">Dedicated to fostering a culture of curiosity and lifelong learning for all engineering students.</p>
            </div>

            <div class="col-lg-4 col-md-6 mb-1">
                <h5 class="footer-heading">Quick Links</h5>
                <div class="d-flex flex-column">
                    <a href="index.php" class="footer-link">Home</a>
                    <a href="student_login.php" class="footer-link">Student Login</a>
                    <a href="register.php" class="footer-link">Registration</a>
                    <a href="admin_login.php" class="footer-link">Admin Login</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mb-1">
                <h5 class="footer-heading">Contact Details</h5>
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                    <span><?= $settingRow['company_phone']; ?></span>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-envelope-at-fill"></i></div>
                    <span><?= $settingRow['company_email']; ?></span>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <span><?= $settingRow['company_address']; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-light py-3 border-top mt-2">
        <div class="container text-center text-muted small">
            © 2026 <?= $settingRow['system_name']; ?>. Designed for Excellence by <a class="text-success text-decoration-none" href="https://www.linkedin.com/in/shivam-kumar-28cse23/">Shivam kumar</a>.
        </div>
    </div>
</footer>

<style>
    :root {
        --primary-color: #1a237e;
        /* Deep Indigo */
        --accent-color: #00c853;
        /* Fresh Green */
        --text-dark: #2c3e50;
        --bg-light: #f8f9fc;
    }

    /* --- Footer Styles --- */
    footer {
        background-color: #ffffff;
        color: var(--text-dark);
        padding-top: 50px;
        border-top: 1px solid #eee;
    }

    .footer-heading {
        font-weight: 700;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .footer-link {
        text-decoration: none;
        color: #6c757d;
        transition: all 0.3s ease;
        display: inline-block;
        margin-bottom: 10px;
    }

    .footer-link:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .contact-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        color: #6c757d;
    }

    .contact-icon {
        width: 35px;
        height: 35px;
        background: rgba(26, 35, 126, 0.05);
        transition: all 0.3s ease;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: var(--primary-color);
    }

    .contact-item:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }
</style>