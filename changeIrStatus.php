<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}

$json = file_get_contents('php://input');
// file_put_contents('/var/www/trinityapplab.in/html/AzurePower/api/log/changeIrStatus_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$irId = $jsonData->irId;
$beforeStatus = $jsonData->beforeStatus;
$tpiEmpId = $jsonData->tpiEmpId;
// $offerQty = $jsonData->offerQty;
// $sampleType = $jsonData->sampleType;
$tpiAuditorEmpId = $jsonData->tpiAuditorEmpId;
$status = $jsonData->status;
$observation = $jsonData->observation;
$materialDispatchStatus = $jsonData->materialDispatchStatus;
$declaration = $jsonData->declaration;
$rework = $jsonData->rework;
$reworkDoc = $jsonData->reworkDoc;
$probDesc = $jsonData->probDesc;
$defectPhoto = $jsonData->defectPhoto;
$immeCorrecDet = $jsonData->immeCorrecDet;
$defineAndVerifyRootCause = $jsonData->defineAndVerifyRootCause;
$defineAndVerifyRootCauseDoc = $jsonData->defineAndVerifyRootCauseDoc;
$correctiveActions = $jsonData->correctiveActions;
$targetDate = $jsonData->targetDate;
$remark = $jsonData->remark;
$mdccNo = $irId.'_'.rand(1000,9999);
$auditRemark="";
$moreUpdate="";
if($beforeStatus == "IR_0"){
	// $sampleSize = 1;

	// $samSql = "SELECT ss.SampleSize FROM LotSizeLogic ls join SampleSizeLogic ss on ls.$sampleType = ss.SampleSizeCodeLetter where ls.LotSizeMin <= $offerQty and ls.LotSizeMax >= $offerQty";

	// $samQuery = mysqli_query($conn,$samSql);
	// $samRowCount=mysqli_num_rows($samQuery);
	// if($samRowCount !=0){
	// 	$samRow = mysqli_fetch_assoc($samQuery);
	// 	$sampleSize = $samRow["SampleSize"];
	// }

	// $moreUpdate .= ", `Remark`='$remark', `TPI`='$tpiEmpId', `SampleType`='$sampleType', `SampleSize`=$sampleSize";

	$moreUpdate .= ", `Remark`='$remark', `TPI`='$tpiEmpId'";
	$auditRemark = $remark;
}
else if($beforeStatus == "IR_1"){
	$moreUpdate .= ", `TPI_Remark`='$remark', `TPI_Auditor`='$tpiAuditorEmpId'";
	$auditRemark = $remark;
}
else if($beforeStatus == "IR_3"){
	$moreUpdate .= ", `TPI_Observation`='$observation', `Declarartion`='$declaration'";
	$auditRemark = $remark;
}
else if($beforeStatus == "IR_4"){
	$moreUpdate .= ", `SQT_Observation`='$observation', `MaterialDispatchStatus`='$materialDispatchStatus'";
	$auditRemark = $remark;
	if($materialDispatchStatus == "MDCC"){
		$status = "IR_5";
		$moreUpdate .= ", `MDCC_No`='$mdccNo'";
	}
	else if($materialDispatchStatus == "Reject"){
		$status = "IR_101";
	}
}
else if($beforeStatus == "IR_101"){
	if($status == "IR_5.1"){
		$moreUpdate .= ", `Rework`='Deviation'";
	}
	else if($status == "IR_102"){
		$moreUpdate .= ", `Rework`='Rework & offer'";
	}
	else if($status == "IR_102.1"){
		$moreUpdate .= ", `Rework`='Rework with deviation'";
	}

	require 'Base64ToAnyClass.php';
	$base64 = new Base64ToAnyClass();
	if($reworkDoc !=""){	
		$reworkDoc = $base64->base64ToAny($reworkDoc,$irId.'_Rework');
		$moreUpdate .= ", `ReworkDoc`='$reworkDoc'";
	}
	if($defectPhoto !=""){
		$defectPhoto = $base64->base64ToAny($defectPhoto,$irId.'_DefectPhoto');
		$moreUpdate .= ", `DefectPhoto`='$defectPhoto'";
	}
	if($defineAndVerifyRootCauseDoc != ""){
		$defineAndVerifyRootCauseDoc = $base64->base64ToAny($defineAndVerifyRootCauseDoc,$irId.'_DefectPhoto');
		$moreUpdate .= ", `DefineAndVerifyRootCauseDoc`='$defineAndVerifyRootCauseDoc'";
	}

	$moreUpdate .= ", `ProbDesc`='$probDesc', `ImmeCorrecDet`='$immeCorrecDet', `DefineAndVerifyRootCause`='$defineAndVerifyRootCause', `CorrectiveActions`='$correctiveActions', `TargetDate`='$targetDate'";
	$auditRemark = $probDesc;
}

$sql = "UPDATE `InsReqMaster` SET `Status`=? $moreUpdate where `IR_Id`=? and `Status`=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $status,$irId,$beforeStatus);

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
	else{
		$auditSql = "INSERT INTO `IR_Audit`(`IR_Id`, `EmpId`, `RoleId`, `AfterStatus`, `Remark`) VALUES (?,?,?,?,?)";
		$auditStmt = $conn->prepare($auditSql);
		$auditStmt->bind_param("ssiss",$irId,$loginEmpId,$loginEmpRoleId,$status,$auditRemark);
		$auditStmt->execute();

		if($status == "IR_5"){
			$irSql = "SELECT `OfferQty` FROM `InsReqMaster` where `IR_Id`='$irId'";
			$irQuery = mysqli_query($conn,$irSql);
			$irRow = mysqli_fetch_assoc($irQuery);
			$offerQty = $irRow["OfferQty"];

			
			$mdccSql = "INSERT INTO `IR_MDCC`(`IR_Id`, `MDCC_No`, `Observations`, `Remarks`, `MaterialDispatchStatus`, `OfferQty`, `RemainingQty`) VALUES (?,?,?,?,?,?,?)";
			$mdccStmt = $conn->prepare($mdccSql);
			$mdccStmt->bind_param("sssssii", $irId, $mdccNo, $observation, $remark, $materialDispatchStatus, $offerQty, $offerQty);
			$mdccStmt->execute();
		}
		$cancelIrStatusArr = ['IR_100','IR_104','IR_105'];
		if(in_array($status,$cancelIrStatusArr)){
			$cancelIrSql = "UPDATE `Mapping` set `IsActive`=0 where `ActivityId`=0 and `IR_Id`='$irId'";
			$cancelIrStmt = $conn->prepare($cancelIrSql);
			$cancelIrStmt->execute();
		}
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