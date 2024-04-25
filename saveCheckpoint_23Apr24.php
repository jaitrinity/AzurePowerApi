<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/AzurePower/api/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json,true);
$req = $jsonData;
//echo json_encode($req);

$irId=$req['irId'];
// $mapId=$req['mappingId'];
$mapId='';
$empId=$req['empId'];
$mId=$req['menuId'];
$lId=$req['locationId'];
$event=$req['event'];
$geolocation=$req['geolocation'];
// $distance=$req['distance'];
$distance="0";
$mobiledatetime=$req['mobiledatetime'];
// $caption = $req['caption'];
$caption = "";
$transactionId = $req['timeStamp'];
$checklist = $req['checklist'];
// $dId = $req['did'];
$dId = 0;
$assignId = $req['assignId'];
$actId = $req['activityId'];
// $status = $req["status"];
$status = "";


 $lastTransHdrId = "";
 $activityId = 0;
 if($lId == ""){
 	$lId = '1';
 }

 if($mId == ''){
 	$mId = '0';
 }

 if($mapId == ''){
 	$mapId = '0';
 }
 if($assignId == ""){
 	$assignId = 0;
 }
 if($actId == null){
	 $actId = "";
 }
 if($irId == ""){
 	$irId=0;
 }

if ((strpos($mobiledatetime, 'AM') !== false) || (strpos($mobiledatetime, 'PM')) || (strpos($mobiledatetime, 'am') !== false) || (strpos($mobiledatetime, 'pm')))   {
	$date = date_create_from_format("Y-m-d h:i:s A","$mobiledatetime");
	$date1 = date_format($date,"Y-m-d H:i:s");
}
else{
	$date1 = $mobiledatetime;
} 

