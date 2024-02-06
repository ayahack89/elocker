<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "db_config.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
     <title>Edit password</title>
</head>

<body style="background-color:gainsboro;">

     <?php
     $get_id = mysqli_real_escape_string($conn, isset($_GET['id']) ? $_GET['id'] : '');
     if (isset($_POST['submit'])) {

          $get_user_id = mysqli_real_escape_string($conn, isset($_POST['user_id']) ? $_POST['user_id'] : 'Error');
          $get_username = mysqli_real_escape_string($conn, isset($_POST['username']) ? $_POST['username'] : 'Error');
          $get_user_email = mysqli_real_escape_string($conn, isset($_POST['user_email']) ? $_POST['user_email'] : 'Error');
          $get_user_links = mysqli_real_escape_string($conn, isset($_POST['user_links']) ? $_POST['user_links'] : 'Error');
          $get_user_password = mysqli_real_escape_string($conn, isset($_POST['user_password']) ? $_POST['user_password'] : 'Error');
          $get_user_note = mysqli_real_escape_string($conn, isset($_POST['user_note']) ? $_POST['user_note'] : 'Error');


          if (!empty($get_username) && !empty($get_user_email) && !empty($get_user_links) && !empty($get_user_password) && !empty($get_user_note)) {
               $sql_query = "UPDATE `storage` SET email = '{$get_user_email}', links = '{$get_user_links}', password = '{$get_user_password}', note = '{$get_user_note}' WHERE id = '{$get_user_id}'";
               $run = mysqli_query($conn, $sql_query);
               if ($run) {
                    header("Location: managepassword.php");
                    exit();

               } else {
                    echo '<h3 style="color:red;">Opps! Somthing went wrong : (</h3>';
                    die("Erro!" . mysqli_error($conn));

               }

          } else {
               echo '<h3 style="color:red;">Please fill all the details carefully!</h3>';
          }

     }

     ?>
     <form class="container mt-5 border rounded p-3 shadow-sm bg-light"
          action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" style="width:500px;">
          <?php
          $sql = "SELECT * FROM `storage` WHERE id = '{$get_id}'";
          $result = mysqli_query($conn, $sql);
          if ($result) {
               if (mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_assoc($result)) {
                         ?>
                         <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                   <span class="input-group-text" id="basic-addon1">User ID:</span>
                              </div>
                              <input class="form-control" type="text" name="user_id" placeholder="Disabled input"
                                   aria-label="Disabled input example" value="<?php echo $data['id']; ?>" disabled>
                         </div>

                         <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                   <span class="input-group-text" id="basic-addon1">User Name:</span>
                              </div>
                              <input class="form-control" type="text" name="username" placeholder="Disabled input"
                                   aria-label="Disabled input example" value="<?php echo $data['username']; ?>" disabled>
                         </div>

                         <div class="input-group mb-3">
                              <div class="input-group-append">
                                   <span class="input-group-text" id="basic-addon2">Email:</span>
                              </div>
                              <input type=" text" name="user_email" class="form-control" value="<?php echo $data['email']; ?>"
                                   aria-label="Recipient's username" aria-describedby="basic-addon2">
                         </div>

                         <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                   <span class="input-group-text" id="basic-addon3">Sites
                                        Link:</span>
                              </div>
                              <input type="text" name="links" class="form-control" name="user_links" id="basic-url"
                                   value="<?php echo $data['links'] ?>" aria-describedby="basic-addon3">
                         </div>

                         <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                   <span class="input-group-text" id="basic-addon3">Password:</span>
                              </div>
                              <input type="password" class="form-control" id="basic-url" aria-describedby="basic-addon3"
                                   name="user_password" value="<?php echo $data['password']; ?>" required>
                         </div>

                         <div class="input-group">
                              <div class="input-group-prepend">
                                   <span class="input-group-text p-4">Note:</span>
                              </div>
                              <textarea class="form-control" aria-label="textarea"
                                   name="user_note"><?php echo $data['note']; ?></textarea>
                         </div>

                         <div class="">
                              <button class="btn btn-primary p-2 mt-2" type="submit" name="submit" style="font-size:1rem;">Update
                                   Data</button>
                         </div>
                         <?php

                    }
               } else {
                    echo "No data found !";

               }

          } else {
               echo '<h3 style="color:red">Opps! Somthing went wrong : (</h3>';
          }


          ?>
     </form>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>