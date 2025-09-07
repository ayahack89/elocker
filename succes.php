<?php
session_start();

// If the user lands on this page without having a username in the session,
// redirect them to the registration page. This prevents direct access.
if (!isset($_SESSION['username'])) {
    header('Location: register.php');
    exit();
}

// Get the username from the session and sanitize it for safe display.
$username = htmlspecialchars($_SESSION['username']);

// Unset the session variable after displaying it, so the message is only shown once.
// You can remove this line if you want the user to be able to refresh the success page.
// unset($_SESSION['username']);

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "include/cdn.php"; ?>
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Your CSS -->
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Registration Successful</title>
</head>

<body class="background text-light">

    <div class="login-container">
        <div class="login-card text-center">
            <!-- Success Icon -->
            <div class="success-icon">
                <i class="ri-checkbox-circle-line"></i>
            </div>

            <!-- Header -->
            <div class="login-header">
                <h2>Registration Successful!</h2>
                <p>Welcome to Elocker, <strong><?php echo $username; ?></strong>!</p>
            </div>

            <!-- Informational Text -->
            <p class="success-note">
                Your account has been created securely. We are committed to protecting your data with robust encryption and advanced security features.
            </p>

            <!-- Action Button -->
            <a href="index.php" class="btn btn-primary w-100 mt-4">
                Proceed to Log In <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>

</body>

</html>