<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$scarId = $jsonData->scarId;
$activityId = $jsonData->activityId;
$status = $jsonData->status;
$remark = $jsonData->remark;
$rejectReason = $jsonData->rejectReason;
$probDesc = $jsonData->probDesc;
$defectPhoto = $jsonData->defectPhoto;
$immeCorrecDet = $jsonData->immeCorrecDet;
$defineAndVerifyRootCause = $jsonData->defineAndVerifyRootCause;
$defineAndVerifyRootCauseDoc = $jsonData->defineAndVerifyRootCauseDoc;
$correctiveActions = $jsonData->correctiveActions;
$targetDate = $jsonData->targetDate;

// SQT
if($status == "Approve" && $loginEmpRoleId == 3){
	$sql = "UPDATE `ScarMaster` SET `Action`=1, `Remark`=?, `ActionDate`=current_timestamp where `Id`=? and `Action`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$scarId);
}
else if($status == "Reject" && $loginEmpRoleId == 3){
	$sql = "UPDATE `ScarMaster` SET `Action`=2, `Remark`=?, `RejectReason`='$rejectReason', `ActionDate`=current_timestamp where `Id`=? and `Action`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$scarId);
}
// Vendor
else if($status == "Submit" && $loginEmpRoleId == 2){

	$moreUpdate = "";

	require 'Base64ToAnyClass.php';
	$base64 = new Base64ToAnyClass();
	if($defectPhoto !=""){
		$defectPhoto = $base64->base64ToAny($defectPhoto,$scarId.'_DefectPhoto');
		$moreUpdate .= ", `DefectPhoto`='$defectPhoto'";
	}
	if($defineAndVerifyRootCauseDoc != ""){
		$defineAndVerifyRootCauseDoc = $base64->base64ToAny($defineAndVerifyRootCauseDoc,$scarId.'_DefectPhoto');
		$moreUpdate .= ", `DefineAndVerifyRootCauseDoc`='$defineAndVerifyRootCauseDoc'";
	}

	$moreUpdate .= ", `ProbDesc`=?, `ImmeCorrecDet`='$immeCorrecDet', `DefineAndVerifyRootCause`='$defineAndVerifyRootCause', `CorrectiveActions`=?, `TargetDate`='$targetDate'";

	$sql = "UPDATE `ScarMaster` SET `Action`=3, `Remark1`=?, `ActionDate1`=current_timestamp $moreUpdate where `Id`=? and `Action`=1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssi", $remark,$probDesc,$correctiveActions,$scarId);
}
// else if($status == "Reject" && $loginEmpRoleId == 5){
// 	$sql = "UPDATE `ScarMaster` SET `Action`=4, `Remark1`=?, `ActionDate1`=current_timestamp where `Id`=? and `Action`=1";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("si", $remark,$scarId);
// }
// SQT
// else if($status == "Submit" && $loginEmpRoleId == 3){
// 	$sql = "UPDATE `ScarMaster` SET `Action`=4, `Remark2`=?, `ActionDate2`=current_timestamp where `Id`=? and `Action`=3";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("si", $remark,$scarId);
// }
else if($status == "Approve" && $loginEmpRoleId == 3){
	$sql = "UPDATE `ScarMaster` SET `Action`=4, `Remark2`=?, `ActionDate2`=current_timestamp where `Id`=? and `Action`=3";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$scarId);
}
else if($status == "Reject" && $loginEmpRoleId == 3){
	$sql = "UPDATE `ScarMaster` SET `Action`=5, `Remark2`=?, `ActionDate2`=current_timestamp where `Id`=? and `Action`=3";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$scarId);
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