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
if($insertType == "project"){
	$projectName = $jsonData->projectName;
	$materialName = $jsonData->materialName;
	$poNo = $jsonData->poNo;
	$poDate = $jsonData->poDate;
	$lotNo = $jsonData->lotNo;
	$lotQty = $jsonData->lotQty;

	$code=0;
	$message="";
	$sql = "INSERT INTO `ProjectMaster2`(`ProjectName`, `MaterialName`, `PO_No`, `PO_Date`, `LOT_No`, `LOT_Qty`) VALUES (?,?,?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssssi",$projectName, $materialName, $poNo, $poDate, $lotNo, $lotQty);
	if($stmt->execute()){
		$code = 200;
		$message = "Successfully";
	}
	else{
		$code = 500;
		$message = "Something wrong";
	}

	$output = array(
		'code' => $code, 
		'message' => $message
	);
	echo json_encode($output);
}
// else if($insertType == "tpi"){
// 	$empCode = $jsonData->empCode;
// 	$name = $jsonData->name;
// 	$mobile = $jsonData->mobile;
// 	$emailId = $jsonData->emailId;
// 	$roleId = 4;
// 	$zone = $jsonData->zone;

// 	$sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `Zone`=? and `IsActive`=1";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("ssis", $empCode, $mobile,$roleId,$zone);
// 	$stmt->execute();
// 	$query = $stmt->get_result();
// 	$rowCount = mysqli_num_rows($query);
// 	if($rowCount != 0){
// 		$output = array(
// 			'code' => 204, 
// 			'message' => "Employee already exist on $mobile"
// 		);
// 		echo json_encode($output);
// 		return;
// 	}
// 	$password = base64_encode($mobile);

// 	$sql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `Zone`) VALUES (?,?,?,?,?,?,?)";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("sssssis", $empCode, $name, $mobile, $emailId, $password, $roleId, $zone);
// 	if($stmt->execute()){

// 		$code = 200;
// 		$message = "Success";
// 	}
// 	else{
// 		$code = 0;
// 		$message = "Something wrong";
// 	}

