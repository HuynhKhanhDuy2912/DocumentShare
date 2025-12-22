<?php
session_start();
// kết nối csdl
$hostname     = "localhost";
$username     = "root";
$password     = "";
$databasename = "db_sharedocument";

$conn = mysqli_connect($hostname, $username, $password) or 
	die("Không thể kết nối host !");
mysqli_select_db($conn,$databasename)or 
	die("Không thể kết nối cơ sở dữ liệu !");
mysqli_query($conn,"SET NAMES 'utf8'");

?>