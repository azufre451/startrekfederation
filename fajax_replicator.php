<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		$user = new PG($_SESSION['pgID']);
		$string= (htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));	
		
		
		//$userN = $user->pgUser;
		$amb= addslashes($_POST['amb']);
		$ambient = Ambient::getAmbient($amb);
		if($ambient['ambientType'] != 'ALLOGGIO' && $ambient['ambientType'] != 'REPLICATORE') exit;
		else 
		
		{
			$user = new PG($_SESSION['pgID']);
			$userN = addslashes($user->pgUser);
			$string = '<p class="auxAction">'.$userN.' ha ordinato '.$string.'. Si materializza l\\\'ordinazione&nbsp;&nbsp;&nbsp;<img src="TEMPLATES/img/interface/replicatore_x.gif" style="vertical-align:middle;" alt="replicatore" /></p>';
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','voy_replicator',".time().",'AUDIO')");
		}


?>						