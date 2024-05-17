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
	$remark = $jsonData->remark;

	$sql="UPDATE `Employees` set `IsActive`=$status, `ActionRemark`='$remark' where `Id`=$id";
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
else if($updateType == "tpiAuditor"){
	$irId = $jsonData->irId;
	$newTpiAuditorId = $jsonData->newTpiAuditorId;

	$sql="UPDATE `InsReqMaster` set `TPI_Auditor`='$newTpiAuditorId' where `IR_Id`='$irId'";
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "IR TPI Auditor update";

		$sql1="UPDATE `Mapping` set `EmpId`='$newTpiAuditorId' where `ActivityId`=0 and `IR_Id`='$irId'";
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
else if($updateType == "portalColumn"){
	$portalId = $jsonData->portalId;
	$columns = $jsonData->columns;

	$sql = "SELECT * FROM `PortalColumn` where `EmpId`='$loginEmpId' and `PortalId`=$portalId";
	$query = mysqli_query($conn,$sql);
	$rowCount = mysqli_num_rows($query);
	if($rowCount == 0){
		$sql = "INSERT INTO `PortalColumn`(`EmpId`, `PortalId`, `MenuColumns`) VALUES ('$loginEmpId',$portalId,'$columns')";
	}
	else{
		$sql="UPDATE `PortalColumn` set `MenuColumns`='$columns' where `EmpId`='$loginEmpId' and `PortalId`=$portalId";
	}
	
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "updated column";
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
else if($updateType == "projectEmpMapping"){
	$id = $jsonData->id;
	$ctEmpId = $jsonData->ctEmpId;
	$sqEmpId = $jsonData->sqEmpId;

	$sql="UPDATE `ProjectMaster` set `CT_EmpId`=$ctEmpId, `SQ_EmpId`='$sqEmpId' where `Id`=$id";
	$stmt = $conn->prepare($sql);

	if($stmt->execute()){
		$code = 200;
		$message = "Project mapping updated";
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