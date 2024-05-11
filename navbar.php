<nav class="navbar bg-dark border-0">
  <div class="container-fluid">
    <a class="navbar-brand" href="userAccount.php">
      <strong class="text-light"><i class="ri-door-lock-line"></i> ELocker</strong>
      
    </a>
    <strong class="text-light btn btn-dark px-5"><i class="ri-user-line"></i><a href="userAccount.php" style="text-decoration:none; color:white;"> <?php echo $_SESSION['username']; ?></a></strong>
    <a href="logout.php" style="text-decoration:none; color:white;"><i class="ri-logout-circle-line"></i> Logout</a>

  </div>
</nav>