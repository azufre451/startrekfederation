<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		
		$vali = new validator();  
		
		$dest= ($_POST['destination']);
		$victim = $vali->numberOnly($_POST['sended']);
		
		$user = new PG($_SESSION['pgID']);
		$userName = $user->pgUser;
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit;
		
		$string = addslashes("<script>doRedirectToMona('$dest')</script>");
		
$string1 = '<div style="position:relative;" data-timecode="'.$curTime.'" data-loctag="'.$stag.'" class="auxAction">
				<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$userName.' ('.date('H:i').')\nAzione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)."/> Teletrasporto</div>'.addslashes(PG::getSomething($victim,'username')).' si smaterializza per effetto del teletrasporto</div>';
			
$string2 = '<div style="position:relative;" data-timecode="'.$curTime.'" data-loctag="'.$stag.'" class="auxAction">
				<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$userName.' ('.date('H:i').')\nAzione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)."/> Teletrasporto</div>'.addslashes(PG::getSomething($victim,'username')).' si materializza per effetto del teletrasporto</div>';

		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",(SELECT pgRoom FROM pg_users WHERE pgID = $victim),'$string1',".time().",'MASTER')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$dest','$string2',".time().",'MASTER')");		
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES($victim,(SELECT pgRoom FROM pg_users WHERE pgID = $victim),'$string',".time().",'SPECIFIC')");
		
		//echo "INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES($victim,'$amb','$string',".time().",'SPECIFIC')";
		
		PG::updatePresence($_SESSION['pgID']);
		

					 

?>	