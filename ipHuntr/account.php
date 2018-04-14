<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//For Cloudflare Only - Disable in Test Environment
//if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
// $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
//}
//$country_code = $_SERVER["HTTP_CF_IPCOUNTRY"]; // to access in PHP
$country_code = 'US';

$userIP = $_SERVER['REMOTE_ADDR'];
$longIP = ip2long($userIP);
$shortIP = long2ip($longIP);


if(!empty($_GET["page"])) { //gets page and send to the page
	//echo 'Hello ' . htmlspecialchars($_GET["page"]) . '!';
	$location = trim($_GET["page"]);
	header('location: '.$location.'.php');
}

if(isset($_POST['ipButton'])) { //Called when user claims a new IP
	if ($loggedInUser->ipExists($userIP)) { //Double checks that IP is not in database
	header('location: account.php?error=1');//ERROR
	}
	else {
		if ($loggedInUser->checkLimit(time())) { // Checks if the user has claimed for than 3 IPS in the past 24 hr
		header('location: account.php?error=2');//ERROR
		}
		else {
		$loggedInUser->addIP($userIP, $loggedInUser->user_id, time()); //function is called that adds IP to database
		$loggedInUser->setLog($userIP, 'CLAIM', time()); //Logs the action
		header('location: account.php?page=account');
		}
	}
}

if(isset($_POST['farmButton'])) { //Called when user tries to farm their IP
	if (!$loggedInUser->isfarmedIP($userIP)) { //Double checks that the IP can be farmed
	header('location: account.php?error=1');//ERROR
	}
	else {
    $loggedInUser->farmIP($userIP, time()); //function is called that starts farming the IP
	$loggedInUser->setLog($userIP, 'FARM', time()); //Logs the action
	header('location: account.php?page=account');
	}
}

if(isset($_POST['campusFarm'])) {
	if (!$loggedInUser->isfarmedIP($userIP)) { //Double checks that the IP can be farmed
	header('location: account.php?error=1');//ERROR
	}
	else {
	$loggedInUser->campusFarm($userIP, time()); //function is called that starts farming the IP
	$loggedInUser->setLog($userIP, 'CAMPUS_FARM', time()); //Logs the action
	header('location: account.php?page=account');
	}
}

if(isset($_POST['ipSteal'])) { //Called when the user tries to steal an IP address
	if (!$loggedInUser->ipOwned($userIP) && $loggedInUser->isfarmedIP($userIP)) {//Double check that the IP can be stolen
		$ownerID = $loggedInUser->getOwnerID($userIP); // get the current IPs owner ID
		$ownerEmail = $loggedInUser->getEmail($ownerID);//Gets the original owners Email address
		$ownerDName = $loggedInUser->getDisplayName($ownerID);//Gets the original owners Display Name
		
		$loggedInUser->stealIP($userIP, $ownerID, time());//call function to update and steal IP information
		$loggedInUser->setLog($userIP, 'STEAL', time()); //Logs the action
		
		if ($ownerID != 0) {
			$loggedInUser->updatePoints($ownerID); //Updates the other users points
			
			
			//call function to mail the current ownerID the IP has been stolen
			$mail = new userCakeMail();
			$today = date("F j, Y");
			$hooks = array(
				"searchStrs" => array("#USERNAME#","#ADDRESS#"),
				"subjectStrs" => array($ownerDName, $userIP)
				);	
			if(!$mail->newTemplateMsg("ip-stolen.txt",$hooks)) {
			header('location: account.php?error=1');//ERROR
			}
			else {
				if(!$mail->sendMail($ownerEmail, "Your IP has been stolen!", NULL, "ipHuntr Auto Alert"))
				{
					header('location: account.php?error=1');//ERROR
				}
				else {		
					//Successfully Sent!
					header('location: account.php?page=account');
				}
			}
		}
		else { header('location: account.php?page=account'); }
	} 
	else { header('location: account.php?error=1'); }//ERROR
	
}

$loggedInUser->updatePoints($loggedInUser->user_id); //function updates the score of current user
$loggedInUser->updateCountry($country_code);//function updates the country of current user

