<?php 
include('/var/www/trinityapplab.in/html/PHPMailerAutoload.php');

class SendMailClass{
	public function sendMail($toMailId, $ccMailId, $bccMailId, $subject, $msg, $attachment){
		$status = false;
	    $message = $msg;
	    $mail = new PHPMailer;
	    $mail->isSMTP();                                      
	    $mail->Host = 'smtp.gmail.com';
	    $mail->SMTPAuth = true;
	    $mail->Username = 'emailId';
	    $mail->Password = 'password';   
	    $mail->Port = 587;
	    $mail->SMTPSecure = 'tls';
	    
	    $mail->setFrom("communication@trinityapplab.co.in","Trinity");
	    $mail->addAttachment($attachment);
	    $mail->isHTML(true);   

	    // To mail's
	    // $mail->addAddress("pushkar.tyagi@trinityapplab.co.in");
	    if($toMailId != ""){
	    	$toMailIdList = explode(",", $toMailId);
	    	for($i=0;$i<count($toMailIdList);$i++){
	    		$mail->addAddress($toMailIdList[$i]);
	    	}
	    }

	    // CC mail's
	    // $mail->addCC("ankita.verma@trinityapplab.co.in");
	    if($ccMailId != ""){
	    	$ccMailIdList = explode(",", $ccMailId);
	    	for($i=0;$i<count($ccMailIdList);$i++){
	    		$mail->addCC($ccMailIdList[$i]);
	    	}
	    	
	    }
	    // BCC mail's
	    // $mail->addBCC("jai.prakash@trinityapplab.co.in");
	    if($bccMailId != ""){
	    	$bccMailIdList = explode(",", $bccMailId);
	    	for($i=0;$i<count($bccMailIdList);$i++){
	    		$mail->addBCC($bccMailIdList[$i]);
	    	}
	    	
	    }
	    

	    $mail->Subject = $subject;
	    $mail->Body = "$message<br>";
	    
	        
	    if(!$mail->send())
	    {
	        // echo 'Mailer Error: ' . $mail->ErrorInfo;
	        // echo"<br>Could not send";
	        $status = false;
	    }
	    else{
	        // echo "mail sent";
	        $status = true;
	    }
	    return $status;
	}

	public function sendMailTest($toMailId, $subject, $msg, $attachment){
		$status = false;
	    $message = $msg;
	    $mail = new PHPMailer;
	    $mail->isSMTP();                                      
	    $mail->Host = 'smtp.gmail.com';
	    $mail->SMTPAuth = true;
	    $mail->Username = 'emailId';
	    $mail->Password = 'password';   
	    $mail->Port = 587;
	    $mail->SMTPSecure = 'tls';
	    
	    // To mail's
	    $mail->addAddress($toMailId);
	    // $mail->addAddress("pushkar.tyagi@trinityapplab.co.in");
	    
	    $mail->setFrom("communication@trinityapplab.co.in","Trinity");
	    $mail->addAttachment($attachment);
	    $mail->isHTML(true);   

	    // CC mail's
	    // $mail->addCC('ankita.verma@trinityapplab.co.in');
    	// $mail->addCC('ayush.agarwal@trinityapplab.co.in');
    	// $mail->addCC('pushkar.tyagi@trinityapplab.co.in');
	    
	    // BCC mail's
	    // $mail->addBCC("jai.prakash@trinityapplab.co.in");

	    $mail->Subject = $subject;
	    $mail->Body = "$message<br>";
	    
	        
	    if(!$mail->send())
	    {
	        // echo 'Mailer Error: ' . $mail->ErrorInfo;
	        // echo"<br>Could not send";
	        $status = false;
	    }
	    else{
	        // echo "mail sent";
	        $status = true;
	    }
	    return $status;
	}
}
?>
