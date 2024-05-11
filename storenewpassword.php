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
  <title>Store new password</title>
</head>
<style>
  <?php include "css/style.css" ?>
</style>

<body class="background text-light">
  <!-- Navbar -start  -->
  <?php include "navbar.php" ?>

  <!-- Navbar -end  -->

  <!-- Form php -stat -->
  <?php
  if (isset($_POST['submit'])) {
    //User Input
    $username = $_SESSION['username'];
    $email = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email']));
    $link = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['link']));
    $password = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['password']));

    if (!empty($_SESSION['username']) && !empty($_POST['password'])) {
      //Insert query
      $query = "INSERT INTO `storage` (`username`, `email`, `links`, `password`) VALUES ('$username', '$email', '$link', '$password');";
      $run = mysqli_query($conn, $query);
      if ($run) {
        ?>
        <script>
          window.location.href="managepassword.php";
        </script>
        <?php 
        mysqli_close($conn);
      } else {
        echo "<h3 style='color:red;'>Somthing went wrong!</h3>";
        die("Error!" . mysqli_error($conn));
      }

    }


  }

  ?>
  <!-- Form php -end  -->

  <!-- Form -start  -->
  <form class="container border rounded-bottom mt-3 rounded col-md-6 shadow-sm bg-dark border-0 py-5 px-5" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <div class="mb-3">
      <label for="username" class="form-label"><i class="ri-user-line"></i> Username</label>
      <input class="form-control bg-secondary border-0" type="text" placeholder="Disabled input"
        aria-label="Disabled input example" value="<?php echo $_SESSION['username']; ?>" disabled>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label"><i class="ri-mail-line"></i> Email</label>
      <input type="text" name="email" class="form-control bg-secondary border-0" placeholder="...@email.com">
    </div>

    <div class="mb-3">
      <label for="link" class="form-label"><i class="ri-links-line"></i> Sites Link</label>
      <input type="text" name="link" class="form-control bg-secondary border-0" placeholder="Optional...">
    </div>

    <div class="mb-3">
      <label for="password" class="form-label"><i class="ri-key-line"></i> Password</label>
      <input type="password" name="password" class="form-control bg-secondary border-0" placeholder="Enter password"
        required>
    </div>

    <button class="btn btn-light btn-md btn-block" type="submit" name="submit">Store Password <i
        class="ri-database-2-line"></i></button>
  </form>
  <!-- Form -end -->

</body>
</script>

</html>