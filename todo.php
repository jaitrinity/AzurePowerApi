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

$wrappedListArray = array();

$geofenceSql = "SELECT `Geofence` FROM `Configuration`";
$geofenceQuery=mysqli_query($conn,$geofenceSql);
$geofenceRow = mysqli_fetch_assoc($geofenceQuery);
$configGeoFence = $geofenceRow["Geofence"];

$assignSql = "SELECT mp.MenuId,mp.LocationId,mp.StartDate,mp.EndDate,mp.MappingId,l.Name,l.GeoCoordinates,
		m.Caption,m.SubCategory,m.Category,m.Icons,m.CheckpointId,m.Colors,mp.IR_Id, (case when m.SampleSize is not null then m.SampleSize else ir.SampleSize end) as SampleSize, concat(im.ItemName,' - ',im.SubItemName) as ProductName
		FROM Mapping mp 
		left join Menu m  on (mp.MenuId = m.MenuId)
		left join Location l on (mp.LocationId = l.LocationId)
        left join InsReqMaster ir on mp.IR_Id = ir.IR_Id
        left join ItemMaster im on ir.OfferItem = im.ItemId 
		WHERE mp.IsActive = 1 and mp.EmpId = '$empId' AND date(mp.StartDate) <= date(now()) AND date(mp.EndDate) >= date(now())
		AND mp.ActivityId = 0 order by mp.MappingId";

		// echo $assignSql;
		
$assignQuery=mysqli_query($conn,$assignSql);

