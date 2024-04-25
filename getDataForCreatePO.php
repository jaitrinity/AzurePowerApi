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

$empInfo = $classObj->getEmployeeInfo($loginEmpId);
$itemName = $empInfo["ItemName"];
$itemList = [];
$sql = "SELECT `ItemId` as `itemId`, `SubItemName` as `subItemName` FROM `ItemMaster` where `ItemName`='$itemName' and `IsActive`=1";
$query = mysqli_query($conn,$sql);
while ($row = mysqli_fetch_assoc($query)) {
	array_push($itemList, $row);
}

$projectList = [];
$sql = "SELECT `Id` as `projectId`, `ProjectName` as `projectName` FROM `ProjectMaster` where `IsActive`=1";
$query = mysqli_query($conn,$sql);
while ($row = mysqli_fetch_assoc($query)) {
	array_push($projectList, $row);
}


$output = array('itemList' => $itemList, 'projectList' => $projectList);
echo json_encode($output);



?>