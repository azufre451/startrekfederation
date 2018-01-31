<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		$string= str_replace("\xE2\x80\x8B", "", trim(preg_replace('/[\n\r]/','',htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'))));
		
		$amb= addslashes($_POST['amb']);
		$user = new PG($_SESSION['pgID']);
		$performExit = false;
		
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit; 
		if ($string == '' || $string == '+' || $string == '-' || $string == '@' || $string == '#') exit;
 
			if(in_array(strtolower($string),array('exit','+exit')))
			{ 
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','<p class=\"directiveRemove\">".addslashes($user->pgUser)."</p>',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0) )");
				exit;
			} 
			
		// if($string[0] == '+')
		// {
		
			// $userN = addslashes($user->pgUser);
			// $tag = ($_POST['chatTag'] == '') ? '' : '['.(strtoupper(addslashes($_POST['chatTag']))).']';
			// $string[0] = ''; 
			
			// $string = '<p class="chatAction">'.date('H:i')." <span class=\"actionUser\">$userN</span> ".$tag.' '.str_replace(array('&lt;','&gt;'),array('<span class="chatQuotationAction">&laquo;','&raquo;</span>'),$string).'</p>';			
			// mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'ACTION')");

		// }
		
		else if($string == '*mst::sounder**')
		{
			
			//$tag = ($_POST['chatTag'] == '') ? '' : '['.(strtoupper(addslashes($_POST['chatTag']))).']';
			$string = str_replace('-','',$string); 
			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Il sensore della porta suona, emettendo il suo inconfondibile squillo</div>'; 
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','voy_door_chime',".time().",'AUDIO',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
				
		else if($string[0] == '-' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{
			$vali=new validator();
			$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
			
			$string[0] = '';
			$sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));
			 
			if($userSpecific != 0){
			$masSpec = 'MASTERSPEC';
			$received = addslashes(PG::getSomething($userSpecific,'username')); 
			$string = '<div style="position:relative;" class="specificMasterAction">
			<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni di un singolo personaggio, la visione di questo responso &egrave; riservata al master ed al giocatore del personaggio selezionato. Indica elementi di cui solo il personaggio sembra accorgersi come consolle personali, impressioni sensoriali, stati fisici e mentali"/> Responso Master: '.$received.'</div>'.ucfirst(ltrim($string)).'</div>';
			}

			else {
			$masSpec= 'MASTER';
			$string = '<div style="position:relative;" class="masterAction">
			<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni del/i personaggio/i o utilizzata per descrizioni ambientali di singole location di gioco."/> Responso Master</div>'.ucfirst(ltrim($string)).'</div>';
			}
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'$masSpec',$userSpecific,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
		}
		else if($string[0] == '=' && PG::mapPermissions('SM',PG::getOMA($_SESSION['pgID'])))
		{
			$vali=new validator();
			$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
			$string[0] = '';
			if($userSpecific != 0)
			
			$string = '<div style="position:relative;" class="offAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: Moderazione\nAzione di moderazione che un Moderatore indirizza all\\\'attenzione del giocatore. Va interpretata come un avviso formale da parte della moderazione a correggere atteggiamenti che si allontanano dal regolamento di gioco o dai principi di netiquette. La moderazione pu&ograve; utilizzare questo strumento anche per suggerimenti pi&ugrave; bonari :-)"/> Moderazione (Privata)</div>'.ucfirst(ltrim($string)).'</div>'; 
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'SPECIFIC')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$userSpecific.",'$amb','$string',".time().",'SPECIFIC')");
		}
			
		else if($string[0] == '*' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{
			
			$tag = ($_POST['chatTag'] == '') ? '' : '['.(strtoupper(addslashes($_POST['chatTag']))).']';
			$string = str_replace('*','',$string);
			$string = '<p class="imageAction"><img src="'.$string.'" alt="'.$string.'"/></p>';
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'IMAGE',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
		
		else if($string[0] == '%' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{ 
			$string = str_replace('%','',$string);
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'AUDIOE',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
		}
		
		// else if(ltrim(substr($string,0,7)) == '_audio:' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID']))))
		// {
			
			// $string = substr($string,7);
			// $string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity">Comando Utente</div>Il sensore della porta suona, emettendo il suo inconfondibile squillo</div>'; 
			// $string = '<p class="auxAction">Audio di ambiente: <audio controls="controls"><source type="audio/ogg" src="'.$string.'" />Audio non supportato!</audio></p>';
			// mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'MASTER')");
		// }

		else if($string[0] == '@' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{
			$string[0] = '';
			 $sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));
			$string = '<div style="position:relative;" class="globalAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nEvento che un Master estende a pi&ugrave; location di gioco. Quando inserito esso &egrave; visibile in tutte le location dell\\\'unit&agrave;." /> Evento Globale</div>'.ucfirst(ltrim($string)).'</div>';
			$locations = mysql_query("SELECT locID FROM fed_ambient WHERE ambientLocation = (SELECT ambientLocation FROM fed_ambient WHERE locID = '$amb')");
			
			while ($resLoc = mysql_fetch_array($locations))
			{
				$ambientTo = $resLoc['locID'];
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'GLOBAL')");

			}
		}
		
		else if($string[0] == '#' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{
			$string = str_replace('#','',$string);
			$string = '<p class="imageAction"><img src="'.$string.'" alt="'.$string.'"/></p>';
			
			$locations = mysql_query("SELECT locID FROM fed_ambient WHERE ambientLocation = (SELECT ambientLocation FROM fed_ambient WHERE locID = '$amb')");
			

			while ($resLoc = mysql_fetch_array($locations))
			{
				$ambientTo = $resLoc['locID'];
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'GLOBAL_IMAGE')");
			}
		}
				
		else if ($string == 'CHAT_ZAP' && PG::mapPermissions('SM',PG::getOMA($_SESSION['pgID'])))
		{
			mysql_query("DELETE FROM federation_chat WHERE ambient = '$amb'");
			$string = '<p class="offAction">(CHAT AZZERATA)</p>';
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'OFF')");
			exit;
		}
		
		// else if ($string == 'ALL_ZAP' && PG::mapPerissions('A',PG::getOMA($_SESSION['pgID'])))
		// {
			// mysql_query("DELETE FROM federation_chat");
			// $string = '<p class="offAction">(TUTTE LE CHAT AZZERATE)</p>';
			// mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'OFF')");
			// exit;
		// }
		
		else if ($string[0] == '$' && PG::mapPermissions('G',PG::getOMA($_SESSION['pgID'])))
		{
			$stringc = "<p class=\"directiveRemove\">".str_replace('$','',$string)."</p>";
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringc',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			exit; 
		}
		
		else
		{
		$userN = addslashes($user->pgUser);
		$pgID=$_SESSION['pgID'];
		$grado = addslashes($user->pgGrado);
		$mostrina = $user->pgMostrina;
		$sezione = $user->pgSezione;
		$specie = $user->pgSpecie;
		$ima1 = addslashes("<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/ranks/$mostrina.png\" title=\"$grado - Sezione $sezione\"></img>");
		$ima2 = ($user->pgSesso == 'M') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_m.png\" alt='' title=\"$specie - Maschio\"></img>" : (($user->pgSesso == 'F') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_f.png\" title=\"$specie - Femmina\" alt=''></img>" : "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_t.png\" alt='' title=\"$specie - Sconosciuto\"></img>");
		$tag = ($_POST['chatTag'] == '') ? '' : '<span class="chatTag">['.strtoupper(addslashes($_POST['chatTag'])).']</span>';
		
		$stringe = strtolower($string); 
		$realLen = strlen($string); 
 		
 		$string = '<p class="chatDirect">'.date('H:i')." $ima1 ".addslashes($ima2)." <span onclick=\"javascript:schedaPOpen($pgID);\" onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\" class=\"chatUser\">$userN</span> $tag ".ucfirst(str_replace(array('&lt;','&gt;','&laquo;','&raquo;'),array(' <span class="chatQuotation">&laquo;','&raquo;</span> ',' <span class="chatQuotation">&laquo;','&raquo;</span> '),$string))."</p>";	

 		//$stringu = " $ima1 ".addslashes($ima2)." <span onclick=\"javascript:schedaPOpen($pgID);\" onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\" class=\"chatUser\">$userN</span> $tag ".ucfirst(str_replace(array('&lt;','&gt;'),array(' <span class="chatQuotation">&laquo;','&raquo;</span> '),$string))."</p>";

		//mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,realLen,privateAction) VALUES($pgID,'$amb',CONCAT(CONCAT('<p class=\"chatDirect\">',DATE_FORMAT(FROM_UNIXTIME( IF(EXISTS( (SELECT 1 FROM federation_sessions WHERE sessionPlace = '$amb' AND sessionStatus = 'ONGOING' AND sessionOverrideTime <> 0) ),((SELECT sessionOverrideTime FROM federation_sessions WHERE sessionPlace = '$amb' AND sessionStatus = 'ONGOING')),".time().") ), '%H %i')),'$stringu'),".time().",'DIRECT',$realLen,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
 
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,realLen,privateAction) VALUES($pgID,'$amb','$string',".time().",'DIRECT',$realLen,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		
			if(strpos($stringe,'capo master con incarichi speciali kavanagh') !== false)
			{
			mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_SESSION['pgID'].",'::special::achiev','L\'ho sentita.::Dieci punti a Serpeverde!',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')");
			}
		
			if($stringe == 'computer, che ore sono?')
			{ 
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity">Comando Utente</div>Voce del Computer: &lt;Sono le ore '.date('H').', '.date('i').' minuti e '.date('s').' secondi&gt;</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			}
			
			elseif($stringe == 'computer, spegni le luci')
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si spengono</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 5 WHERE locID='$amb'");
			} 
			elseif($stringe == 'computer, accendi le luci')
			{ 
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si accendono</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 5 WHERE locID='$amb'");
			}
			
			elseif($stringe == 'computer, abbassa le luci')
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si abbassano di intensit&agrave;</div>'; 
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER')");
				mysql_query("UPDATE fed_ambient SET ambientLight = 2 WHERE locID='$amb'");
			} 
		
		
		PG::updatePresence($_SESSION['pgID']);
		}
		 
			
		/* Laura backup System. Adds spaces after and before actions sent with < and >, to prevent people not using chat properly. (FIX-CODE: LAURA#21.2)*/
		/* Laura backup System. Forces TAG to  (FIX-CODE: LAURA#21.2)*/
?>						