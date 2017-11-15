<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		
		$vali = new validator();  
		$user = $vali->numberOnly($_SESSION['pgID']);
		
		$toUser = addslashes($_POST['pgUser']);
		
		$ambient = addslashes($_POST['ambient']);
		
		$dUser = new PG($user);
		if($dUser->pgUser == $toUser)
		{
			$res = mysql_fetch_array(mysql_query("SELECT IDE,ambient FROM federation_chat WHERE sender = $user ORDER BY time DESC LIMIT 1"));
			$amb = $res['ambient'];
			$idc = $res['IDE'];
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES($user,(SELECT pgRoom FROM pg_users WHERE pgID = $user),'$command',".time().",'SPECIFIC')");
			
			
		}

?>						