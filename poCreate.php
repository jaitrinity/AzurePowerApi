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
$projectId = $jsonData->projectId;
$poNo = $jsonData->poNo;
$poDate = $jsonData->poDate;
$noOfItem = $jsonData->noOfItem;
$poAttachment = $jsonData->poAttachment;
$itemList = $jsonData->itemList;

$sql="SELECT * FROM `PO_Master` where `PO_No`='$poNo'";
$query=mysqli_query($conn,$sql);
$rowCount=mysqli_num_rows($query);
if($rowCount !=0){
	$output = array(
		'code' => 403,
		'message' => "$poNo already exist"
	);
	echo json_encode($output);
	return;
}

if($poAttachment !=null && $poAttachment !=""){
	require 'Base64ToAnyClass.php';
	$base64 = new Base64ToAnyClass();
	$poAttachment = $base64->base64ToAny($poAttachment,$poNo.'_PO_Attachment');
}

$sql = "INSERT INTO `PO_Master`(`ProjectId`, `PO_No`, `PO_Date`, `NoOfItems`, `PO_Attachment`, `CreateBy`) VALUES (?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss",$projectId, $poNo, $poDate, $noOfItem, $poAttachment, $loginEmpId);
if($stmt->execute()){
	
	$dataList = array();
	for($i=0;$i<count($itemList);$i++){
		$itemObj = $itemList[$i];
		$itemId = $itemObj->itemId;
		$itemQty = $itemObj->itemQty;

		$data = "('$poNo', $itemId, $itemQty)";
		array_push($dataList, $data);
	}
	$dataImp = implode(",", $dataList);

	$poItemSql = "INSERT INTO `PO_Items`(`PO_No`, `ItemId`, `Qty`) Values $dataImp";
	$poItemStmt = $conn->prepare($poItemSql);
	if($poItemStmt->execute()){
		$code = 200;
		$message = "Success";
	}
	else{
		$code = 500;
		$message = "Something wrong in `PO_Items` table";

		$delPo = "DELETE FROM `PO_Master` WHERE `PO_No`='$poNo'";
		mysqli_query($conn,$delPo);
	}
}
else{
	$code = 500;
	$message = "Something wrong in `PO_Master` table";
}

$output = array(
	'code' => $code,
	'message' => $message
);
echo json_encode($output);

?>