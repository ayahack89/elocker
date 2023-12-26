<?php 
session_start();
include "db_config.php";
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Store new password</title>
  </head>
  <body>
  <div class="container border rounded-bottom">
  <div style="display:flex;  padding:20px; width:1100px; margin:auto;" class="bg-primary rounded-bottom shadow-sm">
    <a class="btn btn-lg bg-primary border" href="userAccount.php" role="button" style=" color:whitesmoke;margin-left:10px;">&larr;</a>
    <a class="btn btn-lg bg-primary" href="logout.php" role="button" style=" color:whitesmoke;margin-left:10px;">Logout</a>
    </div>
     <!-- Form  -->
     <?php 
     if(isset($_POST['submit'])){
      //User Input
      $username = $_SESSION['username'];
      $email = $_POST['email'];
      $link = $_POST['link'];
      $password = $_POST['password'];
      $note = $_POST['note'];

      if(!empty($_SESSION['username']) && !empty($_POST['password'])){
        //Insert query
        $query = "INSERT INTO `storage` (`username`, `email`, `links`, `password`, `note`) VALUES ('$username', '$email', '$link', '$password', '$note');";
        $run = mysqli_query($conn, $query);
        if($run){
          header("Location: managepassword.php");
          mysqli_close($conn);
        }else{
          echo "<h3 style='color:red;'>Somthing went wrong!</h3>";
          die("Error!".mysqli_error($conn));
        }

      }


     }
     
     ?>
     <form class="container border rounded-bottom p-3 shadow-sm bg-light" action="<?php echo htmlentities ($_SERVER['PHP_SELF']); ?>" method="post" style="width:700px;"> 
        
          <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">User Name:</span>
  </div>
  <input class="form-control" type="text" placeholder="Disabled input" aria-label="Disabled input example" value="<?php echo $_SESSION['username']; ?>" disabled>
</div>

<div class="input-group mb-3">
<div class="input-group-append">
    <span class="input-group-text" id="basic-addon2">Email:</span>
  </div>
  <input type="text" name="email" class="form-control" placeholder="...@email.com" aria-label="Recipient's username" aria-describedby="basic-addon2">
  
</div>


<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon3">Sites Link:</span>
  </div>
  <input type="text" name="link" class="form-control" id="basic-url" aria-describedby="basic-addon3" placeholder="Optional...">
</div>


  <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon3">Password:</span>
  </div>
  <input type="password" name="password" class="form-control" id="basic-url" aria-describedby="basic-addon3" placeholder="Enter password" required>
</div>

<div class="input-group">
  <div class="input-group-prepend">
    <span class="input-group-text p-4">Note:</span>
  </div>
  <textarea class="form-control" aria-label="textarea" name="note"></textarea>
</div>
<div class=""> 
<button class="btn btn-primary p-2 mt-2" type="submit" name="submit" style="font-size:1rem;">Store password</button>
</div>
</form>
<div class="container">
  <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
    <div class="col-md-4 d-flex align-items-center">
      <a href="/" class="mb-3 me-2 mb-md-0 text-muted text-decoration-none lh-1">
        <svg class="bi" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
      </a>
      <span class="mb-3 mb-md-0 text-muted">© 2023 Elocker, Inc</span>
    </div>

    <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
      <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#twitter"></use></svg></a></li>
      <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#instagram"></use></svg></a></li>
      <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#facebook"></use></svg></a></li>
    </ul>
  </footer>
</div>
  </div>
  </body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
  </script>
</html>