while($row = mysqli_fetch_assoc($assignQuery)){
	$assignObj = new StdClass;
	
	$iconArr = explode(",",$row['Icons']);

	$colors = $row["Colors"];
	$colorsExplode = explode(":", $colors);

	$catColors = $colorsExplode[0];
	$catColorsExplode = explode(",", $catColors);
	$catBgColor = $catColorsExplode[0];
	$catFontColor = $catColorsExplode[1];


	$subCatColors = $colorsExplode[1];
	$subCatColorsExplode = explode(",", $subCatColors);
	$subCatBgColor = $subCatColorsExplode[0];
	$subCatFontColor = $subCatColorsExplode[1];


	$captionColors = $colorsExplode[2];
	$captionColorsExplode = explode(",", $captionColors);
	$captionBgColor = $captionColorsExplode[0];
	$captionFontColor = $captionColorsExplode[1];

	$moreCaption="";
	$irId = $row["IR_Id"];
	if($irId !=0){
		$moreCaption .= " - IR ".$irId;
	}
	$cat = $row["Category"];
	$sub = $row["SubCategory"];
	$caption = $row["Caption"];
	$sampleSize = $row["SampleSize"] == null ? "1" : $row["SampleSize"];
	$productName = $row["ProductName"] == null ? "" : $row["ProductName"];
	
	// $hh = $row["GeoFence"];
	$hh = "0/0:100";
	$hhExplode = explode(":", $hh);
	$GeoCoordinate = $hhExplode[0];
	if($GeoCoordinate == ""){
		$GeoCoordinate = null;
	}
	$GeoFence = $hhExplode[1];
	if($GeoFence == ""){
		$GeoFence = null;
	}

	// for all todo checklist geofence..
	if($GeoFence == null)
		$GeoFence = $configGeoFence; 
	
	$isDataSend = "";
	$cpIdArray = explode(":",$row['CheckpointId']);
	for($cpId = 0; $cpId < count($cpIdArray); $cpId++){
		if($cpId == 0){
			$isDataSend .= "1";
		}
		else{
			$isDataSend .= ":1";
		}	
	}
	
	if($sub == '' && $caption == ''){
		$assignObj->irId = $irId;
		$assignObj->Caption = $cat.$moreCaption;
		$assignObj->productName = $productName;
		$assignObj->Icon = $iconArr[0];
		$assignObj->bgColor = $catBgColor;
		$assignObj->fontColor = $catFontColor;
		$assignObj->subCategoryList = array();
		$assignObj->menuId = $row["MenuId"];
		$assignObj->locationId = $row["LocationId"];
		$assignObj->startDate = $row["StartDate"];
		$assignObj->endDate = $row["EndDate"];
		$assignObj->assignId = $row["MappingId"];
		$assignObj->name = $row["Name"];
		$assignObj->latlong = $row["GeoCoordinates"];
		$assignObj->GeoFence = $GeoFence ;
		$assignObj->GeoCoordinate = $GeoCoordinate ;
		$assignObj->activityId = '';
		$assignObj->status = '';
		$assignObj->uniqueId = $row["MappingId"];
		$assignObj->sampleSize = $sampleSize;
		$assignObj->isDataSend = $isDataSend;
		$assignObj->checkpointId = $row['CheckpointId'];
		$assignObj->value = [];
		
		array_push($wrappedListArray,$assignObj);
	}
	else if($sub != '' && $caption == ''){
		// $assignObj->Caption = $cat;
		// $assignObj->Icon = $iconArr[0];
		// $assignObj->bgColor = $catBgColor;
		// $assignObj->fontColor = $catFontColor;
		
		$assignArray1 = array();
		$assignObj1 = new StdClass;
		$assignObj1->irId = $irId;
		$assignObj1->Caption = $sub.$moreCaption;
		$assignObj1->productName = $productName;
		$assignObj1->Icon = $iconArr[1];
		$assignObj1->bgColor = $subCatBgColor;
		$assignObj1->fontColor = $subCatFontColor;
		// $assignObj1->captionList = array();
		$assignObj1->subCategoryList = array();
		$assignObj1->menuId = $row["MenuId"];
		$assignObj1->locationId = $row["LocationId"];
		$assignObj1->startDate = $row["StartDate"];
		$assignObj1->endDate = $row["EndDate"];
		$assignObj1->assignId = $row["MappingId"];
		$assignObj1->name = $row["Name"];
		$assignObj1->latlong = $row["GeoCoordinates"];
		$assignObj1->GeoFence = $GeoFence ;
		$assignObj1->GeoCoordinate = $GeoCoordinate ;
		$assignObj1->activityId = '';
		$assignObj1->status = '';
		$assignObj1->uniqueId = $row["MappingId"];
		$assignObj1->sampleSize = $sampleSize;
		$assignObj1->isDataSend = $isDataSend;
		$assignObj1->checkpointId = $row['CheckpointId'];
		$assignObj1->value = [];

		array_push($wrappedListArray,$assignObj1);

		// array_push($assignArray1,$assignObj1);
		// $assignObj->subCategoryList = $assignArray1;
		
		// array_push($wrappedListArray,$assignObj);
	}
	else if($sub != '' && $caption != ''){
		// $assignObj->Caption = $cat;
		// $assignObj->Icon = $iconArr[0];
		// $assignObj->bgColor = $catBgColor;
		// $assignObj->fontColor = $catFontColor;
		
		// $assignArray1 = array();
		// $assignObj1 = new StdClass;
		// $assignObj1->Caption = $sub;
		// $assignObj1->Icon = $iconArr[1];
		// $assignObj1->bgColor = $subCatBgColor;
		// $assignObj1->fontColor = $subCatFontColor;


		$assignArray2 = array();
		$assignObj2 = new StdClass;
		$assignObj2->irId = $irId;
		$assignObj2->Caption = $caption.$moreCaption;
		$assignObj2->productName = $productName;
		$assignObj2->Icon = $iconArr[2];
		$assignObj2->bgColor = $captionBgColor;
		$assignObj2->fontColor = $captionFontColor;
		$assignObj2->subCategoryList = array();
		$assignObj2->menuId = $row["MenuId"];
		$assignObj2->locationId = $row["LocationId"];
		$assignObj2->startDate = $row["StartDate"];
		$assignObj2->endDate = $row["EndDate"];
		$assignObj2->assignId = $row["MappingId"];
		$assignObj2->name = $row["Name"];
		$assignObj2->latlong = $row["GeoCoordinates"];
		$assignObj2->GeoFence = $GeoFence ;
		$assignObj2->GeoCoordinate = $GeoCoordinate ;
		$assignObj2->activityId = '';
		$assignObj2->status = '';
		$assignObj2->uniqueId = $row["MappingId"];
		$assignObj2->sampleSize = $sampleSize;
		$assignObj2->isDataSend = $isDataSend;
		$assignObj2->checkpointId = $row['CheckpointId'];
		$assignObj2->value = [];

		array_push($wrappedListArray,$assignObj2);
				
		// array_push($assignArray2,$assignObj2);
		// $assignObj1->captionList = $assignArray2;
		
		// array_push($assignArray1,$assignObj1);			
		// $assignObj->subCategoryList = $assignArray1;
		
		// array_push($wrappedListArray,$assignObj);
	}
}


