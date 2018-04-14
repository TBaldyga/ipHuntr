<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

class userCakeMail {
	//UserCake uses a text based system with hooks to replace various strs in txt email templates
	public $contents = NULL;
	
	//Function used for replacing hooks in our templates
	public function newTemplateMsg($template,$additionalHooks)
	{
		global $mail_templates_dir,$debug_mode;
		
		$this->contents = file_get_contents($mail_templates_dir.$template);
		
		//Check to see we can access the file / it has some contents
		if(!$this->contents || empty($this->contents))
		{
			return false;
		}
		else
		{
			//Replace default hooks
			$this->contents = replaceDefaultHook($this->contents);
			
			//Replace defined / custom hooks
			$this->contents = str_replace($additionalHooks["searchStrs"],$additionalHooks["subjectStrs"],$this->contents);
			
			return true;
		}
	}
	
	public function sendMail($email, $subject, $msg = NULL, $from = NULL, $replyTo = NULL, $name = null)
	{
		global $websiteName,$emailAddress;
		
		$header = "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$header .= "From: ". $websiteName . " <" . $emailAddress . ">\r\n";
		
		//Check to see if we sending a template email.
		if($msg == NULL)
			$msg = $this->contents;
		//Check to see if there are any special email requirements
		if($from == NULL)
			$from = "ipHuntr Support";
		if($replyTo == NULL)
			$replyTo = "no-reply@iphuntr.com";
		if($name == NULL)
			$name = "no-reply";
		
		$message = $msg;
		
		$message = wordwrap($message, 80);
		
		require_once 'PHPMailerAutoload.php';
 
					$results_messages = array();
 
					$mail = new PHPMailer(true);
					$mail->CharSet = 'utf-8';
 
 
					try {
					$to = 'support@iphuntr.com';
					if(!PHPMailer::validateAddress($to)) {
					  throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!");
					}
					$mail->isSMTP();
					$mail->SMTPDebug  = 0;
					$mail->Host       = "mail.iphuntr.com";
					$mail->Port       = "25";
					$mail->SMTPSecure = "none";
					$mail->SMTPAuth   = true;
					$mail->Username   = "support@iphuntr.com";
					$mail->Password   = "P@ssw0rd";
					$mail->addReplyTo("support@iphuntr.com", "ipHuntr Support");
					$mail->From       = "support@iphuntr.com";
					$mail->FromName   = $from;
					$mail->addAddress($email, "ipHuntr");
					$mail->Subject  = $subject;
					$body = $message;

					$mail->WordWrap = 80;
					$mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images
 
					try {
					  $mail->send();
					  return true;
					}
					catch (phpmailerException $e) {
					  return false;
					}
					}
					catch (phpmailerAppException $e) {
					  return false;
					}
		}
}

?>