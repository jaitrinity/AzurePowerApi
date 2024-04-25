<?php
require_once 'FirebaseNotificationClass.php';
$tokens = "cy4EIVUNTpCj-N_Q3qnba3:APA91bF35SDcI1yjsi0CoQyuou29cJLxa8P76oOxMp0WmNQWh2R0pYPgYjycn9WW9cNNzSThj5AwoEK266T5goBp5dKs2oDWCWobPfi0GNfwcs7LbAVe8JlYQI3LBVLqhYMuHX9gVA8w";
$title = "Happy birthday";
$body = "Many many happy return of the day...";
$image = "";
$link = "";
$classObj = new FirebaseNotificationClass();
$notiResult = $classObj->sendNotification($tokens, $title, $body, $image, $link);
$notificationResult = json_decode($notiResult);
echo $notificationResult;
?>