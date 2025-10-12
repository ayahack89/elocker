<?php
session_start();

// Security Check: If the admin is not logged in, redirect them to the login page.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index');
    exit();
}

// Include required files
require_once "../private/config.php";
require_once "../private/encryption.php";
require_once "../private/db_config.php"; // this file MAY define $db_host, $db_user, $db_pass, $db_name, $db_port

// -----------------------
// Database connection (robust, forces TCP, prefers private/db_config.php)
// -----------------------
$port = 3306; // default port

// Prefer variables from private/db_config.php if present
if (isset($db_host) && !empty($db_host)) {
    $host = trim($db_host);
    $user = trim($db_user ?? '');
    $pass = trim($db_pass ?? '');
    $name = trim($db_name ?? '');
    if (isset($db_port) && !empty($db_port)) $port = (int)$db_port;
} else {
    // Fallback to Config::get(...) if you're storing values in config.php
    $host = trim(Config::get('DB_HOST'));
    $user = trim(Config::get('DB_USER'));
    $pass = trim(Config::get('DB_PASS'));
    $name = trim(Config::get('DB_NAME'));
    $cfgPort = Config::get('DB_PORT');
    if (!empty($cfgPort)) $port = (int)$cfgPort;
}

// Defensive validation
if (empty($host) || empty($user) || empty($name)) {
    error_log('Admin dashboard DB config missing values. host:' . var_export($host, true) . ' user:' . var_export($user, true) . ' name:' . var_export($name, true));
    die('Database connection failed: missing database configuration.');
}

// Build DSN forcing host+port (TCP) and UTF8 charset
$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log('PDO connect error (' . $host . ':' . $port . ' / ' . $name . '): ' . $e->getMessage());
    // Show a safe message to the browser
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Initialize encryption
$encryption = new Encryption();

// Handle CSV exports
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'feedback') {
        exportFeedbackCSV($pdo, $encryption);
    } elseif ($_GET['export'] === 'newsletter') {
        exportNewsletterCSV($pdo);
    }
}

// Function to export feedback as CSV
function exportFeedbackCSV($pdo, $encryption) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=feedback_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Display Name', 'Email', 'Feedback', 'Time']);
    
    $stmt = $pdo->query("SELECT * FROM feedback ORDER BY feedback_time DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Decrypt all data before export
        $decrypted_feedback = $encryption->decrypt($row['feedback_oppinion']);
        $decrypted_real_name = !empty($row['real_name']) ? $encryption->decrypt($row['real_name']) : '';
        $decrypted_email = !empty($row['email_id']) ? $encryption->decrypt($row['email_id']) : '';
        
        // Determine display name for CSV
        $display_name = "anonymous";
        if (!empty($decrypted_real_name) && !empty($decrypted_email)) {
            $display_name = $decrypted_real_name . " (" . $decrypted_email . ")";
        } elseif (!empty($decrypted_email)) {
            $display_name = $decrypted_email;
        } elseif (!empty($decrypted_real_name)) {
            $display_name = $decrypted_real_name;
        }
        
        fputcsv($output, [
            $row['id'],
            $display_name,
            $decrypted_email,
            $decrypted_feedback,
            $row['feedback_time']
        ]);
    }
    fclose($output);
    exit();
}

// Function to export newsletter as CSV
function exportNewsletterCSV($pdo) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=newsletter_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Email', 'Subscribed At', 'Status']);
    
    $stmt = $pdo->query("SELECT * FROM newsletter_subscriptions WHERE status = 'active' ORDER BY subscribed_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['subscribed_at'],
            $row['status']
        ]);
    }
    fclose($output);
    exit();
}

// Get total user count
$userCountStmt = $pdo->query("SELECT COUNT(*) as count FROM register");
$userCount = $userCountStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get total feedback count
$feedbackCountStmt = $pdo->query("SELECT COUNT(*) as count FROM feedback");
$feedbackCount = $feedbackCountStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get active newsletter subscriptions count
$newsletterCountStmt = $pdo->query("SELECT COUNT(*) as count FROM newsletter_subscriptions WHERE status = 'active'");
$newsletterCount = $newsletterCountStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get all feedback for display (latest 10)
$feedbackStmt = $pdo->query("SELECT * FROM feedback ORDER BY feedback_time DESC LIMIT 10");
$allFeedback = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

// Get active newsletter subscriptions for display (latest 10)
$newsletterStmt = $pdo->query("SELECT * FROM newsletter_subscriptions WHERE status = 'active' ORDER BY subscribed_at DESC LIMIT 10");
$activeSubscriptions = $newsletterStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user registration data for chart (last 7 days)
$userRegistrationsStmt = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM register 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
    GROUP BY DATE(created_at) 
    ORDER BY date
