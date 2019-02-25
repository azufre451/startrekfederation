<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		$user = new PG($_SESSION['pgID']);
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit;
		
		$vali = new validator();
		$mode = $vali->numberOnly($_POST['mode']);
			
		$amb= addslashes($_POST['amb']);
		$ambient = Ambient::getAmbient($amb);
		
	if($mode == 0)
	{	$string= (htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));	
		if($ambient['ambientType'] != 'ALLOGGIO' && $ambient['ambientType'] != 'REPLICATORE') exit;
		else 
		
		{
			$user = new PG($_SESSION['pgID']);
			$userN = addslashes($user->pgUser);
			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>'.$userN.' ha ordinato '.$string.'. Si materializza l\\\'ordinazione&nbsp;&nbsp;&nbsp;<img src="TEMPLATES/img/interface/replicatore_x.gif" style="vertical-align:middle;" alt="replicatore" /></div>';   
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','voy_replicator',".time().",'AUDIO')");
		}
	}
	if($mode == 1)
	{	$string= (htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));	

		if($ambient['ambientType'] != 'INFERMERIA') exit;
		else 
		{ 
			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Il bioletto si attiva e l\\\'arco sensorio si chiude. L\\\'analisi inizia.</div>'; 
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
	}
	if($mode == 2)
	{	$string= (htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));	

		if($ambient['ambientType'] != 'INFERMERIA') exit;
		else 
		{
			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>L\\\'arco sensorio si riapre e la scansione termina.</div>';  
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
	}
	
	if($mode == 3)
	{	$food= $_POST['food'];
		$label= $_POST['label']; 
		if($ambient['ambientType'] != 'ALLOGGIO' && $ambient['ambientType'] != 'REPLICATORE') exit;
		else 
		{
		
			if($food) 
			{
				$res = mysql_query("SELECT foodDescription,foodImage FROM fed_food WHERE foodID = $food"); 
				if(mysql_affected_rows())
				{	
					$foodDe = mysql_fetch_assoc($res);
					$foodDes= $foodDe['foodDescription'];
					$foodImage= $foodDe['foodImage'];
					
					$fLen = (strlen($foodDes) > 300) ? '<span>'.substr($foodDes,0,300).'<a href="javascript:void(0);" class="interfaceLink" onclick="repliOpenP('.$food.')"> [...] </a></span>' : '<span>'.$foodDes.'</span>';
			  
					$string = addslashes('<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente: Replicatore</div><div class="repliLine"><p class="repliLeft"><img src="'.$foodImage.'"></img></p><p class="repliRight">'.$user->pgUser.' ordina '.$label.'<br/>'.$fLen.'</p></div></div>');  
				}
			}
			else
				$string = addslashes('<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente: Replicatore</div>'.$user->pgUser.' ordina '.$label.'</div>'); 
			
			
			echo json_encode('ok');
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			if(isSet($food)) mysql_query("INSERT INTO fed_food_replications (food,timer,user) VALUES($food,$curTime,".$_SESSION['pgID'].')'); 
			
			if($food == 34) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','tasson',".time().",'AUDIO')");
			else if($food == 321) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','malkoth',".time().",'AUDIO')");
			else if($food == 342) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','mazurk',".time().",'AUDIO')");
			else if($food == 343) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','sandiego',".time().",'AUDIO')");
			else if($food == 334) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','ballor',".time().",'AUDIO')");
			else if($food == 348) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','bibbia',".time().",'AUDIO')");
			else if($food == 368) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','tumancia',".time().",'AUDIO')");
			



			

			else mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','voy_replicator',".time().",'AUDIO')");
		}
		 
	} 
	
	if($mode == 90) 
	{
		$targetpgID = $vali->numberOnly($_POST['chatLine']);
		if ($targetpgID == $_SESSION['pgID'] || PG::mapPermissions("SM",$user->pgAuthOMA))
		{
			$stringC = "<p data-timecode=\"$curTime\" class=\"directiveRemove\">".addslashes(PG::getSomething($targetpgID,'username'))."</p>";
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringC',".time().",'OFF',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
		 
	}
?>						