<?php
session_start();
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname="restaurantpos";
$myCon = new mysqli($servername, $username, $password, $dbname, 3307);

if($myCon->connect_error){
    die("Connection Failed: ".$myCon->connect_error);
}
$brandName = "SOVANNARITH STORE";
?>