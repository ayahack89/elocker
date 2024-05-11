<?php
session_start();
include "db_config.php";
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php include "cdn.php" ?>
  <title>Elocker - Registration</title>
</head>
<style>
  <?php include "css/style.css" ?>
</style>

<body class="background text-light">

<!-- Form php -start  -->
  <?php
  if (isset($_POST['submit'])) {
    // User Input
    $username = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['username']));
    $password = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['password']));
    $repass = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['repass']));

    // Encryption
    $encpass = password_hash($password, PASSWORD_BCRYPT);
    $rencpass = password_hash($repass, PASSWORD_BCRYPT);

    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['repass'])) {
      $sql_query = "SELECT * FROM `register` WHERE username ='$username'";
      $user_validation = mysqli_query($conn, $sql_query);

      if (mysqli_num_rows($user_validation) == 0) { // Check if username doesn't exist
        if ($password === $repass) {
          // Insert into database
          $sqlquery = "INSERT INTO `register` (`username`, `password`, `repass`) VALUES ('$username', '$encpass', '$rencpass');";
          // Run query
          $run = mysqli_query($conn, $sqlquery);
          if ($run) {
            $_SESSION['username'] = $username; // Assign the session variable
            mysqli_close($conn);
            ?>
            <script>
              window.location.href = "succes.php";
            </script>
            <?php
            exit();
          } else {
            echo '<div class="alert alert-light rounded-0" role="alert">
                                Something went wrong!
                            </div>';
            die("Error" . mysqli_error($conn));
          }
        } else {
          echo '<div class="alert alert-light rounded-0" role="alert">
                            Password not matched!
                        </div>';
        }
      } else {
        echo '<div class="alert alert-light rounded-0" role="alert">
                        User already exists.
                    </div>';
      }
    }
  }
  ?>

  <!-- Form php -end  -->


  <!-- Form box -start  -->
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <!-- Form -start  -->
        <form class=" py-5 px-5 bg-dark my-5 rounded" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>"
          method="post">
          <h1 class="text-center p-3">Register</h1>
          <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="ri-user-line"></i></span>
            <input type="text" name="username" class="form-control bg-dark text-light border" aria-label="Username"
              aria-describedby="basic-addon1" required>
          </div>
          <div class="mb-3">
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon1"><i class="ri-lock-password-line"></i></span>
              <input type="password" name="password" class="form-control bg-dark text-light border" id="basic-url"
                aria-describedby="basic-addon3" required>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon1"><i class="ri-lock-password-line"></i></span>
              <input type="password" name="repass" class="form-control bg-dark text-light border" id="basic-url"
                aria-describedby="basic-addon3" required>
            </div>
            <button class="btn btn-light mt-1 w-100" type="submit" name="submit">Register <i
                class="ri-lock-star-fill"></i></button>
          </div>
          <p style="text-align:center; margin-top:10px; font-size:10px;">Please enter your password very carefully if
            your password is not match then the process will be stop.<br><b>& please notedown your password of a safe
              place because it is the only key to enter. No dublicate and everything is encrypted.</b></p>
          <p style="text-align:center; margin-top:10px;">Already a member! go to <a href="index.php">login</a> </p>
        </form>
        <!-- Form -end  -->

      </div>
    </div>
  </div>
  <!-- Form box -end  -->


</body>

</html>