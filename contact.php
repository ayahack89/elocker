<?php
session_start();

// Security Check: If the user is not logged in, redirect them to the login page.
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "include/cdn.php"; ?>
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Contact Support</title>
</head>
<body class="background text-light">

    <?php include "include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card text-center">

                    <div class="contact-icon">
                        <i class="ri-customer-service-2-line"></i>
                    </div>

                    <div class="login-header">
                        <h2>Contact Support</h2>
                        <p>We're here to help with any questions or issues you may have.</p>
                    </div>

                    <div class="contact-info">
                        <p>For any problems, security concerns, or other inquiries, please send an email to:</p>
                        <a href="mailto:ayanabhachatterjee@gmail.com" class="contact-email-link">ayanabhachatterjee@gmail.com</a>
                        <p class="contact-signature">- Ayanabha Chatterjee</p>
                    </div>

                    <p class="back-link">
                        <a href="userAccount.php"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
                    </p>

                </div>
            </div>
        </div>
    </main>
</body>
</html>