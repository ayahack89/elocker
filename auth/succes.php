<?php
session_start();

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: register');
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
    <!-- Your CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <title>Wolfallet - Registration Successful</title>
    <style>
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.55);
            border-radius: 10px;
            padding: 1.5rem;
            color: rgba(255, 193, 7, 0.55);
        }

         .list-group-item {
            background: transparent;
            color: inherit;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            border-radius: 8px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .list-group-item::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 0.1rem;
        }
        

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
                <p class="mt-3">Welcome to Wolfallet, <strong><?php echo $username; ?></strong>!</p>
            </div>

            <!-- Informational Text -->
            <p class="success-note">
                Your account has been created securely. We are committed to protecting your data with robust encryption and advanced security features.
            </p>

            <!-- Security Notice -->
            <div class="alert alert-warning mb-4" role="alert">
                <div class="security-notice-header">
                    <i class="ri-error-warning-line"></i>
                    <span>Security Notice</span>
                </div>
                <p class="mb-2"><strong>Your username and password are the only keys to access your dashboard. Never share them with anyone.</strong></p>
                <p class="mb-0"><strong>Remember: safeguarding your credentials is your responsibility.</strong></p>
            </div>


            <!-- Security Instructions -->
            <ul class="list-group mb-4">
                <li class="list-group-item">Write down your credentials on paper or store them safely in a secure offline location</li>
                <li class="list-group-item">All data is encrypted and securely stored on our backend servers</li>
                <li class="list-group-item">We do not have access to your private information at any time</li>
            </ul>


            <!-- Action Button -->
            <a href="../index" class="btn btn-primary w-100 mt-2 py-2">
                Proceed to dashboard <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>
    </div>

</body>

</html>