<?php 
// $photograph = "https://photograph.jpg";
// $photograph1 = "https://photograph1.jpg";
// $photograph2 = "https://photograph2.jpg";
// $photograph3 = "https://photograph3.jpg";

// $allPhotoList = array();
// if($photograph !="")
// 	array_push($allPhotoList, $photograph);
// if($photograph1 !="")
// 	array_push($allPhotoList, $photograph1);
// if($photograph2 !="")
// 	array_push($allPhotoList, $photograph2);
// if($photograph3 !="")
// 	array_push($allPhotoList, $photograph3);

// $allPhotoUrl = implode(",", $allPhotoList);

// $irChkList = array();
// $irChkJson = array('chkpId' => 1, 'value' => 'abc');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 11, 'value' => '2024-04-23');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 2, 'value' => 'xyz');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 4, 'value' => 'Cable');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 5, 'value' => '32');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 10, 'value' => 'Noida');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 6, 'value' => 'https://readinessReport.com');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 7, 'value' => 'https://dimensionalReport.com');
// array_push($irChkList, $irChkJson);

// $irChkJson = array('chkpId' => 8, 'value' => $allPhotoUrl);
// array_push($irChkList, $irChkJson);
// $saveIrJson = array(
// 	'irId' => 0, 'empId' => '1234', 'mId' => 1, 
// 	'lId' => 1, 'event' => 'Submit', 'geolocation' => '0/0', 
// 	'mobiledatetime' => '2024-04-23 12:05:33', 'timeStamp' => '1234567890', 
// 	'checklist' => $irChkList, 'assignId' => '', 'activityId' => ''
// );
// require 'SaveIrCheckpointClass.php';
// $saveIrClassObj = new SaveIrCheckpointClass();
// $saveIrClassObj->saveIrCheckpoint($saveIrJson);

$d = date('YmdHis', time());
echo $d;

?>