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

$menuArr = array();
$sql = "SELECT distinct `MenuId` FROM `Mapping` WHERE `EmpId` = '$empId'";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	array_push($menuArr,$menuId);
}

$sql = "SELECT distinct `MenuId` FROM `FlowActivityMaster` WHERE find_in_set('$empId', `EmpId`) <> 0 ";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	array_push($menuArr,$menuId);
}

$roleSql = "SELECT distinct `MenuId` FROM `RoleMaster` WHERE `RoleId` = '$roleId' ";
$roleQuery=mysqli_query($conn,$roleSql);
while($roleRow = mysqli_fetch_assoc($roleQuery)){
	$roleMenuId = $roleRow['MenuId'];
	$roleMenuIdExplode = explode(",",$roleMenuId);
	for($i=0;$i<count($roleMenuIdExplode);$i++){
		array_push($menuArr,$roleMenuIdExplode[$i]);
	}
	
}


$newArr = array_unique($menuArr);
$newArr = array_values($newArr);

$menuIds = convertListInOperatorValue($newArr);
//echo $menuIds;
$chkIdString = "";
$menuSql = "SELECT `MenuId`,`CheckpointId`,`VerifierChkId`,`ApproverChkId` FROM `Menu` WHERE `MenuId` in ($menuIds)";
$menuQuery=mysqli_query($conn,$menuSql);
while($menuRow = mysqli_fetch_assoc($menuQuery)){
		$chkId = $menuRow["CheckpointId"];
		$chkId = str_replace(":",",",$chkId);
		if($chkIdString == ""){
			$chkIdString .= $chkId;
		}
		else{
			$chkIdString .= ",".$chkId;
		}
}

$appSql = "SELECT DISTINCT `FlowCheckpointId` FROM `FlowActivityMaster` where `MenuId` in ($menuIds)";
$appQuery=mysqli_query($conn,$appSql);
while($appRow = mysqli_fetch_assoc($appQuery)){
	$flowCheckpointId = $appRow["FlowCheckpointId"];
	$flowCheckpointId = str_replace(":",",",$flowCheckpointId);
	if($chkIdString == ""){
		$chkIdString .= $flowCheckpointId;
	}
	else{
		$chkIdString .= ",".$flowCheckpointId;
	}
}

