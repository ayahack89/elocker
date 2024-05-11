<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "db_config.php"; // Assuming this file contains database connection code

// Check if ID is provided in the URL
$get_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if (isset($_POST['submit'])) {
    $get_user_email = isset($_POST['user_email']) ? mysqli_real_escape_string($conn, $_POST['user_email']) : '';
    $get_user_links = isset($_POST['user_links']) ? mysqli_real_escape_string($conn, $_POST['user_links']) : '';
    $get_user_password = isset($_POST['user_password']) ? mysqli_real_escape_string($conn, $_POST['user_password']) : '';

    // Update query
    $sql_update = "UPDATE `storage` SET email = '{$get_user_email}', links = '{$get_user_links}', password = '{$get_user_password}' WHERE id = '{$get_id}'";

    $run_update = mysqli_query($conn, $sql_update);

    if ($run_update) {
        // Redirect after successful update
        ?>
        <script>
          window.location.href="managepassword.php";
        </script>
        <?php 
        exit();
    } else {
        // Error handling
        echo '<div class="alert alert-danger rounded-0" role="alert">Oops! Something went wrong. Please try again later.</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <?php include "cdn.php" ?>
     <title>Edit password</title>
     <style>
          <?php include "css/style.css" ?>
     </style>
</head>

<body class="background text-light">

     <form class="container border rounded-bottom mt-3 rounded col-md-6 shadow-sm bg-dark border-0 py-5 px-5"
          action="<?php echo htmlentities($_SERVER['PHP_SELF']) . '?id=' . $get_id; ?>" method="post">
          <?php
          // Fetching data filtering by the unique id
          $sql = "SELECT * FROM `storage` WHERE id = '{$get_id}'";
          $result = mysqli_query($conn, $sql);

          if ($result && mysqli_num_rows($result) > 0) {
               $data = mysqli_fetch_assoc($result);
               ?>
               <div class="mb-3">
                    <input class="form-control bg-secondary border-0" type="hidden" name="user_id"
                         placeholder="Disabled input" aria-label="Disabled input example"
                         value="<?php echo $data['id']; ?>" disabled>
               </div>

               <div class="mb-3">
                    <input class="form-control bg-secondary border-0" type="hidden" name="username"
                         placeholder="Disabled input" aria-label="Disabled input example"
                         value="<?php echo $data['username']; ?>" disabled>
               </div>

               <div class="mb-3">
                    <label for="user_email" class="form-label"><i class="ri-mail-line"></i> Email</label>
                    <input type="text" name="user_email" class="form-control bg-secondary border-0"
                         value="<?php echo $data['email']; ?>" aria-describedby="basic-addon2">
               </div>

               <div class="mb-3">
                    <label for="links" class="form-label"><i class="ri-links-line"></i> Sites Link</label>
                    <input type="text" name="user_links" class="form-control bg-secondary border-0" id="basic-url"
                         value="<?php echo $data['links'] ?>" aria-describedby="basic-addon3">
               </div>

               <div class="mb-3">
                    <label for="user_password" class="form-label"><i class="ri-key-line"></i> Password</label>
                    <input type="password" class="form-control bg-secondary border-0" id="basic-url"
                         aria-describedby="basic-addon3" name="user_password"
                         value="<?php echo $data['password']; ?>" required>
               </div>
               <div class="mb-3">
                    <button class="btn btn-light btn-md" type="submit" name="submit">Update Data <i
                              class="ri-database-2-line"></i></button>
               </div>
          <?php
          } else {
               echo '<div class="alert alert-light rounded-0" role="alert">No data found!</div>';
          }
          ?>
     </form>

</body>
</html>
