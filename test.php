<?php

session_start();
include('includes/app_include.php');


$rea=mysql_query("SELECT sessionID,sessionStart,sessionEnd,sessionPlace,archived FROM federation_sessions");

while($res = mysql_fetch_assoc($rea)){

	echo '   '.$res['sessionID'].' '.$res['archived'].'<br />';
	$sessionPlace = $res['sessionPlace'];
	$sessionStart = $res['sessionStart'];
	$sessionEnd = $res['sessionEnd'];
 	$sessionID= $res['sessionID'];

	if ($res['archived'])
		echo "SELECT * FROM pg_users_pointStory WHERE causeM LIKE '%Log:$sessionID%'";
	else
		echo "SELECT * FROM federation_chat WHERE ambient='$sessionPlace' AND time BETWEEN $sessionStart AND $sessionEnd --";
}


?>