$rr=0;
$responseArr = array();
// $newCpArr = explode(",", $chkIdString);
// for($ii=0;$ii<count($newCpArr);$ii++){
	// $chIds = $newCpArr[$ii];
	$chkpointSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($chkIdString)";
	$chkpointQuery=mysqli_query($conn,$chkpointSql);
	while($chkpointRow = mysqli_fetch_assoc($chkpointQuery)){
		$json = new StdClass;
		$json -> chkpId = $chkpointRow["CheckpointId"];
		$json -> description = $chkpointRow["Description"];
		$json -> value = $chkpointRow["Value"];
		$json -> typeId = $chkpointRow["TypeId"];
		$json -> mandatory = $chkpointRow["Mandatory"];
		$json -> editable = $chkpointRow["Editable"];
		$json -> correct = $chkpointRow["Correct"];
		$json -> size = $chkpointRow["Size"];
		$json -> score = $chkpointRow["Score"];
		$json -> language = $chkpointRow["Language"];
		$json -> active = $chkpointRow["Active"];
		$json -> isDept = $chkpointRow["Dependent"];
		$json -> logic = $chkpointRow["Logic"];
		$json -> isGeofence = $chkpointRow["IsGeofence"];
		$json -> sampleSize = $chkpointRow["SampleSize"];
		$json -> isShow = $chkpointRow["IsShow"];
		$json -> answer = "";
		$json -> sampleNo = "";
		$json -> info = $chkpointRow["Info"];

		if($chkpointRow['IsSql'] == 1){
		    $valueSql = $chkpointRow["Value"];
		    $stmt = mysqli_prepare($conn,$valueSql);
		    // if(str_contains($valueSql, "?")){
		    // 	mysqli_stmt_bind_param($stmt, 'si', $empId,$tenentId);
		    // }
		    mysqli_stmt_execute($stmt);
		    mysqli_stmt_store_result($stmt);
		    mysqli_stmt_bind_result($stmt,$project);
		    if(mysqli_stmt_num_rows($stmt) > 0){
		       $valueArray = array();
		       while($v = mysqli_stmt_fetch($stmt)){
		            array_push($valueArray,$project);
		       }
		       $json -> value =implode(',',$valueArray); 
			
		    }
		    else{
		        $json -> value = "";    
		    }
		    mysqli_stmt_close($stmt);
		}
		else{
		    $json -> value = $chkpointRow["Value"];    
		}

		
		// getting of login checkpint id in loginChkIdArr
		$logic = $chkpointRow["Logic"];
		$isDependent = $chkpointRow["Dependent"];
		if($logic != "" && ($isDependent == 1 || $isDependent == 5)){
			// $chkpointLogicString = "";
			// $logicChkIdString1 = "";
			// $logicChkIdArr1 = array();			
			// $logicArray = explode(":",$logic);

			$logicChkIdString1 = str_replace(":", ",", $logic);
			
			// for($l=0; $l < count($logicArray);$l++){
			// 	if(trim($logicArray[$l]," ")!= ""){
					
			// 		if($logicChkIdString1 == ""){
			// 			$logicChkIdString1 .= trim($logicArray[$l]," ");
			// 		}
			// 		else{
			// 			$logicChkIdString1 .= ",".trim($logicArray[$l]," ");
			// 		}
			// 		$csLogicString = "";
			// 		$commaseperatedlogicArray = explode(",",$logicArray[$l]);
			// 		for($csl=0;$csl<count($commaseperatedlogicArray);$csl++){
			// 			if($csLogicString != ""){
			// 				// $csLogicString .= ",".$chkpointRow["CheckpointId"]."_".$commaseperatedlogicArray[$csl];
			// 				$csLogicString .= ",".$commaseperatedlogicArray[$csl];
			// 			}
			// 			else{
			// 				// $csLogicString .= $chkpointRow["CheckpointId"]."_".$commaseperatedlogicArray[$csl]; 
			// 				$csLogicString .= $commaseperatedlogicArray[$csl]; 
			// 			}
			// 		}
			// 		if($chkpointLogicString != ""){
			// 			$chkpointLogicString .= ":".$csLogicString;
			// 		}
			// 		else{
			// 			$chkpointLogicString .= $csLogicString; 
			// 		}
					
			// 	}
			// 	else{
			// 		if($chkpointLogicString != ""){
			// 			$chkpointLogicString .= ": ";
			// 		}
			// 		else{
			// 			$chkpointLogicString .= " "; 
			// 		}
			// 	}
			// }
			$rrr = 0;
			if($logicChkIdString1 != ""){
				// $logicChkIdArr1 = explode(",",$logicChkIdString1);
				// for($jj=0;$jj<count($logicChkIdArr1);$jj++){
					// $newlogicIds1 = $logicChkIdArr1[$jj];
					$logicSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($logicChkIdString1) ";
					$logicQuery=mysqli_query($conn,$logicSql);
					while($logicRow = mysqli_fetch_assoc($logicQuery)){
						$logicJson = new StdClass;
						// $logicJson -> chkpId = $chkpointRow["CheckpointId"]."_".$logicRow["CheckpointId"];
						$logicJson -> chkpId = $logicRow["CheckpointId"];
						$logicJson -> description = $logicRow["Description"];
						$logicJson -> value = $logicRow["Value"];
						$logicJson -> typeId = $logicRow["TypeId"];
						$logicJson -> mandatory = $logicRow["Mandatory"];
						$logicJson -> editable = $logicRow["Editable"];
						$logicJson -> correct = $logicRow["Correct"];
						$logicJson -> size = $logicRow["Size"];
						$logicJson -> score = $logicRow["Score"];
						$logicJson -> language = $logicRow["Language"];
						$logicJson -> active = $logicRow["Active"];
						$logicJson -> isDept = $logicRow["Dependent"];
						$logicJson -> logic = $logicRow["Logic"];
						$logicJson -> isGeofence = $logicRow["IsGeofence"];
						$logicJson -> sampleSize = $logicRow["SampleSize"];
						$logicJson -> isShow = $logicRow["IsShow"];
						$logicJson -> answer = "";
						$logicJson -> sampleNo = "";
						$logicJson -> info = $logicRow["Info"];
						array_push($responseArr,$logicJson);
					}
				}

					
			// }
			// $json -> logic = $chkpointLogicString;
		}
		array_push($responseArr,$json);
	}
// }

// echo json_encode($responseArr);

$code = 0;
if(count($responseArr) !=0){
	$code=200;
}
else{
	$code=403;
}
$output=array(
	'code'=>$code,
	'checkpoint'=>$responseArr
);
echo json_encode($output);

// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/checkpoint_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($responseArr)."\n", FILE_APPEND);
?>

<?php
function convertListInOperatorValue($arrName){
	$inOperatorValue = "";
	for ($x = 0; $x < count($arrName); $x++) {
		if($arrName[$x] != ""){
			if($x == 0){
				$inOperatorValue .= $arrName[$x];
			}
			else{
				$inOperatorValue .= ",".$arrName[$x];
			}	
		}
		
		
	}
	return $inOperatorValue;
}
?>