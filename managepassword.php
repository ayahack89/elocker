<?php
session_start();
include "db_config.php";
error_reporting(E_ALL);
ini_set('display_errors', true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "cdn.php" ?>
    <title>Manage your passwords</title>
</head>
<style>
    <?php include "css/style.css" ?>
</style>

<body class="background text-light">

    <!-- Navbar -start  -->
    <?php include "navbar.php" ?>
    <!-- Nabvar -end  -->

    <!-- Pagination -start  -->
    <?php
    //User Varification;
    $username = $_SESSION['username'];
    $limit = 10;

    $page = isset($_GET["page"]) ? $_GET["page"] : 1;
    if ($page == '' || $page == 1) {
        $page1 = 0;
    } else {
        $page1 = ($page * $limit) - $limit;
    }

    $sql = "SELECT * FROM `storage` WHERE username = '$username'  LIMIT $page1, $limit";
    $result = mysqli_query($conn, $sql);
    ?>
    <!-- Pagination -end -->


    <!-- Searchbar -start  -->
    <div class="container">
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" class="d-flex col-md-8 py-5 px-1" role="search"
            method="post">
            <input class="form-control me-2 bg-secondary border-0" style="border-radius: 50px;" type="search"
                name="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-light rounded-circle" type="submit" name="enter"><i
                    class="ri-search-line"></i></button>
        </form>
    </div>
    <!-- Searchbar -end  -->

    <!-- Search php -start  -->
    <?php
    // Search Input
    if (isset($_POST['enter'])) {
        $search = mysqli_real_escape_string($conn, $_POST['search']);
        $search_query = "SELECT * FROM `storage` WHERE username = '$username' AND (email LIKE '%$search%' OR links LIKE '%$search%')";
        $run_sql = mysqli_query($conn, $search_query);

        if ($run_sql) {
            echo '  <div class="container">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark table-striped">
                            <thead class="thead-dark">
                                <tr>
                                  
                                </tr>
                            </thead>
                            <tbody>
              
              ';
            if (mysqli_num_rows($run_sql) > 0) {
                while ($row = mysqli_fetch_assoc($run_sql)) {

                    echo '<tr>';
                    echo '<td><i class="ri-mail-line"></i> ' . $row['email'] . '</td>';
                    echo '<td><i class="ri-link-m"></i> ' . $row['links'] . '</td>';
                    echo '<td><i class="ri-lock-2-line"></i> ' . $row['password'] . '</td>';
                    echo '<td><a href="update.php?id=' . $row['id'] . '" style="text-decoration:none; color:white;"><i class="ri-edit-box-line"></i></a></td>';
                    echo '<td><a href="delete.php?delete=' . $row['id'] . '" style="text-decoration:none; color:white;"><i class="ri-delete-bin-line"></i></a></td>';
                    echo '</tr>';

                }
                echo ' 
                        </tbody>
                        </table>
                    </div>
                </div>';
            } else {
                echo "<tr><td colspan='4'><strong style='color: gray;'>No data found</strong></td></tr>";
            }
        } else {
            echo "<tr><td colspan='4'><strong style='color: red;'>Error in query execution</strong></td></tr>";
        }
    }
    ?>

    </div>
    <!-- Search php -end  -->


    <!-- Table -start -->
    <div class="container">
        <div class="table-responsive">
            <table class="table table-hover table-dark table-striped">
                <thead class="thead-dark">
                    <tr>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td><i class="ri-mail-line"></i> ' . $row['email'] . '</td>';
                            echo '<td><i class="ri-link-m"></i> ' . $row['links'] . '</td>';
                            echo '<td>
                            
                                <input type="password" value=' . $row['password'] . ' class="password-field bg-dark text-light" disabled>
                                <i class="ri-eye-fill toggle-password"></i>
                            </div>
                        </td>
                        ';
                            echo '<td><a href="update.php?id=' . $row['id'] . '" style="text-decoration:none; color:white;"><i class="ri-edit-box-line"></i></a></td>';
                            echo '<td><a href="delete.php?delete=' . $row['id'] . '" style="text-decoration:none; color:white;"><i class="ri-delete-bin-line"></i></a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Table -end  -->

    <!-- Pagination --start -->
    <?php
    // Pagination
    $run = mysqli_query($conn, "SELECT COUNT(*) as total FROM `storage` WHERE username = '$username' ");
    $row = mysqli_fetch_assoc($run);
    $total_records = $row['total'];

    $pages = ceil($total_records / $limit);

    for ($i = 1; $i <= $pages; $i++) {



        echo '<div class="container">
        <button type="button" class="btn btn-secondary border-0 rounded-circle "><a href="managepassword.php?page=' . $i . '" style="text-decoration:none; color:white;">', $i, '</a></button>
        </div>';

    }
    ?>
    </div>
    <!-- Pagination --end -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelectorAll('.toggle-password');
            togglePassword.forEach(function (icon) {
                icon.addEventListener('click', function () {
                    const passwordField = this.parentNode.querySelector('.password-field');
                    this.classList.toggle('ri-eye-fill');
                    this.classList.toggle('ri-eye-off-fill');
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                    } else {
                        passwordField.type = 'password';
                    }
                });
            });
        });
    </script>
</body>

</html>