// $pendingSql = "SELECT mp.ActivityId,mp.MenuId,mp.LocationId,l.Name,l.GeoCoordinates,mp.StartDate,mp.EndDate,
		// m.Category,m.SubCategory,m.Caption,m.Icons,m.CheckpointId,m.Colors,fa.FlowCheckpointId,h.Status FROM FlowActivityMaster fa join TransactionHDR h on fa.ActivityId = h.ActivityId and fa.Status = h.Status join Mapping mp on h.ActivityId = mp.ActivityId join Menu m on (mp.MenuId = m.MenuId) left join Location l on (mp.LocationId = l.LocationId) where find_in_set('$empId', fa.EmpId) <> 0";

$pendingSql = "SELECT mp.ActivityId,mp.MenuId,mp.LocationId,l.Name,l.GeoCoordinates,mp.StartDate,mp.EndDate,
		m.Category,m.SubCategory,m.Caption,m.Icons,m.CheckpointId,m.Colors,fa.FlowCheckpointId,h.Status,mp.IR_Id, (case when m.SampleSize is not null then m.SampleSize else ir.SampleSize end) as SampleSize, concat(im.ItemName,' - ',im.SubItemName) as ProductName FROM FlowActivityMaster fa join TransactionHDR h on fa.ActivityId = h.ActivityId and fa.Status = h.Status join Mapping mp on h.ActivityId = mp.ActivityId and mp.IsActive=1 join Menu m on (mp.MenuId = m.MenuId) left join Location l on (mp.LocationId = l.LocationId) left join InsReqMaster ir on mp.IR_Id = ir.IR_Id left join ItemMaster im on ir.OfferItem = im.ItemId where find_in_set('$empId', fa.EmpId) <> 0 and fa.FlowActivityId is null";

// echo $pendingSql;

