<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$updateType = $_REQUEST["updateType"];

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;

if($updateType == "employeeStatus"){
	$id = $jsonData->id;
	$status = $jsonData->status;

	$sql="UPDATE `Employees` set `IsActive`=$status where `Id`=$id";
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "Employee status updated";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}

	$output = array(
		'code' => $code, 
		'message' => $message
	);
	echo json_encode($output);
}
else if($updateType == "projectStatus"){
	$id = $jsonData->id;
	$status = $jsonData->status;

	$sql="UPDATE `ProjectMaster` set `IsActive`=$status where `Id`=$id";
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "Project status updated";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}

	$output = array(
		'code' => $code, 
		'message' => $message
	);
	echo json_encode($output);
}
else if($updateType == "inspectionDate"){
	$irId = $jsonData->irId;
	$newIrDate = $jsonData->newIrDate;

	$sql="UPDATE `InsReqMaster` set `InspectionCloseDate`='$newIrDate' where `IR_Id`='$irId'";
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "IR date update";

		$sql1="UPDATE `Mapping` set `EndDate`='$newIrDate' where `ActivityId`=0 and `IR_Id`='$irId'";
		$stmt1 = $conn->prepare($sql1);
		$stmt1->execute();
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}

	$output = array(
		'code' => $code, 
		'message' => $message
	);
	echo json_encode($output);
}
else{
	$output = array(
		'code' => 404, 
		'message' => 'Invalid URL'
	);
	echo json_encode($output);
}
?>