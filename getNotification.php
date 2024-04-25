<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$empId=$jsonData->empId;
$roleId=$jsonData->roleId;

$sql = "SELECT `Id` as `noticationId`, `Subject` as `subject`, `Body` as `body`, `IsRead` as `isRead`, `CreateDate` as `createDate` FROM `Notification` where `EmpId`='$empId'  ORDER by `Id` desc";
$query = mysqli_query($conn,$sql);
$resultList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($resultList, $row);
}
$code = 0;
if(count($resultList) !=0 ){
	$code = 200;
}
else{
	$code = 404;
}

header("HTTP/1.1 ".$code);
$output = array('code' => $code, 'notifications' => $resultList);
echo json_encode($output);

?>