$pendingQuery=mysqli_query($conn,$pendingSql);
while($pendingRow = mysqli_fetch_assoc($pendingQuery)){
	$pendingActId = $pendingRow["ActivityId"];
	$sampleSize = $pendingRow["SampleSize"] == null ? "1" : $pendingRow["SampleSize"];
	$productName = $pendingRow["ProductName"] == null ? "" : $pendingRow["ProductName"];

	$fillActArr=array();
	$firstObj = array(
		'flowChkId' => $pendingRow["CheckpointId"],
		'flowActId' => $pendingRow["ActivityId"]
	);
	array_push($fillActArr, $firstObj);

	$fillSql="SELECT `FlowCheckpointId`, `FlowActivityId` FROM `FlowActivityMaster` where `ActivityId`=$pendingActId and `FlowActivityId` is not null";
	$fillQuery=mysqli_query($conn,$fillSql);
	while($fillRow = mysqli_fetch_assoc($fillQuery)){
		$fillObj = array(
			'flowChkId' => $fillRow["FlowCheckpointId"],
			'flowActId' => $fillRow["FlowActivityId"]
		);
		array_push($fillActArr, $fillObj);
	}
	$nonFillObj = array(
		'flowChkId' => $pendingRow["FlowCheckpointId"],
		'flowActId' => 0
	);
	array_push($fillActArr, $nonFillObj);
	$colors = $pendingRow["Colors"];
	$colorsExplode = explode(":", $colors);

	$iconArr = explode(",",$pendingRow['Icons']);

	$cat = $pendingRow["Category"];
	$sub = $pendingRow["SubCategory"];
	$caption = $pendingRow["Caption"];

	// $aa = $pendingRow["GeoFence"];
	$aa = "0/0:100";
	$aaExplode = explode(":", $aa);
	$GeoCoordinate = $aaExplode[0];
	if($GeoCoordinate == ""){
		$GeoCoordinate = null;
	}
	$GeoFence = $aaExplode[1];
	if($GeoFence == ""){
		$GeoFence = null;
	}

	// for all todo checklist geofence..
	if($GeoFence == null)
		$GeoFence = $configGeoFence;

	$moreCaption="";
	$irId = $pendingRow["IR_Id"];
	if($irId !=0){
		$moreCaption .= " - IR ".$irId;
	}

	$pendingObj = new StdClass;
	$pendingObj->irId = $irId;
	if($cat != '' && $sub == '' && $caption == ''){
		$pendingObj->Caption = $cat.$moreCaption;
		$pendingObj->productName = $productName;
		$pendingObj->Icon = $iconArr[0];
		$catColors = $colorsExplode[0];

		$catColorsExplode = explode(",", $catColors);
		$pendingObj->bgColor = $catColorsExplode[0];
		$pendingObj->fontColor = $catColorsExplode[1];
		$pendingObj->subCategoryList = array();
	}
	else if($cat !='' && $sub != '' && $caption == ''){
		$pendingObj->Caption = $sub.$moreCaption;
		$pendingObj->productName = $productName;
		$pendingObj->Icon = $iconArr[1];

		$subCatColors = $colorsExplode[1];
		$subCatColorsExplode = explode(",", $subCatColors);
		$pendingObj->bgColor = $subCatColorsExplode[0];
		$pendingObj->fontColor = $subCatColorsExplode[1];
	}
	else if($cat != '' && $sub != '' && $caption != ''){
		$pendingObj->Caption = $caption.$moreCaption;
		$pendingObj->productName = $productName;
		$pendingObj->Icon = $iconArr[2];

		$captionColors = $colorsExplode[2];
		$captionColorsExplode = explode(",", $captionColors);
		$pendingObj->bgColor = $captionColorsExplode[0];
		$pendingObj->fontColor = $captionColorsExplode[1];
	}
	
	$pendingObj->menuId = $pendingRow["MenuId"];
	$pendingObj->locationId = $pendingRow["LocationId"];
	$pendingObj->startDate = $pendingRow["StartDate"];
	$pendingObj->endDate = $pendingRow["EndDate"];
	$pendingObj->assignId = "";
	$pendingObj->name = $pendingRow["Name"];
	$pendingObj->latlong = $pendingRow["GeoCoordinates"];
	$pendingObj->GeoFence = $GeoFence ;
	$pendingObj->GeoCoordinate = $GeoCoordinate ;
	$pendingObj->activityId = $pendingActId;
	$pendingObj->status = $pendingRow["Status"];
	$pendingObj->uniqueId = $pendingActId;
	$pendingObj->sampleSize = $sampleSize;
	$pendingObj->isDataSend = '';
	getInfiniteLevelCheckpoints($fillActArr,$pendingObj);


	array_push($wrappedListArray,$pendingObj);

}
// echo json_encode($wrappedListArray);

$code = 0;
if(count($wrappedListArray) !=0){
	$code=200;
}
else{
	$code=403;
}
$output=array(
	'code'=>$code,
	'todo'=>$wrappedListArray
);
echo json_encode($output);

// file_put_contents('/var/www/trinityapplab.in/html/AzurePower/api/log/todo_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
?>

