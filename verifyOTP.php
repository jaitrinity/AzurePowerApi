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
$token = $jsonData->token;
$make = $jsonData->make;
$model = $jsonData->model;
$os = $jsonData->os;
$osVer = $jsonData->osVersion;
$appVer = $jsonData->appVersion;
$networkType = $jsonData->networkType;


$sql = "SELECT *, r.Role FROM Employees e join RoleMaster r on e.RoleId = r.RoleId where e.IsActive=1 and e.Mobile=? and e.OTP=? and e.IsOTPExpired=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$empId = $row["EmpId"];
	$responseData = array(
		'empId' => $row["EmpId"], 
		'name' => $row["Name"],
		'email' => $row["EmailId"],
		'roleId' => $row["RoleId"],
		'role' => $row["Role"]
	);

	$output = array(
		'code' => 200, 
		'message' => 'Valid OTP', 
		'employeeInfo' => $responseData
	);
	echo json_encode($output);

	$sql = "UPDATE `Employees` set `IsOTPExpired`=1 where `Mobile`=? and `OTP`=? and `IsOTPExpired`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $mobile, $otp);
	$stmt->execute();

	if($token !=null && $token != ''){
		$deviceSql = "SELECT * FROM `Devices` where `Mobile`=? and `EmpId`='$empId'";
		$stmt = $conn->prepare($deviceSql);
		$stmt->bind_param("s", $mobile);
		$stmt->execute();
		$deviceQuery = $stmt->get_result();
		if(mysqli_num_rows($deviceQuery) != 0){
			$updateDevice = "UPDATE `Devices` SET `Token`=?, `Make`=?, `Model`=?, `OS`=?, `OSVer`=?, `AppVer`=?, `NetworkType`=?, `UpdateDate`= current_timestamp WHERE `Mobile`=? and `EmpId`=?";
			$stmt = $conn->prepare($updateDevice);
			$stmt->bind_param("sssssssss", $token, $make, $model, $os, $osVer, $appVer, $networkType, $mobile, $empId);
			$stmt->execute();
		}
		else{
			$insertDevice = "INSERT INTO `Devices`(`EmpId`, `Mobile`, `Token`, `Make`, `Model`, `OS`, `OSVer`, `AppVer`, `NetworkType`) VALUES (?,?,?,?,?,?,?,?,?)";
			$stmt = $conn->prepare($insertDevice);
			$stmt->bind_param("sssssssss", $empId, $mobile, $token, $make, $model, $os, $osVer, $appVer, $networkType);
			$stmt->execute();
		}
	}	

}
else{
	$output = array('code' => 404, 'message'=>'Invalid OTP');
	echo json_encode($output);
}

?>