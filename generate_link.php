<?php
session_start();
include 'db_connect.php';
// Kunin ang base URL (halimbawa: http://localhost:8080/siatrack/)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/student_login.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIATRACK | Generate Link</title>
    <style>
        /* Gamitin mo yung existing sidebar at main-content styles mo */
        .link-card { background: white; padding: 40px; border-radius: 15px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .url-box { background: #f8f9fa; padding: 15px; border: 2px dashed #0d6efd; border-radius: 8px; font-family: monospace; font-size: 16px; margin: 20px 0; color: #333; word-break: break-all; }
        .btn-copy { background: #0d6efd; color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="link-card">
            <i class="fa-solid fa-share-nodes" style="font-size: 50px; color: #0d6efd; margin-bottom: 20px;"></i>
            <h2>Student Evaluation Link</h2>
            <p style="color: #666;">I-copy ang link sa ibaba at i-share sa mga estudyante para makapag-simula sila ng evaluation.</p>
            
            <div class="url-box" id="evalLink"><?php echo $base_url; ?></div>
            
            <button class="btn-copy" onclick="copyLink()">
                <i class="fa-solid fa-copy"></i> Copy Evaluation Link
            </button>
        </div>
    </div>

    <script>
        function copyLink() {
            const linkText = document.getElementById('evalLink').innerText;
            navigator.clipboard.writeText(linkText).then(() => {
                alert("Link copied to clipboard! Pwede mo na itong i-paste sa GC.");
            });
        }
    </script>
</body>
</html>