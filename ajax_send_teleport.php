<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		
		$vali = new validator();  
		
		$dest= ($_POST['destination']);
		$victim = $vali->numberOnly($_POST['sended']);
		
		$user = new PG($_SESSION['pgID']);
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit;
		
		$string = addslashes("<script>doRedirectToMona('$dest')</script>");
		
		$string1 = '<p class="chatAction">'.date('H:i').' '.addslashes(PG::getSomething($victim,'username')).' si smaterializza per effetto del teletrasporto</p>';			
		$string2 = '<p class="chatAction">'.date('H:i').' '.addslashes(PG::getSomething($victim,'username')).' si materializza per effetto del teletrasporto</p>';
		
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",(SELECT pgRoom FROM pg_users WHERE pgID = $victim),'$string1',".time().",'ACTION')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$dest','$string2',".time().",'ACTION')");		
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES($victim,(SELECT pgRoom FROM pg_users WHERE pgID = $victim),'$string',".time().",'SPECIFIC')");
		
		//echo "INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES($victim,'$amb','$string',".time().",'SPECIFIC')";
		
		PG::updatePresence($_SESSION['pgID']);
		

?>						