// 	$output = array(
// 		'code' => $code, 
// 		'message' => $message
// 	);
// 	echo json_encode($output);
// }
else if($insertType == "employees_old"){
	$roleType = $jsonData->roleType;
	$empCode = $jsonData->empCode;
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$emailId = $jsonData->emailId;
	$roleId = $jsonData->roleId;
	$zone = $jsonData->zone;
	$spocPerson = $jsonData->spocPerson;
	$itemName = $jsonData->itemId;
	$sampleType = $jsonData->sampleType;
	// $passTxt = rand();
	$passTxt = $mobile;
	$password = base64_encode($passTxt);

	if($roleType == "Employee"){
		// $sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `IsActive` = 1";
		// $stmt = $conn->prepare($sql);
		// $stmt->bind_param("ssi", $empCode,$mobile,$roleId);	
		// $message = "Employee already exist on $empCode and $mobile";

		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `TenantId`) VALUES (?,?,?,?,?,?,1)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssi", $empCode, $name, $mobile, $emailId, $password, $roleId);
	}
	else if($roleType == "TPI"){
		$roleId = 4;
		// $sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `Zone`=? and `IsActive`=1";
		// $stmt = $conn->prepare($sql);
		// $stmt->bind_param("ssis", $empCode,$mobile,$roleId,$zone);
		// $message = "Employee already exist on $empCode, $mobile and $zone";

		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `Zone`, `TenantId`, `SpocPerson`) VALUES (?,?,?,?,?,?,?,3,?)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssiss", $empCode, $name, $mobile, $emailId, $password, $roleId, $zone, $spocPerson);
	}
	else if($roleType == "Vendor"){
		$roleId = 2;
		// $sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `IsActive`=1";
		// $stmt = $conn->prepare($sql);
		// $stmt->bind_param("ssi",$empCode,$mobile,$roleId);
		// $message = "Employee already exist on $empCode and $mobile";

		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `TenantId`, `SpocPerson`, `ItemName`, `SampleType`) VALUES (?,?,?,?,?,?,2,?,?,?)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssisss", $empCode, $name, $mobile, $emailId, $password, $roleId, $spocPerson, $itemName, $sampleType);
	}

	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	if($rowCount != 0){
		$output = array(
			'code' => 204, 
			'message' => $message
		);
		echo json_encode($output);
		return;
	}
	
	if($insertStmt->execute()){
		$code = 200;
		$message = "Success";
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
}
else if($insertType == "employees"){
	$roleType = $jsonData->roleType;
	$empCode = $jsonData->empCode;
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$emailId = $jsonData->emailId;
	$roleId = $jsonData->roleId;
	$zone = $jsonData->zone;
	$spocPerson = $jsonData->spocPerson;
	$itemName = $jsonData->itemId;
	$sampleType = $jsonData->sampleType;
	// $passTxt = rand();
	$passTxt = $mobile;
	$password = base64_encode($passTxt);

	if($roleType == "Employee"){
		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `SpocPerson`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `TenantId`) VALUES (?,?,'Azure',?,?,?,?,1)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssi", $empCode, $name, $mobile, $emailId, $password, $roleId);
	}
	else if($roleType == "TPI"){
		$roleId = 4;

		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `Zone`, `TenantId`, `SpocPerson`) VALUES (?,?,?,?,?,?,?,3,?)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssiss", $empCode, $name, $mobile, $emailId, $password, $roleId, $zone, $spocPerson);
	}
	else if($roleType == "Vendor"){
		$roleId = 2;

		$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive` = 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $empCode, $mobile);	
		$message = "Employee already exist on $empCode or $mobile";

		$insertSql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `TenantId`, `SpocPerson`, `ItemName`, `SampleType`) VALUES (?,?,?,?,?,?,2,?,?,?)";
		$insertStmt = $conn->prepare($insertSql);
		$insertStmt->bind_param("sssssisss", $empCode, $name, $mobile, $emailId, $password, $roleId, $spocPerson, $itemName, $sampleType);
	}

	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	if($rowCount != 0){
		$output = array(
			'code' => 204, 
			'message' => $message
		);
		echo json_encode($output);
		return;
	}
	
	if($insertStmt->execute()){
		$code = 200;
		$message = "Success";
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
}
else if($insertType == "tpiAuditor"){
	$loginEmpName = $jsonData->loginEmpName;
	$empCode = $jsonData->empCode;
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$emailId = $jsonData->emailId;
	$roleId = 5;
	$dob = $jsonData->dob;
	$aadharNo = $jsonData->aadharNo;
	$profilePic = $jsonData->profilePic;
	$cv = $jsonData->cv;

	require 'Base64ToAnyClass.php';
	$base64 = new Base64ToAnyClass();
	if($profilePic != ""){
		$profilePic = $base64->base64ToAny($profilePic,$empCode.'_Auditor');
	}
	if($cv != ""){
		$cv = $base64->base64ToAny($cv,$empCode.'_CV');
	}

	// $sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `IsActive`=1";
	// $stmt = $conn->prepare($sql);
	// $stmt->bind_param("ssi",$empCode, $mobile,$roleId);

	$sql = "SELECT * from `Employees` where (`EmpId`=? or `Mobile`=?) and `IsActive`=1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss",$empCode, $mobile);
	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	if($rowCount != 0){
		$output = array(
			'code' => 204, 
			'message' => "Employee already exist on $empCode or $mobile"
		);
		echo json_encode($output);
		return;
	}

	// $passTxt = rand();
	$passTxt = $mobile;
	$password = base64_encode($passTxt);

	$sql = "INSERT INTO `Employees`(`EmpId`, `SpocPerson`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`, `RMId`, `DOB`, `AadharNo`, `ProfilePic`, `TenantId`, `IsActive`, `CV`) VALUES (?,?,?,?,?,?,?,?,?,?,?,3,2,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssssssisssss", $empCode,$name,$loginEmpName,$mobile,$emailId,$password,$roleId,$loginEmpId,$dob,$aadharNo,$profilePic,$cv);
	if($stmt->execute()){
		$code = 200;
		$message = "Employee successfully inserted";
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
}
// else if($insertType == "vendor"){
// 	$vendorCode = $jsonData->vendorCode;
// 	$name = $jsonData->name;
// 	$mobile = $jsonData->mobile;
// 	$emailId = $jsonData->emailId;
// 	$roleId = 2;

// 	$sql = "SELECT * from `Employees` where `EmpId`=? and `Mobile`=? and `RoleId`=? and `IsActive`=1";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("ssi",$vendorCode, $mobile,$roleId);
// 	$stmt->execute();
// 	$query = $stmt->get_result();
// 	$rowCount = mysqli_num_rows($query);
// 	if($rowCount != 0){
// 		$output = array(
// 			'code' => 204, 
// 			'message' => "Employee already exist on $mobile"
// 		);
// 		echo json_encode($output);
// 		return;
// 	}
// 	$password = base64_encode($mobile);

// 	$sql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Mobile`, `EmailId`, `Password`, `RoleId`) VALUES (?,?,?,?,?,?)";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->bind_param("sssssi", $vendorCode, $name, $mobile, $emailId, $password, $roleId);
// 	if($stmt->execute()){
// 		$code = 200;
// 		$message = "Employee successfully inserted";
// 	}
// 	else{
// 		$code = 0;
// 		$message = "Something wrong";
// 	}

// 	$output = array(
// 		'code' => $code, 
// 		'message' => $message
// 	);
// 	echo json_encode($output);
// }
else{
	$output = array(
		'code' => 404, 
		'message' => "Invalid API"
	);
	echo json_encode($output);
}



?>