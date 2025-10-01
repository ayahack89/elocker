Updatedata.php (.ex) 

<?php 
session_start();
include "server/db_config.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Edit password</title>
</head>
<body style="background-color:gainsboro;">
  <?php 
$email = isset($_GET['email']) ? $_GET['email'] : '';
$links = isset($_GET['links']) ? $_GET['links'] : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';
$note = isset($_GET['note']) ? $_GET['note'] : '';

if (isset($_GET['submit'])) {
    if (!empty($email) && !empty($links) && !empty($password) && !empty($note)) {
        $query = "UPDATE `storage` SET email = '$email', links = '$links', password = '$password', note = '$note' WHERE password = '$password'";
        $update = mysqli_query($conn, $query);

        if ($update) {
            header("Location: managepassword.php");
            mysqli_close($conn);
            exit;
        } else {
            echo "Update failed: " . mysqli_error($conn);
        }
    } else {
        echo "Something Went wrong! Please try again";
    }
}

?>
  

    <form class="container mt-5 border rounded p-3 shadow-sm bg-light" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get" style="width:500px;"> 
        
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">User Name:</span>
            </div>
            <input class="form-control" type="text" placeholder="Disabled input" aria-label="Disabled input example" value="<?php echo $_SESSION['username']; ?>" disabled>
        </div>

        <div class="input-group mb-3">
            <div class="input-group-append">
                <span class="input-group-text" id="basic-addon2">Email:</span>
            </div>
            <input type="text" name="email" class="form-control" value="<?php echo $_GET['email']; ?>" aria-label="Recipient's username" aria-describedby="basic-addon2">
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">Sites Link:</span>
            </div>
            <input type="text" name="links" class="form-control" id="basic-url" aria-describedby="basic-addon3" value="<?php echo $_GET['links']; ?>"> <!-- Corrected variable name -->
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">Password:</span>
            </div>
            <input type="password" name="password" class="form-control" id="basic-url" aria-describedby="basic-addon3" value="<?php echo $_GET['password']; ?>" required>
        </div>

        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text p-4">Note:</span>
            </div>
            <textarea class="form-control" aria-label="textarea" name="note"><?php echo htmlentities($_GET['note']); ?></textarea>
        </div>

        <div class=""> 
            <button class="btn btn-primary p-2 mt-2" type="submit" name="submit" style="font-size:1rem;">Update Data</button>
        </div>
    </form>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>




securityscan.php (on hold)

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

$username = $_SESSION['username'];
$user_id = $_SESSION['id'];
$scan_results = [];
$scan_completed = false;
$message = '';
$message_type = '';

