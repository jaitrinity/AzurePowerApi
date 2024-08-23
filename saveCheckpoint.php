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
$status = $req["status"];
// $status = "";


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
		// for submit dashboard checklist
		if($assignId == '0' && $actId == ''){
			$insertMapping = "INSERT INTO `Mapping`(`EmpId`,`MenuId`,`LocationId`,`StartDate`,`EndDate`,`ActivityId`) values ('$empId','$mId','$lId',curdate(),curdate(),'$activityId') ";
			mysqli_query($conn,$insertMapping);

			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
			
			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;
				$vendorId="";
				$isAllMatOk="";
				$expIr="";
				// $sum730=0;
				// $sum732=0;
				// $sum734=0;
				// $sum736=0;
				// $sum738=0;
				// $sum740=0;
				// $sum742=0;
				// $sum1009=0;
				// $sum1011=0;
				// $sum1013=0;
				for($ii=0;$ii<count($checklist);$ii++)
				{
					$chObj = $checklist[$ii];
					$chkp_id = $chObj["Chkp_Id"];
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
						else if($chkp_id == 1027 || $chkp_id == 1029 || $chkp_id == 1030){
							$expIr=$value;	
						}

						// if($mId == 109){
						// 	if($chkp_id == 730) $sum730 += $value;
						// 	else if($chkp_id == 732) $sum732 += $value;
						// 	else if($chkp_id == 734) $sum734 += $value;
						// 	else if($chkp_id == 736) $sum736 += $value;
						// 	else if($chkp_id == 738) $sum738 += $value;
						// 	else if($chkp_id == 740) $sum740 += $value;
						// 	else if($chkp_id == 742) $sum742 += $value;
						// 	else if($chkp_id == 1009) $sum1009 += $value;
						// 	else if($chkp_id == 1013) $sum1013 += $value;
						// }
							

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

				// if($mId == 109){
				// 	$avg730=$sum730/10;
				// 	$det730="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1016,'$avg730','$mobiledatetime',1,0)";

				// 	$stmt730 = $conn->prepare($det730);
				// 	$stmt730->execute();

				// 	$avg732=$sum732/10;
				// 	$det732="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1017,'$avg732','$mobiledatetime',1,0)";
				// 	$stmt732 = $conn->prepare($det732);
				// 	$stmt732->execute();

				// 	$avg734=$sum734/10;
				// 	$det734="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1018,'$avg734','$mobiledatetime',1,0)";
				// 	$stmt734 = $conn->prepare($det734);
				// 	$stmt734->execute();

				// 	$avg736=$sum736/10;
				// 	$det736="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1019,'$avg736','$mobiledatetime',1,0)";
				// 	$stmt736 = $conn->prepare($det736);
				// 	$stmt736->execute();

				// 	$avg738=$sum738/10;
				// 	$det738="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1020,'$avg738','$mobiledatetime',1,0)";
				// 	$stmt738 = $conn->prepare($det738);
				// 	$stmt738->execute();

				// 	$avg740=$sum740/10;
				// 	$det740="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1021,'$avg740','$mobiledatetime',1,0)";
				// 	$stmt740 = $conn->prepare($det740);
				// 	$stmt740->execute();

				// 	$avg742=$sum742/10;
				// 	$det742="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1022,'$avg742','$mobiledatetime',1,0)";
				// 	$stmt742 = $conn->prepare($det742);
				// 	$stmt742->execute();

				// 	$avg1009=$sum1009/10;
				// 	$det1009="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1023,'$avg1009','$mobiledatetime',1,0)";
				// 	$stmt1009 = $conn->prepare($det1009);
				// 	$stmt1009->execute();

				// 	$avg1011=$sum1011/10;
				// 	$det1011="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1024,'$avg1011','$mobiledatetime',1,0)";
				// 	$stmt1011 = $conn->prepare($det1011);
				// 	$stmt1011->execute();

				// 	$avg1013=$sum1013/10;
				// 	$det1013="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1025,'$avg1013','$mobiledatetime',1,0)";
				// 	$stmt1013 = $conn->prepare($det1013);
				// 	$stmt1013->execute();
				// }

				// SCAR
				if($mId==2){
					$venExp = explode(" --- ", $vendorId);
					$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `IR_Id`, `VendorId`) VALUES ($activityId, '$expIr', '$venExp[0]')";
					mysqli_query($conn,$scarSql);
				}
				// Travel Expense
				if($mId==3){
					$scarSql="INSERT INTO `ExpenseMaster`(`ActivityId`,`IR_Id`) VALUES ($activityId, '$expIr')";
					mysqli_query($conn,$scarSql);

					$expIrSql = "UPDATE `InsReqMaster` SET `ExpenseStatus` = 3 WHERE `IR_Id`='$expIr'";
					$stmtExpIr = $conn->prepare($expIrSql);
					$stmtExpIr->execute();
				}
				// MRN
				// if($mId==4){
				// 	$action = $isAllMatOk == "No" ? 1 : 0;
				// 	$venExp = explode(" --- ", $vendorId);
				// 	$mrnSql="INSERT INTO `MrnMaster`(`ActivityId`, `IR_Id`, `VendorId`, `Action`) VALUES ($activityId, '$expIr', '$venExp[0]', $action)";
				// 	mysqli_query($conn,$mrnSql);

				// 	if($isAllMatOk == "No"){
				// 		$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `IR_Id`, `VendorId`) VALUES ($activityId, '$expIr', '$venExp[0]')";
				// 		mysqli_query($conn,$scarSql);
				// 	}
				// }
			}
		}
		// for submit todo checklist
		else{
			// for fill first user in todo
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
			$isAllMatOk = "";
			$sum730=0;
			$sum732=0;
			$sum734=0;
			$sum736=0;
			$sum738=0;
			$sum740=0;
			$sum742=0;
			$sum1009=0;
			$sum1011=0;
			$sum1013=0;
			$sum843=0;
			$sum977=0;
			$sum647=0;
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

					if($chkp_id == 745) $tpiId=$value;
					else if($chkp_id == 725) $tpiRemark=$value;
					else if($chkp_id == 435) $isAllMatOk=$value;

					if($mId == 109 || $mId == 86){
						if($chkp_id == 730) $sum730 += $value;
						else if($chkp_id == 732) $sum732 += $value;
						else if($chkp_id == 734) $sum734 += $value;
						else if($chkp_id == 736) $sum736 += $value;
						else if($chkp_id == 738) $sum738 += $value;
						else if($chkp_id == 740) $sum740 += $value;
						else if($chkp_id == 742) $sum742 += $value;
						else if($chkp_id == 1009) $sum1009 += $value;
						else if($chkp_id == 1011) $sum1011 += $value;
						else if($chkp_id == 1013) $sum1013 += $value;
					}
					else if($mId == 122){
						if($chkp_id == 843) $sum843 += $value;
					}
					else if($mId == 131){
						if($chkp_id == 977) $sum977 += $value;
					}
					else if($mId == 87){
						if($chkp_id == 647) $sum647 += $value;
					}

					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES (?,?,?,?,?,?)";
					$stmt = $conn->prepare($insertInTransDtl);
					$stmt->bind_param("iissii", $activityId, $chkp_id, $value, $dateTime, $sampleNo, $dependChkId);
					try {
						$stmt->execute();
					} catch (Exception $e) {
						
					}	
				}	
			}

			if($mId == 109 || $mId == 86){
				$avg730=$sum730/10;
				$det730="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1016,'$avg730','$mobiledatetime',1,0)";
				$stmt730 = $conn->prepare($det730);
				$stmt730->execute();

				$avg732=$sum732/10;
				$det732="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1017,'$avg732','$mobiledatetime',1,0)";
				$stmt732 = $conn->prepare($det732);
				$stmt732->execute();

				$avg734=$sum734/10;
				$det734="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1018,'$avg734','$mobiledatetime',1,0)";
				$stmt734 = $conn->prepare($det734);
				$stmt734->execute();

				$avg736=$sum736/10;
				$det736="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1019,'$avg736','$mobiledatetime',1,0)";
				$stmt736 = $conn->prepare($det736);
				$stmt736->execute();

				$avg738=$sum738/10;
				$det738="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1020,'$avg738','$mobiledatetime',1,0)";
				$stmt738 = $conn->prepare($det738);
				$stmt738->execute();

				$avg740=$sum740/10;
				$det740="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1021,'$avg740','$mobiledatetime',1,0)";
				$stmt740 = $conn->prepare($det740);
				$stmt740->execute();

				$avg742=$sum742/10;
				$det742="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1022,'$avg742','$mobiledatetime',1,0)";
				$stmt742 = $conn->prepare($det742);
				$stmt742->execute();

				$avg1009=$sum1009/10;
				$det1009="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1023,'$avg1009','$mobiledatetime',1,0)";
				$stmt1009 = $conn->prepare($det1009);
				$stmt1009->execute();

				$avg1011=$sum1011/10;
				$det1011="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1024,'$avg1011','$mobiledatetime',1,0)";
				$stmt1011 = $conn->prepare($det1011);
				$stmt1011->execute();

				$avg1013=$sum1013/10;
				$det1013="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1025,'$avg1013','$mobiledatetime',1,0)";
				$stmt1013 = $conn->prepare($det1013);
				$stmt1013->execute();
			}
			else if($mId == 122){
				$avg843=$sum843/10;
				$det="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1026,'$avg843','$mobiledatetime',1,0)";
				$stmt = $conn->prepare($det);
				$stmt->execute();
			}
			else if($mId == 131){
				$avg977=$sum977/10;
				$det="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1026,'$avg977','$mobiledatetime',1,0)";
				$stmt = $conn->prepare($det);
				$stmt->execute();
			}
			else if($mId == 87){
				$avg647=$sum647/10;
				$det="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`Datetime`,`SampleNo`,`DependChkId`) VALUES ($activityId,1026,'$avg647','$mobiledatetime',1,0)";
				$stmt = $conn->prepare($det);
				$stmt->execute();
			}
			// else if($mId == 4 && $isAllMatOk == "No"){
			// 	$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `IR_Id`) VALUES ($actId, '$irId')";
			// 	mysqli_query($conn,$scarSql);
			// }
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

		if($mId == "1"){
			$tpiIdExp = explode(" --- ", $tpiId);
			$tpiEmpId = $tpiIdExp[0];

			$irStatus="UPDATE `InsReqMaster` SET `Status`='$afterStatus', `TPI`='$tpiEmpId', `Remark`='$tpiRemark' where `IR_Id`='$irId'";
			mysqli_query($conn,$irStatus);
		}
		else if($mId == "4"){
			$action = $isAllMatOk == "Yes" ? 1 : 0;
			if($isAllMatOk == "No"){
				$scarSql="INSERT INTO `ScarMaster`(`ActivityId`, `IR_Id`) VALUES ($actId, '$irId')";
				mysqli_query($conn,$scarSql);
			}

			$upMrn = "UPDATE `MrnMaster` set `Action`=$action, `Remark`='$tpiRemark', `FlowActivityId`=$activityId, `ActionDate`=CURRENT_TIMESTAMP where `ActivityId`=$actId and `IR_Id`='$irId'";
			mysqli_query($conn,$upMrn);

			$upMrnDate = "UPDATE `MDCC_DI` set `MRN_DoneDatetime`=CURRENT_TIMESTAMP where `MRN_ActivityId`=$actId";
			mysqli_query($conn,$upMrnDate);
		}
			
	}
	
	$output = new StdClass;
	if($lastTransHdrId != ""){
		$output -> code = "200";
		$output -> message = "success";
		$output -> transId = "$activityId";	
		if($irId !=0 && $mId != "1"){
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