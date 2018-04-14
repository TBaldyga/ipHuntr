<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

class loggedInUser {
	public $email = NULL;
	public $hash_pw = NULL;
	public $user_id = NULL;
	
	//Simple function to update the last sign in of a user
	public function updateLastSignIn()
	{
		global $mysqli,$db_table_prefix;
		$time = time();
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET
			last_sign_in_stamp = ?
			WHERE
			id = ?");
		$stmt->bind_param("ii", $time, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Return the timestamp when the user registered
	public function signupTimeStamp()
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT sign_up_stamp
			FROM ".$db_table_prefix."users
			WHERE id = ?");
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($timestamp);
		$stmt->fetch();
		$stmt->close();
		return ($timestamp);
	}
	
	//Update a users password
	public function updatePassword($pass)
	{
		global $mysqli,$db_table_prefix;
		$secure_pass = generateHash($pass);
		$this->hash_pw = $secure_pass;
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET
			password = ? 
			WHERE
			id = ?");
		$stmt->bind_param("si", $secure_pass, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Update a users email
	public function updateEmail($email)
	{
		global $mysqli,$db_table_prefix;
		$this->email = $email;
		$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
			SET 
			email = ?
			WHERE
			id = ?");
		$stmt->bind_param("si", $email, $this->user_id);
		$stmt->execute();
		$stmt->close();	
	}
	
	//Is a user has a permission
	public function checkPermission($permission)
	{
		global $mysqli,$db_table_prefix,$master_account;
		
		//Grant access if master user
		
		$stmt = $mysqli->prepare("SELECT id 
			FROM ".$db_table_prefix."user_permission_matches
			WHERE user_id = ?
			AND permission_id = ?
			LIMIT 1
			");
		$access = 0;
		foreach($permission as $check){
			if ($access == 0){
				$stmt->bind_param("ii", $this->user_id, $check);
				$stmt->execute();
				$stmt->store_result();
				if ($stmt->num_rows > 0){
					$access = 1;
				}
			}
		}
		if ($access == 1)
		{
			return true;
		}
		if ($this->user_id == $master_account){
			return true;	
		}
		else
		{
			return false;	
		}
		$stmt->close();
	}
	
	//Logout
	public function userLogOut()
	{
		destroySession("userCakeUser");
	}	
	
//Functions that interact with ip_list table and scores
//------------------------------------------------------------------------------
public function getScore() //This function simply displays the 'current' users score
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT score FROM ".$db_table_prefix."users WHERE id = ?");
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($score);
		$stmt->fetch();
		$stmt->close();
		return ($score);
	}
	
public function getEmail($userID) //This function simply returns a users emails
	{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT email FROM ".$db_table_prefix."users WHERE id = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->bind_result($email);
		$stmt->fetch();
		$stmt->close();
		return ($email);
	}	
	
public function getDisplayName($userID) //This function simply returns a users Display name
{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT display_name FROM ".$db_table_prefix."users WHERE id = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->bind_result($dName);
		$stmt->fetch();
		$stmt->close();
		return ($dName);

}

public function setLog($userIP, $action, $time) //adds a place in the log for a preset action
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare(" 
			INSERT 
			INTO ip_logs (ip_address, username, action, ip_int, user_id, time_stamp) 
			VALUES (?, ?, ?, ?, ?, ?)
			");
		$stmt->bind_param("sssiii", $userIP, $this->username, $action, $longIP, $this->user_id, $time);	
		$stmt->execute();
		$stmt->close();	
}
	
public function addIP($userIP, $user_id, $time) //This function adds a new IP into the system and permanently rewards the user
	{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		$stmt = $mysqli->prepare(" 
			INSERT 
			INTO ip_list (ip_address, founder_id, owner_id, name, base_value, ip_value, capture_stamp, last_claim, claim_expire, level ) 
			VALUES ($longIP, $user_id, $user_id, 'IP_ADDRESS', 50, 100, $time, $time, $time + 3600, 1)
			");
		$stmt->execute();
		$stmt->close();	
		
		//This adds 50 points to the users score
		//$stmt = $mysqli->prepare(" 
		//UPDATE uc_users SET points = points + 50 WHERE id = $this->user_id
		//");
		//$stmt->execute();
		//$stmt->close();	

	}

public function ipExists($userIP) //This function checks if the users IP is already in the system
{
	global $mysqli,$db_table_prefix;
	$longIP = ip2long($userIP);
	$stmt = $mysqli->prepare("
	SELECT ip_address 
	FROM ip_list 
	WHERE ip_address = $longIP 
	LIMIT 1
	");	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

public function checkStats($ipID) //This function checks if the users IP is already in the stats database
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("
	SELECT id 
	FROM ip_stats 
	WHERE ip_id = ? 
	AND user_id = ? 
	LIMIT 1
	");
	$stmt->bind_param("ii", $ipID, $this->user_id);
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

public function setStats($ipID, $type) //sets a new row for the ip_stats table
{
		global $mysqli,$db_table_prefix;
		$stmt = $mysqli->prepare(" 
			INSERT 
			INTO ip_stats (ip_id, user_id, type) 
			VALUES (?, ?, ?)
			");
		$stmt->bind_param("iii", $ipID, $this->user_id, $type);	
		$stmt->execute();
		$stmt->close();	
}

public function fetchStats($ipID, $type) //fetch the number of rows of and IP request for a certain type
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("
	SELECT id 
	FROM ip_stats 
	WHERE ip_id = ? 
	AND type = ? 
	");
	$stmt->bind_param("ii", $ipID, $type);
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	return $num_returns;	
}

public function getIPID($userIP) //Returns the given IP addresses ID
{	
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT id FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		$stmt->close();
		return ($id);
}

public function getOwnerID($userIP) //Returns the given IP addresses owner
{	
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT owner_id FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		$stmt->close();
		return ($id);
}

public function ipOwned($userIP) //This function checks if the current user owns the IP they have
{
	global $loggedInUser,$mysqli,$db_table_prefix;
	$longIP = ip2long($userIP);
	$stmt = $mysqli->prepare("SELECT 
		id 
		FROM ip_list
		WHERE
		ip_address = ?
		AND 
		owner_id = ? 
		LIMIT 1");
	$stmt->bind_param("is", $longIP, $loggedInUser->user_id);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if($loggedInUser == NULL)
	{
		return false;
	}
	else
	{
		if ($num_returns > 0)
		{
			return true;
		}
		else
		{
			return false;	
		}
	}
}
public function stealIP($userIP, $oldID, $time)
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("
			UPDATE ip_list
			SET prior_id = $oldID, owner_id = ?, last_claim = $time, claim_expire = $time + 3600, 
			level = 1, base_value = base_value + 50, ip_value = 100 
			WHERE ip_address = ?");
		$stmt->bind_param("ii", $this->user_id, $longIP);	
		$stmt->execute();
		$stmt->close();	
}
public function fetchOwned() //This Function returns all user owned IP Addresses
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT ip_address FROM ip_list WHERE owner_id = ? ORDER BY level DESC");
	$stmt->bind_param("i", $this->user_id);
	$stmt->execute();
	$stmt->bind_result($ip);
	
	while ($stmt->fetch()){
		$row[] = array('tempIP' => $ip);
	}
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns == 0) {
		return FALSE;
	}
	else {
		return ($row);
	}
}

public function isfarmedIP($userIP) //This function tests if an IP can be farmed
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT claim_expire FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($expire);
		$stmt->fetch();
		$stmt->close();
		
		if ($expire < time()){
		return TRUE;
		}
		else {
		return FALSE;
		}
}

public function farmIP($userIP, $time) //This function starts the farming process
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT level FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($xlevel);
		$stmt->fetch();
		$stmt->close();
		
		if ($xlevel == 1) {
			$level = 2; 
			$expireTime = 14399;
			$award = 50;
			$base = 50;
			$next = 150;
		}
		else if($xlevel == 2) {
			$level = 3; 
			$expireTime = 28799;
			$award = 100;
			$base = 50;
			$next = 250;
		}
		else if($xlevel == 3) {
			$level = 4; 
			$expireTime = 43199;
			$award = 200;
			$base = 50;	
			$next = 450;
		}
		else if($xlevel == 4) {
			$level = 5; 
			$expireTime = 43199;
			$award = 400;
			$base = 50;
			$next = 500;
		}
		else {
			$level = $xlevel;
			$expireTime = 43199;
			$award = 400;
			$base = 100;
			$next = 500;
		}
		
		$stmt = $mysqli->prepare("
			UPDATE ip_list
			SET last_claim = $time, claim_expire = $time + $expireTime, 
			level = $level, base_value = base_value + $base, ip_value = $next 
			WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);	
		$stmt->execute();
		$stmt->close();	
		
		$stmt = $mysqli->prepare("
			UPDATE uc_users 
			SET points = points + $award 
			WHERE id = $this->user_id");	
		$stmt->execute();
		$stmt->close();	
}

public function checkCampus($userIP) //Checks that the IP is on a campus
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT level FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($xlevel);
		$stmt->fetch();
		$stmt->close();	
		
		if ($xlevel == 6) {return TRUE;}
		else {return FALSE;}
}

public function checkLimit($time) // Checks the number of times the user has claimed a new IP in the past 24 hours
{
		global $mysqli,$db_table_prefix;
		
		$stmt = $mysqli->prepare("SELECT id FROM ip_list WHERE founder_id = ? AND capture_stamp > ? - 86400");
		$stmt->bind_param("ii", $this->user_id, $time);
		$stmt->execute();
		$stmt->store_result();
		$num_returns = $stmt->num_rows;
		$stmt->close();
		
		if ($num_returns > 2) {return TRUE;}
		else {return FALSE;}
}

public function campusFarm($userIP, $time) //This function farms a campus IP Only
{
			global $mysqli,$db_table_prefix;
			$longIP = ip2long($userIP);
		
			$stmt = $mysqli->prepare("
				UPDATE ip_list
				SET last_claim = $time, claim_expire = $time + 7200, 
				owner_id = ?, base_value = base_value + 50, ip_value = 100 
				WHERE ip_address = ?");
			$stmt->bind_param("ii", $this->user_id, $longIP);	
			$stmt->execute();
			$stmt->close();	
			
			$stmt = $mysqli->prepare("
				UPDATE uc_users 
				SET points = points + 50 
				WHERE id = $this->user_id");	
			$stmt->execute();
			$stmt->close();			
}

public function farmTime($userIP, $time) //This Functions displays the time left on the current IP
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);
		
		$stmt = $mysqli->prepare("SELECT claim_expire FROM ip_list WHERE ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->bind_result($expire);
		$stmt->fetch();
		$stmt->close();
		
		return $expire;
		// $timeLeft = $expire - $time;
		// if ($timeLeft > 3600) { 
		// return Date('G \H\o\u\r\s', $timeLeft);
		// }
		// elseif ($timeLeft == 3600) {
			// return '1 Hour';
		// }
		// elseif ($timeLeft > 60) {
			// return Date('i \M\i\n\u\t\e\s', $timeLeft);
		// }
		// else {
			// return Date('s \S\e\c\o\n\d\s', $timeLeft);
		// }
		
}

public function  ipInfo($userIP) //Returns information about a given IP address
{
	global $mysqli,$db_table_prefix;
	$longIP = ip2long($userIP);	
	
	$stmt = $mysqli->prepare("SELECT name, base_value, ip_value, level FROM ip_list WHERE ip_address = ?");
	$stmt->bind_param("i", $longIP);
	$stmt->execute();
	$stmt->bind_result($name, $base, $value, $lvl);
	
	while ($stmt->fetch()){
		$row= [$name, $base, $value, $lvl];
	}
	$stmt->close();
	return $row;
}

public function updatePoints($userID) //This Function updates the users points
{
	global $mysqli,$db_table_prefix;
	
		$ipBase = 0;
		//Gets the users current points
		$stmt = $mysqli->prepare("SELECT points FROM ".$db_table_prefix."users WHERE id = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->bind_result($points);
		$stmt->fetch();
		$stmt->close();

		//Calculates every base_value the user owns
		$stmt = $mysqli->prepare("SELECT base_value FROM ip_list WHERE owner_id = ?");
		$stmt->bind_param("i", $userID);
		$stmt->execute();
		$stmt->bind_result($value);
		
		while ($stmt->fetch()){
			$ipBase = $ipBase + $value;
		}
		$stmt->close();
	
	$stmt = $mysqli->prepare("
			UPDATE uc_users 
			SET score = $points + $ipBase 
			WHERE id = $userID");	
		$stmt->execute();
		$stmt->close();	
		
	//RUN AT EVERY ACCOUNT PAGE LOAD: UPDATE uc_users SET score = $permScore + $ipBase WHERE id = $this->user_id
}

public function updateCountry($country)
{
	global $mysqli,$db_table_prefix;
		$stmt = $mysqli->prepare("
			UPDATE uc_users 
			SET country = ? 
			WHERE id = ?");	
		$stmt->bind_param("si", $country, $this->user_id);
		$stmt->execute();
		$stmt->close();	
}

public function updateName($userIP, $ipName) //Updates the IP Name
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);	
		
		$stmt = $mysqli->prepare("UPDATE ip_list 
			SET 
			name = ?
			WHERE
			ip_address = ?");
		$stmt->bind_param("si", $ipName, $longIP);
		$stmt->execute();
		$stmt->close();	
}

public function trashIP($userIP, $time) //Resets the Owner ID on an IP
{
		global $mysqli,$db_table_prefix;
		$longIP = ip2long($userIP);	
		
		$stmt = $mysqli->prepare("UPDATE ip_list 
			SET 
			owner_id = 0, level = 0, claim_expire = claim_expire + 3600, ip_value = base_value + 50 
			WHERE
			ip_address = ?");
		$stmt->bind_param("i", $longIP);
		$stmt->execute();
		$stmt->close();	

}

} ?>