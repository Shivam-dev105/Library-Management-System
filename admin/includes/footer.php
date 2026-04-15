<?php
include("config.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>

<footer class="footer bg-white rounded-4 shadow" style="margin-top: 55px;">
    <div class="container py-3">
        <div class="row align-items-center text-center text-md-start">

            <!-- Left -->
            <div class="col-md-6 mb-2 mb-md-0">
                <small class="text-muted">
                    © <?= date("Y"); ?> <?= $settingRow['system_name']; ?>. All Rights Reserved.
                </small>
            </div>

            <!-- Right -->
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    Designed with ❤️ by 
                    <a href="https://www.linkedin.com/in/shivam-kumar-28cse23/" 
                       target="_blank" 
                       class="text-success text-decoration-none fw-semibold">
                        Shivam Kumar
                    </a>
                </small>
            </div>

        </div>
    </div>
</footer>