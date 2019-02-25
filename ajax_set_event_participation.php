<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
$vali = new Validator();

$eventID=$vali->numberOnly($_POST['eventID']);
$person=$vali->numberOnly($_SESSION['pgID']);

$ru = mysql_query("SELECT 1 FROM calendar_events_attendance WHERE pgID = $person AND evID = $eventID");
if (!mysql_affected_rows())
{
	mysql_query("INSERT INTO calendar_events_attendance (pgID,evID,time) VALUES ($person,$eventID,$curTime)");
	if(mysql_affected_rows())
	{
		$ral=mysql_fetch_assoc(mysql_query("SELECT pgAvatarSquare,pgUser,pgID FROM pg_users WHERE pgID = $person"));
		
		echo json_encode(array('mode'=>"ADD",'pgID'=>$person,'pgAvatarSquare' => $ral['pgAvatarSquare'],'pgUser'=>$ral['pgUser']));
	}
}
else {
	mysql_query("DELETE FROM calendar_events_attendance WHERE pgID = $person AND evID = $eventID");	
	if(mysql_affected_rows())
		echo json_encode(array('mode'=>"REM",'pgID'=>$person));
}



?>						