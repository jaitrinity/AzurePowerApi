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
$irId = $jsonData->irId;
$status = $jsonData->status;
$remark = $jsonData->remark;

if($status == "Approve"){
	$sql = "UPDATE `InsReqMaster` SET `Status`='IR_4', `Remark`=? where `IR_Id`=? and `Status`='IR_3'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $remark,$irId);
}
else if($status == "Reject"){
	$sql = "UPDATE `InsReqMaster` SET `Status`='IR_102', `Remark`=? where `IR_Id`=? and `Status`='IR_3'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $remark,$irId);
}
if($stmt->execute()){
	$code = 200;
	$message = "Status updated";

	$updateRowCount = mysqli_affected_rows($conn);
	if($updateRowCount == 0){
		$code = 404;
		$message = "No record found";
		$output = array(
			'code' => $code, 
			'message' => $message
		);
		echo json_encode($output);
		return;
	}
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
?>