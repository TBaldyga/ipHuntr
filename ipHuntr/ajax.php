<?php 
//This file takes ajax post functions and processes them

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
//  die();
//}

if(isset($_POST['name'])) { //Updates a specific IPs name
	$editData = $_POST['name'];//used for debug also
	$tempName = strip_tags($editData[0]);
	$tempIP = $editData[1];
	
	//Double check that users owns the IP, Scrub the Name
	if ($loggedInUser->ipOwned($tempIP)) {
		if(minMaxRange(1,16,$tempName))
			{
				echo 3; //error
			}
		else{
			$loggedInUser->updateName($tempIP, $tempName); //function is called that changes the name
			$loggedInUser->setLog($tempIP, 'IP_NAME_CHANGE', time());  //Logs the action
			echo 1;//success
		}
	}
	else { echo 2; }

}

if(isset($_POST['trash'])) { //Deletes a specific IP address
	$tempIP = $_POST['trash'];
	if ($loggedInUser->ipOwned($tempIP)) { //checks that the IP is owned by the user
			$loggedInUser->trashIP($tempIP, time()); //function is called resets the owner_ID and Locks IP for 1 hour
			$loggedInUser->setLog($tempIP, 'TRASH', time()); //Logs the action
			echo 1;
		}
	else {echo 0;}
	
}

if(isset($_POST['campus'])) { //Sets a report that the IP is on a campus
	$tempIP = $_POST['campus'];
	$type = 1; //1 = campus request
	if ($loggedInUser->ipOwned($tempIP)) { //checks that the IP is owned by the user
		$ipID = $loggedInUser->getIPID($tempIP);//get IP ID
		if (!$loggedInUser->checkStats($ipID)) { //check if there is a log in ip_stats already for this IP by the user
			$loggedInUser->setStats($ipID, $type);//add a log in ip_stats
			$loggedInUser->setLog($tempIP, 'CAMPUS_REQUEST', time());  //Logs the action
			echo 1;
			$campusNum = $loggedInUser->fetchStats($ipID, $type);
			if ($campusNum == 5 || $campusNum == 10) {
				$mail = new userCakeMail();
				$today = date("F j, Y");
				$hooks = array(
					"searchStrs" => array("DATE","#ALERT#","#IP#","#ID#","#VALUE#","#MESSAGE#"),
					"subjectStrs" => array($today, "Campus Request", $tempIP, $ipID, $campusNum, "Please confirm if IP is on a campus")
					);	
				if(!$mail->newTemplateMsg("admin-alert-form.txt",$hooks)) {
				//ERROR
				echo 4;
				}
				else {
					if(!$mail->sendMail("support@iphuntr.com", "Notification - Campus Request", NULL, "ipHuntr Auto Alert"))
					{
					//ERROR
					echo 4;
					}
					else {	
						//success
						NULL;
					}
				}	
			}
		}
		else { echo 2;} //A request has already been made
	}
	else { echo 3;} //Not owned
}

if(isset($_POST['report'])) { //Sets a report that the IP should be blacklisted
	$tempIP = $_POST['report'];
	$type = 2; //2 = IP Report
	if ($loggedInUser->ipOwned($tempIP)) {
		$ipID = $loggedInUser->getIPID($tempIP);//get IP ID
		if (!$loggedInUser->checkStats($ipID)) { //check if there is a log in ip_stats already for this IP by the user
			$loggedInUser->setStats($ipID, $type);//add a log in ip_stats
			$loggedInUser->setLog($tempIP, 'REPORT_REQUEST', time());  //Logs the action
			echo 1;
			$reportNum = $loggedInUser->fetchStats($ipID, $type); //fetches the number of logs in the system
			if ($reportNum == 5 || $reportNum == 10) {
				$mail = new userCakeMail();
				$today = date("F j, Y");
				$hooks = array(
					"searchStrs" => array("DATE","#ALERT#","#IP#","#ID#","#VALUE#","#MESSAGE#"),
					"subjectStrs" => array($today, "IP Report Request", $tempIP, $ipID, $reportNum, "Check if this IP is Dangerous or Private")
					);	
				if(!$mail->newTemplateMsg("admin-alert-form.txt",$hooks)) {
				//ERROR
				echo 4;
				}
				else {
					if(!$mail->sendMail("support@iphuntr.com", "Notification - IP Report Request", NULL, "ipHuntr Auto Alert"))
					{
					//ERROR
					echo 4;
					}
					else {	
						//success
						NULL;
					}
				}	
			}
		}
		else { echo 2;} //A request has already been made
	}
	else { echo 3;} //Not owned
}

?>

