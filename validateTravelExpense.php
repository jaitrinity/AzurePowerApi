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
$expenseId = $jsonData->expenseId;
$irId = $jsonData->irId;
$activityId = $jsonData->activityId;
$status = $jsonData->status;
$remark = $jsonData->remark;
$action = 0;

if($status == "Approve"){
	$action = 1;
	$sql = "UPDATE `ExpenseMaster` SET `Action`=$action, `Remark`=?, `ActionDate`=current_timestamp where `Id`=? and `Action`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$expenseId);
}
else if($status == "Reject"){
	$action = 2;
	$sql = "UPDATE `ExpenseMaster` SET `Action`=$action, `Remark`=?, `ActionDate`=current_timestamp where `Id`=? and `Action`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$expenseId);
}
if($stmt->execute()){
	$code = 200;
	$message = "Status updated";

	$expIr = "UPDATE `InsReqMaster` SET `ExpenseStatus` = $action WHERE `IR_Id`='$irId'";
	$stmtExpIr = $conn->prepare($expIr);
	$stmtExpIr->execute();

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

	$irEmpSql = "SELECT `TPI`, `TPI_Auditor` FROM `InsReqMaster` where `IR_Id`='$irId'";
	$irEmpQuery = mysqli_query($conn,$irEmpSql);
	$irEmpRow = mysqli_fetch_assoc($irEmpQuery);
	$tpiEmpId = $irEmpRow["TPI"];
	$tpiAuditorEmpId = $irEmpRow["TPI_Auditor"];
	$tokenEmpList = array();
	array_push($tokenEmpList, $tpiEmpId);
	array_push($tokenEmpList, $tpiAuditorEmpId);

	$tokenEmpImp = implode("','", $tokenEmpList);

	$tokens = "";
	$tokenSql = "SELECT `Token` FROM `Devices` where `EmpId` in ('$tokenEmpImp') and `Active`=1";
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
		$title = "Traval expense";
		$body = "Traval expense is $status";
		$image = "";
		$link = "";
		$classObj = new FirebaseNotificationClass();
		$notiResult = $classObj->sendNotification($tokens, $title, $body, $image, $link);	

		$insNoti = "INSERT INTO `Notification`(`EmpId`, `Subject`, `Body`, `NotiResponse`) VALUES ('$tokenEmpImp','$title','$body','$notiResult')";
		$notiStmt = $conn->prepare($insNoti);
		$notiStmt->execute();
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