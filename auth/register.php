<?php
session_start();
include "../private/db_config.php";
include "../private/config.php";
include "../private/encryption.php"; 

$message = '';
$message_type = 'alert-danger'; 

if (isset($_POST['submit'])) {
    try {
        // User Input
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $repass = trim($_POST['repass']);

        // Form Validation
        if (empty($username) || empty($password) || empty($repass)) {
            $message = 'Please fill out all fields.';
        } elseif (strlen($password) < 8) {
            $message = 'Password must be at least 8 characters long.';
        } elseif ($password !== $repass) {
            $message = 'Passwords do not match.';
        } else {
            // Initialize encryption
            $encryption = new Encryption();
            
            // Check if username already exists
            $sql_check = "SELECT id, username FROM `register`";
            $stmt_check = mysqli_prepare($conn, $sql_check);
            
            if ($stmt_check === false) {
                throw new Exception('Database prepare failed: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            
            $username_exists = false;
            
            if ($result_check && mysqli_num_rows($result_check) > 0) {
                while ($row = mysqli_fetch_assoc($result_check)) {
                    try {
                        $decrypted_existing_username = $encryption->decrypt($row['username']);
                        if ($decrypted_existing_username === $username) {
                            $username_exists = true;
                            break;
                        }
                    } catch (Exception $e) {
                        // Skip rows that can't be decrypted
                        continue;
                    }
                }
            }
            
            // Free result
            mysqli_free_result($result_check);

            if ($username_exists) {
                $message = 'This username is already taken. Please choose another.';
            } else {
                // Username is available, proceed with registration
                $encrypted_username = $encryption->encrypt($username);
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insert new user
                $sql_insert = "INSERT INTO `register` (username, password) VALUES (?, ?)";
                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                
                if ($stmt_insert === false) {
                    throw new Exception('Prepare failed: ' . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt_insert, "ss", $encrypted_username, $password_hash);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $id = mysqli_insert_id($conn);
                    
                    $_SESSION['username'] = $username;
                    $_SESSION['id'] = $id; 
                    
                    header("Location: succes");
                    exit();
                } else {
                    $message = 'Database error: Could not create account. Please try again.';
                }
            }
        }
    } catch (Exception $e) {
        $message = 'Registration error: ' . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "../include/cdn.php"; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <title>Elocker - Registration</title>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Create an Account</h2>
                <p>Get started with Elocker today!</p>
            </div>

            <form action="" method="post" class="login-form">

                <?php
                if (!empty($message)) {
                    echo '<div class="alert ' . $message_type . '" role="alert">' . htmlspecialchars($message) . '</div>';
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Choose a Username" aria-label="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-lock-password-line"></i></span>
                    <input type="password" name="password" id="password-field" class="form-control" placeholder="Password (min. 8 characters)" aria-label="Password" required>
                </div>
                
                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="ri-lock-password-line"></i></span>
                    <input type="password" name="repass" id="repass-field" class="form-control" placeholder="Confirm Password" aria-label="Confirm Password" required>
                </div>

                <button type="button" class="btn btn-secondary w-100 mb-3" id="generate-password-btn">
                    <i class="ri-magic-line"></i> Generate Strong Password
                </button>

                <button class="btn btn-primary w-100" type="submit" name="submit">
                    Register <i class="ri-arrow-right-line"></i>
                </button>

                <p class="register-link">
                    Already a member? <a href="../index.php">Sign In</a>
                </p>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const generateBtn = document.getElementById('generate-password-btn');
    const passwordField = document.getElementById('password-field');
    const repassField = document.getElementById('repass-field');

    generateBtn.addEventListener('click', () => {
        const length = 16;
        const charset = {
            lower: "abcdefghijklmnopqrstuvwxyz",
            upper: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            numbers: "0123456789",
            symbols: "!@#$%^&*()_+~`|}{[]:;?><,./-="
        };

        let password = [
            charset.lower[Math.floor(Math.random() * charset.lower.length)],
            charset.upper[Math.floor(Math.random() * charset.upper.length)],
            charset.numbers[Math.floor(Math.random() * charset.numbers.length)],
            charset.symbols[Math.floor(Math.random() * charset.symbols.length)]
        ];

        const allChars = charset.lower + charset.upper + charset.numbers + charset.symbols;
        for (let i = password.length; i < length; i++) {
            password.push(allChars[Math.floor(Math.random() * allChars.length)]);
        }

        const finalPassword = password.sort(() => 0.5 - Math.random()).join('');

        passwordField.value = finalPassword;
        repassField.value = finalPassword;

        // Copy to clipboard
        navigator.clipboard.writeText(finalPassword).then(() => {
            const originalText = generateBtn.innerHTML;
            generateBtn.innerHTML = '<i class="ri-check-line"></i> Copied to Clipboard!';
            generateBtn.disabled = true;

            setTimeout(() => {
                generateBtn.innerHTML = originalText;
                generateBtn.disabled = false;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy password: ', err);
        });
    });
});
</script>

</body>
</html>