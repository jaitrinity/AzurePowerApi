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

$mobile = $jsonData->mobile;
$sql = "SELECT * FROM `Employees` where `Mobile`='$mobile' and `IsActive`=1";
$query = mysqli_query($conn,$sql);
$rowCount = mysqli_num_rows($query);
if($rowCount == 0){
	$code = 404;
	$message = "Invalid mobile";
}
else{
	$randomOtp = rand(100000,999999);
	$msgStatus = sendOtp($mobile,$randomOtp);
	if($msgStatus){
		$sql = "UPDATE `Employees` set `OTP` = ?, `IsOTPExpired` = 0 where `Mobile` = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("is", $randomOtp, $mobile);
		if($stmt->execute()){
			$code = 200;
			$message = "OTP sent to mobile";
		}
		else{
			$code = 500;
			$message = "Something went wrong while sending OTP";
		}
	}
	else{
		$code = 500;
		$message = "Something went wrong while sending OTP";
	}
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);
?>

<?php
function sendOtp($mobile,$taskotp)
{
	//api for sending the otp
	$appName = "Azure Power";
	$message = "Your one time password (OTP) is ".$taskotp." for ".$appName." application.";
	$apikey = "ae6fa4-5cab56-4bc26d-caa56f-b27aab";
	$senderId = "TRIAPP";
	$route = "default";
	$st = true;
	$postData = array(
            'apikey' => $apikey,	
	    'dest_mobileno' => $mobile,
	    'message' => $message,
	    'senderid' => $senderId,
	    'route' => $route,
	    'response' => "Y",
	    'msgtype' => "TXT"
	);
	$url="http://www.smsjust.com/sms/user/urlsms.php";
	// init the resource
	$ch = curl_init();
	curl_setopt_array($ch, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_POST => true,
	    CURLOPT_POSTFIELDS => $postData
	    //,CURLOPT_FOLLOWLOCATION => true

	));
		//Ignore SSL certificate verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//get response
	$output = curl_exec($ch);
	//Print error if any
	if(curl_errno($ch))
	{
	    echo 'error:' . curl_error($ch);
		$st = false;
	}
	curl_close($ch);
	return $st;
	
}

?>