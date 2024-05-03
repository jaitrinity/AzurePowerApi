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
$tpiEmpId = $jsonData->tpiEmpId;

if($status == "Approve"){
	$sql = "UPDATE `InsReqMaster` SET `TPI`=?, `Status`='IR_1', `Remark`=? where `IR_Id`=? and `Status`='IR_0'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssi",$tpiEmpId, $remark,$irId);
}
else if($status == "Reject"){
	$sql = "UPDATE `InsReqMaster` SET `Status`='IR_101', `Remark`=? where `IR_Id`=? and `Status`='IR_0'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$irId);
}
if($stmt->execute()){
	$code = 200;
	$message = "Status updated";

	$tokens = "";
	$tokenSql = "SELECT `Token` FROM `Devices` where `EmpId`='$tpiEmpId' and `Active`=1";
	$tokenQuery = mysqli_query($conn,$tokenSql);
	while($tokenRow = mysqli_fetch_assoc($tokenQuery)){
		$devToken = $tokenRow["Token"];
		if($tokens == ""){
			$tokens .= $devToken;
		}
		else{
			$tokens .= ",".$devToken;
		}

	}

	if($tokens != ""){
		require_once 'FirebaseNotificationClass.php';
		$title = "New IR assign";
		$body = "IR id ".$irId.' is assign to you, please do the needfull';
		$image = "";
		$link = "";
		$classObj = new FirebaseNotificationClass();
		$notiResult = $classObj->sendNotification($tokens, $title, $body, $image, $link);	

		$insNoti = "INSERT INTO `Notification`(`EmpId`, `Subject`, `Body`, `NotiResponse`) VALUES ('$tpiEmpId','$title','$body','$notiResult')";
		$notiStmt = $conn->prepare($insNoti);
		$notiStmt->execute();
	}

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