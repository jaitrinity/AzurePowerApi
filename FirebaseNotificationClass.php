<?php 
class FirebaseNotificationClass{
	function sendNotification($multiToken, $title, $body, $image, $link){
		$API_ACCESS_KEY = "";
		$fcmUrl = 'https://fcm.googleapis.com/fcm/send';

		$notification = [
	            'title' => $title,
	            'body' => $body,
	            'image' => $image,
	            'link' => $link
	    ];
	    $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

	    $tokenList = explode(",", $multiToken);

	    $fcmNotification = [
	    	'registration_ids' => $tokenList,
	        // 'to'        => $token,
	        'notification' => $notification,
	        'data' => $extraNotificationData
	    ];

	    $headers = [
	        'Authorization: key=' .$API_ACCESS_KEY,
	        'Content-Type: application/json'
	    ];

		// echo json_encode($fcmNotification);

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$fcmUrl);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
	    $result = curl_exec($ch);

		if(curl_errno($ch))
		{
		    // echo 'error:' . curl_error($ch);
		}

	    curl_close($ch);

	    // echo $result;
	    return $result;
	}

	
}
?>
