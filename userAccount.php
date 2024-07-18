<?php session_start(); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "cdn.php" ?>
    <title>Elocker - your secure password manager</title>
  </head>
  <style>
    <?php include "css/style.css" ?>
  </style>
  <body class="background text-light">

  <!-- Navbar -start  -->
 <?php include "navbar.php" ?>
<!-- Nabvar -end  -->
<section class="container-fluid py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="row row-cols-1 row-cols-md-2 g-4">
        <!-- Card -start  -->
        <div class="col">
          <div class="card h-100 border-0 rounded text-light">
            <div class="card-body bg-dark">
              <a href="managepassword.php" class="nav-link">
              <h5 class="card-title">Manage Password <i class="ri-tools-line"></i></h5>
              <p class="card-text">Manage your passwords effortlessly with the built-in password management feature.</p>
              </a>
            </div>
          </div>
        </div>
        <!-- Card -end  -->

        <!-- Card -start  -->
        <div class="col">
          <div class="card h-100 border-0 rounded text-light">
            <div class="card-body bg-dark">
              <a href="storenewpassword.php" class="nav-link">
              <h5 class="card-title">Store New Password <i class="ri-database-2-line"></i></h5>
              <p class="card-text">Safely store your new password in our secure database.</p>
              </a>
            </div>
          </div>
        </div>
        <!-- Card -end  -->

        <!-- Card -start  -->
        <div class="col">
          <div class="card h-100 border-0 rounded text-light">
            <div class="card-body bg-dark">
              <a href="faQ.php" class="nav-link">
              <h5 class="card-title">FAQ <i class="ri-questionnaire-line"></i></h5>
              <p class="card-text">FAQ to clear your doubts.</p>
              </a>
            </div>
          </div>
        </div>
        <!-- Card -end  -->

        <!-- Card -start  -->
        <div class="col">
          <div class="card h-100 border-0 text-light">
            <div class="card-body bg-dark">
              <a href="contact.php" class="nav-link">
              <h5 class="card-title">Customer Support <i class="ri-contacts-line"></i></h5>
              <p class="card-text">If you encounter any issues, please don't hesitate to email us.</p>
              </a>
            </div>
          </div>
        </div>
        <!-- Card -end  -->

        <!-- Repeat the card structure for other cards -->
      </div>
    </div>
  </div>
</section>



  </body>
</html>
