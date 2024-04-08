<?php
class EmployeesClass{
	function getEmployeeInfo($empId){
		global $conn;
		
		$sql = "SELECT `e1`.*, rm.`Role` as roleName, e2.`Name` as rmName FROM `Employees` e1 join `RoleMaster` rm on e1.`RoleId` = rm.`RoleId` left join `Employees` e2 on e1.`RMId` = e2.`EmpId` where e1.`EmpId` = '$empId' and e1.`IsActive` = 1 ";
		// echo $sql;
		$result = mysqli_query($conn,$sql);
		
		$row = mysqli_fetch_assoc($result);
		
		return $row;
	}

	// function getEmployeeInfo($empId){
	// 	global $conn;
		
	// 	$sql = "SELECT `e1`.*, `Role`.`Role` as roleName, e2.`Name` as rmName FROM `Employees` e1 join `Role` on `e1`.`RoleId` = `Role`.`RoleId` left join `Employees` e2 on e1.`RMId` = e2.`EmpId` where e1.`EmpId` = '$empId' and e1.`Active` = 1 ";
	// 	$result = mysqli_query($conn,$sql);
		
	// 	$row = mysqli_fetch_assoc($result);
	// 	$id = $row["Id"];
	// 	$empId = $row["EmpId"];
	// 	$empName = $row["Name"];
	// 	// $password = $row["Password"];
	// 	$mobile = $row["Mobile"];
	// 	$emailId = $row["EmailId"];
	// 	$roleId = $row["RoleId"];
	// 	$area = $row["Area"];
	// 	$city = $row["City"];
	// 	$state = $row["State"];
	// 	$rmId = $row["RMId"];
	// 	$fieldUser = $row["FieldUser"];
	// 	$active = $row["Active"];
	// 	$tenentId = $row["Tenent_Id"];
	// 	$roleName = $row["roleName"];
	// 	$rmName = $row["rmName"];
	// 	$empInfo = array(
	// 		'id' => $id,
	// 		'empId' => $empId,
	// 		'empName' => $empName,
	// 		// 'password' => $password,
	// 		'mobile' => $mobile,
	// 		'emailId' => $emailId,
	// 		'roleId' => $roleId,
	// 		'area' => $area,
	// 		'city' => $city,
	// 		'state' => $state,
	// 		'rmId' => $rmId,
	// 		'fieldUser' => $fieldUser,
	// 		'fieldUserValue' => $fieldUser == 1 ? "Yes" : "No",
	// 		'active' => $active,
	// 		'tenentId' => $tenentId,
	// 		'roleName' => $roleName,
	// 		'rmName' => $rmName,
	// 	);
	// 	return $empInfo;
	// }
}
?>