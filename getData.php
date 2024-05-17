<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$selectType = $_REQUEST["selectType"];

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
if($selectType == "items"){
	$sql = "SELECT distinct `ItemName`, `SampleType` FROM `ItemMaster` where `IsActive`=1 and `SampleType` is not null";
	$query = mysqli_query($conn,$sql);
	$itemList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$itemName = $row["ItemName"];
		$sampleType = $row["SampleType"];
		$itemJson = array('itemName' => $itemName, 'sampleType' => $sampleType);
		array_push($itemList, $itemJson);
	}

	$dataList = array();
	for($i=0;$i<count($itemList);$i++){
		$itemName = $itemList[$i]["itemName"];
		$sampleType = $itemList[$i]["sampleType"];
		$sql = "SELECT `ItemId` as `itemId`, `SubItemName` as `subItemName` FROM `ItemMaster` where `ItemName`='$itemName' and `IsActive`=1";
		$query = mysqli_query($conn,$sql);
		$subItemList = array();
		while ($row = mysqli_fetch_assoc($query)) {
			array_push($subItemList, $row);
		}

		$itemJson = array(
			'itemId' => $itemName, 
			'itemName' => $itemName, 
			'sampleType' => explode(",", $sampleType),
			'subItemList' => $subItemList
		);
		array_push($dataList, $itemJson);
	}
	echo json_encode($dataList);
}
else if($selectType == "allRole"){
	$sql = "SELECT `RoleId` as `roleId`, `Role` as `roleName` FROM `RoleMaster` where `CreateBy_TenantId`=1";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "inspectionRequest"){
	$filterSql = "";
	if($loginEmpRoleId == 2){
			$filterSql .= "and ir.`CreateBy`='$loginEmpId' ";
	}
	else if($loginEmpRoleId == 4){
		$filterSql .= "and ir.`TPI`='$loginEmpId' ";
	}
	else if($loginEmpRoleId == 5){
		$filterSql .= "and ir.`TPI_Auditor`='$loginEmpId' ";
	}

	// $sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` WHERE 1=1 order by ir.`Id` desc";
	$sql = "SELECT ir.`IR_Id` as `irId`, ir.`ProjectName` as `projectName`, ir.`PO_No` as `poNo`, ir.`PO_Date` as `poDate`, ir.`LotNo` as `lotNo`, im.`SubItemName` as `offerItem`, ir.`OfferQty` as `offerQty`, ir.`ReadinessReport` as `readinessReport`, ir.`DimensionalReport` as `dimensionalReport`, ir.`Photograph` as `photograph`, ir.`ItemForInspection` as `itemForIns`, ir.`InspectionLocation` as `insLocation`, ir.`InspectionDate` as `insDate`, `InspectionCloseDate` as `insCloseDate`, ir.`Status` as `status`, irs.`StatusTxt` as `statusTxt`, (case when ir.`TPI` is null then '-' else e.`SpocPerson` end) as `tpi`, (case when ir.`TPI_Auditor` is null then '-' else e1.`SpocPerson` end) as `tpiAuditor`, ir.`AuditDate` as `irCompletionDate`, ir.`ProbDesc` as `probDesc`, ir.`DefectPhoto` as `defectPhoto`, ir.`ImmeCorrecDet` as `immeCorrecDet`, ir.`DefineAndVerifyRootCause` as `defineAndVerifyRootCause`, ir.`ValidationCause` as `validationCause`, ir.`CorrectiveActions` as `correctiveActions`, `TargetDate` as `targetDate` FROM `InsReqMaster` ir join `ItemMaster` im on ir.OfferItem = im.ItemId join `IR_Status` irs on ir.`Status` = irs.`Status` left join `Employees` e on ir.`TPI` = e.`EmpId` left join `Employees` e1 on ir.`TPI_Auditor` = e1.`EmpId` WHERE 1=1 $filterSql order by ir.`Id` desc";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "employees"){
	// $sql = "SELECT e.Id as `id`, e.EmpId as `empId`, e.Name as `name`, e.Mobile as `mobile`, e.EmailId as `emailId`, e.Zone as `zone`, `CV` as `cv`, e.RoleId as `roleId`, r.Role as `role`, e.SpocPerson as `spocPerson`, e.SampleType as `sampleType`, e.IsActive as `isActive`, (case when e.IsActive=1 then 'Active' when e.IsActive=2 then 'Pending' when e.IsActive=3 then 'Rejected' else 'Deactive' end) as `activeStatus`, e.`ActionDate` as `actionDate`, e.`ActionRemark` as `actionRemark` FROM Employees e join RoleMaster r on e.RoleId = r.RoleId order by e.Id desc";

	$sql = "SELECT e.Id as `id`, e.EmpId as `empId`, (case when e.`RoleId` = 5 then e1.`Name` else e.`Name` end) as `name`, e.Mobile as `mobile`, e.EmailId as `emailId`, e1.`Name` as 'rmName', e.Zone as `zone`, e.`CV` as `cv`, e.RoleId as `roleId`, r.Role as `role`, e.SpocPerson as `spocPerson`, e.SampleType as `sampleType`, e.IsActive as `isActive`, (case when e.IsActive=1 then 'Active' when e.IsActive=2 then 'Pending' when e.IsActive=3 then 'Rejected' else 'Deactive' end) as `activeStatus`, e.`ActionDate` as `actionDate`, e.`ActionRemark` as `actionRemark`, e.`ItemName` as `itemName` FROM Employees e join RoleMaster r on e.RoleId = r.RoleId left join Employees e1 on e.RMId=e1.EmpId order by e.Id desc";

	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		unset($row["rmName"]);
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "tpiAuditor"){
	$recordType = $jsonData->recordType;
	$filterSql ="";
	if($recordType == ""){
		$filterSql .= "and `IsActive`=1 ";
	}
	if($loginEmpRoleId == 1){

	}
	else{
		$filterSql .= "and `RMId`='$loginEmpId' ";
	}
	
	$sql = "SELECT `EmpId` as `empId`, `SpocPerson` as `name`, `Mobile` as `mobile`, `EmailId` as `emailId`, `DOB` as `dob`, `AadharNo` as `aadharNo`, `ProfilePic` as `profilePic`, `CV` as `cv`, (case when `IsActive`=1 then 'Active' when `IsActive`=2 then 'Pending' when `IsActive`=3 then 'Rejected' else 'Deactive' end) as `activeStatus`, `ActionRemark` as `actionRemark`, `ActionDate` as `actionDate` FROM `Employees` where 1=1 and `RoleId`=5 $filterSql order by `Id` desc";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "tpi"){
	$sql = "SELECT `EmpId` as `empId`, `Name` as `name`, `Mobile` as `mobile`, `EmailId` as `emailId` FROM `Employees` where 1=1 and `RoleId`=4 and `IsActive`=1 order by `Id` desc";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "scar"){
	$recordType= $jsonData->recordType; // all, pending
	$filterSql="";
	// Admin
	if($loginEmpRoleId == 1){}
	// Azure SQT
	// else if($recordType == "pending" && $loginEmpRoleId == 3){
	else if($loginEmpRoleId == 3){
		$filterSql .= "and sm.Action in (0,1) ";
	}
	// Vendor
	// else if($recordType == "pending" && $loginEmpRoleId == 5){
	else if($loginEmpRoleId == 5){
		$filterSql .= "and sm.VendorId='$loginEmpId' and sm.Action in (1,3,4) ";
	}
	$sql="SELECT sm.Id as `scarId`, sm.ActivityId as `activityId`, sm.IR_Id as `irId`, e.SpocPerson as `name`, a.MobileDateTime as `submitDate`, sm.Action as `status`, ss.StatusTxt as `statusTxt`, m.CheckpointId as `checkpointId`, sm.Remark as `sqtRemark`, sm.ActionDate as `sqtActionDate`, sm.Remark1 as `vendorRemark`, sm.ActionDate1 as `vendorActionDate`, ir.ProjectName as `projectName`, ir.InspectionDate as `inspectionDate`, ir.LotNo as `lotNo` from ScarMaster sm join ScarStatus ss on sm.Action=ss.Action join Activity a on sm.ActivityId=a.ActivityId and a.Event='Submit' join Employees e on a.EmpId=e.EmpId join Menu m on a.MenuId=m.MenuId left join InsReqMaster ir on sm.IR_Id = ir.IR_Id where 1=1 $filterSql ORDER by sm.Id desc";
	// echo $sql;
	$query = mysqli_query($conn,$sql);
	$resultList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$actId = $row["activityId"];
		$checkpoint = $row["checkpointId"];
		$dataList = array();
		$detSql = "SELECT d.SRNo as `srNo`, c.Description as `description`, d.Value as `value`, c.TypeId as `typeId`, d.SampleNo as `sampleNo` FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$actId order by field(d.ChkId, $checkpoint), d.SampleNo";
		$detQuery = mysqli_query($conn,$detSql);
		while ($detRow = mysqli_fetch_assoc($detQuery)) {
			array_push($dataList, $detRow);
		}

		$row["dataList"] = $dataList;
		unset($row["checkpointId"]);
		array_push($resultList, $row);
	}
	echo json_encode($resultList);
}
else if($selectType == "mrn"){
	$recordType= $jsonData->recordType; // all, pending
	$filterSql="";
	// Admin
	if($loginEmpRoleId == 1){}
	// Azure SQT
	// else if($recordType == "pending" && $loginEmpRoleId == 3){
	else if($loginEmpRoleId == 3){
		$filterSql .= "and mrn.Action in (0,1) ";
	}
	// Vendor
	// else if($recordType == "pending" && $loginEmpRoleId == 5){
	else if($loginEmpRoleId == 5){
		$filterSql .= "and mrn.VendorId='$loginEmpId' and mrn.Action in (1,3,4) ";
	}
	$sql="SELECT mrn.Id as `mrnId`, mrn.IR_Id as `irId`, mrn.ActivityId as `activityId`, e.SpocPerson as `name`, ms.StatusTxt as `statusTxt`, a.MobileDateTime as `submitDate`, m.CheckpointId as `checkpointId`, ir.ProjectName as `projectName`, ir.InspectionDate as `inspectionDate`, ir.LotNo as `lotNo` from MrnMaster mrn join MrnStatus ms on mrn.Action = ms.Action join Activity a on mrn.ActivityId=a.ActivityId and a.Event='Submit' join Employees e on a.EmpId=e.EmpId join Menu m on a.MenuId=m.MenuId left join InsReqMaster ir on mrn.IR_Id = ir.IR_Id where 1=1 $filterSql ORDER by mrn.Id desc";
	// echo $sql;
	$query = mysqli_query($conn,$sql);
	$resultList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$actId = $row["activityId"];
		$checkpoint = $row["checkpointId"];
		$dataList = array();
		$detSql = "SELECT d.SRNo as `srNo`, c.Description as `description`, d.Value as `value`, c.TypeId as `typeId` FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$actId order by field(d.ChkId, $checkpoint), d.SampleNo";
		$detQuery = mysqli_query($conn,$detSql);
		while ($detRow = mysqli_fetch_assoc($detQuery)) {
			array_push($dataList, $detRow);
		}

		// $row["dataList"] = $dataList;

		$dataJson = array('mrnId' => $row["mrnId"], 'irId' => $row["irId"], 'name' => $row["name"], 'statusTxt' => $row["statusTxt"], 'submitDate' => $row["submitDate"], 'projectName' => $row["projectName"], 'inspectionDate' => $row["inspectionDate"], 'lotNo' => $row["lotNo"], 'dataList' => $dataList);
		array_push($resultList, $dataJson);
	}
	echo json_encode($resultList);
}
else if($selectType == "travelExpense"){
	$recordType= $jsonData->recordType; // all, pending
	$filterSql="";
	if($recordType == "pending"){
		$filterSql .= "and sm.Action=0 ";
	}
	// $sql="SELECT sm.Id as `expenseId`, sm.ActivityId as `activityId`, sm.IR_Id as `irId`, e.SpocPerson as `name`, a.MobileDateTime as `submitDate`, sm.Action as `status`, (case when sm.Action=0 then 'Pending' when sm.Action=1 then 'Approved' else 'Rejected' end) as `statusTxt`, m.CheckpointId as `checkpointId`, sm.Remark as `remark`, sm.ActionDate as actionDate from ExpenseMaster sm join Activity a on sm.ActivityId=a.ActivityId and a.Event='Submit' join Employees e on a.EmpId=e.EmpId join Menu m on a.MenuId=m.MenuId where 1=1 $filterSql ORDER by sm.Id desc";
	$sql = "SELECT sm.Id as `expenseId`, sm.ActivityId as `activityId`, sm.IR_Id as `irId`, e.SpocPerson as `name`, a.MobileDateTime as `submitDate`, sm.Action as `status`, (case when sm.Action=0 then 'Pending' when sm.Action=1 then 'Approved' else 'Rejected' end) as `statusTxt`, m.CheckpointId as `checkpointId`, sm.Remark as `remark`, sm.ActionDate as actionDate, ir.ProjectName as projectName, ir.InspectionDate as inspectionDate, ir.LotNo as lotNo from ExpenseMaster sm join Activity a on sm.ActivityId=a.ActivityId and a.Event='Submit' join Employees e on a.EmpId=e.EmpId join Menu m on a.MenuId=m.MenuId left join InsReqMaster ir on sm.IR_Id = ir.IR_Id where 1=1 $filterSql ORDER by sm.Id desc";
	$query = mysqli_query($conn,$sql);
	$resultList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$actId = $row["activityId"];
		$checkpoint = $row["checkpointId"];
		$dataList = array();
		$detSql = "SELECT d.SRNo as `srNo`, c.Description as `description`, d.Value as `value`, c.TypeId as `typeId`, d.SampleNo as `sampleNo` FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$actId order by field(d.ChkId, $checkpoint), d.SampleNo";
		$detQuery = mysqli_query($conn,$detSql);
		while ($detRow = mysqli_fetch_assoc($detQuery)) {
			array_push($dataList, $detRow);
		}

		$row["dataList"] = $dataList;

		unset($row["checkpointId"]);
		array_push($resultList, $row);
	}
	echo json_encode($resultList);
}
else if($selectType == "portalMenu"){
	$filterSql="";
	if($loginEmpRoleId !=1){
		$filterSql .= "and find_in_set($loginEmpRoleId,pm.RoleId) <> 0 ";
	}
	// $sql = "SELECT `RouterLink` as `routerLink`, `PageName` as `pageName`, `MenuName` as `menuName` FROM `PortalMenu` where `IsActive`=1 $filterSql ORDER by `DisplayOrder`";

	$sql = "SELECT pm.Id as portalId, pm.RouterLink as `routerLink`, pm.PageName as `pageName`, pm.MenuName as `menuName`, (case when pc.MenuColumns is null then '' else pc.MenuColumns end) as `menuColumns` FROM PortalMenu pm left join PortalColumn pc on pm.Id=pc.PortalId and pc.EmpId='$loginEmpId' where pm.IsActive=1 $filterSql ORDER by pm.DisplayOrder";
	// echo $sql;
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "project"){
	// $sql = "SELECT `Id` as `id`, `ProjectName` as `projectName`, `CreateDate` as createDate, (case when `IsActive`=0 then 'Disabled' when `IsActive`=1 then 'Enabled' end) as `status` FROM `ProjectMaster` order by `Id` desc ";
	// $sql = "SELECT `Id` as `id`, `ProjectName` as `projectName`, `Capacity` as `capacity`, `SPV_Name` as `spvName`, `Address` as `address`, `CreateDate` as createDate, (case when `IsActive`=0 then 'Disabled' when `IsActive`=1 then 'Enabled' end) as `status` FROM `ProjectMaster` order by `IsActive` desc ";
	$sql = "SELECT pm.Id as `id`, pm.ProjectName as `projectName`, pm.Capacity as `capacity`, pm.SPV_Name as `spvName`, pm.Address as `address`, e.SpocPerson as ctName, e1.SpocPerson as sqName, pm.CreateDate as createDate, (case when pm.IsActive=0 then 'Disabled' when pm.IsActive=1 then 'Enabled' end) as `status` FROM ProjectMaster pm left join Employees e on pm.CT_EmpId=e.EmpId left join Employees e1 on pm.SQ_EmpId=e1.EmpId order by pm.IsActive desc ";

	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "po"){
	$filterSql="";
	if($loginEmpRoleId != 3){
		$filterSql .= "and `CreateBy`='$loginEmpId' ";
	}
	$sql = "SELECT * FROM `PO_Master` where 1=1 $filterSql and `IsActive`=1 order by `Id` desc";
	// echo $sql;
	$query = mysqli_query($conn,$sql);
	$poList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$id = $row["Id"];
		$poNo = $row["PO_No"];
		$poDate = $row["PO_Date"];
		$poAttachment = $row["PO_Attachment"];
		$noOfItems = $row["NoOfItems"];

		$poItemList = array();
		$poItemSql = "SELECT pi.ItemId as `itemId`, im.subItemName as `itemName`, pi.Qty as `itemQty` FROM PO_Items pi join ItemMaster im on pi.ItemId = im.ItemId where pi.PO_No='$poNo'";
		$poItemQuery = mysqli_query($conn,$poItemSql);
		while ($poItemRow = mysqli_fetch_assoc($poItemQuery)) {
			array_push($poItemList, $poItemRow);
		}

		$poJson = array(
			'id' => $id, 'poNo' => $poNo, 'poDate' => $poDate, 'poAttachment' => $poAttachment, 
			'noOfItems' => $noOfItems,
			'poItemList' => $poItemList
		);
		array_push($poList, $poJson);
	}
	echo json_encode($poList);
}
else if($selectType == "mdcc"){
	$filterSql = "";
	if($loginEmpRoleId != 1){
		$filterSql .= "and pm.CT_EmpId='$loginEmpId'";
	}
	// $sql = "SELECT `IR_Id` as irId, `MDCC_No` as mdccNo, `OfferQty` as offerQty, `DeliveredQty` as deliveredQty, `RemainingQty` as remainingQty FROM `IR_MDCC` order by `Id` desc ";
	$sql = "SELECT md.IR_Id as irId, md.MDCC_No as mdccNo, ir.LotNo as lotNo, e.Name as vendorName, ir.ProjectName as projectName, pm.Address as siteAddress, ir.PO_No as poNo, im.SubItemName as itemDescription, md.OfferQty as offerQty, md.DeliveredQty as deliveredQty, md.RemainingQty as remainingQty, date_format(md.CreateDate,'%d-%m-%Y') as mdccDate FROM IR_MDCC md join InsReqMaster ir on md.IR_Id=ir.IR_Id join Employees e on ir.CreateBy=e.EmpId join ProjectMaster pm on ir.ProjectName=pm.ProjectName $filterSql join ItemMaster im on ir.OfferItem=im.ItemId order by md.Id desc";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$mdccNo = $row["mdccNo"];
		$diList = array();
		$diSql = "SELECT `Id` as `id`, `DI_No` as `diNo`, `DeliverQty` as `deliverQty`, `DeliverDate` as `deliverDate`, `Remark` as `remark`, date(`CreateDate`) as `diDate` FROM `MDCC_DI` where `MDCC_No`='$mdccNo'";
		$diQuery = mysqli_query($conn,$diSql);
		while ($diRow = mysqli_fetch_assoc($diQuery)) {
			array_push($diList, $diRow);
		}
		$row["diList"] = $diList;
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
else if($selectType == "mdccDi"){
	$sql = "SELECT `DI_No` as `diNo`, `MDCC_No` as `mdccNo`, `IR_Id` as `irId`, `LotNo` as `lotNo`, `ProjectName` as `projectName`, `SiteAddress` as `siteAddress`, `PO_No` as `poNo`, `MatDesc` as `matDesc`, `DeliverQty` as `qty`, `DeliverDate` as `deliverDate`, `Remark` as `remark`, `Status` as `status`, `StatusTxt` as `statusTxt` FROM `V_MDCC_DI` where `VendorId` = '$loginEmpId'";
	$query = mysqli_query($conn,$sql);
	$dataList = array();
	while ($row = mysqli_fetch_assoc($query)) {
		array_push($dataList, $row);
	}
	echo json_encode($dataList);
}
?>