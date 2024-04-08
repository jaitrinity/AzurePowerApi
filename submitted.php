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

$submitList = array();
// $submitSql = "SELECT DISTINCT `ActivityId` from `FlowActivityMaster` where `FlowEmpId` = '$empId'";
// $submitSql .= "UNION SELECT DISTINCT `ActivityId` from `Activity` where `EmpId` = '$empId' and `Event` = 'Submit'";
$submitSql = "SELECT DISTINCT `ActivityId` from `Activity` where `EmpId` = '$empId' and `Event` = 'Submit' order by `ActivityId` desc";
$submitQuery = mysqli_query($conn,$submitSql);
$submitRowSize = mysqli_num_rows($submitQuery);
if($submitRowSize != 0){
	while($subRow = mysqli_fetch_array($submitQuery)){
		$actId = $subRow["ActivityId"];

		$hisSql="SELECT m.Caption, m.SubCategory, m.Category, m.Icons, m.CheckpointId, l.Name as locationName, a.MobileDateTime as SubmitDateTime, a.GeoLocation, a.ActivityId, th.Status, ir.IR_Id, concat(im.ItemName,' - ',im.SubItemName) as ProductName, (case when m.SampleSize is null then ir.SampleSize else m.SampleSize end)  as `SampleSize` from Activity a 
		join Menu m on (a.MenuId = m.MenuId) 
		join TransactionHDR th on (a.ActivityId = th.ActivityId) 
		join Location l on (a.LocationId = l.LocationId) 
		left join Mapping mp on a.ActivityId=mp.ActivityId
		left join InsReqMaster ir on mp.IR_Id = ir.IR_Id
		left join ItemMaster im on ir.OfferItem = im.ItemId
		where a.ActivityId = $actId and a.Event = 'Submit'  order by a.MobileDateTime desc";
		$hisQuery = mysqli_query($conn,$hisSql);
		$hisRowSize = mysqli_num_rows($hisQuery);
		if($hisRowSize == 0)
			continue;
		
		$hisRow = mysqli_fetch_array($hisQuery);

		$irId = $hisRow["IR_Id"];
		$cat = $hisRow["Category"];
		$sub = $hisRow["SubCategory"];
		$caption = $hisRow["Caption"];
		$iconArr = explode(",",$hisRow['Icons']);
		$moreCaption="";
		if($irId !=0){
			$moreCaption .= " - IR ".$irId;
		}

		$submitObj = new StdClass;
		// $submitObj->irId = $hisRow['IR_Id'];
		$submitObj->activityId = $hisRow["ActivityId"];
		$submitObj->productName = $hisRow['ProductName'];
		$submitObj->name = $hisRow['locationName'];
		$submitObj->endDate= $hisRow['SubmitDateTime'];
		// $submitObj->uniqueId= $actId;
		$submitObj->geoLocation = $hisRow['GeoLocation'];

		if($cat != '' && $sub == '' && $caption == ''){
			$submitObj->caption = $cat.$moreCaption;
			$submitObj->icon = $iconArr[0];
		}
		else if($cat !='' && $sub != '' && $caption == ''){
			$submitObj->caption = $sub.$moreCaption;
			$submitObj->icon = $iconArr[1];
		}
		else if($cat != '' && $sub != '' && $caption != ''){
			$submitObj->caption = $caption.$moreCaption;
			$submitObj->icon = $iconArr[2];
		}
		$submitObj->subCategoryList = array();
		$submitObj->sampleSize = $hisRow["SampleSize"] == null ? 1 : intval($hisRow["SampleSize"]);


		$fillActArr= array();
		$firstObj = array(
			'flowChkId' => $hisRow["CheckpointId"],
			'flowActId' => $hisRow["ActivityId"]
		);
		array_push($fillActArr, $firstObj);

		// $fillSql="SELECT `FlowCheckpointId`, `FlowActivityId` FROM `FlowActivityMaster` where `ActivityId`=$actId and `FlowActivityId` is not null";
		// $fillQuery=mysqli_query($conn,$fillSql);
		// while($fillRow = mysqli_fetch_assoc($fillQuery)){
		// 	$fillObj = array(
		// 		'flowChkId' => $fillRow["FlowCheckpointId"],
		// 		'flowActId' => $fillRow["FlowActivityId"]
		// 	);
		// 	array_push($fillActArr, $fillObj);
		// }

		getInfiniteLevelCheckpoints($fillActArr,$submitObj);

		array_push($submitList,$submitObj);
	}
}

