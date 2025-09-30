<?php
session_start();

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: register.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Your CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - Registration Successful</title>
    <style>

    </style>
</head>

<body class="background text-light">

    <div class="login-container">
        <div class="login-card text-center">
            <!-- Success Icon -->
            <div class="success-icon">
                <i class="ri-checkbox-circle-line"></i>
            </div>

            <!-- Header -->
            <div class="login-header mb-4">
                <h2>Registration Successful!</h2>
                <p class="mt-3">Welcome to Elocker, <strong><?php echo $username; ?></strong>!</p>
            </div>

            <!-- Informational Text -->
            <p class="success-note">
                Your account has been created securely. We are committed to protecting your data with robust encryption and advanced security features.
            </p>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-notice-header">
                    <i class="ri-error-warning-line"></i>
                    <span>Security Notice</span>
                </div>
                <p class="mb-2">Your username and password are the only keys to access your dashboard. Never share them with anyone.</p>
                <p class="mb-0">Remember: safeguarding your credentials is your responsibility.</p>
            </div>

            <!-- Security Instructions -->
            <ul class="security-list text-start">
                <li>Write down your credentials on paper or store them safely in a secure offline location</li>
                <li>All data is encrypted and securely stored on our backend servers</li>
                <li>We do not have access to your private information at any time</li>
            </ul>

            <!-- Action Button -->
            <a href="../index.php" class="btn btn-primary w-100 mt-2 py-2">
                Proceed to Log In <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>
    </div>

</body>

</html>