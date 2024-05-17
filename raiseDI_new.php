<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$selectType = $_REQUEST["selectType"];

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/AzurePower/api/log/raiseDI_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$irId = $jsonData->irId;
$mdccNo = $jsonData->mdccNo;
$offerQty = $jsonData->offerQty;
$deliverQty = $jsonData->deliverQty;
$deliverDate = $jsonData->deliverDate;
$remark = $jsonData->remark;

$diNo = $mdccNo.'_'.rand(1000,9999);

$sql="INSERT INTO `MDCC_DI`(`IR_Id`, `MDCC_No`, `DI_No`, `DeliverQty`, `DeliverDate`, `Remark`) VALUES (?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiss", $irId,$mdccNo,$diNo,$deliverQty,$deliverDate,$remark);
$code = 0;
$message = "";
if($stmt->execute()){
	$code = 200;
	$message = "Successfully raise DI";

	$diSql = "SELECT sum(`DeliverQty`) as `deliveredQty` FROM `MDCC_DI` where `IR_Id`='$irId' and `MDCC_No`='$mdccNo'";
	$diQuery = mysqli_query($conn,$diSql);
	$diRow = mysqli_fetch_assoc($diQuery);
	$deliveredQty = $diRow["deliveredQty"];

	$remainingQty = $offerQty - $deliveredQty;

	$mdccSql = "UPDATE `IR_MDCC` set `DeliveredQty`=$deliveredQty, `RemainingQty`=$remainingQty where `IR_Id`='$irId' and `MDCC_No`='$mdccNo'";
	// echo $mdccSql;
	$mdccStmt = $conn->prepare($mdccSql);
	$mdccStmt->execute();

	if($remainingQty == 0){
		$sql1 = "UPDATE `InsReqMaster` SET `Status`='IR_6' where `IR_Id`=? and `Status`='IR_5'";
		$stmt1 = $conn->prepare($sql1);
		$stmt1->bind_param("i", $irId);
		$stmt1->execute();
	}

	// 
	$mrnSql = "SELECT di.DI_No, di.MDCC_No, di.IR_Id, ir.LotNo, e.Name as VendorName, ir.ProjectName, pm.Address as SiteAddress, ir.PO_No, im.SubItemName as MatDesc, di.DeliverQty, di.DeliverDate, di.Remark FROM MDCC_DI di join InsReqMaster ir on di.IR_Id=ir.IR_Id join Employees e on ir.CreateBy=e.EmpId join ProjectMaster pm on ir.ProjectName=pm.ProjectName join ItemMaster im on ir.OfferItem=im.ItemId where id.DI_No='$diNo'";
	$mrnRow=mysqli_fetch_assoc($mrnSql);
	$mrnChkList = array();
	foreach ($mrnRow as $key => $value) {
		$mrnChkJson = array('chkpId' => $key, 'value' => $value);
		array_push($mrnChkList, $mrnChkJson);
	}

	$mobiledatetime = date('Y-m-d H:i:s', time());
	$timeStamp = date('YmdHis', time());

	$saveMrnJson = array(
		'irId' => $irId, 'empId' => $loginEmpId, 'mId' => 4, 
		'lId' => 1, 'event' => 'Submit', 'geolocation' => '0/0', 
		'mobiledatetime' => $mobiledatetime, 'timeStamp' => $timeStamp, 
		'checklist' => $mrnChkList, 'assignId' => '', 'activityId' => ''
	);
	require 'SaveMrnCheckpointClass.php';
	$saveMrnClassObj = new SaveMrnCheckpointClass();
	$saveMrnClassObj->saveCheckpoint($saveMrnJson);
		
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