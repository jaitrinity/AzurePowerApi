<?php 

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

?>
<?php
$dir = date("M-Y-d");
if (!file_exists('/var/www/trinityapplab.in/html/AzurePower/api/files/'.$dir)) {
    mkdir('/var/www/trinityapplab.in/html/AzurePower/api/files/'.$dir, 0777, true);
}

$t=date("YmdHis");
$target_dir = "files/".$dir."/";

$activityId=$_REQUEST["transId"];
// $company=$_REQUEST["company"];
$chk_id=$_REQUEST["chk_id"];
// $depend_upon=$_REQUEST["depend_upon"];
$depend_upon=0;
// $caption=$_REQUEST["caption"];
$timestamp = $_REQUEST["timestamp"];
$latlong = $_REQUEST["latLong"];
$dateTime = $_REQUEST["dateTime"];
$sampleNo = $_REQUEST["sampleNo"];



$cpId = "";
$dependId = "";
$cpIdlist = explode("_",$chk_id);
$dIdlist = explode("_",$depend_upon);
if(count($cpIdlist) > 2){
	$cpId = $cpIdlist[1];
}
else{
	$cpId = $cpIdlist[0];
}
$dependId = $dIdlist[0];

$prevValue = "";
$fileName = $_FILES["attachment"]["name"];
$target_file = $target_dir."".$t.$fileName;
	
	
if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) 
{
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$link = $_SERVER['HTTP_HOST']; 
	$fileURL = "https://".$link."/".$parts[1]."/api/".$target_file;
	
	$selectQuery = "Select `Value`, `LatLong`, `Datetime` from `TransactionDTL` where `ActivityId` = '$activityId' and `ChkId` = '$cpId' and `SampleNo`=$sampleNo and `DependChkId` = '$dependId' and `Value` like 'https%'";
	// echo $selectQuery;
	$selectData = mysqli_query($conn,$selectQuery);
	$rowcount = mysqli_num_rows($selectData);
	if($rowcount > 0){
		$sr = mysqli_fetch_assoc($selectData);
		$prevValue = $sr['Value'];
		$prevLat_Long = $sr['LatLong'];
		$prevDatetime = $sr["Datetime"];
		$query = "Update TransactionDTL set `Value` = '$prevValue,$fileURL', `LatLong` = '$prevLat_Long:$latlong', `Datetime` = '$prevDatetime,$dateTime' where `ActivityId` = '$activityId' and ChkId = '$cpId' and `SampleNo`=$sampleNo and `DependChkId` = '$dependId'";	
	}
	else{
		// $query = "Update TransactionDTL set `Value` = '$fileURL', `LatLong` = '$latlong', `Datetime` = '$dateTime' where `ActivityId` = '$activityId' and `ChkId` = '$cpId' and `SampleNo`=$sampleNo and `DependChkId` = '$dependId'";	
		$query = "INSERT into `TransactionDTL`(`ActivityId`, `ChkId`, `Value`, `LatLong`, `Datetime`, `SampleNo`, `DependChkId`) Values ($activityId, $cpId, '$fileURL', '$latlong', '$dateTime', $sampleNo, $dependId)";	
	}
	
	mysqli_query($conn,$query);

	$arr = array('error' => '200','message'=>'Save Successfully!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>$fileURL);
	header('Content-Type: application/json');
	echo json_encode($arr);
} 
else 
{
	$arr =array('error' => '201','message'=>'Error!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>'');

	header('Content-Type: application/json');
	echo json_encode($arr);
}
?>