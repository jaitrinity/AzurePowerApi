<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
$conn=mysqli_connect("[hostname]","[username]","[password]","[dbname]");
mysqli_set_charset($conn, 'utf8');
?>