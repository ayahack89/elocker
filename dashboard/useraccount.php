<?php
session_start();

// Security Check: If the user is not logged in, redirect them to the login page.
if (!isset($_SESSION['username'])) {
    header('Location: ../index');
    exit();
}

// Get the username from the session and sanitize it for safe display.
$username = htmlspecialchars($_SESSION['username']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - Dashboard</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>
    <main class="container py-5">
        <div class="dashboard-header mb-5">
            <h1>Welcome, <?php echo $username; ?>!</h1>
            <p class="dashboard-header-sub">This is your secure dashboard. Manage your passwords and settings from here.</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4">
            <div class="col">
                <a href="managepassword" class="dashboard-card">
                    <div class="card-icon"><i class="ri-shield-keyhole-line"></i></div>
                    <h5 class="card-title">Manage Passwords</h5>
                    <p class="card-text">View, edit, and organize your saved passwords.</p>
                </a>
            </div>

            <div class="col">
                <a href="storenewpassword" class="dashboard-card">
                    <div class="card-icon"><i class="ri-database-2-line"></i></div>
                    <h5 class="card-title">Store New Password</h5>
                    <p class="card-text">Safely store a new password in our secure database.</p>
                </a>
            </div>

            <div class="col">
                <a href="faq" class="dashboard-card">
                    <div class="card-icon"><i class="ri-questionnaire-line"></i></div>
                    <h5 class="card-title">faq</h5>
                    <p class="card-text">Have questions? Find answers to clear your doubts.</p>
                </a>
            </div>

            <div class="col">
                <a href="contact" class="dashboard-card">
                    <div class="card-icon"><i class="ri-customer-service-2-line"></i></div>
                    <h5 class="card-title">Customer Support</h5>
                    <p class="card-text">Encounter an issue? Don't hesitate to contact us.</p>
                </a>
            </div>

        </div>
    </main>

</body>
</html>