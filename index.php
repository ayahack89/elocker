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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Elocker - Login</title>
  </head>
  <body>
    <div class="shadow-sm rounder-bottom border p-2" style="width:800px; margin:auto;">
     <div  class="bg-primary rounded p-2  shadow-sm" style=" width:780px; margin:auto; ">
     <h1 style="color:white; font-family:Space mono; text-align:center; font-weight:bolder;">eLocker</h1>
     <p style="text-align:center; color:white; font-size:12px">Your secure password manager : )</p>
     </div>
     <!-- Form  -->
     <?php 
     
     if(isset($_POST['submit'])){
          //User Input
          $username = $_POST['username'];
          $password = $_POST['password'];

          if(!empty($_POST['username']) && !empty($_POST['password'])){
               //Filter Sql query
               
               $sql = "SELECT * FROM `register` WHERE username = '$username'";
               $result = mysqli_query($conn, $sql);
               $user_account = mysqli_num_rows($result);

                  if($user_account){
                    $user_pass = mysqli_fetch_assoc($result);
                    $dbpass = trim($user_pass['password']);
                    $_SESSION['username'] = $user_pass['username'];
                    $password_decode = password_verify(trim($password), $dbpass);
                    if($password_decode){
                         header("Location: userAccount.php");
                         

                    }else{
                         echo "Password Incorrect";
                    }
                   
                    
          }else{
               echo "Invalid username!";

          }
     }
}
     
     ?>
     <form class="container mt-5 border rounded p-5 shadow-sm bg-light" action="<?php echo htmlentities($_SERVER['PHP_SELF'])  ?>" method="post" style="width:450px; background-color:white;"> 
        <h3 style="text-align:center; margin-bottom:10px;">Login</h3>
          <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">User Name:</span>
  </div>
  <input type="text" name="username" class="form-control" placeholder="Enter a your username" aria-label="Username" aria-describedby="basic-addon1" required>
</div>



  <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon3">Password:</span>
  </div>
  <input type="password" name="password" class="form-control" id="basic-url" aria-describedby="basic-addon3" placeholder="Enter your password" required>
</div>


<div class="d-grid"> 
<button class="btn btn-primary mt-2" type="submit" name="submit">LogIn</button>
</div>
</form>
<p style="text-align:center; margin-top:10px;">If you are a new member! go to <a href="register.php">register</a></p>
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
