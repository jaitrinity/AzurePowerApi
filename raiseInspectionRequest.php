<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}

require 'EmployeesClass.php';
$classObj = new EmployeesClass();


$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$oldIrId = $jsonData->oldIrId;
$oldIrId = $oldIrId == null ? "" : $oldIrId;
$projectName = $jsonData->projectName;
$poNo = $jsonData->poNo;
$poDate = $jsonData->poDate;
$lotNo = $jsonData->lotNo;
$offerItem = $jsonData->offerItem;
$offerQty = $jsonData->offerQty;
$readinessReport = $jsonData->readinessReport;
$dimensionalReport = $jsonData->dimensionalReport;
$photograph = $jsonData->photograph;
$photograph1 = $jsonData->photograph1;
$photograph2 = $jsonData->photograph2;
$photograph3 = $jsonData->photograph3;
$qapGtp = $jsonData->qapGtp;
$itemForIns = $jsonData->itemForIns;
$insLocation = $jsonData->insLocation;
$insDate = $jsonData->insDate;

$newIrId = 0;
if($oldIrId == ""){
	$empInfo = $classObj->getEmployeeInfo($loginEmpId);
	$sampleCode = $empInfo["SampleType"];

	$poSql = "SELECT * FROM `InsReqMaster` where `ProjectName`='$projectName' and `OfferItem`=$offerItem and `PO_No`='$poNo' and `Status` not in ('IR_100', 'IR_103')";
	$poQuery = mysqli_query($conn,$poSql);
	$rowCount=mysqli_num_rows($poQuery);
	if($rowCount !=0 ){
		$output = array(
			'code' => 403, 
			'message' => "IR already exist on `$projectName` project, offerItem `$offerItem`, and `$poNo` PO."
		);
		echo json_encode($output);
		return;
	}

	require 'Base64ToAnyClass.php';
	$base64 = new Base64ToAnyClass();
	if($readinessReport !="")
		$readinessReport = $base64->base64ToAny($readinessReport,$poNo.'_Readiness');
	if($dimensionalReport !="")
		$dimensionalReport = $base64->base64ToAny($dimensionalReport,$poNo.'_Dimension');
	if($photograph !="")
		$photograph = $base64->base64ToAny($photograph,$poNo.'_Photograph');
	if($photograph1 !="")
		$photograph1 = $base64->base64ToAny($photograph1,$poNo.'_Photograph1');
	if($photograph2 !="")
		$photograph2 = $base64->base64ToAny($photograph2,$poNo.'_Photograph2');
	if($photograph3 !="")
		$photograph3 = $base64->base64ToAny($photograph3,$poNo.'_Photograph3');
	if($qapGtp !="")
		$qapGtp = $base64->base64ToAny($qapGtp,$poNo.'_QAPnGTP');
	$sampleSize=1;

	// $samSql = "SELECT ss.SampleSize FROM LotSizeLogic ls join SampleSizeLogic ss on ls.$sampleCode = ss.SampleSizeCodeLetter where ls.LotSizeMin <= $lotNo and ls.LotSizeMax >= $lotNo";
	$samSql = "SELECT ss.SampleSize FROM LotSizeLogic ls join SampleSizeLogic ss on ls.$sampleCode = ss.SampleSizeCodeLetter where ls.LotSizeMin <= $offerQty and ls.LotSizeMax >= $offerQty";
	$samQuery = mysqli_query($conn,$samSql);
	$samRowCount=mysqli_num_rows($samQuery);
	if($samRowCount !=0){
		$samRow = mysqli_fetch_assoc($samQuery);
		$sampleSize = $samRow["SampleSize"];
	}

	$confSql="SELECT (`IR_Count`+1) as irCount FROM `Configuration`";
	$confQuery = mysqli_query($conn,$confSql);
	$confRow = mysqli_fetch_assoc($confQuery);
	$newIrId = $confRow["irCount"];	

	$sql = "INSERT INTO `InsReqMaster`(`IR_Id`, `ProjectName`, `PO_No`, `PO_Date`, `LotNo`, `OfferItem`, `OfferQty`, `ReadinessReport`, `DimensionalReport`, `Photograph`, `Photograph1`, `Photograph2`, `Photograph3`, `QAPnGTP`, `ItemForInspection`, `InspectionLocation`, `InspectionDate`, `InspectionCloseDate`, `SampleSize`, `CreateBy`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssssssssssssssssssis", $newIrId,$projectName,$poNo,$poDate,$lotNo,$offerItem,$offerQty,$readinessReport,$dimensionalReport,$photograph,$photograph1,$photograph2,$photograph3,$qapGtp,$itemForIns,$insLocation,$insDate,$insDate,$sampleSize,$loginEmpId);
}
else{
	$newIrId = $oldIrId.'_1';
	$sql = "INSERT INTO `InsReqMaster`(`IR_Id`, `ProjectName`, `PO_No`, `PO_Date`, `LotNo`, `OfferItem`, `OfferQty`, `ReadinessReport`, `DimensionalReport`, `Photograph`, `Photograph1`, `Photograph2`, `Photograph3`, `QAPnGTP`, `ItemForInspection`, `InspectionLocation`, `InspectionDate`, `InspectionCloseDate`, `SampleSize`, `CreateBy`) ";
	$sql .= "SELECT '$newIrId' as `IR_Id`, `ProjectName`, `PO_No`, `PO_Date`, `LotNo`, `OfferItem`, `OfferQty`, `ReadinessReport`, `DimensionalReport`, `Photograph`, `Photograph1`, `Photograph2`, `Photograph3`, `QAPnGTP`, `ItemForInspection`, `InspectionLocation`, '$insDate' as `InspectionDate`, '$insDate' as `InspectionCloseDate`, `SampleSize`, `CreateBy` from `InsReqMaster` where `IR_Id`='$oldIrId'";
	$stmt = $conn->prepare($sql);
}

