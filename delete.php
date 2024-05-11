<?php 
include "db_config.php";
$delete_id = $_GET['delete'];
$sql = "DELETE FROM `storage` WHERE id = {$delete_id}";
if(mysqli_query($conn, $sql)){
    ?>
    <script>
     window.location.href="managepassword.php";
    </script>
    <?php 
}else{
     echo "Error deleting record: " . mysqli_error($conn);
}


?>