//START PROCESS FOR CURRENT IP
if (!$loggedInUser->ipExists($userIP)) { //if IP has never been claimed
$status = "Never Captured";
$button = "<form action='account.php' method='post'>
<input class='meg' type='submit' name='ipButton' value='Claim' />
</form>"; 
$details = FALSE;
}
else { //If IP has been claimed already
	$ipInfo = $loggedInUser->ipInfo($userIP); //Returns an array of IP information for the current IP
	$details = TRUE;
	if ($loggedInUser->checkCampus($userIP)) { //is IP on a campus
		if ($loggedInUser->isfarmedIP($userIP)) {
		$status = "Campus IP Ready";
		$button = " <form action='account.php' method='post'>
				<input type='submit' name='campusFarm' value='Farm!' />
				</form>";
		}
		else {
				$status = "Campus IP";
				$timeLeft = $loggedInUser->farmTime($userIP, time());
				$button = "<div><span id='clock'></span></div>";
				$ipInfo[3] = "Campus";
		}
	}
	//if IP has been blacklisted
		//details = false
	else if ($loggedInUser->ipOwned($userIP)) { //is IP owned by the current user
		if ($loggedInUser->isfarmedIP($userIP)) { //if IP can be farmed
			$status = "Ready to Process";
			$button = " <form action='account.php' method='post'>
				<input type='submit' name='farmButton' value='Farm!' />
				</form>";
		}
		else { //IP is being farmed
			$status = "Processing";
				$timeLeft = $loggedInUser->farmTime($userIP, time());
				$button = "<div><span id='clock'></span></div>";
		}
	}
	else { //This IP is owned by another user
		if ($loggedInUser->isfarmedIP($userIP)) { //has the expired time passed?
			$status = "Can be Stolen";
			$button = " <form action='account.php' method='post'>
				<input type='submit' name='ipSteal' value='Steal!'/>
				</form>";
		}
		else { //IP is Locked by another user
			$status = "Locked";
			$timeLeft = $loggedInUser->farmTime($userIP, time());
				$button = "<div><span id='clock'></span></div>";

		}
	
	}
	
}
require_once("models/header.php");
echo "
<section>
<div id='wrapper'>
<div id='content'>
<center>
<div id= 'scoreimg'> <i>score</i> <br /> <h1>".$loggedInUser->getScore()."</h1> </div>
<br /></center>
<div id='left-nav'>";

include("left-nav.php");

echo "
</div>
<div id='main'>
<div id='greeting'>
<br />

<table class='current_ip'>
	<tr>
		<th class='myIP' colspan ='12'>Current IP Address</th> 
	</tr>
	<tr class='basic_ip'>
		<td class='ip' colspan ='3'>$userIP</td> 
		<td class='status' colspan ='4'><i>$status</i></td>
		<td class='btn' colspan ='4'>$button</td>";
