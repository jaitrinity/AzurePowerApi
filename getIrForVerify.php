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
if($loginEmpRoleId == 4){
	$filterSql .= "and ir.`TPI`='$loginEmpId' ";
}
else if($loginEmpRoleId == 5){
	$filterSql .= "and ir.`TPI_Auditor`='$loginEmpId' ";
}

// $sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` WHERE 1=1 and irs.Status='IR_3'";

// $sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` WHERE 1=1 $filterSql order by ir.`IR_Id` desc";

$sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, `InspectionCloseDate` as `insCloseDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt`, (case when ir.`TPI` is null then '-' else e.`Name` end) as `tpi`, (case when ir.`TPI_Auditor` is null then '-' else e1.`Name` end) as `tpiAuditor`, ir.`AuditDate` as `irCompletionDate` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` left join `Employees` e on ir.`TPI` = e.`EmpId` left join `Employees` e1 on ir.`TPI_Auditor` = e1.`EmpId` WHERE 1=1 $filterSql order by ir.`Id` desc";
$query = mysqli_query($conn,$sql);
$dataList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($dataList, $row);
}
echo json_encode($dataList);

?>