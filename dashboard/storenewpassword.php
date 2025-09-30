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
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
$message = '';
$message_type = '';

// Additional security check - if user_id is not set, something is wrong
if (!$user_id) {
    $message = 'User session error. Please log in again.';
    $message_type = 'alert-danger';
}

if (isset($_POST['submit']) && $user_id) {
    try {
        // Initialize encryption
        $encryption = new Encryption();
        
        // User Input - trim all inputs
        $email = trim($_POST['email']);
        $link = trim($_POST['link']);
        $password = trim($_POST['password']);
        $passkeys = trim($_POST['passkeys']);
        $notes = trim($_POST['notes']);
        
        if (!empty($password)) {
            // Encrypt all sensitive fields
            $encrypted_username = $encryption->encrypt($username);
            $encrypted_email = $encryption->encrypt($email);
            $encrypted_link = $encryption->encrypt($link);
            $encrypted_password = $encryption->encrypt($password);
            $encrypted_passkeys = $encryption->encrypt($passkeys);
            $encrypted_notes = $encryption->encrypt($notes);
            
            // --- Use a prepared statement to prevent SQL injection ---
            $query = "INSERT INTO `storage` (`user_id`, `username`, `email`, `links`, `password`, `passkeys`, `notes`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "issssss", 
                $user_id, 
                $encrypted_username, 
                $encrypted_email, 
                $encrypted_link, 
                $encrypted_password, 
                $encrypted_passkeys, 
                $encrypted_notes
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_close($conn);
                header("Location: managepassword?status=success"); // CHANGED: removed .php
                exit();
            } else {
                $message = "Database error: Could not store the password.";
                $message_type = 'alert-danger';
            }
        } else {
            $message = "The password field cannot be empty.";
            $message_type = 'alert-danger';
        }
    } catch (Exception $e) {
        $message = "Encryption error: " . $e->getMessage();
        $message_type = 'alert-danger';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - Store New Password</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Store a New Password</h2>
                        <p>Fill in the details below to save a new password securely.</p>
                    </div>

                    <form class="login-form" method="post" action="">
                        
                        <?php
                        // Display message here if it exists
                        if (!empty($message)) {
                            echo '<div class="alert ' . $message_type . '" role="alert">' . $message . '</div>';
                        }
                        ?>

                        <div class="form-group mb-3">
                            <label for="username" class="form-label"><i class="ri-user-line"></i> Account</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label"><i class="ri-user-smile-line"></i> Username/Email/Account ID/Phone</label>
                            <input type="text" name="email" id="email" class="form-control" placeholder="e.g., user123, user@example.com, +1234567890, account_id" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="link" class="form-label"><i class="ri-links-line"></i> Website Link</label>
                            <input type="text" name="link" id="link" class="form-control" placeholder="Optional: https://example.com">
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="form-label"><i class="ri-key-line"></i> Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter the password to store" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="passkeys" class="form-label"><i class="ri-key-2-line"></i> Passkeys (Optional)</label>
                            <input type="text" name="passkeys" id="passkeys" class="form-control" placeholder="Optional: Passkeys or recovery codes">
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label"><i class="ri-sticky-note-line"></i> Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" placeholder="Optional: Any additional notes" rows="3"></textarea>
                        </div>

                        <button class="btn btn-primary w-100" type="submit" name="submit" <?php echo !$user_id ? 'disabled' : ''; ?>>
                            Store Password <i class="ri-database-2-line"></i>
                        </button>
                    </form>

                     <p class="back-link">
                        <a href="useraccount"><i class="ri-arrow-left-line"></i> Back to Dashboard</a> <!-- CHANGED: removed .php -->
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>