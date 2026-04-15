<?php
session_start();
include('includes/config.php');

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    $_SESSION['error'] = "No student selected.";
    header("Location: manage_students.php");
    exit(0);
}

$user_id = mysqli_real_escape_string($con, $_GET['user_id']);
$query = "SELECT * FROM `users` WHERE `id` = '$user_id'";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Student not found.";
    header("Location: manage_students.php");
    exit(0);
}

$student = mysqli_fetch_assoc($result);

$qrData = "Registration No: " . $student['reg_no'] . "\n" .
    "Name: " . $student['name'] . "\n" .
    "Email: " . $student['email'] . "\n" .
    "Phone: " . $student['phone'];

$encodedData = urlencode($qrData);
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $encodedData;

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code | <?= htmlspecialchars($student['name']); ?></title>
    <link rel="icon" href="uploads/favicon/<?= $settingRow['favicon'] ?? ''; ?>" type="image/jpeg/png" />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        :root {
            --bg-color: #f0f2f5;
        }

        body {
            background: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .qr-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* --- ID Card Styling --- */
        #printable-card {
            width: 100%;
            max-width: 320px;
            background: #ffffff;
            border: 2px dashed #adb5bd;
            border-radius: 12px;
            padding: 24px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: border-color 0.2s ease;
        }

        #printable-card:hover {
            border-color: #6c757d;
        }

        #printable-card h4 {
            font-size: 20px;
            letter-spacing: -0.02em;
            color: #212529;
        }

        #printable-card span {
            font-size: 14px;
            font-weight: 500;
        }

        #printable-card img {
            width: 180px;
            height: 180px;
            border: 1px solid #e9ecef !important;
            border-radius: 8px !important;
            padding: 8px;
            background: #fff;
        }

        /* --- Print Specific Rules --- */
        @media print {
            @page {
                size: auto;
                margin: 0mm;
            }

            body {
                background: white !important;
                display: block;
            }

            .qr-wrapper {
                padding: 15mm;
                align-items: flex-start;
                justify-content: flex-start;
            }

            .no-print {
                display: none !important;
            }

            #printable-card {
                box-shadow: none !important;
                border: 1px dashed #999 !important; /* Cutline for print */
                border-radius: 0 !important;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="qr-wrapper">
        <div class="text-center mb-4" id="printable-card">
            <div class="mb-3">
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($student['name']); ?></h4>
                <span class="text-muted d-block">Reg No: <?= htmlspecialchars($student['reg_no']); ?></span>
            </div>

            <div class="mb-2">
                <img src="<?= $qrImageUrl; ?>" alt="QR Code" crossorigin="anonymous">
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2 no-print bg-white p-3 rounded-pill shadow-sm border">
            <a href="manage_students.php" class="btn btn-light border btn-sm px-3 fw-medium">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-medium">
                <i class="bi bi-printer"></i> Print
            </button>
            <button onclick="downloadCard()" class="btn btn-dark btn-sm px-3 fw-medium" id="downloadBtn">
                <i class="bi bi-download"></i> Download
            </button>
            <button onclick="saveToDatabase()" class="btn btn-success btn-sm px-3 fw-medium" id="saveDbBtn">
                <i class="bi bi-cloud-arrow-up"></i> Save to DB
            </button>
        </div>
    </div>

    <script>
        // Convert to async/await for cleaner syntax
        async function downloadCard() {
            const btn = document.getElementById('downloadBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
            btn.disabled = true;

            try {
                const cardElement = document.getElementById('printable-card');
                const canvas = await html2canvas(cardElement, {
                    useCORS: true, 
                    scale: 2, 
                    backgroundColor: "#ffffff"
                });

                let imageURL = canvas.toDataURL("image/png");
                let downloadLink = document.createElement('a');
                downloadLink.href = imageURL;
                downloadLink.download = "ID_Card_<?= htmlspecialchars($student['reg_no']); ?>.png";

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            } catch (error) {
                console.error("Error generating download:", error);
                alert("Failed to generate image for download.");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        async function saveToDatabase() {
            const btn = document.getElementById('saveDbBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            btn.disabled = true;

            try {
                const cardElement = document.getElementById('printable-card');
                const canvas = await html2canvas(cardElement, {
                    useCORS: true,
                    scale: 2,
                    backgroundColor: "#ffffff"
                });

                let base64Image = canvas.toDataURL("image/png");

                let response = await fetch('ajax/save_qr_db.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: <?= $student['id']; ?>,
                        reg_no: '<?= htmlspecialchars($student['reg_no']); ?>',
                        image: base64Image
                    })
                });

                let data = await response.json();

                if (data.success) {
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Saved!';
                    btn.classList.replace('btn-success', 'btn-outline-success');
                    alert('Success! QR Code saved successfully.');
                    window.location.href = "manage_students.php?qr_saved=<?= htmlspecialchars($student['reg_no']); ?>";
                } else {
                    throw new Error(data.message || 'Unknown error occurred on the server.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save: ' + error.message);
                btn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Save to DB';
                btn.disabled = false;
            }
        }
    </script>

</body>

</html>