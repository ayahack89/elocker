<?php
session_start();
include "server/db_config.php";

$error_message = ''; // Variable to hold our error message

if (isset($_POST['submit'])) {
    // Check if inputs are not empty
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        // Sanitize User Input
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = trim($_POST['password']); // Trim password before verification

        // Use prepared statements to prevent SQL injection
        $sql = "SELECT username, password FROM `register` WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user_data = mysqli_fetch_assoc($result);
            $db_password_hash = $user_data['password'];

            // Verify the password
            if (password_verify($password, $db_password_hash)) {
                $_SESSION['username'] = $user_data['username'];
                header("Location: userAccount.php");
                exit(); // Always exit after a header redirect
            } else {
                $error_message = 'The password you entered is incorrect.';
            }
        } else {
            $error_message = 'No account found with that username.';
        }
    } else {
        $error_message = 'Please fill in both username and password.';
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "include/cdn.php"; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Login</title>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back!</h2>
                <p>Sign in to continue to Elocker</p>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="login-form">

                <?php
                // Display error message here if it exists
                if (!empty($error_message)) {
                    echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" aria-label="Username" required>
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="ri-key-2-line"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password" required>
                </div>

                <button class="btn btn-primary w-100" type="submit" name="submit">
                    Log In <i class="ri-arrow-right-line"></i>
                </button>

                <p class="register-link">
                    Don't have an account? <a href="register.php">Register now</a>
                </p>
            </form>
            </div>
    </div>

</body>

</html>