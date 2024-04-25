<?php
class SaveIrCheckpointClass{
	function saveIrCheckpoint($saveIrJson){
		include("dbConfiguration.php");

		$irId = $saveIrJson["irId"];
		$empId = $saveIrJson["empId"];
		$mId = $saveIrJson["mId"];
		$lId = $saveIrJson["lId"];
		$event = $saveIrJson["event"];
		$geolocation = $saveIrJson["geolocation"];
		$mobiledatetime = $saveIrJson["mobiledatetime"];
		$timeStamp = $saveIrJson["timeStamp"];
		$checklist = $saveIrJson["checklist"];
		$assignId = $saveIrJson["assignId"];
		// $activityId = $saveIrJson["activityId"];
		$mapId = 0;

		$activitySql = "INSERT into `Activity` (`MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `MobileDateTime`) values ('$mapId', '$empId', '$mId', '$lId', '$event', '$geolocation', '$mobiledatetime')";
		if(mysqli_query($conn,$activitySql)){
			$activityId = mysqli_insert_id($conn);
			// echo $activityId;

			$insertMapping = "INSERT INTO `Mapping`(`EmpId`,`MenuId`,`LocationId`,`StartDate`,`EndDate`,`ActivityId`,`IR_Id`) values ('$empId','$mId','$lId',curdate(),curdate(),'$activityId','$irId') ";
			mysqli_query($conn,$insertMapping);

			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','IR_0')";

			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;

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

				$flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				$flowCpResult = mysqli_query($conn,$flowCpSql);
				while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
					$roleId = $flowCpRow["RoleId"];
					$flowStatus = $flowCpRow["Status"];
					$afterStatus = $flowCpRow["AfterStatus"];
					$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
					$flowEmpId = $this->getFlowEmpId($empId, $roleId, $conn);
					// echo $flowEmpId.'-1';
					if($flowEmpId !=""){
						$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
						// echo $flowActSql;
						mysqli_query($conn,$flowActSql);
					}
				}
			}
		}
		// echo $activityId;
	}

	function getFlowEmpId($empId, $flowRoleId, $conn){
		$flowEmpId = "";
		// $flowSql = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $flowRoleId ";
		$flowSql = "SELECT e.EmpId FROM Employees e where e.RoleId = $flowRoleId";
		// echo $flowSql;
		$flowQuery = mysqli_query($conn,$flowSql);
		$ii=0;
		while ($flowRow = mysqli_fetch_assoc($flowQuery)) {
			$flowEmpId .= $flowRow["EmpId"];
			// echo $flowEmpId;
			if($ii<mysqli_num_rows($flowQuery)-1){
				$flowEmpId .= ",";
	 		}
			$ii++;
		}
		return $flowEmpId;
	}
}

?>