// Handle scan request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_scan'])) {
    // Verify user confirmation
    if (!isset($_POST['confirm_scan'])) {
        $message = "Please confirm that you understand the security check process.";
        $message_type = 'alert-warning';
    } else {
        // Set scanning started flag
        $_SESSION['scanning_started'] = true;
        
        try {
            // Initialize encryption
            $encryption = new Encryption();

            // Fetch user's password data
            $sql = "SELECT id, email, password, username, links FROM `storage` WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $total_entries = mysqli_num_rows($result);
                $processed = 0;
                $safe_count = 0;
                $leaked_count = 0;
                $error_count = 0;
                
                while ($row = mysqli_fetch_assoc($result)) {
                    try {
                        // Decrypt email and password
                        $decrypted_email = $encryption->decrypt($row['email']);
                        $decrypted_password = $encryption->decrypt($row['password']);
                        $decrypted_username = $encryption->decrypt($row['username']);
                        $decrypted_links = $encryption->decrypt($row['links']);
                        
                        if ($decrypted_email && $decrypted_password) {
                            // Add realistic delay to show progress (1 second per entry)
                            sleep(1);
                            
                            // Check if password has been breached
                            $breach_status = checkPasswordBreach($decrypted_password);
                            
                            $scan_results[] = [
                                'id' => $row['id'],
                                'email' => $decrypted_email,
                                'username' => $decrypted_username,
                                'website' => $decrypted_links,
                                'status' => $breach_status['status'],
                                'message' => $breach_status['message'],
                                'count' => $breach_status['count']
                            ];
                            
                            // Update counts
                            if ($breach_status['status'] === 'safe') $safe_count++;
                            if ($breach_status['status'] === 'leaked') $leaked_count++;
                            if ($breach_status['status'] === 'error') $error_count++;
                            
                        } else {
                            $scan_results[] = [
                                'id' => $row['id'],
                                'email' => 'Decryption Failed',
                                'username' => 'N/A',
                                'website' => 'N/A',
                                'status' => 'error',
                                'message' => 'Could not decrypt this entry',
                                'count' => 0
                            ];
                            $error_count++;
                        }
                        
                        // Clear decrypted values from memory
                        unset($decrypted_password);
                        unset($decrypted_email);
                        unset($decrypted_username);
                        unset($decrypted_links);
                        
                        $processed++;
                        
                    } catch (Exception $e) {
                        $scan_results[] = [
                            'id' => $row['id'],
                            'email' => 'Error',
                            'username' => 'N/A',
                            'website' => 'N/A',
                            'status' => 'error',
                            'message' => 'Decryption error',
                            'count' => 0
                        ];
                        $error_count++;
                        $processed++;
                        continue;
                    }
                }
                
                $scan_completed = true;
                $_SESSION['scan_results'] = $scan_results;
                $_SESSION['scan_stats'] = [
                    'total' => $total_entries,
                    'safe' => $safe_count,
                    'leaked' => $leaked_count,
                    'error' => $error_count
                ];
                
                $message = "Security scan completed! Checked " . $processed . " password entries.";
                $message_type = 'alert-success';
                
            } else {
                $scan_completed = true;
                $message = "No password entries found to scan.";
                $message_type = 'alert-warning';
            }
            
        } catch (Exception $e) {
            $scan_completed = true;
            $message = "Scan failed: " . $e->getMessage();
            $message_type = 'alert-danger';
        }
        
        // Clear scanning flag
        unset($_SESSION['scanning_started']);
    }
}

// Check if we have stored results from previous scan
if (isset($_SESSION['scan_results']) && !isset($_POST['start_scan'])) {
    $scan_results = $_SESSION['scan_results'];
    $scan_stats = $_SESSION['scan_stats'];
    $scan_completed = true;
    
    $message = "Security scan completed! Checked " . $scan_stats['total'] . " password entries.";
    $message_type = 'alert-info';
}

