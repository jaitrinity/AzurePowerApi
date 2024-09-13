<?php
class SaveTpiCheckpointClass{
	function saveTpiCheckpoint($saveJson){
		include("dbConfiguration.php");

		$irId = $saveJson["irId"];
		$empId = $saveJson["empId"];
		$mId = $saveJson["mId"];
		$lId = $saveJson["lId"];
		$event = $saveJson["event"];
		$geolocation = $saveJson["geolocation"];
		$mobiledatetime = $saveJson["mobiledatetime"];
		$status = $saveJson["status"];
		$checklist = $saveJson["checklist"];
		$mapId = 0;

		$activitySql = "INSERT into `Activity` (`MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `MobileDateTime`) values ('$mapId', '$empId', '$mId', '$lId', '$event', '$geolocation', '$mobiledatetime')";
		if(mysqli_query($conn,$activitySql)){
			$activityId = mysqli_insert_id($conn);

			for($ii=0;$ii<count($checklist);$ii++){
				$chObj = $checklist[$ii];
				$chkpId = $chObj["chkpId"];
				$value = $chObj["value"];

				$dateTime = $mobiledatetime;
				$sampleNo = 1;
				$dependChkId = 0;

				$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES (?,?,?,?,?,?)";
				$stmt = $conn->prepare($insertInTransDtl);
				$stmt->bind_param("iissii", $activityId, $chkpId, $value, $dateTime, $sampleNo, $dependChkId);
				try {
					$stmt->execute();
				} catch (Exception $e) {
					
				}

			}

			$irActSql="SELECT `ActivityId` FROM `InsReqMaster` where `IR_Id`='$irId'";


			$updateFlowActSql="UPDATE `FlowActivityMaster` set `FlowActivityId`=$activityId, `FlowEmpId`='$empId', `FlowSubmitDate`='$mobiledatetime' where `ActivityId`=($irActSql) and `Status`='$status'";	
			mysqli_query($conn,$updateFlowActSql);

			$upFlowSql="SELECT `AfterStatus` FROM `FlowActivityMaster` where `ActivityId`=($irActSql) and `Status`='$status'";
			$upFlowQuery = mysqli_query($conn,$upFlowSql);
			$upFlowRow = mysqli_fetch_assoc($upFlowQuery);
			$afterStatus = $upFlowRow["AfterStatus"];

			$updateTransHdrSql = "UPDATE `TransactionHDR` set `Status`='$afterStatus' where `ActivityId`=($irActSql)";
			mysqli_query($conn,$updateTransHdrSql);
		}
	}
}
?>