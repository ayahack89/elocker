<?php 
session_start();
include "../private/db_config.php";
include "../private/config.php";
include "../private/encryption.php"; 

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index');
    exit();
}

// Create encryption instance
$encryption = new Encryption();

// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $username = $_SESSION['username'];
    $real_name = isset($_POST['real_name']) ? trim($_POST['real_name']) : '';
    $email_id = isset($_POST['email_id']) ? trim($_POST['email_id']) : '';
    $feedback_opinion = isset($_POST['feedback_opinion']) ? trim($_POST['feedback_opinion']) : '';
    
    if (!empty($feedback_opinion)) {
        try {
            // Encrypt the data before inserting
            $encrypted_username = $encryption->encrypt($username);
            $encrypted_real_name = !empty($real_name) ? $encryption->encrypt($real_name) : '';
            $encrypted_email_id = !empty($email_id) ? $encryption->encrypt($email_id) : '';
            $encrypted_feedback = $encryption->encrypt($feedback_opinion);
            
            $sql = "INSERT INTO feedback (username, real_name, email_id, feedback_oppinion, feedback_time) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $encrypted_username, $encrypted_real_name, $encrypted_email_id, $encrypted_feedback);
            
            if ($stmt->execute()) {
                $feedback_success = "Thank you for your feedback!";
                // Clear form fields
                unset($_POST['real_name'], $_POST['email_id'], $_POST['feedback_opinion']);
            } else {
                $feedback_error = "Error submitting feedback. Please try again.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $feedback_error = "Error processing your feedback. Please try again.";
            error_log("Encryption error: " . $e->getMessage());
        }
    } else {
        $feedback_error = "Please provide your feedback opinion.";
    }
}

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe_newsletter'])) {
    $newsletter_email = isset($_POST['newsletter_email']) ? trim($_POST['newsletter_email']) : '';
    
    if (!empty($newsletter_email) && filter_var($newsletter_email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $check_sql = "SELECT id FROM newsletter_subscriptions WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $newsletter_email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $newsletter_error = "You are already subscribed to our newsletter!";
        } else {
            // Encrypt username before storing
            $encrypted_username = $encryption->encrypt($_SESSION['username']);
            
            $sql = "INSERT INTO newsletter_subscriptions (email, username, subscribed_at) 
                    VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $newsletter_email, $encrypted_username);
            
            if ($stmt->execute()) {
                $newsletter_success = "Successfully subscribed to newsletter!";
                unset($_POST['newsletter_email']);
            } else {
                $newsletter_error = "Error subscribing to newsletter.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $newsletter_error = "Please enter a valid email address.";
    }
}

// Fetch current user's feedback - FIXED VERSION
$user_feedback = [];

