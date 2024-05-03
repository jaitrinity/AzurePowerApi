<?php
require_once 'FirebaseNotificationClass.php';
$tokens = "cy4EIVUNTpCj-N_Q3qnba3:APA91bF35SDcI1yjsi0CoQyuou29cJLxa8P76oOxMp0WmNQWh2R0pYPgYjycn9WW9cNNzSThj5AwoEK266T5goBp5dKs2oDWCWobPfi0GNfwcs7LbAVe8JlYQI3LBVLqhYMuHX9gVA8w";
$tokens = "fzh39DZdQy6_ywRUa-_WWj:APA91bGPB-nphKnDTOY2cR_mNvK2F3kYcZMP-ANeilpcPob9SGEb5TB6MrGv3jskz_LY1xuF00WoMWB18eWh3zOlKbN6mZzXvDowejXJz9kHQ2L_lpmKtI_JdMQObTwuQEEwfY9f8UW7";
$title = "Happy birthday";
$body = "Many many happy return of the day...";
$image = "";
$link = "";
$classObj = new FirebaseNotificationClass();
$notiResult = $classObj->sendNotification($tokens, $title, $body, $image, $link);
$notificationResult = json_decode($notiResult);
echo $notiResult;
?>