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

$sql = "SELECT `EmpId` as `empId`, `SpocPerson` as `name`, `RoleId` FROM `Employees` where `RoleId` in (6,9) and `IsActive`=1";
$query = mysqli_query($conn,$sql);
$ctEmpList = array();
$sqEmpList = array();
while ($row = mysqli_fetch_assoc($query)) {
	$roleId = $row["RoleId"];
	unset($row["RoleId"]);
	if($roleId == 6){
		// CT employee
		array_push($ctEmpList, $row);
	}
	else if($roleId == 9){
		// SQ employee
		array_push($sqEmpList, $row);
	}
}

$output = array('ctEmpList' => $ctEmpList, 'sqEmpList' => $sqEmpList);
echo json_encode($output);

?>