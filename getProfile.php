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
$defaultPic = "https://www.trinityapplab.in/AzurePower/api/files/default-pic.png";

$sql = "SELECT e.EmpId as empId, e.SpocPerson as name, e.Mobile as mobile, e.EmailId as emailId, (case when e.ProfilePic is null or e.ProfilePic = '' then '$defaultPic' else e.ProfilePic end) as profilePic, r.Role as role, e.IsActive as isActive FROM Employees e join RoleMaster r on e.RoleId=r.RoleId where e.EmpId='$empId' and e.RoleId='$roleId'";
$query = mysqli_query($conn,$sql);
$rowCount = mysqli_num_rows($query);

$code = 0;
$employeeInfo = new StdClass();
if($rowCount !=0){
	$row = mysqli_fetch_assoc($query);
	$employeeInfo = $row;
	$code = 200;
	$message = "Employee details fetch";
}
else{
	$code = 404;
	$message = "No record found";
}
header("HTTP/1.1 ".$code);
$output = array('code' => $code, 'message' => $message, 'employeeInfo' => $employeeInfo );
echo json_encode($output);
?>