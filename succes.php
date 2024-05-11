<?php session_start(); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "cdn.php" ?>
    <title>Bootstrap demo</title>
    
  </head>
  <style>
    <?php include "css/style.css" ?>
  </style>
  <body class="background text-light">
  <div class="container py-5 px-5 col-md-6" role="alert">
  <h1 class="text-success">Welcome! <?php 
  include "db_config.php";
  $username = $_SESSION['username'];
  $sql = "SELECT * FROM `storage` WHERE username = '$username'";
  $result = mysqli_query($conn, $sql);
  if($result){
    echo $_SESSION['username'];

  }

   ?></h1>
  <p><strong class="text-success">Now you are succesfully register !</strong> <br><br>
 <i>"We take great pride in offering a service that prioritizes trustworthiness and 
exceptional customer care. Security is our main priority when it comes to managing sensitive information like passwords, 
and we understand the significance of providing a reliable solution. Our commitment to trust begins with the 
implementation of robust <b>encryption</b> protocols and <b>advanced security</b> features to safeguard your valuable data. 
We create a platform where users can confidently trust us with their passwords, knowing that their 
information is handled by most careable enviorment."</i></p>
  <hr>
  <p class="mb-0">Now tap to <a href="index.php">LogIn</a> to create your new account : )</p>

</div>
  </body>
</html>