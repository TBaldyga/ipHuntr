<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
require_once("models/recaptchalib.php"); //Connects Captcha 
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = trim($_POST["username"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	
	if(minMaxRange(5,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	if (strpos($username,'fuck') !== false) 
	{
    $errors[] = lang("ACCOUNT_PROFANITY");
	}
	// reCAPTCHA

	$privatekey = "6LdmefsSAAAAAIPJvwUg_HFYVA2VcPpDpuGASRmu"; // the one you received when you registered
	$resp = recaptcha_check_answer ($privatekey,
	$_SERVER["REMOTE_ADDR"],
	$_POST["recaptcha_challenge_field"],
	$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid)
	{
	$errors[] = "The reCAPTCHA wasn't entered correctly. Go back and try it again.";
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username,$displayname,$password,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}
echo"
 <script type='text/javascript'>
 var RecaptchaOptions = {
    theme : 'white'
 };
 </script>";
 
require_once("models/header.php");
echo "
<body>
<section>
<div id='wrapper'>
<div id='top'><div id='logo'></div></div>
<div id='content'>
<h1>Register</h1>

<div id='left-nav'>";
include("left-nav.php");
echo "
</div>

<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<div id='regbox'>
<form name='newUser' action='".$_SERVER['PHP_SELF']."' method='post'>

<p>
<label>User Name:</label>
<input type='text' name='username' />
</p>

<p>
<label>Password:</label>
<input type='password' name='password' />
</p>
<p>
<label>Confirm:</label>
<input type='password' name='passwordc' />
</p>
<p>
<label>Email:</label>
<input type='text' name='email' />
</p>
<div id='reCAPTCHA'>
<form method='post' action='models/verify.php' id='reCAPTCHA'>";

$publickey = '6LdmefsSAAAAADQeteL-i0mNKYJnfhjA5rcbi3V0';
echo recaptcha_get_html($publickey);

echo "
<br />
<input type='submit' value='Register' />
</form>
</div>
<p>By clicking Register you are agreeing to our <a href='terms.php'>Terms and Conditions</a></p>
</div>
</form>
</div>
<div id='bottom'></div>
</div>
</body>
</section>
</html>";

require_once("models/footer.php");
?>