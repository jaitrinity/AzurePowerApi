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
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$irId = $jsonData->irId;
$remark = $jsonData->remark;
$tpiAuditorEmpId = $jsonData->tpiAuditorEmpId;

$sql = "UPDATE `InsReqMaster` SET `Status`='IR_2', `TPI_Auditor`=?, `TPI_Remark`=? where `IR_Id`=? and `Status`='IR_1'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $tpiAuditorEmpId, $remark, $irId);
if($stmt->execute()){
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

	$code = 200;
	$message = "Status updated";

	// $irSql = "SELECT im.ItemId, im.ItemName, ir.OfferQty, im.Logic, ir.InspectionDate FROM InsReqMaster ir join ItemMaster im on ir.OfferItem=im.ItemId where ir.IR_Id=$irId";
	// $irQuery = mysqli_query($conn,$irSql);
	// $irRow = mysqli_fetch_assoc($irQuery);
	// $offerQty = intval($irRow["OfferQty"]);
	// $logic = intval($irRow["Logic"]);
	// $insDate = $irRow["InspectionDate"];

	// $tkt = round($offerQty*$logic/100);
	// echo $tkt;

	$irSql = "SELECT ir.InspectionDate, ir.InspectionCloseDate, im.MenuId FROM InsReqMaster ir join ItemMaster im on ir.OfferItem=im.ItemId where ir.IR_Id='$irId'";
	$irQuery = mysqli_query($conn,$irSql);
	$irRow = mysqli_fetch_assoc($irQuery);
	$insDate = $irRow["InspectionDate"];
	$insCloseDate = $irRow["InspectionCloseDate"];
	$menuId = $irRow["MenuId"];
	$menuList = explode(",", $menuId);
	$tableSql = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `StartDate`, `EndDate`, `IR_Id`)";
	for($i=0;$i<count($menuList);$i++){
		$loopMenuId = $menuList[$i];
		$dataSql = "('$tpiAuditorEmpId', $loopMenuId, '$insDate', '$insCloseDate', '$irId')";
		$mappingSql = $tableSql.' VALUES '.$dataSql;
		$mappingStmt = $conn->prepare($mappingSql);
		$mappingStmt->execute();
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