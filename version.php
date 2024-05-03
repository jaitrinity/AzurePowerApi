<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "GET"){
	header("HTTP/1.1 405");
	$output = array('code' => 405, 'message' => 'Invalid method type');
	echo json_encode($output);
	return;
}

$sql = "SELECT `Android`, `IOS` FROM `Version`";
$result = mysqli_query($conn,$sql);
$code = 0;
$version = new StdClass();
try {
	$code = 200;
	$row = mysqli_fetch_assoc($result);
	$android = $row["Android"];
	$andExp = explode(":", $android);
	$ios = $row["IOS"];
	$iosExp = explode(":", $ios);

	$version->android = $andExp[0];
	$version->androidForce = $andExp[1];
	$version->ios = $iosExp[0];
	$version->iosForce = $iosExp[1];
} catch (Exception $e) {
	$code = 500;
}

header("HTTP/1.1 ".$code);
$output = array('code' => $code, 'version' => $version);
echo json_encode($output);
?>