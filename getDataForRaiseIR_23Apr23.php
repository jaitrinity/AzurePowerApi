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

// $sql = "SELECT * FROM `ScarMaster` where `VendorId`='$loginEmpId' and `Action` in (0,1)";
// $query = mysqli_query($conn,$sql);
// $rowCount = mysqli_num_rows($query);
// if($rowCount != 0){
// 	$output = array('scarInfo'=> 'SCAR availible, plz resolve SCAR first..', 'projectList' => [], 'poList' => []);
// 	echo json_encode($output);
// 	return;
// }

$sql = "SELECT `ProjectName` as `project` FROM `ProjectMaster` where `IsActive`=1 order by `Id` desc";
$query = mysqli_query($conn,$sql);
$projectList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($projectList, $row);
}

$sql = "SELECT * FROM `PO_Master` where `CreateBy`='$loginEmpId' and `IsActive`=1 order by `Id` desc";
$query = mysqli_query($conn,$sql);
$poList = array();
while ($row = mysqli_fetch_assoc($query)) {
	$poNo = $row["PO_No"];
	$poDate = $row["PO_Date"];
	$noOfItems = $row["NoOfItems"];

	$poItemList = array();
	$poItemSql = "SELECT pi.ItemId as `itemId`, im.subItemName as `itemName`, pi.Qty as `itemQty` FROM PO_Items pi join ItemMaster im on pi.ItemId = im.ItemId where pi.PO_No='$poNo'";
	$poItemQuery = mysqli_query($conn,$poItemSql);
	while ($poItemRow = mysqli_fetch_assoc($poItemQuery)) {
		$itemId = $poItemRow["itemId"];
		$itemQty = $poItemRow["itemQty"];
		$offer = "SELECT sum(`OfferQty`) as TotalOffer FROM `InsReqMaster` where `PO_No`='$poNo' and `OfferItem`=$itemId and `Status` not in ('IR_100', 'IR_103')";
		// echo $offer.'--';
		$offerQuery = mysqli_query($conn,$offer);
		$offerRow = mysqli_fetch_assoc($offerQuery);
		$totalOffer = $offerRow["TotalOffer"];
		$offerLimit = $totalOffer == null ? $itemQty : ($itemQty - $totalOffer);
		$poItemRow["offerLimit"] = $offerLimit;
		array_push($poItemList, $poItemRow);
	}

	$poJson = array(
		'poNo' => $poNo, 'poDate' => $poDate, 'noOfItems' => $noOfItems,
		'poItemList' => $poItemList
	);
	array_push($poList, $poJson);
}

$sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, `InspectionCloseDate` as `insCloseDate` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId WHERE `CreateBy`='$loginEmpId' and `Status`='IR_102' order by ir.`Id` desc";
$query = mysqli_query($conn,$sql);
$oldIrList = array();
while ($row = mysqli_fetch_assoc($query)) {
	array_push($oldIrList, $row);
}

$output = array('scarInfo' => '', 'projectList' => $projectList, 'poList' => $poList, 'oldIrList' => $oldIrList);
echo json_encode($output);

?>