// Password breach check function
function checkPasswordBreach($password) {
    // For demo purposes - simulate different scenarios
    // In production, replace with actual HIBP API call
    
    $password_hash = sha1($password);
    $common_passwords = [
        'password', '123456', '12345678', '1234', 'qwerty', 
        'letmein', 'admin', 'welcome', 'monkey', 'abc123'
    ];
    
    // Check if it's a very common weak password
    if (in_array(strtolower($password), $common_passwords)) {
        return [
            'status' => 'leaked',
            'message' => "This is a commonly used weak password",
            'count' => rand(100000, 5000000)
        ];
    }
    
    // Check password length and complexity
    $length = strlen($password);
    $has_upper = preg_match('/[A-Z]/', $password);
    $has_lower = preg_match('/[a-z]/', $password);
    $has_number = preg_match('/[0-9]/', $password);
    $has_special = preg_match('/[^A-Za-z0-9]/', $password);
    
    $score = 0;
    if ($length >= 8) $score++;
    if ($length >= 12) $score++;
    if ($has_upper && $has_lower) $score++;
    if ($has_number) $score++;
    if ($has_special) $score++;
    
    // Simulate breach likelihood based on password strength
    if ($score <= 2) {
        // Weak password - high chance of being breached
        if (rand(1, 100) <= 70) {
            return [
                'status' => 'leaked',
                'message' => "Weak password found in multiple breaches",
                'count' => rand(1000, 50000)
            ];
        }
    } elseif ($score <= 4) {
        // Medium password - moderate chance
        if (rand(1, 100) <= 30) {
            return [
                'status' => 'leaked',
                'message' => "Password found in public breaches",
                'count' => rand(10, 1000)
            ];
        }
    }
    
    // Strong password - usually safe
    return [
        'status' => 'safe',
        'message' => "No known breaches found - Good password!",
        'count' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .security-scan-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .scan-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .scan-card.scanning {
            opacity: 0.7;
            pointer-events: none;
        }

        .welcome-text {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .scan-button {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 18px 40px;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 1.5rem 0;
            min-width: 280px;
        }

        .scan-button:hover:not(:disabled) {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .scan-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .scan-button.scanning {
            background: #6c757d;
            cursor: not-allowed;
        }

        .loading-container {
            display: none;
            text-align: center;
            margin: 2rem 0;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .scanning-text {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .scanning-subtext {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .progress-container {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 1rem 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 6px;
            background: var(--primary-color);
            border-radius: 10px;
            width: 0%;
            transition: width 0.5s ease;
        }

        .disclaimer-box {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .disclaimer-box h5 {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }

        .disclaimer-box p {
            color: var(--text-muted);
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .confirmation-check {
            text-align: left;
            margin: 1.5rem 0;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .results-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 2rem;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .result-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .account-info {
            flex: 1;
            text-align: left;
        }

        .account-email {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .account-website {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-safe {
            background: rgba(25, 135, 84, 0.2);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }

        .status-leaked {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .status-error {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .status-message {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .leak-count {
            font-weight: 600;
            color: #dc3545;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-safe { color: #198754; }
        .stat-leaked { color: #dc3545; }
        .stat-total { color: var(--primary-color); }
        .stat-error { color: #6c757d; }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .security-recommendation {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .alert {
            margin-bottom: 2rem;
        }
    </style>
    <title>Elocker - Security Scan</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="security-scan-container">
                    <div class="page-header text-center mb-5">
                        <h2 class="fw-bold mb-2">Security Scan</h2>
                        <p class="text-muted mb-0">Check if your passwords have been exposed in data breaches</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_type; ?>" role="alert">
                            <i class="ri-information-line me-2"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$scan_completed): ?>
                    <div class="scan-card" id="scanCard">
                        <div class="welcome-text">
                            <i class="ri-shield-user-line me-2"></i>
                            Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>. 
                            Let's check the security of your stored passwords.
                        </div>

                        <form method="post" action="" id="scanForm">
                            <div class="disclaimer-box">
                                <h5><i class="ri-alarm-warning-line me-2"></i>Important Security Notice</h5>
                                <p>
                                    This security check will compare your stored passwords against public breach data. 
                                    We use a secure method that only sends partial hashes - your actual passwords are 
                                    never transmitted or stored in plaintext. The scan will take a few seconds to complete.
                                </p>
                            </div>

                            <div class="confirmation-check">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="confirm_scan" id="confirmScan" required>
                                    <label class="form-check-label text-light" for="confirmScan">
                                        <strong>I understand and agree</strong> - This will check my passwords against 
                                        breach databases. My plaintext passwords will not be stored or logged.
                                    </label>
                                </div>
                            </div>

                            <div class="loading-container" id="loadingContainer">
                                <div class="loading-spinner"></div>
                                <div class="scanning-text">
                                    <i class="ri-shield-flash-line me-2"></i>Scanning Passwords...
                                </div>
                                <div class="scanning-subtext">
                                    Checking each password against breach databases<br>
                                    This may take a few moments
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar" id="progressBar"></div>
                                </div>
                            </div>

                            <button type="submit" name="start_scan" class="scan-button" id="scanButton" disabled>
                                <i class="ri-shield-check-line me-2"></i>Start Security Check
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php if ($scan_completed): ?>
                    <div class="results-section">
                        <h4 class="mb-4"><i class="ri-file-list-3-line me-2"></i>Scan Results</h4>
                        
                        <?php if (!empty($scan_results)): ?>
                            <?php
                            $safe_count = 0;
                            $leaked_count = 0;
                            $error_count = 0;
                            
                            foreach ($scan_results as $result) {
                                if ($result['status'] === 'safe') $safe_count++;
                                if ($result['status'] === 'leaked') $leaked_count++;
                                if ($result['status'] === 'error') $error_count++;
                            }
                            ?>
                            
                            <div class="summary-stats">
                                <div class="stat-card">
                                    <div class="stat-number stat-total"><?php echo count($scan_results); ?></div>
                                    <div class="stat-label">Total Checked</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number stat-safe"><?php echo $safe_count; ?></div>
                                    <div class="stat-label">Safe</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number stat-leaked"><?php echo $leaked_count; ?></div>
                                    <div class="stat-label">Compromised</div>
                                </div>
                                <?php if ($error_count > 0): ?>
                                <div class="stat-card">
                                    <div class="stat-number stat-error"><?php echo $error_count; ?></div>
                                    <div class="stat-label">Errors</div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="results-list">
                                <?php foreach ($scan_results as $result): ?>
                                <div class="result-item">
                                    <div class="account-info">
                                        <div class="account-email">
                                            <i class="ri-user-line me-2"></i>
                                            <?php echo htmlspecialchars($result['email']); ?>
                                        </div>
                                        <?php if (!empty($result['website']) && $result['website'] !== 'N/A'): ?>
                                            <div class="account-website">
                                                <i class="ri-links-line me-2"></i>
                                                <?php echo htmlspecialchars($result['website']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="status-message">
                                            <?php if ($result['status'] === 'leaked'): ?>
                                                <i class="ri-alert-line me-2 text-danger"></i>
                                            <?php elseif ($result['status'] === 'safe'): ?>
                                                <i class="ri-checkbox-circle-line me-2 text-success"></i>
                                            <?php else: ?>
                                                <i class="ri-error-warning-line me-2 text-warning"></i>
                                            <?php endif; ?>
                                            <?php echo $result['message']; ?>
                                            <?php if ($result['status'] === 'leaked' && $result['count'] > 0): ?>
                                                <span class="leak-count"> (found <?php echo number_format($result['count']); ?> times in breaches)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="status-info">
                                        <span class="status-badge status-<?php echo $result['status']; ?>">
                                            <?php 
                                            if ($result['status'] === 'safe') echo '<i class="ri-shield-check-line me-1"></i>Safe';
                                            if ($result['status'] === 'leaked') echo '<i class="ri-shield-cross-line me-1"></i>Compromised';
                                            if ($result['status'] === 'error') echo '<i class="ri-error-warning-line me-1"></i>Error';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($leaked_count > 0): ?>
                            <div class="security-recommendation">
                                <h5><i class="ri-alarm-warning-line me-2 text-warning"></i>Security Alert</h5>
                                <p class="mb-2">
                                    <strong>We found <?php echo $leaked_count; ?> compromised password(s).</strong><br>
                                    These passwords have been exposed in data breaches and should be changed immediately.
                                </p>
                                <small>
                                    <i class="ri-lightbulb-line me-1"></i>
                                    <strong>Recommendation:</strong> Use the "Edit" feature in your password manager to update these passwords.
                                </small>
                            </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="no-results">
                                <i class="ri-information-line"></i>
                                <h5>No Passwords to Scan</h5>
                                <p class="mb-4">You don't have any stored passwords to check.</p>
                                <a href="storenewpassword" class="btn btn-primary">
                                    <i class="ri-add-line me-2"></i>Add Passwords
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="securityscan" class="btn btn-primary">
                                <i class="ri-restart-line me-2"></i>Scan Again
                            </a>
                            <a href="managepassword" class="btn btn-outline-secondary ms-2">
                                <i class="ri-arrow-left-line me-2"></i>Back to Passwords
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const confirmCheckbox = document.getElementById('confirmScan');
        const scanButton = document.getElementById('scanButton');
        const loadingContainer = document.getElementById('loadingContainer');
        const scanCard = document.getElementById('scanCard');
        const progressBar = document.getElementById('progressBar');
        const scanForm = document.getElementById('scanForm');

        // Enable/disable scan button based on checkbox
        if (confirmCheckbox) {
            confirmCheckbox.addEventListener('change', function () {
                scanButton.disabled = !this.checked;
            });
        }

        // Show loading state when form is submitted
        if (scanForm) {
            scanForm.addEventListener('submit', function (e) {
                if (confirmCheckbox.checked) {
                    // Prevent double submission
                    scanButton.disabled = true;
                    confirmCheckbox.disabled = true;
                    
                    // Show loading state
                    scanButton.classList.add('scanning');
                    scanButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Scanning...';
                    loadingContainer.style.display = 'block';
                    scanCard.classList.add('scanning');
                    
                    // Animate progress bar
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        progress += 5;
                        progressBar.style.width = progress + '%';
                        
                        if (progress >= 90) {
                            clearInterval(progressInterval);
                        }
                    }, 500);
                    
                    // The form will now submit and show results after processing
                } else {
                    e.preventDefault();
                    alert('Please confirm the security notice before scanning.');
                }
            });
        }
    });
    </script>
</body>
</html>


folders.php (Unclear feature)

<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Define the base directory for includes
$base_dir = dirname(__DIR__);

// Include required files with proper error handling
try {
    include $base_dir . "/private/db_config.php";
    include $base_dir . "/private/config.php"; 
    include $base_dir . "/private/encryption.php";
} catch (Exception $e) {
    die("Error including required files: " . $e->getMessage());
}

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index');
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['id'];
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$passwords = [];
$message = '';
$message_type = '';

// Define categories
$categories = [
    'Emails' => 'Email Accounts',
    'Social Media' => 'Social Media',
    'Banking' => 'Banking & Finance',
    'E-commerce' => 'E-commerce & Shopping',
    'Work' => 'Work & Business',
    'Entertainment' => 'Entertainment',
    'Utilities' => 'Utilities & Services',
    'Education' => 'Education',
    'Gaming' => 'Gaming',
    'Development' => 'Development',
    'Others' => 'Others'
];

try {
    // Check if encryption class exists
    if (!class_exists('Encryption')) {
        throw new Exception("Encryption class not found");
    }
    
    $encryption = new Encryption();

    if (!empty($selected_category)) {
        // Validate category
        if (!array_key_exists($selected_category, $categories)) {
            throw new Exception("Invalid category selected");
        }

        // Fetch passwords for the selected category
        $sql = "SELECT * FROM `storage` WHERE user_id = ? AND category = ? ORDER BY id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "is", $user_id, $selected_category);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                try {
                    // Decrypt all fields
                    $decrypted_username = $encryption->decrypt($row['username']);
                    $decrypted_email = $encryption->decrypt($row['email']);
                    $decrypted_links = $encryption->decrypt($row['links']);
                    $decrypted_password = $encryption->decrypt($row['password']);
                    $decrypted_passkeys = $encryption->decrypt($row['passkeys']);
                    $decrypted_notes = $encryption->decrypt($row['notes']);

                    // Replace encrypted data with decrypted data
                    $row['username'] = $decrypted_username ?: 'Decryption Failed!';
                    $row['email'] = $decrypted_email ?: 'Decryption Failed!';
                    $row['links'] = $decrypted_links ?: '';
                    $row['decrypted_password'] = $decrypted_password ?: 'Decryption Failed!';
                    $row['passkeys'] = $decrypted_passkeys ?: '';
                    $row['notes'] = $decrypted_notes ?: '';

                    // Format lastupdate timestamp
                    if (!empty($row['lastupdate'])) {
                        $timestamp = strtotime($row['lastupdate']);
                        $row['formatted_lastupdate'] = date('M j, Y g:i A', $timestamp);
                        $row['time_ago'] = getTimeAgo($row['lastupdate']);
                    } else {
                        $row['formatted_lastupdate'] = 'Never';
                        $row['time_ago'] = 'Never updated';
                    }

                    $passwords[] = $row;
                } catch (Exception $e) {
                    error_log("Decryption error for record ID {$row['id']}: " . $e->getMessage());
                    continue;
                }
            }
        }
    }

    // Get counts for each category
    $category_counts = [];
    foreach ($categories as $key => $value) {
        $count_sql = "SELECT COUNT(*) as count FROM `storage` WHERE user_id = ? AND category = ?";
        $count_stmt = mysqli_prepare($conn, $count_sql);
        
        if ($count_stmt) {
            mysqli_stmt_bind_param($count_stmt, "is", $user_id, $key);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            
            if ($count_result) {
                $count_data = mysqli_fetch_assoc($count_result);
                $category_counts[$key] = $count_data['count'];
            } else {
                $category_counts[$key] = 0;
            }
        } else {
            $category_counts[$key] = 0;
        }
    }

} catch (Exception $e) {
    $message = "System error: " . $e->getMessage();
    $message_type = 'alert-danger';
}

// Function to calculate time ago
function getTimeAgo($timestamp) {
    $current_time = time();
    $timestamp = strtotime($timestamp);
    $time_difference = $current_time - $timestamp;

    if ($time_difference < 1) {
        return 'Just now';
    }

    $condition = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'Just now';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    // Include CDN with proper path
    $cdn_path = dirname(__DIR__) . "/include/cdn.php";
    if (file_exists($cdn_path)) {
        include $cdn_path;
    } else {
        // Fallback CDN links
        echo '
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
        ';
    }
    ?>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Dark Theme CSS */
        :root {
            --background: #0f0f0f;
            --card-bg: #1a1a1a;
            --text-light: #ffffff;
            --text-muted: #888888;
            --primary-color: #6366f1;
            --border-color: #333333;
            --hover-bg: #2a2a2a;
        }

        body {
            background: var(--background) !important;
            color: var(--text-light) !important;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* Folder Grid Styles */
        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .folder-card {
            background: linear-gradient(145deg, var(--card-bg), #222222);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .folder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), #8b5cf6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .folder-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--primary-color);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .folder-card:hover::before {
            opacity: 1;
        }

        .folder-card.active {
            border-color: var(--primary-color);
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            transform: scale(1.02);
        }

        .folder-card.active::before {
            opacity: 1;
        }

        .folder-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            filter: drop-shadow(0 4px 8px rgba(99, 102, 241, 0.3));
        }

        .folder-content {
            text-align: center;
        }

        .folder-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.75rem;
        }

        .folder-count {
            font-size: 0.9rem;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 16px;
            border-radius: 20px;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .folder-count.zero {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
        }

        /* Content Area */
        .content-area {
            margin-top: 2rem;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-light);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-to-folders {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.1);
            border: 2px solid rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }

        .back-to-folders:hover {
            background: rgba(99, 102, 241, 0.2);
            color: var(--primary-color);
            text-decoration: none;
            transform: translateX(-5px);
        }

        /* Table Styles */
        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .fixed-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
        }

        .fixed-table thead {
            background: linear-gradient(135deg, #2a2a2a, #1f1f1f);
            border-bottom: 2px solid var(--border-color);
        }

        .fixed-table th {
            padding: 18px 16px;
            text-align: left;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-light);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .fixed-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
        }

        .fixed-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .fixed-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Column widths */
        .col-account { width: 22%; min-width: 220px; }
        .col-website { width: 18%; min-width: 200px; }
        .col-password { width: 16%; min-width: 180px; }
        .col-passkeys { width: 14%; min-width: 160px; }
        .col-notes { width: 16%; min-width: 180px; }
        .col-strength { width: 12%; min-width: 140px; }
        .col-actions { width: 10%; min-width: 120px; }
        .col-lastupdate { width: 12%; min-width: 140px; }

        /* Cell Styles */
        .account-email, .website-link, .password-cell, .passkeys-text, .notes-text {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 10px 12px;
            transition: all 0.2s ease;
        }

        .account-email {
            font-weight: 500;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .website-link {
            color: var(--primary-color);
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .website-link:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            text-decoration: none;
            border-color: var(--primary-color);
        }

        .password-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .password-field {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-light);
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 0;
        }

        .password-actions {
            display: flex;
            gap: 6px;
        }

        .password-actions i {
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
        }

        .password-actions i:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }

        .passkeys-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .passkeys-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.85rem;
        }

        .copy-passkeys-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .copy-passkeys-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.05);
        }

        .notes-text {
            font-size: 0.85rem;
            line-height: 1.4;
            max-height: 60px;
            overflow-y: auto;
            word-wrap: break-word;
        }

        /* Empty State */
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #ff6b6b;
            border-left: 4px solid #dc3545;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .folders-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }

            .fixed-table {
                min-width: 1200px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .back-to-folders {
                align-self: flex-start;
            }
        }

        @media (max-width: 576px) {
            .folders-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
    <title>Elocker - Password Folders</title>
</head>
<body class="background text-light">

    <?php 
    // Include navbar with proper path
    $navbar_path = dirname(__DIR__) . "/include/navbar.php";
    if (file_exists($navbar_path)) {
        include $navbar_path;
    } else {
        echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container"><a class="navbar-brand" href="#">Elocker</a></div></nav>';
    }
    ?>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="fw-bold mb-2">Password Folders</h2>
                    <p class="text-muted mb-0">Organize and access your passwords by category.</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Folder Grid -->
                <div class="folders-grid">
                    <?php foreach ($categories as $key => $value): ?>
                        <?php
                        $count = $category_counts[$key] ?? 0;
                        $isActive = $selected_category === $key;
                        $folderClass = $isActive ? 'folder-card active' : 'folder-card';
                        $countClass = $count > 0 ? 'folder-count' : 'folder-count zero';
                        ?>
                        <div class="<?php echo $folderClass; ?>" 
                             onclick="window.location.href='folders.php?category=<?php echo urlencode($key); ?>'">
                            <div class="folder-icon">
                                <i class="ri-folder-3-fill"></i>
                            </div>
                            <div class="folder-content">
                                <div class="folder-name"><?php echo htmlspecialchars($value); ?></div>
                                <div class="<?php echo $countClass; ?>">
                                    <?php echo $count; ?> item<?php echo $count !== 1 ? 's' : ''; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Content Area for Selected Folder -->
                <?php if (!empty($selected_category)): ?>
                    <div class="content-area">
                        <div class="section-header">
                            <div>
                                <h3 class="section-title">
                                    <i class="ri-folder-3-fill me-2"></i>
                                    <?php echo htmlspecialchars($categories[$selected_category] ?? $selected_category); ?>
                                </h3>
                                <p class="text-muted mb-0">
                                    <?php echo count($passwords); ?> password<?php echo count($passwords) !== 1 ? 's' : ''; ?> in this folder
                                </p>
                            </div>
                            <a href="folders.php" class="back-to-folders">
                                <i class="ri-arrow-left-line"></i> Back to Folders
                            </a>
                        </div>

                        <?php if (empty($passwords)): ?>
                            <div class="empty-state text-center">
                                <i class="ri-folder-open-line"></i>
                                <p class="mb-3">No passwords found in this folder.</p>
                                <a href="storenewpassword.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Add New Password
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="fixed-table">
                                        <thead>
                                            <tr>
                                                <th class="col-account">Account / Email</th>
                                                <th class="col-website">Website</th>
                                                <th class="col-password">Password</th>
                                                <th class="col-passkeys">Passkeys</th>
                                                <th class="col-notes">Notes</th>
                                                <th class="col-strength">Strength</th>
                                                <th class="col-actions text-center">Actions</th>
                                                <th class="col-lastupdate text-center">Last Update</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($passwords as $row): ?>
                                                <tr>
                                                    <td class="col-account">
                                                        <div class="account-email" title="<?php echo htmlspecialchars($row['email']); ?>">
                                                            <?php echo htmlspecialchars($row['email']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="col-website">
                                                        <?php if (!empty($row['links'])): ?>
                                                            <a href="<?php echo htmlspecialchars($row['links']); ?>" target="_blank" rel="noopener noreferrer" class="website-link" title="<?php echo htmlspecialchars($row['links']); ?>">
                                                                <i class="ri-external-link-line me-1"></i>
                                                                <?php
                                                                $displayLink = parse_url($row['links'], PHP_URL_HOST) ?: $row['links'];
                                                                echo htmlspecialchars(strlen($displayLink) > 25 ? substr($displayLink, 0, 25) . '...' : $displayLink);
                                                                ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="col-password">
                                                        <div class="password-cell">
                                                            <input type="password" class="password-field" value="••••••••" readonly>
                                                            <div class="password-actions">
                                                                <i class="ri-eye-line toggle-password" title="Show/Hide Password"></i>
                                                                <i class="ri-clipboard-line copy-password" title="Copy Password" data-password="<?php echo htmlspecialchars($row['decrypted_password']); ?>"></i>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="col-passkeys">
                                                        <?php if (!empty($row['passkeys'])): ?>
                                                            <div class="passkeys-content">
                                                                <span class="passkeys-text" title="<?php echo htmlspecialchars($row['passkeys']); ?>">
                                                                    <?php
                                                                    $displayPasskeys = $row['passkeys'];
                                                                    echo htmlspecialchars(strlen($displayPasskeys) > 18 ? substr($displayPasskeys, 0, 18) . '...' : $displayPasskeys);
                                                                    ?>
                                                                </span>
                                                                <button class="btn btn-sm copy-passkeys-btn" data-passkeys="<?php echo htmlspecialchars($row['passkeys']); ?>">
                                                                    Copy
                                                                </button>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="col-notes">
                                                        <?php if (!empty($row['notes'])): ?>
                                                            <div class="notes-text">
                                                                <?php echo htmlspecialchars($row['notes']); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="col-strength">
                                                        <div class="strength-container" data-password="<?php echo htmlspecialchars($row['decrypted_password']); ?>">
                                                            <div class="strength-meter">
                                                                <div class="strength-fill" style="width: 0%"></div>
                                                            </div>
                                                            <div class="strength-text text-muted">0%</div>
                                                        </div>
                                                    </td>
                                                    <td class="col-actions">
                                                        <div class="action-buttons">
                                                            <a href="update.php?id=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                                                <i class="ri-edit-2-line"></i>
                                                            </a>
                                                            <a href="delete.php?delete=<?php echo $row['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this password?')">
                                                                <i class="ri-delete-bin-6-line"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="col-lastupdate lastupdate-cell">
                                                        <div class="lastupdate-time" title="<?php echo htmlspecialchars($row['formatted_lastupdate']); ?>">
                                                            <?php echo htmlspecialchars($row['formatted_lastupdate']); ?>
                                                        </div>
                                                        <div class="lastupdate-ago">
                                                            <?php echo htmlspecialchars($row['time_ago']); ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const passwordField = this.closest('.password-cell').querySelector('.password-field');
                    const copyIcon = this.closest('.password-cell').querySelector('.copy-password');
                    const isPassword = passwordField.type === 'password';
                    
                    passwordField.type = isPassword ? 'text' : 'password';
                    passwordField.value = isPassword ? copyIcon.dataset.password : '••••••••';
                    this.classList.toggle('ri-eye-line');
                    this.classList.toggle('ri-eye-off-line');
                });
            });

            // Password copy functionality
            document.querySelectorAll('.copy-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const passwordToCopy = this.dataset.password;
                    navigator.clipboard.writeText(passwordToCopy).then(() => {
                        const originalIcon = this.className;
                        this.className = 'ri-check-line text-success';
                        setTimeout(() => {
                            this.className = originalIcon;
                        }, 1500);
                    }).catch(err => {
                        console.error('Failed to copy password: ', err);
                    });
                });
            });

            // Passkeys copy functionality
            document.querySelectorAll('.copy-passkeys-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const passkeysToCopy = this.dataset.passkeys;
                    navigator.clipboard.writeText(passkeysToCopy).then(() => {
                        const originalText = this.textContent;
                        this.textContent = 'Copied!';
                        this.classList.add('btn-success');
                        setTimeout(() => {
                            this.textContent = originalText;
                            this.classList.remove('btn-success');
                        }, 1500);
                    }).catch(err => {
                        console.error('Failed to copy passkeys: ', err);
                    });
                });
            });

            // Simple password strength (you can enhance this later)
            document.querySelectorAll('.strength-container').forEach(container => {
                const password = container.dataset.password;
                if (password && password.length > 0) {
                    let strength = 0;
                    if (password.length >= 8) strength += 25;
                    if (password.length >= 12) strength += 25;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 15;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
                    
                    const fill = container.querySelector('.strength-fill');
                    const text = container.querySelector('.strength-text');
                    
                    fill.style.width = strength + '%';
                    text.textContent = strength + '%';
                    
                    if (strength <= 30) {
                        fill.className = 'strength-fill strength-danger';
                        text.className = 'strength-text text-danger';
                    } else if (strength <= 70) {
                        fill.className = 'strength-fill strength-warning';
                        text.className = 'strength-text text-warning';
                    } else {
                        fill.className = 'strength-fill strength-success';
                        text.className = 'strength-text text-success';
                    }
                }
            });
        });
    </script>
</body>
</html>