// echo json_encode($submitList);

$code = 0;
if(count($submitList) !=0){
	$code=200;
}
else{
	$code=403;
}
$output=array(
	'code'=>$code,
	'completed'=>$submitList
);
echo json_encode($output);


?>

<?php
function getInfiniteLevelCheckpoints($fillActArr,$submitObj){
	global $conn;

	$apcpArray = array();
	$allChkId = "";
	for($ii=0;$ii<count($fillActArr);$ii++){
		$fillObj = $fillActArr[$ii];
		$flowActId = $fillObj["flowActId"];
		$flowChkId = $fillObj["flowChkId"];
		if($allChkId == ""){
			$allChkId .= $flowChkId;
		}
		else{
			$allChkId .= ":".$flowChkId;
		}
		$filledCpString = str_replace(":",",",$flowChkId);

		if($flowActId != 0){
			// $apFilledCpSql = "Select r2.*,r1.* 
			//  from
			//  (Select d.SRNo,d.ChkId,d.Value as answer,d.SampleNo from TransactionDTL d
			//  where d.ActivityId = '$flowActId' and d.DependChkId = 0
			//  )r1
			//  right join 
			//  (Select c.* from Checkpoints c
			//  where c.CheckpointId in ($filledCpString)
			//  ) r2 on (r1.ChkId = r2.CheckpointId) order by r1.SRNo";
			 

			 $apFilledCpSql = "Select r2.*,r1.* 
			 from
			 (Select d.SRNo,d.ChkId,d.Value as answer,d.SampleNo from TransactionDTL d
			 where d.ActivityId = '$flowActId' and d.DependChkId = 0
			 )r1
			 right join 
			 (Select c.* from Checkpoints c
			 where c.CheckpointId in ($filledCpString)
			 ) r2 on (r1.ChkId = r2.CheckpointId) order by field(r1.ChkId, $filledCpString), r1.SampleNo";

			 // echo $apFilledCpSql;

			$nLevelFilledQuery=mysqli_query($conn,$apFilledCpSql);
			while($apfcp = mysqli_fetch_assoc($nLevelFilledQuery)){
				$apfcpObj = new StdClass;
				$apfcpObj->chkp_Id = $apfcp['CheckpointId'];
				// $apfcpObj->editable = '0';
				if($apfcp['answer'] != null){
					$apfcpObj->value = $apfcp['answer'];
				}
				else{
					$apfcpObj->value = "";
				}
				$apfcpObj->sampleNo=$apfcp["SampleNo"];
				
				$apfdpArray = array();
				if($apfcp['Dependent'] == "1"){
					$apfdpSql = " Select r1.*,c.* from
									(Select d.SRNo,d.ChkId,d.Value as answer,d.SampleNo from TransactionDTL d
									where d.ActivityId = '$flowActId' and d.DependChkId = (".$apfcp['CheckpointId'].")
									) r1
									join Checkpoints c on (r1.ChkId = c.CheckpointId) order by r1.SRNo";
									
					$apfdpQuery = mysqli_query($conn,$apfdpSql);
					while($apfdp = mysqli_fetch_assoc($apfdpQuery)){
						$apfdpObj = new StdClass;
						$apfdpObj->chkp_Id = $apfcp['CheckpointId']."_".$apfdp['CheckpointId'];
						// $apfdpObj->editable = '0';
						$apfdpObj->value = $apfdp['answer'];
						$apfdpObj->sampleNo = $apfdp['SampleNo'];
						array_push($apfdpArray,$apfdpObj);
					}
				}
				// $apfcpObj->dependents = $apfdpArray;
				array_push($apcpArray,$apfcpObj);
			}
		}
	}

	$submitObj->checkpointId = $allChkId;
	$cpId = $allChkId;

	$apisDataSend = "";
	$apcpIdArray = explode(":",$cpId);
	for($apcpId = 0; $apcpId < count($apcpIdArray); $apcpId++){
		if($apisDataSend == ""){
			$apisDataSend .= "0";
		}
		else{
			$apisDataSend .= ":0";
		}
	}
	// $submitObj->isDataSend = $apisDataSend;
	$submitObj->value = $apcpArray;
}
?>