");
$userRegistrations = $userRegistrationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get the admin ID from the session and sanitize it for safe display.
$admin_id = htmlspecialchars($_SESSION['admin_id'] ?? 'Admin');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <title>Wolfallet - Admin Dashboard</title>
    <style>
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-bottom: 0;
        }
        
        .data-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .feedback-item, .subscription-item {
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
        }
        
        .feedback-meta, .subscription-meta {
            font-size: 0.85rem;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .chart-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: 300px;
        }
        
        .btn-export {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-export:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            opacity: 0.7;
        }
        
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="background text-light">
    <!-- Logout Button -->
    <a href="logout" class="logout-btn">
        <i class="ri-logout-box-r-line"></i> Logout
    </a>

    <main class="container py-5">
        <div class="dashboard-header mb-5">
            <h1>Admin Dashboard</h1>
            <p class="dashboard-header-sub">Welcome, <?php echo $admin_id; ?>! Manage your application data and analytics.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="stats-card">
                    <h2 class="stats-number"><?php echo htmlspecialchars($userCount); ?></h2>
                    <p class="stats-label">Total Users</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h2 class="stats-number"><?php echo htmlspecialchars($feedbackCount); ?></h2>
                    <p class="stats-label">Total Feedbacks</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h2 class="stats-number"><?php echo htmlspecialchars($newsletterCount); ?></h2>
                    <p class="stats-label">Active Newsletter Subscriptions</p>
                </div>
            </div>
        </div>

        <!-- User Registration Chart -->
        <div class="chart-container">
            <h3>User Registrations (Last 7 Days)</h3>
            <canvas id="userRegistrationsChart"></canvas>
        </div>

        <!-- Feedback Section -->
        <div class="data-section">
            <div class="section-header">
                <h3>Recent Feedbacks</h3>
                <a href="?export=feedback" class="btn-export">
                    <i class="ri-download-line"></i> Export as CSV
                </a>
            </div>
            
            <?php if (count($allFeedback) > 0): ?>
                <?php foreach ($allFeedback as $feedback): ?>
                    <?php
                    // Decrypt all data
                    $decrypted_feedback = $encryption->decrypt($feedback['feedback_oppinion']);
                    $decrypted_real_name = !empty($feedback['real_name']) ? $encryption->decrypt($feedback['real_name']) : '';
                    $decrypted_email = !empty($feedback['email_id']) ? $encryption->decrypt($feedback['email_id']) : '';
                    
                    // Determine display name based on the new logic
                    $display_name = "anonymous";
                    if (!empty($decrypted_real_name) && !empty($decrypted_email)) {
                        $display_name = $decrypted_real_name . " (" . $decrypted_email . ")";
                    } elseif (!empty($decrypted_email)) {
                        $display_name = $decrypted_email;
                    } elseif (!empty($decrypted_real_name)) {
                        $display_name = $decrypted_real_name;
                    }
                    ?>
                    <div class="feedback-item">
                        <div class="feedback-meta">
                            From: <strong><?php echo htmlspecialchars($display_name); ?></strong> | 
                            <?php echo date('M j, Y g:i A', strtotime($feedback['feedback_time'])); ?>
                        </div>
                        <div class="feedback-content">
                            <?php echo htmlspecialchars($decrypted_feedback); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="ri-inbox-line" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p>No feedback available.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Newsletter Subscriptions Section -->
        <div class="data-section">
            <div class="section-header">
                <h3>Active Newsletter Subscriptions</h3>
                <a href="?export=newsletter" class="btn-export">
                    <i class="ri-download-line"></i> Export as CSV
                </a>
            </div>
            
            <?php if (count($activeSubscriptions) > 0): ?>
                <?php foreach ($activeSubscriptions as $subscription): ?>
                    <div class="subscription-item">
                        <div class="subscription-meta">
                            Subscribed: <?php echo date('M j, Y g:i A', strtotime($subscription['subscribed_at'])); ?>
                        </div>
                        <div class="subscription-email">
                            <?php echo htmlspecialchars($subscription['email']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="ri-mail-line" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p>No active newsletter subscriptions.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // User Registrations Chart
        const userRegistrationsCtx = document.getElementById('userRegistrationsChart').getContext('2d');
        const userRegistrationsChart = new Chart(userRegistrationsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $dates = [];
                    foreach ($userRegistrations as $registration) {
                        $dates[] = "'" . date('M j', strtotime($registration['date'])) . "'";
                    }
                    echo implode(', ', $dates);
                    ?>
                ],
                datasets: [{
                    label: 'User Registrations',
                    data: [
                        <?php 
                        $counts = [];
                        foreach ($userRegistrations as $registration) {
                            $counts[] = $registration['count'];
                        }
                        echo implode(', ', $counts);
                        ?>
                    ],
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
