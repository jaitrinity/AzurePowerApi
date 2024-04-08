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

$sql="INSERT INTO `MDCC_DI`(`IR_Id`, `MDCC_No`, `DeliverQty`, `DeliverDate`) VALUES (?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $irId,$mdccNo,$deliverQty,$deliverDate);
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