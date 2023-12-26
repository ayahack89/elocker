<?php session_start(); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Elocker - your secure password manager</title>
    
  </head>
  <body style="background-color:white; color:black;">
  <div class="container border rounded-bottom">
  <div style="padding:20px; width:1110px; margin:auto;" class="bg-primary rounded-bottom shadow-sm">
  <span style="font-weight:bolder; margin-left:2%; font-size:1.6rem; color: white;" class="border-bottom">Dashboard</span>
  <span style="display:flex;">
    <h2 style="color:whitesmoke; margin-left:2%; font-weight:lighter;">Username: <b><?php echo $_SESSION['username']; ?></b></h2>
  </span>
    <a class="btn btn-lg bg-primary border" href="logout.php" role="button" style=" color:whitesmoke;margin-left:2%;">Logout</a>
    </div>
  <div class=" p-5 rounded-bottom container" style="width:800px; margin:auto; box-shadow: 0 1px 4px 0 rgba(107, 107, 107, 0.2), 0 2px 10px 0 rgba(107, 107, 107, 0.19);">
    
    <h6 style="font-size:1.5rem; color:#292524;">How to use?</h6>
    <ul>
      <li style="list-style-type:none;  font-size: 1.2rem; color:#292524;">Tap to 'Manage passwords' to manage your passwords.</li>
      <li style="list-style-type:none;  font-size: 1.2rem; color:#292524;">Tap to 'Store new password' to store new password. </li>
      <li style="list-style-type:none;  font-size: 1.2rem; color:#292524;">Tap to 'FAQ' to clear your doubts.</li>
      <li style="list-style-type:none;  font-size: 1.2rem; color:#292524;">Tap to 'Contact' to contact with us.</li>
    </ul>
    <a class="btn btn-lg bg-primary" href="managepassword.php" role="button" style=" color:whitesmoke;">Manage passwords »</a>
    <a class="btn btn-lg bg-primary" href="storenewpassword.php" role="button" style=" color:whitesmoke;margin-left:10px;">Store new password »</a>
    <a class="btn btn-lg bg-primary" href="faQ.php" role="button" style=" color:whitesmoke;margin-left:10px;">FAQ »</a>
    <a class="btn btn-lg bg-primary" href="contact.php" role="button" style=" color:whitesmoke;margin-left:10px;">Contact »</a>
  </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
