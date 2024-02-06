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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Manage your passwords</title>
</head>
<style>

</style>

<body>
    <div class="container border rounded-bottom">
        <div style="display:flex;  padding:20px; width:1100px; margin:auto;"
            class="bg-primary rounded-bottom shadow-sm">
            <a class="btn btn-lg bg-primary border" href="userAccount.php" role="button"
                style=" color:whitesmoke;margin-left:10px;">&larr;</a>
            <a class="btn btn-lg bg-primary" href="logout.php" role="button"
                style=" color:whitesmoke;margin-left:10px;">Logout</a>
        </div>
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
        <div class="container w-100">

            <!-- Search  -->
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
                <input class="form-control mb-4 mt-4 w-50 border-top" name="search" list="datalistOptions"
                    id="exampleDataList" placeholder="Type to search...">
                <input type="submit" name="enter" value="none" style="display:none;">
            </form>


            <?php
            // Search Input
            if (isset($_POST['enter'])) {
                $search = mysqli_real_escape_string($conn, $_POST['search']);
                $search_query = "SELECT * FROM `storage` WHERE username = '$username' AND (email LIKE '%$search%' OR links LIKE '%$search%')";
                $run_sql = mysqli_query($conn, $search_query);

                if ($run_sql) {
                    echo '<table class="table table-light table-striped shadow-sm w-100">
              
                   <tr>
                       <th>Email</th>
                       <th>Sites Link</th>
                       <th>Password</th>
                       <th>Note</th>
                       <th colspan=2>Action</th>
                   </tr>
              
              ';
                    if (mysqli_num_rows($run_sql) > 0) {
                        while ($row = mysqli_fetch_assoc($run_sql)) {

                            echo '<tr>';
                            echo '<td>' . $row['email'] . '</td>';
                            echo '<td>' . $row['links'] . '</td>';
                            echo '<td>' . $row['password'] . '</td>';
                            echo '<td>' . $row['note'] . '</td>';
                            echo '<td><a href="updatedata.php?id=' . $row['id'] . '&email=' . $row['email'] . '&links=' . $row['links'] . '&password=' . $row['password'] . '&note=' . $row['note'] . '" style="text-decoration:none; background-color:green; color:white; padding:5px; padding-right:10px; padding-left:10px; border-radius:4px;">Edit</a></td>';
                            echo '<td><a href="managepassword.php?delete=' . $row['id'] . '" style="text-decoration:none; background-color:red; color:white; padding:5px; border-radius:4px;">Delete</a></td>';

                            echo '</tr>';

                        }
                        echo ' 
                    </table>';
                    } else {
                        echo "<tr><td colspan='4'><strong style='color: gray;'>No data found</strong></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'><strong style='color: red;'>Error in query execution</strong></td></tr>";
                }
            }
            ?>

        </div>

        <!-- Table -->
        <div class="container">
            <table class="table table-light table-striped shadow-sm">
                <th>Email</th>
                <th>Sites Link</th>
                <th>Password</th>
                <th>Note</th>
                <th colspan="2">Action</th>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['links'] . '</td>';
                        echo '<td>' . $row['password'] . '</td>';
                        echo '<td>' . $row['note'] . '</td>';
                        echo '<td><a href="update.php?id=' . $row['id'] . '" style="text-decoration:none; background-color:green; color:white; padding:5px; padding-right:10px; padding-left:10px; border-radius:4px;">Edit</a></td>';
                        echo '<td><a href="managepassword.php?delete=' . $row['id'] . '" style="text-decoration:none; background-color:red; color:white; padding:5px; border-radius:4px;">Delete</a></td>';

                        echo '</tr>';
                    }
                }
                ?>
                <!-- Delete -->
                <?php
                if (isset($_GET['delete'])) {
                    $id = $_GET['delete'];
                    $delete_query = "DELETE FROM `storage` WHERE id = '$id'";
                    $delete = mysqli_query($conn, $delete_query);

                    if ($delete) {
                        header("Location: managepassword.php");
                        exit();
                    } else {
                        echo "Error deleting record: " . mysqli_error($conn);
                    }
                }
                ?>


            </table>

            <?php
            // Pagination
            $run = mysqli_query($conn, "SELECT COUNT(*) as total FROM `storage` WHERE username = '$username' ");
            $row = mysqli_fetch_assoc($run);
            $total_records = $row['total'];

            $pages = ceil($total_records / $limit);

            for ($i = 1; $i <= $pages; $i++) {



                echo '<button type="button" class="btn btn-primary"><a href="managepassword.php?page=' . $i . '" style="text-decoration:none; color:white;">', $i, '</a></button>';




            }
            ?>
        </div>
        <div class="container">
            <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
                <div class="col-md-4 d-flex align-items-center">
                    <a href="/" class="mb-3 me-2 mb-md-0 text-muted text-decoration-none lh-1">
                        <svg class="bi" width="30" height="24">
                            <use xlink:href="#bootstrap"></use>
                        </svg>
                    </a>
                    <span class="mb-3 mb-md-0 text-muted">© 2023 Elocker, Inc</span>
                </div>

                <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
                    <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24">
                                <use xlink:href="#twitter"></use>
                            </svg></a></li>
                    <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24">
                                <use xlink:href="#instagram"></use>
                            </svg></a></li>
                    <li class="ms-3"><a class="text-muted" href="#"><svg class="bi" width="24" height="24">
                                <use xlink:href="#facebook"></use>
                            </svg></a></li>
                </ul>
            </footer>
        </div>
    </div>

</body>

</html>