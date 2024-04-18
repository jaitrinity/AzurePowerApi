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

$mobile = $jsonData->mobile;
$password = $jsonData->password;

$empArr = array();
$sql = "SELECT e.EmpId, e.SpocPerson, e.RoleId, e.Password, e.IsActive, rm.Role FROM Employees e join RoleMaster rm on e.RoleId = rm.RoleId WHERE e.Mobile = BINARY('$mobile')";
	
$query = mysqli_query($conn,$sql);

if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$myPassword = $row["Password"];
	$isActive = $row["IsActive"];
	$isEq = strcmp($myPassword,$password);
	if($isEq !=0){
		$output = array(
			'code' => 404,
			'message' => 'Password incorrect, please try again'
		);
		echo json_encode($output);
		return;
	}
	else if($isActive != 1){
		$output = array(
			'code' => 404,
			'message' => 'Given mobile is inactive, please try again'
		);
		echo json_encode($output);
		return;
	}

	$empId = $row["EmpId"];
	$empName = $row["SpocPerson"];
	$roleId = $row["RoleId"];
	$role = $row["Role"];

	$empJson = array(
		'empId' => $empId,
		'name' => $empName,
		'roleId' => $roleId,
		'role' => $role
	);
	$output = array(
		'code' => 200,
		'message' => 'SUCCESSFUL',
		'data' => $empJson
	);
	echo json_encode($output);}
else{
	$output = array(
		'code' => 204,
		'message' => 'Invalid mobile, please try again.'
	);
	echo json_encode($output);
}
?>