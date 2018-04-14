<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

echo '
<html lang ="en">
   <head> <!--controls all meta tags and title-->
   <!--

      ipHuntr Alpha
      root/index.html
      Author: Tim Baldyga
      Date:   9/29/14

   -->
	  <title>ipHuntr | Alpha 2.0</title>
      
      <meta charset="UTF-8" />
	  <meta name="keywords" content="ipHuntr, ipHunter, game, ip game, local game, indie game, data game, internet game, IP based game, claim IP address">
	  <meta name="author" content="Tim Baldyga">
	  <meta NAME="robots" CONTENT="index,follow">
	  <meta NAME="Description" CONTENT="Capture your IP and Hunt for new ones! ipHuntr is a free online game where you use your IP Address to gain points! Currently in Alpha, Try it today!">
      
	  <link href="/_content/iconic.css" media="screen" rel="stylesheet" type="text/css" />
	  <link href="'.$template.'" rel="stylesheet" type="text/css" />
	  <link rel="stylesheet" type="text/css" href="/models/site-templates/sweetalert/lib/sweet-alert.css">
	  <link rel="stylesheet" type="text/css" href="/models/site-templates/tableScroll/jquery.tablescroll.css">
	  <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
	  
	  <link rel="apple-touch-icon" href="/_content/images/AppleLogo.png" />
	  <link rel="apple-touch-startup-image" href="/_content/images/apple_opti.png" />
	  <meta name="apple-mobile-web-app-capable" content="yes" />
	  

	  <script src="models/funcs.js" type="text/javascript"></script>
	  <script src="/_content/prefix-free.js" type="text/javascript"></script>
	  <script src="/models/site-templates/sweetalert/lib/sweet-alert.js"></script>
	  <script src="/models/site-templates/jquery.js"></script>
	  <script src="/models/site-templates/jQueryRotate.js"></script>
	  <script type="text/javascript" src="/models/site-templates/data-popup-mode.js"></script>
	  <script src="/models/site-templates/countdown.js"></script>
	  <script src="/models/site-templates/tableScroll/jquery.tablescroll.js"></script>
	  
	  <base target="_self">
      
	</head>';
?>
	<body> <!--controls all content on website-->	
    
    	<header> <!--controls the top bar-->
        <div id="head">
        	<div id="head_logo">	
        		<a href="https://iphuntr.com"> <img src="/_content/images/ipHuntr.png" width="117" height="39" alt="ipHuntr" /> </a>
            </div>
            <div id="head_message"> <!--Header Message (link to php global variable)-->
    			<?php 
					$msgNum = rand(1, 100);
						if ($msgNum>0 && $msgNum<26) {$message = "ipHuntr - Need some <a href='contact.php'>support?</a>";}
						elseif ($msgNum>25 && $msgNum<51) {$message = "ipHuntr - Did you know we have a <a href='/blog'>Dev blog?</a>";}
						elseif ($msgNum>50 && $msgNum<76) {$message = "ipHuntr - We love our members! <3";}
						elseif ($msgNum>75 && $msgNum<101) {$message = "ipHuntr - All you have to do is do it better";}
						else {$message = "I forgot what I was supposed to say :'(";}
					echo $message; 
				?> 
            </div>
			<?php
			if (isUserLoggedIn()) {
			echo '
            <div id="head_account">
            <nav>
				<ul class="menu">
					<li><a><span class="iconic user"></span> <b>'.$loggedInUser->displayname.'</b></a> 
						<ul>
							<li><a href="account.php">Account</a></li>
							<li><a href="user_settings.php">Settings</a></li>
                            <li><a href="logout.php">Logout</a></li>
						</ul>
                     </li>
				</ul>
			</nav>
            </div>';
			}
			?>
			
       	</div>
    </header>
