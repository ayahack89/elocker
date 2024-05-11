<?php
session_start();
include "db_config.php";

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
 <?php include "cdn.php" ?>
  <title>Elocker - Login</title>
</head>
<style>
  <?php include "css/style.css" ?>
</style>

<body class="background text-light">

  <!-- Form php -start  -->
  <?php

  if (isset($_POST['submit'])) {
    //User Input
    $username = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['username']));
    $password = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['password']));

    if (!empty($_POST['username']) && !empty($_POST['password'])) {
      //Filter Sql query
  
      $sql = "SELECT * FROM `register` WHERE username = '$username'";
      $result = mysqli_query($conn, $sql);
      $user_account = mysqli_num_rows($result);

      if ($user_account) {
        $user_pass = mysqli_fetch_assoc($result);
        $dbpass = trim($user_pass['password']);
        $_SESSION['username'] = $user_pass['username'];
        $password_decode = password_verify(trim($password), $dbpass);
        if ($password_decode) {
         ?>
         <script>
          window.location.href = "userAccount.php";
         </script>
         <?php 


        } else {
          echo '<div class="alert alert-light rounded-0" role="alert">
          Password Incorrect!
        </div>';
        }


      } else {
        echo '<div class="alert alert-light rounded-0" role="alert">
        Invalid username!
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
          <h1 class="text-center p-3">Log In</h1>
          <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="ri-user-line"></i></span>
            <input type="text" name="username" class="form-control bg-dark text-light border" aria-label="Username"
              aria-describedby="basic-addon1" required>
          </div>
          <div class="mb-3">
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon1"><i class="ri-key-2-line"></i></span>
              <input type="password" name="password" class="form-control bg-dark text-light border" id="basic-url"
                aria-describedby="basic-addon3" required>
            </div>
            <button class="btn btn-light mt-1 w-100" type="submit" name="submit">Process <i
                class="ri-loop-right-line"></i></button>
          </div>
          <p style="text-align:center; margin-top:10px;">If you are a new member! go to <a
              href="register.php">register</a></p>
        </form>
        <!-- Form -end  -->

      </div>
    </div>
  </div>
  <!-- Form box -end  -->
</body>

</html>