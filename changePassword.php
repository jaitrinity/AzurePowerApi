<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$otp = $jsonData->otp;
$newPassword = $jsonData->newPassword;

$sql = "SELECT * FROM Employees e where e.IsActive=1 and e.Mobile=? and e.OTP=? and e.IsOTPExpired=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$output = array(
		'code' => 200, 
		'message' => 'Valid OTP'
	);
	echo json_encode($output);

	$sql = "UPDATE `Employees` set `Password`=?, `IsOTPExpired`=1 where `Mobile`=? and `OTP`=? and `IsOTPExpired`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssi", $newPassword, $mobile, $otp);
	$stmt->execute();
}
else{
	$output = array('code' => 404, 'message'=>'Invalid OTP');
	echo json_encode($output);
}

?>