<?php 
class FirebaseNotificationClass{
	function sendNotification($multiToken, $title, $body, $image, $link){
		// $API_ACCESS_KEY = "AAAAI9Rhe0c:APA91bHlfE8ydlS-rAtgW1q7FDwgxqA-LTpBegR_ygkwq8TwyqR5VV7Ww6UCRr6glG3UvfLqwf8Sa_BKuO4xUh_smSmkwHS0TP7bOHEMYyiTI6C8iSAkQPhPDBkAbUdLsPT_8yGq445E";
		// $API_ACCESS_KEY = "AIzaSyBsLgxHo3g8mH5oLPvbaPN2JYADtKKLOEI";
		$API_ACCESS_KEY = "AAAAQ7WNkGA:APA91bHvsQhd-cbX9tK-V-b-CtBOceLbsI-heYt0OR6YWBEuuFYmw_7evpCxP5fNRyCLP1G_mI4RApjgaHRBxEyeYKsnDFstAo01aFemIXtyFTsxeUqFlmvGMNqT1biBESyNkukMtJWY";
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

	// function sendNotification($appName, $multiToken, $title, $body, $image, $link, $dataJson){
	// 	if($appName == "Restaurant"){
	// 		$API_ACCESS_KEY = "AAAAI9Rhe0c:APA91bHlfE8ydlS-rAtgW1q7FDwgxqA-LTpBegR_ygkwq8TwyqR5VV7Ww6UCRr6glG3UvfLqwf8Sa_BKuO4xUh_smSmkwHS0TP7bOHEMYyiTI6C8iSAkQPhPDBkAbUdLsPT_8yGq445E";
	// 		$android_channel_id = "restaurantAppId";
	// 	}
	// 	else if($appName == "Rider"){
	// 		$API_ACCESS_KEY = "AAAA5kEFoZ8:APA91bFiOwKF54UjjPlUPIaEFKpk6Bm3dw3B0SUtUcF4rmvaS3AveYfE8WvtbWbC_u5V4Mxow2wp8x4HOvtqC8wc6DvEmXs09GYTCcCJzX0ou_W0kD3JCTg5mnC8NX-VSfJs1uP79t3-";	
	// 		$android_channel_id = "delivery_app";
	// 	}
	// 	else if($appName == "Customer"){
	// 		$API_ACCESS_KEY = "AAAAvFcbKxY:APA91bFaq6bXTwMPAf-u6UgyRwLg7NaqQYBnhyVFILV-XUJIzRKWpNNKvnsdeLHkSrJQ9iVNMPaWLtL_TDr20HGraOuDAlzDD2oLFhTQJiyrY2MfARilwddQGELdkmQ59Nbxt2QGLs1L";
	// 		$android_channel_id = "mealsfly_app";
	// 	}
		

	// 	$fcmUrl = 'https://fcm.googleapis.com/fcm/send';

	// 	$notification = [
	//             'title' => $title,
	//             'body' => $body,
	//             'android_channel_id' => $android_channel_id,
	//             'sound' => "sound.wav",
	//             'image' => $image,
	//             'link' => $link
	//     ];
	    
	//     $tokenList = explode(",", $multiToken);

	//     $fcmNotification = [
	//     	'registration_ids' => $tokenList,
	//     	'priority' => "high",
	//         'notification' => $notification,
	//         'data' => $dataJson
	//     ];

	//     $headers = [
	//         'Authorization: key=' .$API_ACCESS_KEY,
	//         'Content-Type: application/json'
	//     ];

	// 	// echo json_encode($fcmNotification);

	//     $ch = curl_init();
	//     curl_setopt($ch, CURLOPT_URL,$fcmUrl);
	//     curl_setopt($ch, CURLOPT_POST, true);
	//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
	//     $result = curl_exec($ch);

	// 	if(curl_errno($ch))
	// 	{
	// 	    // echo 'error:' . curl_error($ch);
	// 	}

	//     curl_close($ch);

	//     // echo $result;
	//     return $result;
	// }

	
}
?>