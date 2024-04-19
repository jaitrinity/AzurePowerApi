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
$irId = $jsonData->irId;

$sql="SELECT m.Category, m.SubCategory, m.Caption, m.CheckpointId, ma.ActivityId, a.MobileDateTime, (case when m.SampleSize is null then ir.SampleSize else m.SampleSize end) as SampleSize FROM Mapping ma join Menu m on ma.MenuId=m.MenuId left join InsReqMaster ir on ma.IR_Id=ir.IR_Id left join Activity a on ma.ActivityId=a.ActivityId where ma.IR_Id=$irId";
$query = mysqli_query($conn,$sql);
$resultList = array();
while ($row = mysqli_fetch_assoc($query)) {
	$actId = $row["ActivityId"];
	$cat = $row["Category"];
	$subCat = $row["SubCategory"];
	$cap = $row["Caption"];
	$caption = "";
	if($cat != "" && $subCat == "" && $cap == ""){
		$caption = $cat;
	}
	else if($cat != "" && $subCat != "" && $cap == ""){
		$caption = $subCat;
	}
	else if($cat != "" && $subCat != "" && $cap != ""){
		$caption = $cap;
	}

	$checkpoint = $row["CheckpointId"];
	$sampleSize = $row["SampleSize"];
	$auditDate = $row["MobileDateTime"];


	$dataList = array();
	$detSql = "SELECT d.SRNo as `srNo`, c.Description as `description`, d.Value as `value`, c.TypeId as `typeId`, d.SampleNo as `sampleNo` FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$actId order by field(d.ChkId, $checkpoint), d.SampleNo";
	$detQuery = mysqli_query($conn,$detSql);
	while ($detRow = mysqli_fetch_assoc($detQuery)) {
		array_push($dataList, $detRow);
	}

	$resultJson = array(
		// 'category' => $cat.' - '.$subCat.' - '.$cap, 
		'caption' => $caption, 
		'sampleSize' => $sampleSize,
		'auditDate' => $auditDate == null ? "" : $auditDate,
		'dataList' => $dataList
	);
	array_push($resultList, $resultJson);
}
echo json_encode($resultList);

?>