if ($details) { echo "
		<td class='pointer' colspan ='1'><img style='cursor:pointer' src='/_content/images/circle-arrow-down.png' class='arrow' alt='v' /></td>
	</tr>
	<tr id='ip_details' class='details'>
		<td class='name' colspan ='5'>Name: <div class='nametxt' id='ipName' contenteditable='true' id='editor'> $ipInfo[0]</div> 
			<button class='name_btn'>Save</button> <div id='namePics'>
			<img src='/_content/images/checkmark.png' width='10' height='10' class='successPic'id='successPic' />
			<img src='/_content/images/error.png' width='10' height='10' class='errorPic' id='errorPic' />
		</td>  
		<td class='base' colspan ='3'>Base: $ipInfo[1] Points</td>
		<td class='worth' colspan ='2'>Next: $ipInfo[2] Points</td>
		<td class='level' colspan ='1'>$ipInfo[3]</td>
		<td class='trash' colspan ='1'>
		<ul>
			<li> >
				<ul>
					<li class='ipTrash'><img src='/_content/images/trashWhite.png' width='15' height='15'/> Trash</li>
					<li class='ipCampus'><img src='/_content/images/campus.png' width='15' height='15'/> Campus</li>
					<li class='ipReport'><img src='/_content/images/warning.png' width='15' height='15'/> Report</li>
				</ul>
			</li>
		</ul>
		</td>
	</tr>
</table>
<br />
		";
}
else { echo "
		<td colspan ='1'></td>
	</tr>
	</table>
<br /> "; }

require_once("models/user.iplist.php");
echo "</div> <div id='leaderboard'>";
include("models/leaderboard.php");

echo ' <br />
</div>
';
echo "
</div>
<div id='bottom'></div>
</div>
</section>
</body>
";
require_once("models/footer.php");


//Script and Errors
if(!empty($_GET["error"])) { //Get request for an error - redirects to account.php
	$errorType = trim($_GET["error"]);
	if ($errorType == 2) {
	echo '
		<script>
		$(document).ready(function () {
			swal({
			title: "Whoops...",
			text: "You can only claim a maximum of three IPs every 24 hours.",
			type: "error",
			showCancelButton: false,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Okay!",
			closeOnConfirm: false,
			closeOnCancel: false
			},
			function(){
			window.location.replace("http://baldyga.loginto.me/account.php");
		});
	});
		</script>
		';
	}
	else {
	echo '
		<script>
		$(document).ready(function () {
			swal({
			title: "Error ;-;",
			text: "Sorry, you cannot preform this action right now. Please try again or contact support.",
			type: "error",
			showCancelButton: false,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Okay!",
			closeOnConfirm: false,
			closeOnCancel: false
			},
			function(){
			window.location.replace("http://baldyga.loginto.me/account.php");
		});
	});
		</script>
		';	
	}
}
?>

<script>
document.querySelector('.ipTrash').onclick = function(){
	swal({
		title: "Trash this IP Address?",
		text: "You will no longer own this IP Address and it will be locked for an additional hour",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#FF1300',
		confirmButtonText: 'Yes, trash it!',
		closeOnConfirm: false,
		//closeOnCancel: false
	},
	function(){
			var ipAddress = <?php echo json_encode($userIP); ?>;
			$.ajax({
			url: "ajax.php",
			type: "post",
			data: {trash: ipAddress},
			datatype: "html",
			success: function(rsp){
					if (rsp == 1) {
						swal("Trashed!", "you no longer own this IP Address", "success");
						setTimeout(function () { location.reload(); }, 700);
					}
					else{
						swal("Uh-oh!", "You can not trash this IP Address", "error");
					}
				}
		});
	});
};

document.querySelector('.ipCampus').onclick = function(){
	swal({
		title: "Report this IP as a Campus?",
		text: "A campus IP is treated diferently than a normal IP, you can read about campus IPs in the FAQ",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#FF9500',
		confirmButtonText: 'Yes, I am on a campus!',
		closeOnConfirm: false,
		//closeOnCancel: false
	},
	function(){
			var ipAddress = <?php echo json_encode($userIP); ?>;
			$.ajax({
			url: "ajax.php",
			type: "post",
			data: {campus: ipAddress},
			datatype: "html",
			success: function(rsp){
					if (rsp == 1) {
						swal("Thank you!", "This IP has been reported as belonging to a campus.", "success");
						setTimeout(function () { location.reload(); }, 700);
					}
					else if (rsp == 2){swal("Uh-oh!", "You have already made a request for this IP Address", "error");}
					else if (rsp == 3){swal("Uh-oh!", "You do not own this IP Address", "error");}
					else {swal("Uh-oh!", "A fatal error has occured", "error");}
				}
		});
	});
};

document.querySelector('.ipReport').onclick = function(){
	swal({
		title: "Report this IP to be Blocked?",
		text: "You can report an IP address to be blacklisted, this way nobody (including you) can use it. See the FAQ for more details.",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#1F1F21',
		confirmButtonText: 'Yes, Report this IP Address!',
		closeOnConfirm: false,
		//closeOnCancel: false
	},
	function(){
			var ipAddress = <?php echo json_encode($userIP); ?>;
			$.ajax({
			url: "ajax.php",
			type: "post",
			data: {report: ipAddress},
			datatype: "html",
			success: function(rsp){
					if (rsp == 1) {
						swal("Thank you!", "This IP has been reported for review.", "success");
						setTimeout(function () { location.reload(); }, 700);
					}
					else if (rsp == 2){swal("Uh-oh!", "You have already made a report for this IP Address", "error");}
					else if (rsp == 3){swal("Uh-oh!", "You do not own this IP Address", "error");}
					else {swal("Uh-oh!", "A fatal error has occured", "error");}
				}
		});
	});
};

$(function() {  //once the document is ready
  $(".details") //select all details
    .hide();
})

$(document).ready(function(argument) {
	$(".name_btn").click(function(){
		// Get the Name and IP Address, Send to Array to be used in ajax to ajax.php
		$name = $("#ipName").html();
		var ipAddress = <?php echo json_encode($userIP); ?>;
		var nameArray = [$name, ipAddress];
		$.ajax({
			url: "ajax.php",
			type: "post",
			data: {name: nameArray},
			datatype: "html",
			success: function(rsp){
					if(rsp == 1) {
					$( "#successPic" ).fadeIn(500).delay(1500).fadeOut(1500);
					}
					else{
					$( "#errorPic" ).fadeIn(500).delay(1500).fadeOut(1500);
					}
				}
		});
	});
});

value = 0; //sets the current value of the toggle (OFF)

$( ".basic_ip" ).click(function() {
  $( ".details" ).slideToggle( 5, function() {
  });
	
if(value == 0) {
	$(".arrow").rotate(180);
	value = 1;
} 
else {
	$(".arrow").rotate(0);
	value = 0;
}	
	
	
});
var content_id = 'ipName';  
max = 15;

//binding keyup/down events on the contenteditable div
$('#'+content_id).keyup(function(e){ check_charcount(content_id, max, e); });
$('#'+content_id).keydown(function(e){ check_charcount(content_id, max, e); });

function check_charcount(content_id, max, e)
{   
    if(e.which != 8 && $('#'+content_id).text().length > max)
    {
       // $('#'+content_id).text($('#'+content_id).text().substring(0, max));
       e.preventDefault();
    }
}
</script>
<script>
var dateTime = <?php echo $timeLeft?>;
// set the date we're counting down to
//var target_date = new Date(dateTime*1000).getTime();

var timerId =
  countdown(
    new Date(dateTime*1000).getTime(),
    function(ts) {
	if (ts == '1 s') {window.location.replace("http://baldyga.loginto.me/account.php");}
      document.getElementById('clock').innerHTML = ts.toHTML();
    },
    countdown.HOURS|countdown.MINUTES|countdown.SECONDS);

</script>