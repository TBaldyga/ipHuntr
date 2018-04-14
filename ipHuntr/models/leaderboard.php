<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

if (!securePage($_SERVER['PHP_SELF'])){die();}

function fetchGlobal()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT display_name, score FROM uc_users ORDER BY score DESC");
	$stmt->execute();
	$stmt->bind_result($user, $score);
	
	while ($stmt->fetch()){
		$row[] = array('user_name' => $user, 'points' => $score);
	}
	$stmt->close();
	return ($row);
}
function fetchNational($country)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT display_name, score FROM uc_users WHERE country = ? ORDER BY score DESC");
	$stmt->bind_param("s", $country);
	$stmt->execute();
	$stmt->bind_result($user, $score);
	
	while ($stmt->fetch()){
		$row[] = array('user_name' => $user, 'points' => $score);
	}
	$stmt->close();
	return ($row);
}


// START LEADERBOARD
$globalData = fetchGlobal(); //Fetch information for all users
$nationalData = fetchNational($country_code); //Fetch information for users current country code
//$friendData = fetchFriends(); //for use when friends list is implemented

echo "
<br />
<table class='bordered'>
<tr>
<th class='number' colspan='2'>#</th> 
<th class='username' colspan='2'>Top Players</th>
<th class='points' colspan='2'>Score</th>
</tr>
<tr>
<td class='global' colspan='3'>Global</td>
<td class='national' colspan='3'>National</td>
</tr>
";

//Make chart for Global Data
$numG = 0;
foreach ($globalData as $v1) {
	if ($v1['points'] == 0) {
		continue;
	}
	$numG++;
	echo "
		<tr class='globalLead'>
		<td colspan='2'>".$numG."</td> 
		<td colspan='2'>".$v1['user_name']."</td>
		<td colspan='2'>".$v1['points']."</td>
		</tr> ";
	if ($numG == 10) { break; }
}
//Make chart for National Data
$numN = 0;
foreach ($nationalData as $v2) {
	if ($v2['points'] == 0) {
		continue;
	}
	$numN++;
	echo "
		<tr class='nationalLead'>
		<td colspan='2'>".$numN."</td> 
		<td colspan='2'>".$v2['user_name']."</td>
		<td colspan='2'>".$v2['points']."</td>
		</tr> ";
	if ($numN == 10) { break; }
}
echo "</table>";

?>
<script>
$(function() { 
  $(".nationalLead") //select all details to hide
    .hide();
})

$(document).ready(function(argument) {
$( ".global" ).css( "background", "white" );
	$(".global").click(function(){
		$( ".global" ).css( "background", "white" );
		$( ".national" ).css( "background", "none" );		
		$( ".nationalLead" ).slideUp( "fast", function() {
			$( ".globalLead" ).slideDown( "fast", function() {
				//Done
			});
		});
	});
	$(".national").click(function(){
		$( ".global" ).css( "background", "none" );
		$( ".national" ).css( "background", "white" );
		$( ".globalLead" ).slideUp( "fast", function() {
			$( ".nationalLead" ).slideDown( "fast", function() {
				//Done
			});
		});
	});
});
</script>
