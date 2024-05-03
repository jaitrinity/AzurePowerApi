<?php
require 'SendMailClass.php';

$toMailId = "jai.prakash@trinityapplab.co.in";
// $ccMailId = "pushkar.tyagi@trinityapplab.co.in";
$ccMailId = "";
$bccMailId = "";


$msg = "Hi";
$subject = "Cron test";
$classObj = new SendMailClass();
$response = $classObj->sendMail($toMailId, $ccMailId, $bccMailId, $subject, $msg, null);
echo $response;
?>