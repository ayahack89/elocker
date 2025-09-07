<?php
session_start();
include "server/db_config.php";

// Define encryption constants (must be the same as in storenewpassword.php)
define('ENCRYPTION_KEY', 'Your-Super-Secret-32-Character-Key-Here');
define('CIPHER_ALGO', 'aes-256-cbc');

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$get_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';
$data = null;

// --- Fetch existing data securely ---
// Crucial: We check for ID AND the session username to ensure users can only edit their own passwords.
$sql_fetch = "SELECT * FROM `storage` WHERE id = ? AND username = ?";
$stmt_fetch = mysqli_prepare($conn, $sql_fetch);
mysqli_stmt_bind_param($stmt_fetch, "is", $get_id, $username);
mysqli_stmt_execute($stmt_fetch);
$result = mysqli_stmt_get_result($stmt_fetch);

if ($result && mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);

    // Decrypt the password for display in the form
    $encrypted_data = base64_decode($data['password']);
    $iv_length = openssl_cipher_iv_length(CIPHER_ALGO);
    $iv = substr($encrypted_data, 0, $iv_length);
    $encrypted_password = substr($encrypted_data, $iv_length);
    $decrypted_password = openssl_decrypt($encrypted_password, CIPHER_ALGO, ENCRYPTION_KEY, 0, $iv);
    $data['decrypted_password'] = $decrypted_password ?: ''; // Set to empty if decryption fails
} else {
    // No record found for this user with this ID, or ID is invalid.
    // Redirect or show an error. It's safer to redirect.
    header('Location: managepassword.php');
    exit();
}

// Handle form submission for update
if (isset($_POST['submit'])) {
    $get_user_email = trim($_POST['user_email']);
    $get_user_links = trim($_POST['user_links']);
    $get_user_password = trim($_POST['user_password']);

    if (!empty($get_user_password)) {
        // --- Re-encrypt the new or updated password ---
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHER_ALGO));
        $encrypted_password = openssl_encrypt($get_user_password, CIPHER_ALGO, ENCRYPTION_KEY, 0, $iv);
        $stored_password = base64_encode($iv . $encrypted_password);

        // --- Update query using prepared statements ---
        $sql_update = "UPDATE `storage` SET email = ?, links = ?, password = ? WHERE id = ? AND username = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssis", $get_user_email, $get_user_links, $stored_password, $get_id, $username);

        if (mysqli_stmt_execute($stmt_update)) {
            header("Location: managepassword.php?status=updated");
            exit();
        } else {
            $message = "Oops! Something went wrong. Please try again later.";
            $message_type = 'alert-danger';
        }
    } else {
        $message = "Password field cannot be empty.";
        $message_type = 'alert-danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "include/cdn.php"; ?>
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Edit Password</title>
</head>
<body class="background text-light">

    <?php include "include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Edit Password</h2>
                        <p>Update the details for this entry below.</p>
                    </div>

                    <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $get_id; ?>">
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert <?php echo $message_type; ?>" role="alert"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <div class="form-group mb-3">
                            <label for="user_email" class="form-label"><i class="ri-mail-line"></i> Website Username or Email</label>
                            <input type="text" name="user_email" id="user_email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_links" class="form-label"><i class="ri-links-line"></i> Website Link</label>
                            <input type="text" name="user_links" id="user_links" class="form-control" value="<?php echo htmlspecialchars($data['links']); ?>">
                        </div>

                        <div class="form-group mb-4">
                            <label for="user_password" class="form-label"><i class="ri-key-line"></i> Password</label>
                            <div class="password-cell-form">
                                <input type="password" name="user_password" id="user_password" class="form-control" value="<?php echo htmlspecialchars($data['decrypted_password']); ?>" required>
                                <i class="ri-eye-line toggle-password-form" title="Show/Hide Password"></i>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100" type="submit" name="submit">
                            Update Password <i class="ri-save-line"></i>
                        </button>
                    </form>

                     <p class="back-link">
                        <a href="managepassword.php"><i class="ri-arrow-left-line"></i> Cancel and Go Back</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleIcon = document.querySelector('.toggle-password-form');
        if (toggleIcon) {
            toggleIcon.addEventListener('click', function () {
                const passwordField = document.getElementById('user_password');
                const isPassword = passwordField.type === 'password';
                passwordField.type = isPassword ? 'text' : 'password';
                this.classList.toggle('ri-eye-line');
                this.classList.toggle('ri-eye-off-line');
            });
        }
    });
    </script>
</body>
</html>