<?php
function getInfiniteLevelCheckpoints($fillActArr,$pendingObj){
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
			//  (Select d.ChkId,d.Value as answer from TransactionDTL d
			//  where d.ActivityId = '$flowActId' and d.DependChkId = 0
			//  )r1
			//  right join 
			//  (Select c.* from Checkpoints c
			//  where c.CheckpointId in ($filledCpString)
			//  ) r2 on (r1.ChkId = r2.CheckpointId)";
			 // echo $apFilledCpSql;

			 $apFilledCpSql = "Select r2.*,r1.* 
			 from
			 (Select d.SRNo,d.ChkId,d.Value as answer,d.SampleNo from TransactionDTL d
			 where d.ActivityId = '$flowActId' and d.DependChkId = 0
			 )r1
			 right join 
			 (Select c.* from Checkpoints c
			 where c.CheckpointId in ($filledCpString)
			 ) r2 on (r1.ChkId = r2.CheckpointId) order by field(r1.ChkId, $filledCpString), r1.SampleNo";

			$nLevelFilledQuery=mysqli_query($conn,$apFilledCpSql);
			while($apfcp = mysqli_fetch_assoc($nLevelFilledQuery)){
				$apfcpObj = new StdClass;
				$apfcpObj->Chkp_Id = $apfcp['CheckpointId'];
				$apfcpObj->editable = '0';
				if($apfcp['answer'] != null){
					$apfcpObj->value = $apfcp['answer'];
				}
				else{
					$apfcpObj->value = "";
				}
				$apfcpObj->sampleNo=$apfcp["SampleNo"] == null ? "1" : $apfcp["SampleNo"];
				
				$apfdpArray = array();
				if($apfcp['Dependent'] == "1"){
					// $apfdpSql = " Select r1.*,c.* from
					// 				(Select d.ChkId,d.Value as answer from TransactionDTL d
					// 				where d.ActivityId = '$flowActId' and d.DependChkId = (".$apfcp['CheckpointId'].")
					// 				) r1
					// 				join Checkpoints c on (r1.ChkId = c.CheckpointId)";

					$apfdpSql = " Select r1.*,c.* from
									(Select d.SRNo,d.ChkId,d.Value as answer,d.SampleNo from TransactionDTL d
									where d.ActivityId = '$flowActId' and d.DependChkId = (".$apfcp['CheckpointId'].")
									) r1
									join Checkpoints c on (r1.ChkId = c.CheckpointId) order by r1.SRNo";
									
					$apfdpQuery = mysqli_query($conn,$apfdpSql);
					while($apfdp = mysqli_fetch_assoc($apfdpQuery)){
						$apfdpObj = new StdClass;
						$apfdpObj->Chkp_Id = $apfcp['CheckpointId']."_".$apfdp['CheckpointId'];
						$apfdpObj->editable = '0';
						$apfdpObj->value = $apfdp['answer'];
						$apfdpObj->sampleNo = $apfdp['SampleNo'] == null ? "1" : $apfdp['SampleNo'];
						array_push($apfdpArray,$apfdpObj);
					}
				}
				$apfcpObj->Dependents = $apfdpArray;
				array_push($apcpArray,$apfcpObj);
			}
		}
		else{
			$flowChkId = str_replace(":",",",$flowChkId);
			$nonFillCpSql = "Select c.* from Checkpoints c where c.CheckpointId in ($flowChkId)";
			
			$apapprovercpQuery=mysqli_query($conn,$nonFillCpSql);
			while($apcp = mysqli_fetch_assoc($apapprovercpQuery)){
				$apcpObj = new StdClass;
				$apcpObj->Chkp_Id = $apcp['CheckpointId'];
				$apcpObj->editable = $apcp['Editable'];
				$apcpObj->value = "";
				$apcpObj->sampleNo = $apcp['SampleSize'] == null ? "1" : $apcp['SampleSize'];
				$apdpArray = array();
				if($apcp['Dependent'] == "1"){
					$apcplogicArray = explode(":",trim($apcp['Logic']," "));
					$apcplogicString = "";
					for($apcpl=0;$vcpl< count($apcplogicArray);$apcpl++){
						if($apcpl == 0  && $apcplogicArray[$apcpl] != null && $apcplogicArray[$apcpl] != ""){
							$apcplogicString .= $apcplogicArray[$apcpl];
						}
						else if($apcplogicArray[$apcpl] != null && $apcplogicArray[$apcpl] != ""){
							$apcplogicString .= ",".$apcplogicArray[$apcpl];
						}
						
					}
					$apdpSql = " Select c.* from
									   Checkpoints c where c.CheckpointId in ($apcplogicString)";
									
					$apdpQuery = mysqli_query($conn,$apdpSql);
					while($apdp = mysqli_fetch_assoc($apdpQuery)){
						$apdpObj = new StdClass;
						$apdpObj->Chkp_Id = $apdp['CheckpointId'];
						$apdpObj->editable = $apdp['Editable'];
						$apdpObj->value = "";
						$apcpObj->sampleNo = $apdp['SampleSize'] == null ? "1" : $apdp['SampleSize'];
						array_push($apdpArray,$apdpObj);
					}
				}
				$apcpObj->Dependents = $apdpArray;
				array_push($apcpArray,$apcpObj);
			}
		}
	}

	$pendingObj->checkpointId = $allChkId;
	$cpId = $allChkId;

	$apisDataSend = "";
	$apcpIdArray = explode(":",$cpId);
	for($apcpId = 0; $apcpId < count($apcpIdArray); $apcpId++){
		if($apcpId == 0){
			$apisDataSend .= "0";
		}
		else if($apcpId == count($apcpIdArray)-1){
			$apisDataSend .= ":1";
		}
		else{
			$apisDataSend .= ":0";
		}	
	}
	$pendingObj->isDataSend = $apisDataSend;
	$pendingObj->value = $apcpArray;
}
?>