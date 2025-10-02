<?php
//Direct access prevent 
define('APP_RUN', true);


session_start();
include "private/db_config.php";
include "private/config.php";      
include "private/encryption.php";   

$error_message = '';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard/useraccount');
    exit();
}

if (isset($_POST['submit'])) {
    try {
        // Check if inputs are not empty
        if (empty($_POST['username']) || empty($_POST['password'])) {
            $error_message = 'Please fill in both username and password.';
        } else {
            $username_input = trim($_POST['username']);
            $password_input = trim($_POST['password']);

            // Initialize Encryption
            $encryption = new Encryption();

            // NEW APPROACH: Get all users and check each one
            $sql = "SELECT id, username, password FROM `register`";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt === false) {
                throw new Exception('Database prepare failed: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $user_found = false;
            $correct_user_data = null;

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    try {
                        // Try to decrypt the stored username
                        $decrypted_username = $encryption->decrypt($row['username']);
                        
                        // Check if this is our user
                        if ($decrypted_username === $username_input) {
                            $user_found = true;
                            $correct_user_data = $row;
                            break; // Found our user, stop searching
                        }
                    } catch (Exception $e) {
                        // If decryption fails for this record, just continue to next one
                        continue;
                    }
                }
            }

            if ($user_found && $correct_user_data) {
                // Verify password
                if (password_verify($password_input, $correct_user_data['password'])) {
                    // Login successful
                    $_SESSION['username'] = $username_input;
                    $_SESSION['id'] = $correct_user_data['id'];
                    
                    header("Location: dashboard/useraccount");
                    exit();
                } else {
                    $error_message = 'The password you entered is incorrect.';
                }
            } else {
                $error_message = 'No account found with that username.';
            }
            
            // Free result
            mysqli_free_result($result);
        }
    } catch (Exception $e) {
        $error_message = 'A system error occurred. Please try again later.';
        error_log("Login page error: " . $e->getMessage());
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
    <title>Wolfallet - SignIn</title>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome!</h2>
                <p>Sign in to continue to Wolfallet</p>
            </div>

            <form action="" method="post" class="login-form">

                <?php
                if (!empty($error_message)) {
                    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" aria-label="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="ri-key-2-line"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password">
                </div>

                <button class="btn btn-primary w-100" type="submit" name="submit">
                    Sign In <i class="ri-arrow-right-line"></i>
                </button>

                <p class="register-link">
                    Don't have an account? <a href="auth/register.php">Register now</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>