<?php
session_start();
include "server/db_config.php";

$message = '';
$message_type = ''; // Will be 'alert-danger' for errors

if (isset($_POST['submit'])) {
    // User Input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $repass = trim($_POST['repass']);

    // --- Form Validation ---
    if (empty($username) || empty($password) || empty($repass)) {
        $message = 'Please fill out all fields.';
        $message_type = 'alert-danger';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
        $message_type = 'alert-danger';
    } elseif ($password !== $repass) {
        $message = 'Passwords do not match.';
        $message_type = 'alert-danger';
    } else {
        // --- Check if username already exists using a prepared statement ---
        $sql_check = "SELECT username FROM `register` WHERE username = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $message = 'This username is already taken. Please choose another.';
            $message_type = 'alert-danger';
        } else {
            // --- Username is available, proceed with insertion ---
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user using a prepared statement
            $sql_insert = "INSERT INTO `register` (username, password) VALUES (?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ss", $username, $password_hash);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['username'] = $username;
                header("Location: succes.php"); // Redirect to a success page
                exit();
            } else {
                $message = 'Something went wrong during registration. Please try again.';
                $message_type = 'alert-danger';
            }
        }
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
    <title>Elocker - Registration</title>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Create an Account</h2>
                <p>Get started with Elocker today!</p>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="login-form">

                <?php
                if (!empty($message)) {
                    echo '<div class="alert ' . $message_type . '" role="alert">' . $message . '</div>';
                }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Choose a Username" aria-label="Username" required>
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
                    Already a member? <a href="index.php">Log In</a>
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

        // 1. Start with one character from each set to guarantee strength
        let password = [
            charset.lower[Math.floor(Math.random() * charset.lower.length)],
            charset.upper[Math.floor(Math.random() * charset.upper.length)],
            charset.numbers[Math.floor(Math.random() * charset.numbers.length)],
            charset.symbols[Math.floor(Math.random() * charset.symbols.length)]
        ];

        // 2. Fill the rest of the password length with random characters from all sets
        const allChars = charset.lower + charset.upper + charset.numbers + charset.symbols;
        for (let i = password.length; i < length; i++) {
            password.push(allChars[Math.floor(Math.random() * allChars.length)]);
        }

        // 3. Shuffle the array to randomize the order and join to form the final password
        const finalPassword = password.sort(() => 0.5 - Math.random()).join('');

        // 4. Set the value for both password fields
        passwordField.value = finalPassword;
        repassField.value = finalPassword;

        // 5. Copy to clipboard
        navigator.clipboard.writeText(finalPassword).then(() => {
            // 6. Provide visual feedback
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