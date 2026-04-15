<?php
include("config.php");

// fetch setting 
$setting = mysqli_query($con, "SELECT * FROM `setting` WHERE 1");
$settingRow = mysqli_fetch_assoc($setting);
?>
<header class="sticky-top shadow" style="background-color: #f5f9ff;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center">
                <a href="index.php" class="logo-container me-3">
                    <img id="logo1" src="assets/img/sbte-logo.png" class="dynamic-logo" style="opacity: 1;">
                    <img id="logo2" src="assets/img/gov-bih-logo.png" class="dynamic-logo" style="opacity: 0;">
                </a>
                <!-- <a href="index.php"><img src="assets/img/logo.png" alt="Library Logo" class="main-logo me-3"></a> -->
                <div>
                    <h1 class="library-title">Library <span class="fw-light">(Gp Bhojpur)</span></h1>
                    <p class="quote-subtitle mb-0" id="quoteText">"Reading connects great minds."</p>
                </div>
            </div>

        </div>
    </div>
</header>

<style>
    :root {
        --primary-color: #1a237e;
        /* Deep Indigo */
        --accent-color: #00c853;
        /* Fresh Green */
        --text-dark: #2c3e50;
        --bg-light: #f8f9fc;
    }

    /* --- Elegant Header --- */
    header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.8rem 0;
    }

    .main-logo {
        height: 55px;
        width: auto;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .library-title {
        color: var(--primary-color);
        font-weight: 700;
        letter-spacing: -0.5px;
        margin-bottom: 0;
        font-size: 1.4rem;
    }

    .quote-subtitle {
        font-style: italic;
        color: #6c757d;
        font-weight: 300;
        font-size: 0.9rem;
        border-left: 2px solid var(--accent-color);
        padding-left: 10px;
    }

    /* Smooth Logo Fading */
    .logo-container {
        width: 60px;
        height: 60px;
        position: relative;
    }

    .dynamic-logo {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        transition: opacity 1s ease-in-out;
        border-radius: 8px;
    }
</style>

<script>
    // --- Logo Switcher Logic ---
    const logo1 = document.getElementById('logo1');
    const logo2 = document.getElementById('logo2');
    let showFirst = true;

    setInterval(() => {
        if (showFirst) {
            logo1.style.opacity = 0;
            logo2.style.opacity = 1;
        } else {
            logo1.style.opacity = 1;
            logo2.style.opacity = 0;
        }
        showFirst = !showFirst;
    }, 4000);

    // --- Bonus: Changing Quotes ---
    const quotes = [
        "\"Reading connects great minds.\"",
        "\"Books are a uniquely portable magic.\"",
        "\"Today a reader, tomorrow a leader.\""
    ];
    let quoteIndex = 0;
    setInterval(() => {
        quoteIndex = (quoteIndex + 1) % quotes.length;
        document.getElementById('quoteText').innerText = quotes[quoteIndex];
    }, 8000);
</script>