try {
    // Get ALL feedback records first to debug
    $debug_sql = "SELECT username, feedback_oppinion, feedback_time FROM feedback ORDER BY feedback_time DESC LIMIT 10";
    $debug_stmt = $conn->prepare($debug_sql);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    
    $all_records = [];
    $matching_records = [];
    
    while ($debug_row = $debug_result->fetch_assoc()) {
        $all_records[] = $debug_row;
        
        // Try to decrypt each username to find matches
        try {
            $decrypted_username = $encryption->decrypt($debug_row['username']);
            if ($decrypted_username === $_SESSION['username']) {
                $matching_records[] = $debug_row;
            }
        } catch (Exception $e) {
            // Skip if decryption fails
        }
    }
    $debug_stmt->close();
    
    // Now fetch only the matching records for display
    if (!empty($matching_records)) {
        foreach ($matching_records as $record) {
            try {
                // Decrypt the feedback content
                $decrypted_feedback = $encryption->decrypt($record['feedback_oppinion']);
                
                $user_feedback[] = [
                    'feedback_oppinion' => $decrypted_feedback,
                    'feedback_time' => $record['feedback_time']
                ];
            } catch (Exception $e) {
                error_log("Decryption error for feedback: " . $e->getMessage());
                $user_feedback[] = [
                    'feedback_oppinion' => '[Unable to decrypt this feedback]',
                    'feedback_time' => $record['feedback_time']
                ];
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Database error fetching feedback: " . $e->getMessage());
}

// Check if newsletter_subscriptions table exists, if not create it
$check_table_sql = "SHOW TABLES LIKE 'newsletter_subscriptions'";
$table_result = $conn->query($check_table_sql);
if ($table_result->num_rows == 0) {
    $create_table_sql = "CREATE TABLE newsletter_subscriptions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(255) NOT NULL,
        subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active'
    )";
    $conn->query($create_table_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <title>Wolfallet - User Profile</title>
    <style>
        :root {
            --primary-color: #0d6efd;
            --card-color: #1a1a1a;
            --text-color: #f8f9fa;
            --text-muted: #6c757d;
            --border-color: #2d2d2d;
            --background: #0f0f0f;
        }
        
        body {
            background-color: var(--background);
            color: var(--text-color);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .dashboard-header {
            margin-bottom: 3rem;
        }
        
        .dashboard-header h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-header-sub {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .profile-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .dashboard-card {
            background-color: var(--card-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            height: 100%;
            margin-bottom: 2rem;
        }
        
        .dashboard-card:hover {
            background-color: #222121;
        }
        
        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-weight: 500;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .card-text {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 0.75rem;
        }
        
        .form-control {
            background-color: #2a2a2a;
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            background-color: #2a2a2a;
            border-color: var(--primary-color);
            color: var(--text-color);
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .alert {
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #75b798;
            border-left: 4px solid #198754;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ea868f;
            border-left: 4px solid #dc3545;
        }
        
        /* Facebook-style Comment Design */
        .feedback-item {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }
        
        .feedback-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .feedback-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #6f42c1 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 1.2rem;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            margin: 0;
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .feedback-meta {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin: 2px 0 0 0;
        }
        
        .feedback-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 12px 16px;
            margin-left: 52px; /* Align with user info */
            color: var(--text-color);
            line-height: 1.5;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .feedback-content:before {
            content: '';
            position: absolute;
            left: -8px;
            top: 12px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid rgba(255, 255, 255, 0.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        .policy-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .policy-card {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .policy-card:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--primary-color);
        }
        
        .policy-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .policy-card h4 {
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .policy-card p {
            color: var(--text-muted);
            margin: 0;
        }
        
        /* Modal Styling */
        .modal-content {
            background-color: var(--card-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
        }
        
        .modal-title {
            color: var(--text-color);
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
            color: var(--text-color);
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .modal-body h5 {
            color: var(--text-color);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .modal-body p {
            margin-bottom: 1rem;
            line-height: 1.6;
            color: var(--text-color);
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .back-link {
            margin-top: 3rem;
            text-align: center;
        }
        
        .back-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .back-link a:hover {
            background-color: rgba(13, 110, 253, 0.1);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .dashboard-card {
                padding: 1.5rem;
            }
            
            .policy-cards {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .feedback-content {
                margin-left: 0;
                margin-top: 12px;
            }
            
            .feedback-content:before {
                display: none;
            }
        }
    </style>
</head>
<body class="background text-light">
    <?php include "../include/navbar.php"; ?>
    
    <main class="container py-5">
        <div class="dashboard-header">
            <h1>User Profile</h1>
            <p class="dashboard-header-sub">Manage your feedback, subscriptions, and account preferences</p>
        </div>

        <div class="row">
            <!-- About Section -->
            <div class="col-12 profile-section">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="ri-information-line"></i>
                    </div>
                    <h3 class="card-title">About Wolfallet</h3>
                    <p class="card-text">Wolfallet is a secure password management solution designed to help you store and manage your credentials safely. With military-grade encryption and an intuitive interface, Wolfallet ensures your sensitive information remains protected while providing easy access when you need it.</p>
                    <p class="card-text">Our mission is to simplify digital security for everyone, making strong password practices accessible and manageable.</p>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="col-md-6 profile-section">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="ri-feedback-line"></i>
                    </div>
                    <h3 class="card-title">Share Your Feedback</h3>
                    <?php if (isset($feedback_success)): ?>
                        <div class="alert alert-success"><?php echo $feedback_success; ?></div>
                    <?php elseif (isset($feedback_error)): ?>
                        <div class="alert alert-danger"><?php echo $feedback_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="real_name" class="form-label">Your Name (Optional)</label>
                            <input type="text" class="form-control" id="real_name" name="real_name" placeholder="Enter your name" value="<?php echo isset($_POST['real_name']) ? htmlspecialchars($_POST['real_name']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email_id" class="form-label">Email Address (Optional)</label>
                            <input type="email" class="form-control" id="email_id" name="email_id" placeholder="Enter your email" value="<?php echo isset($_POST['email_id']) ? htmlspecialchars($_POST['email_id']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="feedback_opinion" class="form-label">Your Feedback *</label>
                            <textarea class="form-control" id="feedback_opinion" name="feedback_opinion" rows="4" placeholder="Please share your thoughts, suggestions, or issues..." required><?php echo isset($_POST['feedback_opinion']) ? htmlspecialchars($_POST['feedback_opinion']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>

            <!-- Newsletter Subscription -->
            <div class="col-md-6 profile-section">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="ri-mail-send-line"></i>
                    </div>
                    <h3 class="card-title">Stay Updated</h3>
                    <?php if (isset($newsletter_success)): ?>
                        <div class="alert alert-success"><?php echo $newsletter_success; ?></div>
                    <?php elseif (isset($newsletter_error)): ?>
                        <div class="alert alert-danger"><?php echo $newsletter_error; ?></div>
                    <?php endif; ?>
                    
                    <p class="card-text">Subscribe to our newsletter to receive updates about new features, security tips, and product announcements.</p>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <input type="email" class="form-control" name="newsletter_email" placeholder="Enter your email address" required value="<?php echo isset($_POST['newsletter_email']) ? htmlspecialchars($_POST['newsletter_email']) : ''; ?>">
                        </div>
                        <button type="submit" name="subscribe_newsletter" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>

            <!-- User's Previous Feedback -->
            <div class="col-12 profile-section">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="ri-history-line"></i>
                    </div>
                    <h3 class="card-title">Your Previous Feedback</h3>
                    
                    <?php if (empty($user_feedback)): ?>
                        <div class="empty-state">
                            <i class="ri-feedback-line"></i>
                            <p>You haven't submitted any feedback yet.</p>
                        </div>
                    <?php else: ?>
                        <div id="feedback-container">
                            <?php foreach ($user_feedback as $index => $feedback): ?>
                                <div class="feedback-item" id="feedback-<?php echo $index; ?>">
                                    <div class="feedback-header">
                                        <div class="user-avatar">
                                            <i class="ri-user-line"></i>
                                        </div>
                                        <div class="user-info">
                                            <h6 class="user-name">Anonymous</h6>
                                            <p class="feedback-meta">Submitted on <?php echo date('M j, Y g:i A', strtotime($feedback['feedback_time'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="feedback-content">
                                        <?php echo nl2br(htmlspecialchars($feedback['feedback_oppinion'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Policies Section -->
            <div class="col-12 profile-section">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="ri-file-text-line"></i>
                    </div>
                    <h3 class="card-title">Policies & Information</h3>
                    <p class="card-text">Important legal documents and policies for your reference.</p>
                    
                    <div class="policy-cards">
                        <div class="policy-card" data-bs-toggle="modal" data-bs-target="#termsModal">
                            <div class="policy-icon">
                                <i class="ri-file-text-line"></i>
                            </div>
                            <h4>Terms & Conditions</h4>
                            <p>Read our terms of service and usage policies</p>
                        </div>
                        <div class="policy-card" data-bs-toggle="modal" data-bs-target="#privacyModal">
                            <div class="policy-icon">
                                <i class="ri-shield-keyhole-line"></i>
                            </div>
                            <h4>Privacy Policy</h4>
                            <p>Learn how we protect and handle your data</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div class="back-link">
            <a href="useraccount"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
        </div>
    </main>

    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Acceptance of Terms</h5>
                    <p>By accessing and using Wolfallet, you accept and agree to be bound by the terms and provision of this agreement.</p>

                    <h5>Usage Guidelines</h5>
                    <p>Wolfallet is a password management tool. You are responsible for keeping your master password safe and secure. We cannot recover your password if lost.</p>

                    <h5>Refund Policy</h5>
                    <p>All payments made for Wolfallet are non-refundable. Once you complete your purchase, we cannot issue refunds under any circumstances.</p>

                    <h5>Account Recovery</h5>
                    <p>Wolfallet is designed so that only you can access your encrypted vault. For security reasons, if you forget your master password, we cannot decrypt your stored data.</p>

                    <h5>Data Encryption</h5>
                    <p>Passwords are encrypted client-side using AES-256 before being sent to our servers. The encryption/decryption key is derived from your master password.</p>

                    <h5>Cross-Device Access</h5>
                    <p>Once signed in on another device, your encrypted vault syncs across devices so you can access your passwords anywhere.</p>

                    <h5>Data Export</h5>
                    <p>You can export your vault data as a secure .csv file through the Manage Passwords section.</p>

                    <h5>Account Deletion</h5>
                    <p>To delete your account and remove all stored data, go to User profile → Delete Profile. This action is irreversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Information Collection</h5>
                    <p>Wolfallet collects minimal information necessary to provide our service. We store encrypted passwords and basic usage statistics.</p>

                    <h5>Data Encryption</h5>
                    <p>All sensitive data is encrypted using AES-256-CBC encryption before being stored in our databases.</p>

                    <h5>Data Usage</h5>
                    <p>We use your information to provide, maintain, and improve our services and to send you important service updates.</p>

                    <h5>Data Sharing</h5>
                    <p>We do not sell, trade, or rent your personal identification information to others.</p>

                    <h5>Security Measures</h5>
                    <p>We implement appropriate security measures to protect against unauthorized access to your data.</p>

                    <h5>Data Retention</h5>
                    <p>We retain your personal information only for as long as necessary to provide you with our services.</p>

                    <h5>Your Rights</h5>
                    <p>You have the right to access, correct, or delete your personal information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time feedback updates
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
            
            // Clear form fields on successful submission
            <?php if (isset($feedback_success)): ?>
                setTimeout(() => {
                    const form = document.querySelector('form[method="POST"]');
                    if (form) {
                        form.reset();
                    }
                }, 100);
            <?php endif; ?>
            
            <?php if (isset($newsletter_success)): ?>
                setTimeout(() => {
                    const newsletterInput = document.querySelector('input[name="newsletter_email"]');
                    if (newsletterInput) {
                        newsletterInput.value = '';
                    }
                }, 100);
            <?php endif; ?>
        });
    </script>
</body>
</html>