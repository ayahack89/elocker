<?php 
$serverIP = "localhost";
$username = "id21697119_elocker";
$password = "elocker@#$%12345DB";
$db_name = "id21697119_elockerdb";

//Db Connection
$conn = mysqli_connect($serverIP, $username, $password, $db_name);
if(!$conn){
     die("Connected failed!".mysqli_error($conn));
}
?>