<?php
session_start();

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index');
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
    <title>Wolfallet - faq</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Frequently Asked Questions</h2>
                        <p>Find answers to common questions about Wolfallet</p>
                    </div>

                    <div class="accordion accordion-flush" id="faqAccordion">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    How do I use Wolfallet to manage passwords?
                                </button>
                            </h2>
                            <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>After signin, you will land on your personal dashboard with several options:</p>
                                    <h6>Store New Password:</h6>
                                    <p>Click this option to securely save new credentials. Fill in the form and submit it to add the entry to your vault.</p>
                                    <h6>Manage Passwords:</h6>
                                    <p>Here you can view all your stored passwords. You can easily <strong>Edit</strong>, <strong>Delete</strong>, or <strong>Search</strong> for specific entries to keep everything organized.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                    Is my data safe with Wolfallet?
                                </button>
                            </h2>
                            <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Yes, your security is our top priority.</h5>
                                    <p>All credentials you store are protected with strong <strong>AES-256-CBC encryption</strong> before they are saved in our database. This means your sensitive information is unreadable to anyone without the secret key. We are committed to providing a secure and trustworthy environment for your data.</p>
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

                        <!-- New / Additional FAQ Items - keep pattern & UI intact -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                                    What is Wolfallet's refund policy?
                                </button>
                            </h2>
                            <div id="flush-collapseFour" class="accordion-collapse collapse" aria-labelledby="flush-headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Refunds and billing</h5>
                                    <p>All payments made for Wolfallet are non-refundable. Once you complete your purchase, we cannot issue refunds under any circumstances. However, your satisfaction and security are our top priority. If you face any issues with the service, please contact our <a href="contact" class="faq-link">Customer Support</a> team immediately, and we will make every effort to resolve your problem within 24 hours.</p>

                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                                    What happens if I forget my master password?
                                </button>
                            </h2>
                            <div id="flush-collapseFive" class="accordion-collapse collapse" aria-labelledby="flush-headingFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Account recovery limitations</h5>
                                    <p>Wolfallet is designed so that only you can access your encrypted vault. For security reasons, if you forget your master password, we cannot decrypt your stored data. However, you can export your login credentials from your profile. The process is simple—after a basic verification, an <strong>Export Credentials</strong> button will appear. Just click it to download your login data. We recommend writing down your credentials on paper or storing them securely in an offline location. Otherwise, you may need to create a new account.</p>
                                </div></div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSix" aria-expanded="false" aria-controls="flush-collapseSix">
                                    How are my passwords encrypted and where are encryption keys stored?
                                </button>
                            </h2>
                            <div id="flush-collapseSix" class="accordion-collapse collapse" aria-labelledby="flush-headingSix" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Encryption & key handling</h5>
                                    <p>Passwords are encrypted client-side using <strong>AES-256</strong> before being sent to our servers. The encryption/decryption key is derived from your master password and is never stored on our servers in plaintext. This ensures that even if the database were compromised, encrypted entries remain secure without the master key.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingEight">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseEight" aria-expanded="false" aria-controls="flush-collapseEight">
                                    Can I access Wolfallet on multiple devices and sync my vault?
                                </button>
                            </h2>
                            <div id="flush-collapseEight" class="accordion-collapse collapse" aria-labelledby="flush-headingEight" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Cross-device access</h5>
                                    <p>Yes. Once signed in on another device, your encrypted vault syncs across devices so you can access your passwords anywhere. All syncing is done using encrypted payloads — your data is decrypted only on your device after entering the master password.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingNine">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseNine" aria-expanded="false" aria-controls="flush-collapseNine">
                                    How can I export or import my passwords?
                                </button>
                            </h2>
                            <div id="flush-collapseNine" class="accordion-collapse collapse" aria-labelledby="flush-headingNine" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Export your data</h5>
                                   <p>You can export your vault data as a secure <code>.csv</code> file through the <strong>Manage New Passwords</strong> section. Simply go to that tab, locate the <strong>Export Data</strong> button, and tap it to securely download your stored credentials in <code>.csv</code> format. Please remember to keep this file safe and protected.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingTen">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTen" aria-expanded="false" aria-controls="flush-collapseTen">
                                    How do I permanently delete my account and data?
                                </button>
                            </h2>
                            <div id="flush-collapseTen" class="accordion-collapse collapse" aria-labelledby="flush-headingTen" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <h5>Account and data deletion</h5>
                                    <p>To delete your account and remove all stored data, go to <strong>User profile &gt; Delete Profie</strong>. After confirming, your encrypted vault and associated metadata will be removed from our servers permanently. Note: this action is irreversible. If you need assistance, <a href="contact" class="faq-link">contact support</a> before proceeding.</p>
                                </div>
                            </div>
                        </div>

                        <!-- End of new FAQ items -->

                    </div>

                    <p class="back-link">
                        <a href="useraccount"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
