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
$afterStatus="";

if($status == "Approve"){
	// ---
	$offerQty = $jsonData->offerQty;
	$sampleType = $jsonData->sampleType;
	$sampleSize = 1;

	$samSql = "SELECT ss.SampleSize FROM LotSizeLogic ls join SampleSizeLogic ss on ls.$sampleType = ss.SampleSizeCodeLetter where ls.LotSizeMin <= $offerQty and ls.LotSizeMax >= $offerQty";

	$samQuery = mysqli_query($conn,$samSql);
	$samRowCount=mysqli_num_rows($samQuery);
	if($samRowCount !=0){
		$samRow = mysqli_fetch_assoc($samQuery);
		$sampleSize = $samRow["SampleSize"];
	}
	$afterStatus = "IR_1";
	$sql = "UPDATE `InsReqMaster` SET `Status`='$afterStatus', `TPI`=?, `Remark`=?, `SampleType`=?, `SampleSize`=? where `IR_Id`=? and `Status`='IR_0'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssis",$tpiEmpId,$remark,$sampleType,$sampleSize,$irId);
}
else if($status == "Reject"){
	$afterStatus = "IR_101";
	$sql = "UPDATE `InsReqMaster` SET `Status`='$afterStatus', `Remark`=? where `IR_Id`=? and `Status`='IR_0'";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $remark,$irId);
}
if($stmt->execute()){
	$code = 200;
	$message = "Status updated";

	$auditSql = "INSERT INTO `IR_Audit`(`IR_Id`, `EmpId`, `RoleId`, `AfterStatus`, `Remark`) VALUES (?,?,?,?,?)";
	$auditStmt = $conn->prepare($auditSql);
	$auditStmt->bind_param("ssiss",$irId,$loginEmpId,$loginEmpRoleId,$afterStatus,$remark);
	$auditStmt->execute();

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

	// $updateRowCount = mysqli_affected_rows($conn);
	// if($updateRowCount == 0){
	// 	$code = 404;
	// 	$message = "No record found";
	// 	$output = array(
	// 		'code' => $code, 
	// 		'message' => $message
	// 	);
	// 	echo json_encode($output);
	// 	return;
	// }
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