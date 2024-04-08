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
// if($loginEmpRoleId == 4){
	$filterSql .= "and ir.`Status`='IR_1' ";
// }

$sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId where 1=1 $filterSql order by ir.Id desc";
$query = mysqli_query($conn,$sql);
$dataList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($dataList, $row);
}
echo json_encode($dataList);
?>