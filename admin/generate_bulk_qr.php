<?php
session_start();
include('includes/config.php');

if (!isset($_POST['generate_bulk_qr'])) {
    header("Location: manage_students.php");
    exit(0);
}

$department_id = mysqli_real_escape_string($con, $_POST['department_id']);
$session_id = mysqli_real_escape_string($con, $_POST['session_id']);

// Fetch users based on selection and status = 1
$query = "SELECT * FROM `users` WHERE `department_id` = '$department_id' AND `session_id` = '$session_id' AND `status` = '1' ORDER BY `reg_no` ASC";
// $query = "SELECT * FROM `users` WHERE `department_id` = '$department_id' AND `session_id` = '$session_id' AND `status` = '1' AND (`qr_code` IS NULL OR `qr_code` = '') ORDER BY `reg_no` ASC";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('No students found for this selection.');window.location.href='manage_students.php#bulkqr';</script>";
    exit();
}

$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (count($users) == 0) {
    $_SESSION['error'] = "No students found for this selection.";
    header("Location: manage_students.php");
    exit(0);
}

// Fetch setting
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);

// Chunk users into groups of 6 for A4 pages
$pages = array_chunk($users, 6);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk QR Print | Student ID</title>
    <link rel="icon" href="uploads/favicon/<?= $settingRow['favicon'] ?? ''; ?>" type="image/jpeg/png" />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        :root {
            --bg-color: #f0f2f5;
            --card-border: #dee2e6;
        }

        body {
            background: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        /* --- Modern Sticky Toolbar --- */
        .toolbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            z-index: 1000;
        }

        /* --- Workspace & A4 Page Styling (Screen) --- */
        .workspace {
            padding: 2rem 0;
        }

        .a4-page {
            width: 210mm;
            height: 297mm; /* Strict A4 height */
            margin: 0 auto 2rem auto;
            background: white;
            padding: 10mm; /* Reduced padding from 15mm to 10mm */
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(3, 1fr);
            gap: 10mm; /* Reduced gap from 15mm to 10mm */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 8px; /* Slightly rounded for screen view */
            box-sizing: border-box;
        }

        /* --- QR Card Enhancements --- */
        .qr-card-capture {
            border: 2px dashed #adb5bd; /* Distinct dashed border for cutting */
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px; /* Comfortable inner padding */
            text-align: center;
            background: #fff;
            height: 100%; /* Force it to stay inside its grid box */
            box-sizing: border-box;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        /* Subtle hover effect for screen interaction */
        .qr-card-capture:hover {
            border-color: #6c757d;
            background-color: #f8f9fa;
        }

        .qr-card-capture h5 {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px !important;
            letter-spacing: -0.02em;
        }

        .qr-card-capture span {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }

        .qr-card-capture img {
            width: 120px;
            height: 120px;
            margin-top: 12px;
            padding: 4px;
            background: #fff;
            border: 1px solid #e9ecef !important;
            border-radius: 8px !important; /* Soft corners for the QR container */
        }

        #saveStatus {
            font-weight: 500;
            min-height: 20px; /* Prevents layout shift */
        }

        /* --- Print Specific Rules --- */
        @media print {
            @page {
                size: A4;
                margin: 0; /* Stops the browser from adding its own margins */
            }

            body {
                background: white !important;
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact; /* Forces borders and background colors to print */
            }

            .no-print {
                display: none !important;
            }

            .workspace {
                padding: 0;
            }

            .a4-page {
                margin: 0;
                box-shadow: none;
                border-radius: 0; /* Remove rounding for actual print */
                page-break-after: always;
                page-break-inside: avoid;
            }

            .qr-card-capture {
                border-color: #999; /* Ensure print uses pure greys for cutlines */
            }
        }
    </style>
</head>

<body>

    <div class="toolbar sticky-top shadow-sm py-3 no-print">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-qr-code-scan fs-4 text-primary me-2"></i>
                <div>
                    <h5 class="mb-0 fw-bold">Bulk QR Generator</h5>
                    <small class="text-muted">Generating <?= count($users); ?> Student ID(s)</small>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <a href="manage_students.php" class="btn btn-light border shadow-sm"><i class="bi bi-arrow-left"></i> Back</a>
                <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="bi bi-printer"></i> Print All</button>
                <button onclick="saveAllToDatabase()" id="saveAllDbBtn" class="btn btn-warning shadow-sm fw-medium"><i class="bi bi-cloud-arrow-up"></i> Save All to DB</button>
            </div>
        </div>
        <div class="container text-center mt-2">
            <p class="mb-0 text-primary small" id="saveStatus"></p>
        </div>
    </div>

    <div class="workspace">
        <?php foreach ($pages as $page_users): ?>
            <div class="a4-page">
                <?php foreach ($page_users as $student):
                    // Kept QR Data generation exactly the same
                    $qrData = "Registration No: " . $student['reg_no'] . "\nName: " . $student['name'] . "\nEmail: " . $student['email'] . "\nPhone: " . $student['phone'];
                    $encodedData = urlencode($qrData);
                    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $encodedData;
                ?>
                    <div class="qr-card-capture" data-userid="<?= $student['id']; ?>" data-regno="<?= $student['reg_no']; ?>">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($student['name']); ?></h5>
                        <span class="d-block mb-2">Reg No: <?= htmlspecialchars($student['reg_no']); ?></span>
                        <img src="<?= $qrImageUrl; ?>" alt="QR Code" crossorigin="anonymous">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Async function to process and save all QR cards one by one
        async function saveAllToDatabase() {
            const btn = document.getElementById('saveAllDbBtn');
            const statusText = document.getElementById('saveStatus');
            const cards = document.querySelectorAll('.qr-card-capture');

            if (cards.length === 0) return;

            // UI Update for loading state
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            btn.classList.add('disabled');
            btn.disabled = true;

            let successCount = 0;

            for (let i = 0; i < cards.length; i++) {
                let card = cards[i];
                let userId = card.getAttribute('data-userid');
                let regNo = card.getAttribute('data-regno');

                statusText.innerHTML = `<i class="bi bi-hourglass-split"></i> Saving ${i + 1} of ${cards.length}...`;

                try {
                    let canvas = await html2canvas(card, {
                        useCORS: true,
                        scale: 2,
                        backgroundColor: "#ffffff"
                    });

                    let base64Image = canvas.toDataURL("image/png");

                    // Send to your existing AJAX file
                    let response = await fetch('ajax/save_qr_db.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            reg_no: regNo,
                            image: base64Image
                        })
                    });

                    let data = await response.json();
                    if (data.success) {
                        successCount++;
                    }

                } catch (error) {
                    console.error('Error saving card for Reg No: ' + regNo, error);
                }
            }

            // UI Update for success state
            statusText.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> Successfully saved ${successCount} out of ${cards.length} QR codes.`;
            statusText.className = "mb-0 text-success small fw-bold";
            
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Saved';
            btn.classList.replace('btn-warning', 'btn-outline-success');
            
            // Optional alert fallback
            // alert(`Process complete! Saved ${successCount} QR codes.`);
        }
    </script>
</body>

</html>