$code = 0;
$message = "";
if($stmt->execute()){
	$code = 200;
	$message = "Successfully raise IR";

	if($oldIrId == ""){
		$confUpdate="UPDATE `Configuration` SET `IR_Count`=$newIrId";
		$confStmt = $conn->prepare($confUpdate);
		$confStmt->execute();
	}
	else{
		$confUpdate="UPDATE `InsReqMaster` set `Status`='IR_103' where `IR_Id`='$oldIrId'";
		$confStmt = $conn->prepare($confUpdate);
		$confStmt->execute();
	}

	$status="IR_0";
	$remark="IR raised";

	$auditSql = "INSERT INTO `IR_Audit`(`IR_Id`, `EmpId`, `RoleId`, `AfterStatus`, `Remark`) VALUES (?,?,?,?,?)";
	$auditStmt = $conn->prepare($auditSql);
	$auditStmt->bind_param("ssiss",$newIrId,$loginEmpId,$loginEmpRoleId,$status,$remark);
	$auditStmt->execute();	

	$allPhotoList = array();
	if($photograph !="")
		array_push($allPhotoList, $photograph);
	if($photograph1 !="")
		array_push($allPhotoList, $photograph1);
	if($photograph2 !="")
		array_push($allPhotoList, $photograph2);
	if($photograph3 !="")
		array_push($allPhotoList, $photograph3);

	$allPhotoUrl = implode(",", $allPhotoList);

	$irChkList = array();
	$irChkJson = array('chkpId' => 1, 'value' => $projectName);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 11, 'value' => $insDate);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 2, 'value' => $poNo);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 4, 'value' => $offerItem);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 5, 'value' => $offerQty);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 10, 'value' => $insLocation);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 3, 'value' => $lotNo);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 6, 'value' => $readinessReport);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 7, 'value' => $dimensionalReport);
	array_push($irChkList, $irChkJson);

	$irChkJson = array('chkpId' => 8, 'value' => $allPhotoUrl);
	array_push($irChkList, $irChkJson);

	$saveIrJson = array(
		'irId' => $newIrId, 'empId' => $loginEmpId, 'mId' => 1, 
		'lId' => 1, 'event' => 'Submit', 'geolocation' => '0/0', 
		'mobiledatetime' => '2024-04-23 12:05:33', 'timeStamp' => '1234567890', 
		'checklist' => $irChkList, 'assignId' => '', 'activityId' => ''
	);
	require 'SaveIrCheckpointClass.php';
	$saveIrClassObj = new SaveIrCheckpointClass();
	$saveIrClassObj->saveIrCheckpoint($saveIrJson);

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