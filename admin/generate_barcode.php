<?php
session_start();
include('includes/config.php');

if (!isset($_GET['book_id']) || empty($_GET['book_id'])) {
    echo "<script>alert('Invalid Request!'); window.location.href='manage_books.php';</script>";
    exit();
}

$book_id = mysqli_real_escape_string($con, $_GET['book_id']);

$bookQuery = mysqli_query($con, "SELECT `title`, `author`, `isbn` FROM `books` WHERE `id` = '$book_id'");
if (mysqli_num_rows($bookQuery) == 0) {
    echo "<script>alert('Book not found.'); window.location.href='manage_books.php';</script>";
    exit();
}
$bookDetails = mysqli_fetch_assoc($bookQuery);

$copiesQuery = mysqli_query($con, "SELECT * FROM `book_copies` WHERE `book_id` = '$book_id' ORDER BY `unique_code` ASC");
if (mysqli_num_rows($copiesQuery) == 0) {
    echo "<script>alert('No copies found. Add quantity first.'); window.location.href='manage_books.php';</script>";
    exit();
}

$bookCopies = mysqli_fetch_all($copiesQuery, MYSQLI_ASSOC);
$pages = array_chunk($bookCopies, 32);

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Small Barcodes | <?= htmlspecialchars($bookDetails['title']); ?></title>
    <link rel="icon" href="uploads/favicon/<?= $settingRow['favicon']; ?>" type="image/jpeg/png" />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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

        /* --- A4 Page Styling (Screen) --- */
        .workspace {
            padding: 2rem 0;
        }

        .a4-page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto 2rem auto;
            background: white;
            padding: 10mm;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 5mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 8px; /* Slightly rounded for screen view */
            box-sizing: border-box;
        }

        /* --- Barcode Card Enhancements --- */
        .barcode-card {
            border: 1px dashed var(--card-border);
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px;
            text-align: center;
            background: #fff;
            height: 100%;
            box-sizing: border-box;
            overflow: hidden;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        /* Subtle hover effect for screen interaction */
        .barcode-card:hover {
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }

        .barcode-card h6 {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 2px !important;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .barcode-card span {
            font-size: 9px;
            color: #6c757d;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .barcode-card img {
            width: 100%;
            max-width: 130px;
            height: 38px;
            object-fit: contain;
        }

        #statusText {
            font-weight: 500;
            min-height: 20px; /* Prevents layout layout shift when text appears */
        }

        /* --- Print Specific Rules --- */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: white !important;
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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

            .barcode-card {
                border-color: #ccc; /* Ensure print uses pure greys */
            }
        }
    </style>
</head>

<body>

    <div class="toolbar sticky-top shadow-sm py-3 no-print">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-upc-scan fs-4 text-primary me-2"></i>
                <div>
                    <h5 class="mb-0 fw-bold">Barcode Generator</h5>
                    <small class="text-muted"><?= htmlspecialchars($bookDetails['title']); ?></small>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <a href="manage_books.php" class="btn btn-light border shadow-sm"><i class="bi bi-arrow-left"></i> Back</a>
                <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="bi bi-printer"></i> Print</button>
                <button onclick="saveAllToDatabase()" id="saveBtn" class="btn btn-success shadow-sm"><i class="bi bi-cloud-arrow-up"></i> Save to Database</button>
            </div>
        </div>
        <div class="container text-center mt-2">
            <p class="mb-0 text-primary small" id="statusText"></p>
        </div>
    </div>

    <div class="workspace">
        <?php foreach ($pages as $page_copies): ?>
            <div class="a4-page">
                <?php
                foreach ($page_copies as $copy):
                    $barcodeApiUrl = "https://bwipjs-api.metafloor.com/?bcid=code128&text=" . urlencode($copy['unique_code']) . "&scale=2&includetext";
                ?>
                    <div class="barcode-card capture-target" data-copyid="<?= $copy['id']; ?>" data-code="<?= $copy['unique_code']; ?>">
                        <img src="<?= $barcodeApiUrl; ?>" alt="<?= $copy['unique_code']; ?>" crossorigin="anonymous">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        async function saveAllToDatabase() {
            const btn = document.getElementById('saveBtn');
            const statusText = document.getElementById('statusText');
            const cards = document.querySelectorAll('.capture-target');

            if (cards.length === 0) return;

            // UI Update for loading state
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            btn.classList.add('disabled');
            btn.disabled = true;

            let successCount = 0;

            for (let i = 0; i < cards.length; i++) {
                let card = cards[i];
                let copyId = card.getAttribute('data-copyid');
                let uniqueCode = card.getAttribute('data-code');

                statusText.innerHTML = `<i class="bi bi-hourglass-split"></i> Saving ${i + 1} of ${cards.length} to server... Please wait.`;

                try {
                    // Capture the div as an image
                    let canvas = await html2canvas(card, {
                        useCORS: true,
                        scale: 2, // High resolution
                        backgroundColor: "#ffffff"
                    });

                    let base64Image = canvas.toDataURL("image/png");

                    // Send directly to PHP backend to save in Database & Server Folder
                    let response = await fetch('ajax/save_barcode.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            copy_id: copyId,
                            unique_code: uniqueCode,
                            image: base64Image
                        })
                    });

                    let data = await response.json();
                    if (data.success) {
                        successCount++;
                    }
                } catch (error) {
                    console.error('Error saving: ' + uniqueCode, error);
                }
            }

            // UI Update for success state
            statusText.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> Successfully saved ${successCount} barcodes to the database.`;
            statusText.className = "mb-0 text-success small fw-bold";
            
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Saved';
            btn.classList.replace('btn-success', 'btn-outline-success');
            
            // Optional alert (can be removed if the on-screen status text is enough)
            // alert(`Process complete! Saved ${successCount} barcodes to the server.`);
        }
    </script>
</body>

</html>