<?php 
// $serverIP = "localhost";
// $username = "id21697119_elocker";
// $password = "elocker@#$%12345DB";
// $db_name = "id21697119_elockerdb";

$serverIP = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "elocker_new";

//Db Connection
$conn = mysqli_connect($serverIP, $username, $password, $db_name);
if(!$conn){
     die("Connected failed!".mysqli_error($conn));
}
?>