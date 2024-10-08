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
$irId = $jsonData->irId;

$sql="SELECT m.Category, m.SubCategory, m.Caption, m.CheckpointId, ma.ActivityId, date_format(a.MobileDateTime,'%d-%m-%Y %H:%i:%s') as `MobileDateTime`, (case when m.SampleSize is null then ir.SampleSize else m.SampleSize end) as SampleSize FROM Mapping ma join Menu m on ma.MenuId=m.MenuId left join InsReqMaster ir on ma.IR_Id=ir.IR_Id left join Activity a on ma.ActivityId=a.ActivityId where ma.IR_Id='$irId' and ma.MenuId != 1 order by ma.MappingId";
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

	$dataList = array();
	if($actId != 0){
		$checkpoint = $row["CheckpointId"];
		$sampleSize = $row["SampleSize"];
		$auditDate = $row["MobileDateTime"];

		$allChp = str_replace(":", ",", $checkpoint);
		$chpList = explode(",", $allChp);

		
		for($i=0;$i<count($chpList);$i++){
			$loopChk = $chpList[$i];

			for($j=1;$j<=$sampleSize;$j++){
				$detSql = "SELECT d.SRNo as `srNo`, c.Description as `description`, d.Value as `value`, c.TypeId as `typeId`, d.SampleNo as `sampleNo` FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$actId and d.ChkId=$loopChk and d.SampleNo=$j";
				$detQuery = mysqli_query($conn,$detSql);
				$detRowcount = mysqli_num_rows($detQuery);
				if($detRowcount !=0){
					$detRow = mysqli_fetch_assoc($detQuery);
					array_push($dataList, $detRow);
				}
				else{
					$chpSql = "SELECT ($j*$loopChk) as `srNo`, `Description` as `description`, '' as `Value`, `TypeId` as `typeId`, $j as `sampleNo` FROM `Checkpoints` where `CheckpointId`=$loopChk";
					$chpQuery = mysqli_query($conn,$chpSql);
					$chpRow = mysqli_fetch_assoc($chpQuery);
					array_push($dataList, $chpRow);
				}
			}
		}
	}

	$resultJson = array(
		// 'category' => $cat.' - '.$subCat.' - '.$cap, 
		'caption' => $caption, 
		'sampleSize' => $sampleSize == null ? "1" : $sampleSize,
		'auditDate' => $auditDate == null ? "" : $auditDate,
		'dataList' => $dataList
	);
	array_push($resultList, $resultJson);
}
// echo json_encode($resultList);

$audSql = "SELECT (case when e.RoleId in (2,4,5) then e.SpocPerson else e.Name end) as name, s.StatusTxt as status, a.Remark as remark, a.AuditDate as auditDate FROM IR_Audit a join IR_Status s on a.AfterStatus=s.Status join Employees e on a.EmpId=e.EmpId WHERE a.IR_Id = '$irId' ORDER by a.Id";
$audQuery = mysqli_query($conn,$audSql);
$auditList = array();
while ($audRow = mysqli_fetch_assoc($audQuery)) {
	array_push($auditList, $audRow);
}

$output = array('resultList' => $resultList, 'auditList' => $auditList);
echo json_encode($output);



?>