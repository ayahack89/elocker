<?php 
$serverIP = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "elocker";

//Db Connection
$conn = mysqli_connect($serverIP, $username, $password, $db_name);
if(!$conn){
     die("Connected failed!".mysqli_error($conn));
}
?>