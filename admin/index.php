<?php
// Direct access prevent 
define('APP_RUN', true);

session_start();
include "../private/db_config.php";
include "../private/config.php";      

$error_message = '';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard');
    exit();
}

if (isset($_POST['submit'])) {
    try {
        // Check if inputs are not empty
        if (empty($_POST['admin_id']) || empty($_POST['password'])) {
            $error_message = 'Please fill in both Admin ID and password.';
        } else {
            $admin_id = trim($_POST['admin_id']);
            $password = trim($_POST['password']);

            // Prepare statement to prevent SQL injection
            $sql = "SELECT id, admin_id, password FROM admin WHERE admin_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt === false) {
                throw new Exception('Database prepare failed: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $admin_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $admin = mysqli_fetch_assoc($result);
                
                // Verify password (plain text comparison as requested)
                if ($password === $admin['password']) {
                    // Login successful - set secure session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_db_id'] = $admin['id'];
                    $_SESSION['login_time'] = time();
                    
                    // Update last logged time
                    $update_sql = "UPDATE admin SET logged = CURRENT_TIMESTAMP WHERE id = ?";
                    $update_stmt = mysqli_prepare($conn, $update_sql);
                    mysqli_stmt_bind_param($update_stmt, "i", $admin['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    
                    header("Location: admin_dashboard");
                    exit();
                } else {
                    $error_message = 'The password you entered is incorrect.';
                }
            } else {
                $error_message = 'No admin account found with that Admin ID.';
            }
            
            mysqli_stmt_close($stmt);
        }
    } catch (Exception $e) {
        $error_message = 'A system error occurred. Please try again later.';
        error_log("Admin login error: " . $e->getMessage());
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
    <title>Wolfallet - Admin SignIn</title>
    <style>
        :root {
            --vercel-black: #000000;
            --vercel-gray: #171717;
            --vercel-light-gray: #f5f5f5;
            --vercel-border: #e5e5e5;
            --vercel-blue: #0070f3;
        }
        
        body {
            background-color: var(--vercel-black);
            color: #fff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
            background: var(--vercel-gray);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 40px 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #fff;
        }
        
        .login-header p {
            color: #a1a1a1;
            margin: 0;
            font-size: 14px;
        }
        
        .login-form {
            width: 100%;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background: #262626;
            border: 1px solid #404040;
            border-right: none;
            color: #a1a1a1;
        }
        
        .form-control {
            background: #262626;
            border: 1px solid #404040;
            border-left: none;
            color: #fff;
            font-size: 14px;
            height: 44px;
        }
        
        .form-control:focus {
            background: #262626;
            border-color: var(--vercel-blue);
            color: #fff;
            box-shadow: none;
        }
        
        .form-control::placeholder {
            color: #666;
        }
        
        .btn-primary {
            background: #fff;
            color: #000;
            border: none;
            border-radius: 8px;
            height: 44px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-primary:hover {
            background: #e5e5e5d5;
            color: #000;
        }
        
        .alert {
            border-radius: 8px;
            border: 1px solid #dc3545;
            background: rgba(220, 53, 69, 0.1);
            color: #f8d7da;
            font-size: 14px;
            padding: 12px 16px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Admin Access</h2>
                <p>Sign in to Wolfallet Admin Dashboard</p>
            </div>

            <form action="" method="post" class="login-form">

                <?php
                if (!empty($error_message)) {
                    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input type="text" name="admin_id" class="form-control" placeholder="Admin ID" aria-label="Admin ID" value="<?php echo isset($_POST['admin_id']) ? htmlspecialchars($_POST['admin_id']) : ''; ?>">
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="ri-key-2-line"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password">
                </div>

                <button class="btn btn-primary w-100 mt-0" type="submit" name="submit">
                    Sign In <i class="ri-arrow-right-line"></i>
                </button>

            </form>
        </div>
    </div>

</body>
</html>