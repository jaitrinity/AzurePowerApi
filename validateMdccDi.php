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
$diNo = $jsonData->diNo;
$mdccNo = $jsonData->mdccNo;
$irId = $jsonData->irId;


$sql = "UPDATE `MDCC_DI` set `Status`=1, `ActionDate`=current_timestamp where `IR_Id`=? and `MDCC_No`=? and `DI_No`=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss",$irId, $mdccNo, $diNo);
if($stmt->execute()){
	$code = 200;
	$message = "Status updated";

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

	$mrnSql = "SELECT di.DI_No as `1040`, di.MDCC_No as `1031`, di.IR_Id as `1032`, ir.LotNo as `1033`, e.Name as `1035`, ir.ProjectName as `1036`, pm.Address as `1037`, ir.PO_No as `1038`, im.SubItemName as `1039`, di.DeliverQty as `1041`, di.DeliverDate as `1034`, di.Remark as `1042`, pm.SQ_EmpId as `sqEmpId` FROM MDCC_DI di join InsReqMaster ir on di.IR_Id=ir.IR_Id join Employees e on ir.CreateBy=e.EmpId join ProjectMaster pm on ir.ProjectName=pm.ProjectName join ItemMaster im on ir.OfferItem=im.ItemId where di.DI_No='$diNo'";
	// echo $mrnSql;
	$mrnQuery = mysqli_query($conn,$mrnSql);
	$mrnRow=mysqli_fetch_assoc($mrnQuery);
	$mrnChkList = array();
	foreach ($mrnRow as $key => $value) {
		// echo $key.'--'.$value;
		$mrnChkJson = array('chkpId' => $key, 'value' => $value);
		array_push($mrnChkList, $mrnChkJson);
	}

	$mobiledatetime = date('Y-m-d H:i:s', time());
	$timeStamp = date('YmdHis', time());

	$saveMrnJson = array(
		'irId' => $irId, 'diNo' => $diNo, 'mdccNo' => $mdccNo, 'empId' => $loginEmpId, 'mId' => 4, 
		'lId' => 1, 'event' => 'Submit', 'geolocation' => '0/0', 
		'mobiledatetime' => $mobiledatetime, 'timeStamp' => $timeStamp, 
		'checklist' => $mrnChkList, 'assignId' => '', 'activityId' => ''
	);

	// echo json_encode($saveMrnJson);
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