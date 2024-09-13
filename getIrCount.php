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

$sql = "SELECT 'Raise' as type, count(*) as dataCount FROM `InsReqMaster` where `Status`='IR_0'
UNION
SELECT 'Pending' as type, count(*) as dataCount FROM `InsReqMaster` where `Status` in ('IR_1','IR_2','IR_3','IR_4')
UNION
SELECT 'Complete' as type, count(*) as dataCount FROM `InsReqMaster` where `Status` in ('IR_5','IR_6')";
$query = mysqli_query($conn,$sql);
$dataList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($dataList, $row);
}

$output = array(
	'dashboardList' => $dataList
);
echo json_encode($output);

?>