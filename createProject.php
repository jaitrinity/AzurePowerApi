<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$insertType = $_REQUEST["insertType"];

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$projectName = $jsonData->projectName;
$capacity = $jsonData->capacity;
$spvName = $jsonData->spvName;
$address = $jsonData->address;

$code=0;
$message="";

$sql = "SELECT * FROM `ProjectMaster` where `ProjectName`=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$projectName);
$stmt->execute();
$query = $stmt->get_result();
$rowCount = mysqli_num_rows($query);
if($rowCount != 0){
	$output = array(
		'code' => 409, 
		'message' => $projectName.' project aleady exist'
	);
	echo json_encode($output);
	return;
}

$sql = "INSERT INTO `ProjectMaster`(`ProjectName`, `Capacity`, `SPV_Name`, `Address`) VALUES (?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siss",$projectName, $capacity, $spvName, $address);
if($stmt->execute()){
	$code = 200;
	$message = "Successfully";
}
else{
	$code = 500;
	$message = "Something wrong";
}

$output = array(
	'code' => $code, 
	'message' => $message
);
echo json_encode($output);

?>