<?php 
/*
UserCake Version: 2.0.1
http://usercake.com
*/
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Set Global Variables
$sent = 0;
$badEmail = FALSE;
$badName = FALSE;
$badMsg = FALSE;
$badSubject = FALSE;

if(!empty($_GET["s"])) {
	$sent = trim($_GET["s"]);
}

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$fName = trim($_POST["fm_name"]);
	$fEmail = trim($_POST["fm_email"]);
	$fSubject = trim($_POST["fm_subject"]);
	$fMessage = trim($_POST["fm_message"]);
	
	if(isset($_POST['fmoptinyes']) && $_POST['fmoptinyes'] == TRUE) 
	{
		$fCheckbox = "YES";
	}
	else
	{
		$fCheckbox = "NO";
	}
	//Perform some validation
	//Feel free to edit / change as required
	if(trim($fEmail) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
		$badEmail = TRUE;
	}
	//Check to ensure message info is in the correct format
	else if(!isValidEmail($fEmail))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		$badEmail = TRUE;
	}
	
	if(trim($fName) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
		$badName = TRUE;
	}
	
	if(trim($fMessage) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
		$badMsg = TRUE;
	}
	
	if(trim($fSubject) == "Choose A Subject...")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
		$badSubject = TRUE;
	}
	
	if(count($errors) == 0) //If there are no errors the mail is formed and sent
	{		
				$mail = new userCakeMail();
				$today = date("F j, Y");
				
				//Setup our custom hooks for template
				$hooks = array(
					"searchStrs" => array("DATE","#FROM#","#MESSAGE#","#NAME#","#SUBJECT#","#CHECKBOX#"),
					"subjectStrs" => array($today, $fEmail, $fMessage, $fName, $fSubject, $fCheckbox)
					);
				
				if(!$mail->newTemplateMsg("contact-form.txt",$hooks))
				{
					$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
					$sent = 2; //sent 2 means there was an error
				}
				else
				{
					if(!$mail->sendMail("support@iphuntr.com", "$fSubject - $fEmail", NULL, "ipHuntr Contact Form", $fEmail, $fName))
					{
						$sent = 2; //Error
						header('location: contact.php?s=2');
					}
					else {		
						$sent = 1; //Successfully Sent!
						header('location: contact.php?s=1');
					}
				}			
	}
}

require_once("models/header.php");

echo '
<body>
<section>
<br /><h1> Contact Us </h1><br />
<h2>We love to hear from you!</h2>

<div class="form-wrapper">
<form class="ddfm" method="post" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" _lpchecked="1">
';
//Notification if sent mail
if ($sent == 1) { 
	echo '
	<div class="notification success">
		<p>Success! Your Message has been sent!</p>
	</div> ';
}
elseif ($sent == 2){
 echo '
	<div class="notification error">
		<p>Error: Your Message was not sent ;-;</p>  
	</div> ';
} 

echo ' 
<p class="fieldwrap"><label for="fm_name" class="disp"><span class="required">*</span> Name</label><input class="fmtext" type="text" name="fm_name" id="fm_name" value="" placeholder="Name"></p>

<p class="fieldwrap"><label for="fm_email" class="disp"><span class="required">*</span> Email</label><input class="fmtext" type="text" name="fm_email" id="fm_email" value="" placeholder="Email Address"></p>

  <p class="fieldwrap"><label class="disp"><span class="required">*</span> Subject</label><div class="ff-only"><select class="" code="" id="fm_subject" name="fm_subject">
<option>Choose A Subject...</option>
<option>Feedback</option>
<option>Technical Support</option>
<option>Business Inquiry</option>
<option>Other</option>
    </select></div></p>
';

//Notification of Form Errors
if(count($errors) > 0) { 
	echo '<p class="fieldwrap"><div class="faq"> ';
		if ($badName == TRUE) { echo '<p>- Please type a valid <b>Name</b>!</p>';} 
		if ($badEmail == TRUE) { echo '<p>- Please type a valid <b>Email address</b>!</p>';}
		if ($badSubject == TRUE) { echo '<p>- Please pick a <b>Subject</b>!</p>';}
		if ($badMsg == TRUE) { echo '<p>- Please type a <b>Message</b>!</p>';}
	echo '
	</div>
	</p>
';
}

echo ' 
<p class="fieldwrap"><label for="fm_message" class="disp"><span class="required">*</span> Message</label>
<textarea class="fmtextarea" name="fm_message" cols="20" rows="6" id="fm_message" placeholder="Your Message Goes Here&hellip;"></textarea></p>

<p class="fieldwrap"><label></label><span class="fmoptin">
<input type="checkbox" name="fmoptinyes" id="fmoptinyes" value="TRUE" checked="checked"><label for="fmoptinyes" class="fmchecklabel">Yes, please add me to your mailing list for future updates.</label>
<br></span></p>
  
  <p class="fieldwrap"><div class="submit"><input type="submit" name="form_submitted" value="Send Message"></div></p>
  
</form>
</div>

</section>
</body>
</html>';

require_once("models/footer.php");
?>