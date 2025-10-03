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
$message = '';
$message_type = '';
$data = null;

// Define categories (same as in storenewpassword.php)
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
    $encryption = new Encryption();
    
    // --- Get and decrypt the encrypted ID from URL ---
    $encrypted_id = isset($_GET['token']) ? trim($_GET['token']) : '';
    
    if (empty($encrypted_id)) {
        $message = "Invalid request. No token provided.";
        $message_type = 'alert-danger';
    } else {
        // Decrypt the ID
        $get_id = $encryption->decrypt($encrypted_id);
        
        // Validate that we got a valid integer ID
        if (!is_numeric($get_id) || $get_id <= 0) {
            $message = "Invalid token provided.";
            $message_type = 'alert-danger';
        } else {
            $get_id = (int)$get_id;
            
            // --- Fetch existing data securely using user_id (not username) ---
            $sql_fetch = "SELECT * FROM `storage` WHERE id = ? AND user_id = ?";
            $stmt_fetch = mysqli_prepare($conn, $sql_fetch);
            mysqli_stmt_bind_param($stmt_fetch, "ii", $get_id, $user_id);
            mysqli_stmt_execute($stmt_fetch);
            $result = mysqli_stmt_get_result($stmt_fetch);

            if ($result && mysqli_num_rows($result) === 1) {
                $data = mysqli_fetch_assoc($result);
                
                // Debug: Check if data is fetched
                if (!$data) {
                    die("Error: Failed to fetch data for ID: $get_id");
                }

                // Decrypt all fields for display in the form
                $data['email'] = $encryption->decrypt($data['email']);
                $data['links'] = $encryption->decrypt($data['links']);
                $data['decrypted_password'] = $encryption->decrypt($data['password']);
                $data['passkeys'] = $encryption->decrypt($data['passkeys']);
                $data['notes'] = $encryption->decrypt($data['notes']);
                
                // Handle decryption failures
                $data['email'] = $data['email'] ?: '';
                $data['links'] = $data['links'] ?: '';
                $data['decrypted_password'] = $data['decrypted_password'] ?: '';
                $data['passkeys'] = $data['passkeys'] ?: '';
                $data['notes'] = $data['notes'] ?: '';
                
            } else {
                // No record found for this user with this ID
                $message = "Record not found or you don't have permission to edit it.";
                $message_type = 'alert-danger';
            }
        }
    }
} catch (Exception $e) {
    $message = "System error: " . $e->getMessage();
    $message_type = 'alert-danger';
}

// Handle form submission for update
if (isset($_POST['submit']) && $data) {
    $get_user_category = trim($_POST['category']);
    $get_user_email = trim($_POST['email']);
    $get_user_links = trim($_POST['link']);
    $get_user_password = trim($_POST['password']);
    $get_user_passkeys = trim($_POST['passkeys']);
    $get_user_notes = trim($_POST['notes']);

    // Validate category
    if (empty($get_user_category) || !array_key_exists($get_user_category, $categories)) {
        $message = 'Please select a valid category.';
        $message_type = 'alert-danger';
    } elseif (!empty($get_user_password)) {
        try {
            // Encrypt all fields before updating
            $encrypted_email = $encryption->encrypt($get_user_email);
            $encrypted_links = $encryption->encrypt($get_user_links);
            $encrypted_password = $encryption->encrypt($get_user_password);
            $encrypted_passkeys = $encryption->encrypt($get_user_passkeys);
            $encrypted_notes = $encryption->encrypt($get_user_notes);
            
            // Get the original ID from the data
            $original_id = $data['id'];
            
            // Update query using prepared statements (including category)
            $sql_update = "UPDATE `storage` SET 
                          category = ?,
                          email = ?, 
                          links = ?, 
                          password = ?, 
                          passkeys = ?, 
                          notes = ?,
                          lastupdate = CURRENT_TIMESTAMP 
                          WHERE id = ? AND user_id = ?";
            
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ssssssii", 
                $get_user_category,
                $encrypted_email, 
                $encrypted_links, 
                $encrypted_password, 
                $encrypted_passkeys, 
                $encrypted_notes, 
                $original_id, 
                $user_id
            );

            if (mysqli_stmt_execute($stmt_update)) {
                header("Location: managepassword?status=updated");
                exit();
            } else {
                $message = "Database error: " . mysqli_error($conn);
                $message_type = 'alert-danger';
            }
        } catch (Exception $e) {
            $message = "Encryption error: " . $e->getMessage();
            $message_type = 'alert-danger';
        }
    } else {
        $message = "Password field cannot be empty.";
        $message_type = 'alert-danger';
    }
}

// If no data found and we're not processing a form, redirect
if (!$data && !isset($_POST['submit'])) {
    header('Location: managepassword');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - Edit Password</title>
</head>
<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Edit Password</h2>
                        <p>Update the details for this entry below.</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($data): ?>
                    <form class="login-form" method="post" action="">
                        
                        <div class="form-group mb-3">
                            <label for="username" class="form-label"><i class="ri-user-line"></i> Account Owner</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
                            <div class="form-text">This is your account username - it cannot be changed.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="category" class="form-label">
                                <i class="ri-price-tag-3-line"></i> Label *
                                <?php if (isset($data['category'])): ?>
                                    <span class="category-badge-edit">
                                        
                                        <i class="ri-price-tag-3-line"></i> <?php echo isset($categories[$data['category']]) ? htmlspecialchars($categories[$data['category']]) : htmlspecialchars($data['category']); ?>
                                    </span>
                                <?php endif; ?>
                            </label>
                            <select name="category" id="category" class="form-control">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>" 
                                        <?php echo (isset($data['category']) && $data['category'] === $key) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label"><i class="ri-user-smile-line"></i> Username/Email/Account ID/Phone *</label>
                            <input type="text" name="email" id="email" class="form-control" 
                                   placeholder="e.g., user123, user@example.com, +1234567890, account_id" 
                                   value="<?php echo htmlspecialchars($data['email']); ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="link" class="form-label"><i class="ri-links-line"></i> Website Link (Optional)</label>
                            <input type="text" name="link" id="link" class="form-control" 
                                   placeholder="https://example.com" 
                                   value="<?php echo htmlspecialchars($data['links']); ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="form-label"><i class="ri-key-line"></i> Password *</label>
                            <div class="password-cell-form">
                                <input type="password" name="password" id="password" class="form-control" 
                                       placeholder="Enter the password to store" 
                                       value="<?php echo htmlspecialchars($data['decrypted_password']); ?>">
                                <i class="ri-eye-line toggle-password-form" title="Show/Hide Password"></i>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="passkeys" class="form-label"><i class="ri-key-2-line"></i> Passkeys (Optional)</label>
                            <input type="text" name="passkeys" id="passkeys" class="form-control" 
                                   placeholder="Passkeys or recovery codes" 
                                   value="<?php echo htmlspecialchars($data['passkeys']); ?>">
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label"><i class="ri-sticky-note-line"></i> Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" 
                                      placeholder="Any additional notes" rows="3"><?php echo htmlspecialchars($data['notes']); ?></textarea>
                        </div>

                        <button class="btn btn-primary w-100 py-2" type="submit" name="submit">
                            <i class="ri-loop-left-line"></i> Update 
                        </button>
                    </form>
                    <?php endif; ?>

                    <p class="back-link">
                        <a href="managepassword"><i class="ri-arrow-left-line"></i> Back to Manage Passwords</a>
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
                const passwordField = document.getElementById('password');
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