<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "GET"){
	header("HTTP/1.1 405");
	$output = array('code' => 405, 'message' => 'Invalid method type');
	echo json_encode($output);
	return;
}
$noticationId = $_REQUEST["noticationId"];

$sql="UPDATE `Notification` set `IsRead`=1, `ReadDatetime`=current_timestamp where `Id`=$noticationId";
$stmt = $conn->prepare($sql);

$code = 0;
if($stmt->execute()){
	$code = 200;
	$message = "Success";
}
else{
	$code = 500;
	$message = "Something wrong";
}

header("HTTP/1.1 ".$code);
$output = array(
	'code' => $code, 
	'message' => $message
);
echo json_encode($output);

?>