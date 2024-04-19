<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "GET"){
	header("HTTP/1.1 405");
	$output = array('code' => 405, 'message' => 'Invalid method type');
	echo json_encode($output);
	return;
}

$sql = "SELECT `Android` as `android`, `IOS` as `ios` FROM `Version`";
$result = mysqli_query($conn,$sql);
$code = 0;
$row = new StdClass();
try {
	$code = 200;
	$row = mysqli_fetch_assoc($result);
} catch (Exception $e) {
	$code = 500;
}

header("HTTP/1.1 ".$code);
$output = array('code' => $code, 'version' => $row);
echo json_encode($output);
?>