if($event == 'Submit'){
		
	
	$activitySql = "INSERT into `Activity` (`MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `MobileDateTime`) values ('$mapId', '$empId', '$mId', '$lId', '$event', '$geolocation', '$date1')";
	if(mysqli_query($conn,$activitySql)){
		$activityId = mysqli_insert_id($conn);
	}
	
	if($checklist != null && count($checklist) != 0){

		if($assignId == '0' && $actId == ''){
			$insertMapping = "INSERT INTO `Mapping`(`EmpId`,`MenuId`,`LocationId`,`StartDate`,`EndDate`,`ActivityId`) values ('$empId','$mId','$lId',curdate(),curdate(),'$activityId') ";
			mysqli_query($conn,$insertMapping);

			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
			
			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;
				$vendorId="";
				$isAllMatOk="";
				for($ii=0;$ii<count($checklist);$ii++)
				{
					$chObj = $checklist[$ii];
					$chkp_id = $chObj["Chkp_Id"];
					// echo $chkp_id;
					$valueList=$chObj["valueList"];
					for($i=0;$i<count($valueList);$i++){
						$valueObj = $valueList[$i];
						$value = $valueObj["value"];
						$dateTime = $valueObj["dateTime"];
						$sampleNo = $valueObj["sampleNo"];
						$dependUpon = $valueObj["dependUpon"];
						$dependChkId = ($dependUpon == null || $dependUpon == "") ? 0 : $dependUpon;

						if($chkp_id == 434) $vendorId=$value;
						else if($chkp_id == 435) $isAllMatOk=$value;

						$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES (?,?,?,?,?,?)";
						$stmt = $conn->prepare($insertInTransDtl);
						$stmt->bind_param("iissii", $activityId, $chkp_id, $value, $dateTime, $sampleNo, $dependChkId);
						try {
							$stmt->execute();
						} catch (Exception $e) {
							
						}	
					}	
				}

				// $flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				// $flowCpResult = mysqli_query($conn,$flowCpSql);
				// while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
				// 	$roleId = $flowCpRow["RoleId"];
				// 	$flowStatus = $flowCpRow["Status"];
				// 	$afterStatus = $flowCpRow["AfterStatus"];
				// 	$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
				// 	$flowEmpId = getFlowEmpId($empId, $roleId);
				// 	// echo $flowEmpId.'-1';
				// 	if($flowEmpId !=""){
				// 		$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
				// 		// echo $flowActSql;
				// 		mysqli_query($conn,$flowActSql);
				// 	}
				// }

				if($mId==2){
					$venExp = explode(" --- ", $vendorId);
					$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `VendorId`) VALUES ($activityId, '$venExp[0]')";
					mysqli_query($conn,$scarSql);
				}
				if($mId==3){
					$scarSql="INSERT INTO `ExpenseMaster`(`ActivityId`) VALUES ($activityId)";
					mysqli_query($conn,$scarSql);
				}
				if($mId==4){
					$action = $isAllMatOk == "No" ? 1 : 0;
					$venExp = explode(" --- ", $vendorId);
					$mrnSql="INSERT INTO `MrnMaster`(`ActivityId`, `VendorId`, `Action`) VALUES ($activityId, '$venExp[0]', $action)";
					mysqli_query($conn,$mrnSql);

					if($isAllMatOk == "No"){
						$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `VendorId`) VALUES ($activityId, '$venExp[0]')";
						mysqli_query($conn,$scarSql);
					}
				}
			}
		}
		else{
			if($actId == ''){
				$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
				mysqli_query($conn,$insertInTransHdr);

				// $flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				// $flowCpResult = mysqli_query($conn,$flowCpSql);
				// while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
				// 	$roleId = $flowCpRow["RoleId"];
				// 	$flowStatus = $flowCpRow["Status"];
				// 	$afterStatus = $flowCpRow["AfterStatus"];
				// 	$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
				// 	$flowEmpId = getFlowEmpId($empId, $roleId);
				// 	// echo $flowEmpId.'-2';
				// 	if($flowEmpId !=""){
				// 		$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
				// 		mysqli_query($conn,$flowActSql);
				// 	}
				// }
			}
			
			$lastTransHdrId = $activityId;	
			for($ii=0;$ii<count($checklist);$ii++)
			{
				$chObj = $checklist[$ii];
				$chkp_id = $chObj["Chkp_Id"];
				// echo $chkp_id;
				$valueList=$chObj["valueList"];
				for($i=0;$i<count($valueList);$i++){
					$valueObj = $valueList[$i];
					$value = $valueObj["value"];
					$dateTime = $valueObj["dateTime"];
					$sampleNo = $valueObj["sampleNo"];
					$dependUpon = $valueObj["dependUpon"];
					$dependChkId = ($dependUpon == null || $dependUpon == "") ? 0 : $dependUpon;

					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES (?,?,?,?,?,?)";
					$stmt = $conn->prepare($insertInTransDtl);
					$stmt->bind_param("iissii", $activityId, $chkp_id, $value, $dateTime, $sampleNo, $dependChkId);
					try {
						$stmt->execute();
					} catch (Exception $e) {
						
					}	
				}	
			}
		}	
	}
	//Change in Mapping table from now onwards
	if($assignId != "0"){
		$updateAssignTaskSql = "UPDATE `Mapping` set `ActivityId` = '$activityId' where `MappingId` = $assignId";
		mysqli_query($conn,$updateAssignTaskSql);

	}
	if($actId != ''){
		$updateFlowActSql="UPDATE `FlowActivityMaster` set `FlowActivityId`=$activityId, `FlowEmpId`='$empId', `FlowSubmitDate`='$date1' where `ActivityId`=$actId and `Status`='$status'";	
		mysqli_query($conn,$updateFlowActSql);

		$upFlowSql="SELECT `AfterStatus` FROM `FlowActivityMaster` where `ActivityId`=$actId and `Status`='$status'";
		$upFlowQuery = mysqli_query($conn,$upFlowSql);
		$upFlowRow = mysqli_fetch_assoc($upFlowQuery);
		$afterStatus = $upFlowRow["AfterStatus"];

		$updateTransHdrSql = "UPDATE TransactionHDR set `Status`='$afterStatus' where ActivityId = $actId";
		mysqli_query($conn,$updateTransHdrSql);
	}
	
	$output = new StdClass;
	if($lastTransHdrId != ""){
		$output -> code = "200";
		$output -> message = "success";
		$output -> transId = "$activityId";	
		if($irId !=0){
			$irSql="SELECT `ActivityId` FROM `Mapping` where `IR_Id`='$irId' and `ActivityId`=0";
			$irQuery = mysqli_query($conn,$irSql);
			$irRowCount = mysqli_num_rows($irQuery);
			if($irRowCount == 0){
				$irStatus="UPDATE `InsReqMaster` SET `Status`='IR_3', `AuditDate`='$mobiledatetime' where `IR_Id`='$irId'";
				mysqli_query($conn,$irStatus);

				$loginEmpRoleId = 5;
				$status = "IR_3";
				$remark = "TODO task complete";

				$auditSql = "INSERT INTO `IR_Audit`(`IR_Id`, `EmpId`, `RoleId`, `AfterStatus`, `Remark`) VALUES (?,?,?,?,?)";
				$auditStmt = $conn->prepare($auditSql);
				$auditStmt->bind_param("ssiss",$irId,$empId,$loginEmpRoleId,$status,$remark);
				$auditStmt->execute();
			}
		}	
	}
	else{
		$output -> code = "0";
		$output -> message = "something wrong";
		$output -> transId = "$activityId";
	}
	echo json_encode($output);

	file_put_contents('/var/www/trinityapplab.in/html/AzurePower/api/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
}

// function getFlowEmpId($empId, $flowRoleId){
// 	global $conn; 
// 	$flowEmpId = "";
// 	// $flowSql = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $flowRoleId ";
// 	$flowSql = "SELECT e.EmpId FROM Employees e where e.RoleId = $flowRoleId";
// 	$flowQuery = mysqli_query($conn,$flowSql);
// 	$ii=0;
// 	while ($flowRow = mysqli_fetch_assoc($flowQuery)) {
// 		$flowEmpId .= $flowRow["EmpId"];
// 		if($ii<mysqli_num_rows($flowQuery)-1){
// 			$flowEmpId .= ",";
//  		}
// 		$ii++;
// 	}
// 	return $flowEmpId;
// }



?>