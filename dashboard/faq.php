<?php
session_start();

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - faq</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Frequently Asked Questions</h2>
                        <p>Find answers to common questions about Elocker.</p>
                    </div>

                    <div class="accordion accordion-flush" id="faqAccordion">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    How do I use Elocker to manage passwords?
                                </button>
                            </h2>
                            <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>After logging in, you will land on your personal dashboard with several options:</p>
                                    <h6>Store New Password:</h6>
                                    <p>Click this option to securely save new login credentials. Fill in the form and submit it to add the entry to your vault.</p>
                                    <h6>Manage Passwords:</h6>
                                    <p>Here you can view all your stored passwords. You can easily <strong>Edit</strong>, <strong>Delete</strong>, or <strong>Search</strong> for specific entries to keep everything organized.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                    Is my data safe with Elocker?
                                </button>
                            </h2>
                            <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Yes, your security is our top priority.</h5>
                                    <p>All passwords you store are protected with strong <strong>AES-256-CBC encryption</strong> before they are saved in our database. This means your sensitive information is unreadable to anyone without the secret key. We are committed to providing a secure and trustworthy environment for your data.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                    What if I have other issues or questions?
                                </button>
                            </h2>
                            <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>We're here to help.</h5>
                                    <p>For any other problems, security concerns, or inquiries, please navigate to the <a href="contact.php" class="faq-link">Customer Support</a> page from your dashboard. Our team will promptly assist you with any issues you may have.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="back-link">
                        <a href="useraccount.php"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>