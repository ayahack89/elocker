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
            // Encrypt the data before inserting using the Encryption class
            $encrypted_real_name = !empty($real_name) ? $encryption->encrypt($real_name) : '';
            $encrypted_email_id = !empty($email_id) ? $encryption->encrypt($email_id) : '';
            $encrypted_feedback = $encryption->encrypt($feedback_opinion);
            
            $sql = "INSERT INTO feedback (username, real_name, email_id, feedback_oppinion, feedback_time) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $encrypted_real_name, $encrypted_email_id, $encrypted_feedback);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $feedback_success = "Thank you for your feedback!";
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
        // No encryption needed for newsletter email
        $sql = "INSERT INTO newsletter_subscriptions (email, subscribed_at, username) 
                VALUES (?, NOW(), ?) 
                ON DUPLICATE KEY UPDATE subscribed_at = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newsletter_email, $_SESSION['username']);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0 || $stmt->errno == 0) {
            $newsletter_success = "Successfully subscribed to newsletter!";
        } else {
            $newsletter_error = "Error subscribing to newsletter.";
        }
        $stmt->close();
    } else {
        $newsletter_error = "Please enter a valid email address.";
    }
}

// Fetch current user's feedback
$user_feedback = [];
try {
    $sql = "SELECT real_name, email_id, feedback_oppinion, feedback_time FROM feedback WHERE username = ? ORDER BY feedback_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        try {
            // Decrypt the data for display using the Encryption class
            $row['real_name'] = !empty($row['real_name']) ? $encryption->decrypt($row['real_name']) : 'Not provided';
            $row['email_id'] = !empty($row['email_id']) ? $encryption->decrypt($row['email_id']) : 'Not provided';
            $row['feedback_oppinion'] = $encryption->decrypt($row['feedback_oppinion']);
            $user_feedback[] = $row;
        } catch (Exception $e) {
            // If decryption fails, show placeholder text
            error_log("Decryption error for feedback: " . $e->getMessage());
            $row['real_name'] = 'Not provided';
            $row['email_id'] = 'Not provided';
            $row['feedback_oppinion'] = '[Unable to decrypt this feedback]';
            $user_feedback[] = $row;
        }
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Database error fetching feedback: " . $e->getMessage());
    $feedback_error = "Error loading your previous feedback.";
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
    <title>Elocker - User Profile</title>
    <style>
        :root {
            --primary-color: #0d6efd;
            --card-bg: #1e1e1e;
            --text-light: #f8f9fa;
            --text-muted: #6c757d;
            --border-color: #343a40;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-header h1 {
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }
        
        .card-header h3 {
            margin: 0;
            color: var(--text-light);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            color: var(--text-light);
            padding: 12px;
            border-radius: 6px;
        }
        
        .form-control:focus {
            background-color: #27272a;
            border-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-outline-secondary {
            border-color: var(--border-color);
            color: var(--text-muted);
            padding: 10px 20px;
            border-radius: 6px;
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--border-color);
            color: var(--text-light);
        }
        
        .alert {
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.2);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #75b798;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ea868f;
        }
        
        .feedback-item {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .feedback-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .feedback-content {
            color: var(--text-light);
            line-height: 1.5;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .back-link {
            margin-top: 2rem;
            text-align: center;
        }
        
        .back-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        /* Accordion Styling */
        .accordion-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            margin-bottom: 10px;
            border-radius: 6px;
        }
        
        .accordion-button {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            font-weight: 500;
            padding: 1rem 1.25rem;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }
        
        .accordion-button:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
            border-color: var(--primary-color);
        }
        
        .accordion-body {
            color: var(--text-light);
            padding: 1rem 1.25rem;
        }
        
        .accordion-body h5, .accordion-body h6 {
            color: var(--text-light);
            margin-top: 1rem;
        }
        
        .accordion-body p {
            color: var(--text-muted);
        }
        
        .faq-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .faq-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .feedback-meta {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="page-header">
            <h1>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Manage your profile, provide feedback, and stay updated with Elocker.</p>
        </div>
        
        <!-- About Section -->
        <div class="card">
            <div class="card-header">
                <h3>About Elocker</h3>
            </div>
            <div class="card-body">
                <p>Elocker is a secure password management solution designed to help you store and manage your credentials safely. With military-grade encryption and an intuitive interface, Elocker ensures your sensitive information remains protected while providing easy access when you need it.</p>
                <p>Our mission is to simplify digital security for everyone, making strong password practices accessible and manageable.</p>
            </div>
        </div>
        
        <!-- Feedback Form -->
        <div class="card">
            <div class="card-header">
                <h3>Share Your Feedback</h3>
            </div>
            <div class="card-body">
                <?php if (isset($feedback_success)): ?>
                    <div class="alert alert-success"><?php echo $feedback_success; ?></div>
                <?php elseif (isset($feedback_error)): ?>
                    <div class="alert alert-danger"><?php echo $feedback_error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="real_name" class="form-label">Your Name (Optional)</label>
                            <input type="text" class="form-control" id="real_name" name="real_name" placeholder="Enter your name">
                        </div>
                        <div class="col-md-6">
                            <label for="email_id" class="form-label">Email Address (Optional)</label>
                            <input type="email" class="form-control" id="email_id" name="email_id" placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feedback_opinion" class="form-label">Your Feedback *</label>
                        <textarea class="form-control" id="feedback_opinion" name="feedback_opinion" rows="4" placeholder="Please share your thoughts, suggestions, or issues..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- User's Previous Feedback -->
        <div class="card">
            <div class="card-header">
                <h3>Your Previous Feedback</h3>
            </div>
            <div class="card-body">
                <?php if (empty($user_feedback)): ?>
                    <div class="empty-state">
                        <i class="ri-feedback-line"></i>
                        <p>You haven't submitted any feedback yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($user_feedback as $feedback): ?>
                        <div class="feedback-item">
                            <div class="feedback-meta">
                                <span><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($feedback['feedback_time'])); ?></span>
                                <span><strong>Contact:</strong> <?php echo htmlspecialchars($feedback['email_id']); ?></span>
                            </div>
                            <div class="feedback-content">
                                <?php echo nl2br(htmlspecialchars($feedback['feedback_oppinion'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Newsletter Subscription -->
        <div class="card">
            <div class="card-header">
                <h3>Stay Updated</h3>
            </div>
            <div class="card-body">
                <?php if (isset($newsletter_success)): ?>
                    <div class="alert alert-success"><?php echo $newsletter_success; ?></div>
                <?php elseif (isset($newsletter_error)): ?>
                    <div class="alert alert-danger"><?php echo $newsletter_error; ?></div>
                <?php endif; ?>
                
                <p>Subscribe to our newsletter to receive updates about new features, security tips, and product announcements.</p>
                <form method="POST" action="" class="d-flex gap-2">
                    <input type="email" class="form-control" name="newsletter_email" placeholder="Enter your email address" required>
                    <button type="submit" name="subscribe_newsletter" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
        
        <!-- Policies Section -->
        <div class="card">
            <div class="card-header">
                <h3>Policies & Information</h3>
                <p class="mb-0 text-muted">Find answers to common questions about Elocker.</p>
            </div>
            <div class="card-body">
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
                                <p>For any other problems, security concerns, or inquiries, please use the feedback form above or navigate to the <a href="contact.php" class="faq-link">Customer Support</a> page from your dashboard. Our team will promptly assist you with any issues you may have.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div class="back-link">
            <a href="useraccount"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>