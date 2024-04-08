<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}

$json = file_get_contents('php://input');
$jsonData=json_decode($json,true);
$req = $jsonData;
//echo json_encode($req);

$mapId=$req['mappingId'];
$empId=$req['empId'];
$mId=$req['menuId'];
$lId=$req['locationId'];
$event=$req['event'];
$geolocation=$req['geolocation'];
$distance=$req['distance'];
$mobiledatetime=$req['mobiledatetime'];
$caption = $req['caption'];
$transactionId = $req['timeStamp'];
$checklist = $req['checklist'];
$dId = $req['did'];
$assignId = $req['assignId'];
$actId = $req['activityId'];
$status = $req["status"];


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
				foreach($checklist as $k=>$v)
				{
					$answer=$v['value'];
					$dateTime=$v['dateTime'];
					$chkp_idArray=explode("_",$v['Chkp_Id']);
					$dependentChpId=0;
					if(count($chkp_idArray) > 1){
						$chkp_id = $chkp_idArray[1];
						$dependentChpId = $chkp_idArray[0];
					}
					else{
						$chkp_id = $chkp_idArray[0];
					}	
					
					
					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`) VALUES (?,?,?,?)";
					$stmt = $conn->prepare($insertInTransDtl);
					$stmt->bind_param("iisi", $activityId, $chkp_id, $answer, $dependentChpId);
					try {
						$stmt->execute();
					} catch (Exception $e) {
						
					}	
				}

				$flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				$flowCpResult = mysqli_query($conn,$flowCpSql);
				while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
					$roleId = $flowCpRow["RoleId"];
					$flowStatus = $flowCpRow["Status"];
					$afterStatus = $flowCpRow["AfterStatus"];
					$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
					$flowEmpId = getFlowEmpId($empId, $roleId);
					if($flowEmpId !=0){
						$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
						mysqli_query($conn,$flowActSql);
					}
				}
			}
		}
		else{
			if($actId == ''){
				$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
				mysqli_query($conn,$insertInTransHdr);

				$flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				$flowCpResult = mysqli_query($conn,$flowCpSql);
				while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
					$roleId = $flowCpRow["RoleId"];
					$flowStatus = $flowCpRow["Status"];
					$afterStatus = $flowCpRow["AfterStatus"];
					$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
					$flowEmpId = getFlowEmpId($empId, $roleId);
					if($flowEmpId !=0){
						$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
						mysqli_query($conn,$flowActSql);
					}
				}
			}
			
			$lastTransHdrId = $activityId;	
			foreach($checklist as $k=>$v)
			{
				$answer=$v['value'];
				$dateTime=$v['dateTime'];
				$chkp_idArray=explode("_",$v['Chkp_Id']);
				$dependentChpId=0;
				if(count($chkp_idArray) > 1){
					$chkp_id = $chkp_idArray[1];
					$dependentChpId = $chkp_idArray[0];
				}
				else{
					$chkp_id = $chkp_idArray[0];
				}	
				

				$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`) VALUES (?,?,?,?)";
				$stmt = $conn->prepare($insertInTransDtl);
				$stmt->bind_param("iisi", $activityId, $chkp_id, $answer, $dependentChpId);
				try {
					if($stmt->execute()){
						$isAllSave = true;
					}
				} catch (Exception $e) {
					
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
	}
	else{
		$output -> code = "0";
		$output -> message = "something wrong";
		$output -> transId = "$activityId";
	}
	echo json_encode($output);
}

function getFlowEmpId($empId, $flowRoleId){
	global $conn; 
	$flowEmpId = "";
	$flowSql = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $flowRoleId ";
	$flowQuery = mysqli_query($conn,$flowSql);
	$ii=0;
	while ($flowRow = mysqli_fetch_assoc($flowQuery)) {
		$flowEmpId .= $flowRow["EmpId"];
		if($ii<mysqli_num_rows($flowQuery)-1){
			$flowEmpId .= ",";
 		}
		$ii++;
	}
	return $flowEmpId;
}



?>