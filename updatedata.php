<?php 
session_start();
include "db_config.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Edit password</title>
</head>
<body style="background-color:gainsboro;">
  <?php 
$email = isset($_GET['email']) ? $_GET['email'] : '';
$links = isset($_GET['links']) ? $_GET['links'] : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';
$note = isset($_GET['note']) ? $_GET['note'] : '';

if (isset($_GET['submit'])) {
    if (!empty($email) && !empty($links) && !empty($password) && !empty($note)) {
        $query = "UPDATE `storage` SET email = '$email', links = '$links', password = '$password', note = '$note' WHERE password = '$password'";
        $update = mysqli_query($conn, $query);

        if ($update) {
            header("Location: managepassword.php");
            mysqli_close($conn);
            exit;
        } else {
            echo "Update failed: " . mysqli_error($conn);
        }
    } else {
        echo "Something Went wrong! Please try again";
    }
}

?>
  

    <form class="container mt-5 border rounded p-3 shadow-sm bg-light" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get" style="width:500px;"> 
        
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
            <input type="text" name="email" class="form-control" value="<?php echo $_GET['email']; ?>" aria-label="Recipient's username" aria-describedby="basic-addon2">
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">Sites Link:</span>
            </div>
            <input type="text" name="links" class="form-control" id="basic-url" aria-describedby="basic-addon3" value="<?php echo $_GET['links']; ?>"> <!-- Corrected variable name -->
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">Password:</span>
            </div>
            <input type="password" name="password" class="form-control" id="basic-url" aria-describedby="basic-addon3" value="<?php echo $_GET['password']; ?>" required>
        </div>

        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text p-4">Note:</span>
            </div>
            <textarea class="form-control" aria-label="textarea" name="note"><?php echo htmlentities($_GET['note']); ?></textarea>
        </div>

        <div class=""> 
            <button class="btn btn-primary p-2 mt-2" type="submit" name="submit" style="font-size:1rem;">Update Data</button>
        </div>
    </form>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>
