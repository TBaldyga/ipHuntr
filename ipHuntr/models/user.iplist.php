<script>
$.fn.tableScroll.defaults =
{
    flush: true, // makes the last thead and tbody column flush with the scrollbar
    width: 550, // width of the table (head, body and foot), null defaults to the tables natural width
    height: 275, // height of the scrollable area
    containerClass: 'tablescroll' // the plugin wraps the table in a div with this css class
};

jQuery(document).ready(function($)
{

    // by default the plugin will wrap everything in a div with this 
    // css class, if it finds that you have manually wrapped the 
    // table with a custom element using this same css class it 
    // will forgo creating a container DIV element
    $('#thetable').tableScroll({containerClass:'myCustomClass'});
});
</script>

<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

if (!securePage($_SERVER['PHP_SELF'])){die();}

//List all of the users Owned IP Addresses
$num = 0;
$ipData = $loggedInUser->fetchOwned(); //Fetch all IPs the user Owned
$tempTime = NULL;

if ($ipData != FALSE)
{
echo "
<br />
<table class='current_ip' id='thetable'>
	<thead>
	<tr>
		<th class='ipList' >Remote IP Addresses </th> 
	</tr>
	</thead>";

//Builds the table for each IP Address
foreach ($ipData as $v1) {

	if ($v1['tempIP'] == $longIP) {
		continue;
	}
	$currentIP = long2ip($v1['tempIP']);
	$num++;
	
	$tempIPInfo = $loggedInUser->ipInfo($currentIP); //Returns an array of IP information
	if ($loggedInUser->checkCampus($currentIP)) { //is IP on a campus
		if ($loggedInUser->isfarmedIP($currentIP)) {
		$tempStatus = "Campus IP Ready";
		}
		else {
				$tempTime = $loggedInUser->farmTime($currentIP, time());
				$tempStatus = "Being Processed - <span id='clock".$num."'></span>";
				$tempIPInfo[3] = "Campus";
		}
	}
	else if ($loggedInUser->ipOwned($currentIP)) { //is IP owned by the current user
		if ($loggedInUser->isfarmedIP($currentIP)) { //if IP can be farmed
			$tempStatus = "Ready to Process";
			//echo "<script>$( '.tempOwnedIP".$num."' ).css( 'background', '#8DCB5E' );</script>";
		}
		else { //IP is being farmed
			$tempTime = $loggedInUser->farmTime($currentIP, time());
			$tempStatus = "Being Processed - <span id='clock".$num."'></span>";
		}
	}
	else { //This IP is owned by another user
		if ($loggedInUser->isfarmedIP($userIP)) { //has the expired time passed?
			$status = "Can be Stolen";
		}
		else { //IP is being farmed by another user
			$tempStatus = "Locked by another user";
		}
	
	}
	?> <script> var ipAddress = <?php echo json_encode($currentIP); ?>; </script> <?php
	echo "
	<tr class='tempOwnedIP".$num."'> 
		<td class='ip' id='tempIP".$num."' colspan ='5'>$currentIP</td>
		<td class='status' colspan ='6'><i>$tempStatus</i></td>
		<td class='pointer' colspan ='1'><img style='cursor:pointer' src='/_content/images/circle-arrow-down.png' class='arrow".$num."' alt='v' colspan ='4' /></td>
	</tr>
	<tr id='ip_details' class='tempDetails".$num."'>
		<td class='name' colspan ='5'>Name: <div class='nametxt' id='ipName".$num."' contenteditable='true'> $tempIPInfo[0]</div> 
			<button class='name_btn".$num."'>Save</button> <div id='namePics'>
			<img src='/_content/images/checkmark.png' width='10' height='10' class='successPic' id='successPic".$num."' />
			<img src='/_content/images/error.png' width='10' height='10' class='errorPic' id='errorPic".$num."' />
		</td>  
		<td class='base' colspan ='3'>Base: $tempIPInfo[1] Points</td>
		<td class='worth' colspan ='2'>Next: $tempIPInfo[2] Points</td>
		<td class='level' colspan ='1'>$tempIPInfo[3]</td>
		<td class='level' colspan ='1'><img src='/_content/images/trash.png' width='15' height='15' class='ipTrash' id='ipTrash".$num."' title='Delete IP Address'/></td>
	</tr>
	";
	//TEMP SCRIPTS
	echo '
	<script>
	document.querySelector("#ipTrash'.$num.'").onclick = function(){
	swal({
		title: "Trash this IP Address?",
		text: "You will no longer own '.$currentIP.'",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Yes, trash it!",
		closeOnConfirm: false,
		//closeOnCancel: false
	},
	function(){
			$.ajax({
			url: "ajax.php",
			type: "post",
			data: {trash: ipAddress},
			datatype: "html",
			success: function(rsp){
					if(rsp == 1) {
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
	
	
	$(function() {  //once the document is ready
	  $(".tempDetails'.$num.'") //select all details
		.hide();
	})
$(document).ready(function(argument) {
	$(".name_btn'.$num.'").click(function(){
		// Get edit field value
		$name = $("#ipName'.$num.'").html();
		var ipAddress = $("#tempIP'.$num.'").html();
		var nameArray = [$name, ipAddress];
		$.ajax({
			url: "ajax.php",
			type: "post",
			data: {name: nameArray},
			datatype: "html",
			success: function(rsp){
					if(rsp == 1) {
					$( "#successPic'.$num.'" ).fadeIn(500).delay(1500).fadeOut(1500);
					}
					else{
					alert(rsp);
					$( "#errorPic'.$num.'" ).fadeIn(500).delay(1500).fadeOut(1500);
					}
				}
		});
	});
});

	tempValue'.$num.' = 0; //sets the current value of the toggle (OFF)

	$( ".tempOwnedIP'.$num.'" ).click(function() {
	  $( ".tempDetails'.$num.'" ).slideToggle( 5, function() {
	  });
		
	if(tempValue'.$num.' == 0) {
		$(".arrow'.$num.'").rotate(180);
		tempValue'.$num.' = 1;
	} 
	else {
		$(".arrow'.$num.'").rotate(0);
		tempValue'.$num.' = 0;
	}	
		
		
	});
	var content_id'.$num.' = "ipName'.$num.'";  
	max = 15;

	//binding keyup/down events on the contenteditable div
	$("#"+content_id'.$num.').keyup(function(e){ check_charcount(content_id'.$num.', max, e); });
	$("#"+content_id'.$num.').keydown(function(e){ check_charcount(content_id'.$num.', max, e); });

	function check_charcount(content_id'.$num.', max, e)
	{   
		if(e.which != 8 && $("#"+content_id'.$num.').text().length > max)
		{
		   // $("#"+content_id'.$num.').text($("#"+content_id'.$num.').text().substring(0, max));
		   e.preventDefault();
		}
	}
	</script>
	<script>
	var dateTime'.$num.' = '.$tempTime.';

	var timerId'.$num.' =
	countdown(
	new Date(dateTime'.$num.'*1000).getTime(),
	function(ts) {
	  //if (dateTime'.$num.' == time()) {window.location.replace("http://iphuntr.com/account.php");}
	  document.getElementById("clock'.$num.'").innerHTML = ts.toHTML();
	},
	countdown.HOURS|countdown.MINUTES);
	</script>
	';
	
if ($num == 25) {
	break; }
	
}
if ($num == 0) {
	echo "
	<tr class='tempOwnedIP'> 
		<td class='status' colspan ='12'><i>Capture more IP Addresses!</i></td>
	</tr>
	";
	}
}
echo "</div></table>";
?>


