<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$filterSql = "";
// Vendor
if($loginEmpRoleId == 2){
		$filterSql .= "and ir.`CreateBy`='$loginEmpId' ";
}
// TPI
else if($loginEmpRoleId == 4){
	$filterSql .= "and ir.`TPI`='$loginEmpId' ";
}
// TPI Auditor
else if($loginEmpRoleId == 5){
	$filterSql .= "and ir.`TPI_Auditor`='$loginEmpId' ";
}

$sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, `InspectionCloseDate` as `insCloseDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt`, (case when ir.`TPI` is null then '-' else e.`Name` end) as `tpi`, (case when ir.`TPI_Auditor` is null then '-' else e1.`Name` end) as `tpiAuditor`, ir.`AuditDate` as `irCompletionDate`, f.RoleId as actionRoleId, f.BeforeStatus as beforeStatus, f.AfterStatus as afterStatus, ir.`MaterialDispatchStatus` as `materialDispatchStatus`, ir.`Declarartion` as `declarartion`, ir.`Rework` as `rework`, ir.`ReworkDoc` as `reworkDoc`, ir.`TPI_Observation` as `tpiObservation`, ir.`SQT_Observation` as `sqtObservation` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` left join `Employees` e on ir.`TPI` = e.`EmpId` left join `Employees` e1 on ir.`TPI_Auditor` = e1.`EmpId` left join IR_Flow f on ir.Status=f.BeforeStatus and f.`RoleId`=$loginEmpRoleId WHERE 1=1 $filterSql order by ir.`Id` desc";
$query = mysqli_query($conn,$sql);
$dataList = array();
while ($row = mysqli_fetch_assoc($query)) {
	$actionRoleId = $row["actionRoleId"];
	$actionButtonArr = array();
	if($actionRoleId == $loginEmpRoleId){
		$afterButton = $row["afterStatus"];
		$abExp = explode(",", $afterButton);
		for($i=0;$i<count($abExp);$i++){
			$abExpLoop = $abExp[$i];
			$abExp2 = explode(":", $abExpLoop);
			$buttonJson = array(
				'button' => $abExp2[0],
				'status' => $abExp2[1]
			);
			array_push($actionButtonArr, $buttonJson);
		}
	}
	
	$row["actionButtonArr"] = $actionButtonArr;
	unset($row["actionRoleId"]);
	unset($row["afterStatus"]);
	array_push($dataList, $row);
}
echo json_encode($dataList);

?>