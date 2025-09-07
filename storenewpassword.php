<?php
session_start();
include "server/db_config.php";

// IMPORTANT: This key MUST be kept secret and should ideally be loaded from a secure environment file (.env).
// It must be a 256-bit (32 characters) long key for the 'aes-256-cbc' cipher.
// Generate a secure key using: echo base64_encode(random_bytes(32));
define('ENCRYPTION_KEY', 'Your-Super-Secret-32-Character-Key-Here'); 
define('CIPHER_ALGO', 'aes-256-cbc');

// Security Check: If the user is not logged in, redirect them to the login page.
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$message = '';
$message_type = '';

if (isset($_POST['submit'])) {
    // User Input
    $email = trim($_POST['email']);
    $link = trim($_POST['link']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        // --- Encrypt the password ---
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHER_ALGO));
        $encrypted_password = openssl_encrypt($password, CIPHER_ALGO, ENCRYPTION_KEY, 0, $iv);
        
        // Combine IV with the encrypted password for storage. Base64 encode for safe storage.
        $stored_password = base64_encode($iv . $encrypted_password);

        // --- Use a prepared statement to prevent SQL injection ---
        $query = "INSERT INTO `storage` (`username`, `email`, `links`, `password`) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $link, $stored_password);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_close($conn);
            header("Location: managepassword.php?status=success"); // Redirect on success
            exit();
        } else {
            $message = "Something went wrong! Could not store the password.";
            $message_type = 'alert-danger';
        }
    } else {
        $message = "The password field cannot be empty.";
        $message_type = 'alert-danger';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "include/cdn.php"; ?>
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Store New Password</title>
</head>
<body class="background text-light">

    <?php include "include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Store a New Password</h2>
                        <p>Fill in the details below to save a new password securely.</p>
                    </div>

                    <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        
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
                            <label for="email" class="form-label"><i class="ri-mail-line"></i> Website Username or Email</label>
                            <input type="text" name="email" id="email" class="form-control" placeholder="e.g., user@example.com" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="link" class="form-label"><i class="ri-links-line"></i> Website Link</label>
                            <input type="text" name="link" id="link" class="form-control" placeholder="Optional: https://example.com">
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="form-label"><i class="ri-key-line"></i> Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter the password to store" required>
                        </div>

                        <button class="btn btn-primary w-100" type="submit" name="submit">
                            Store Password <i class="ri-database-2-line"></i>
                        </button>
                    </form>

                     <p class="back-link">
                        <a href="userAccount.php"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>