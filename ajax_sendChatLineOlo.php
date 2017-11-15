<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		$string= trim(preg_replace('/[\n\r]/','',htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8')));
		if ($string == '' || $string == '+' || $string == '-' || $string == '@' || $string == '#') exit;
		$user = new PG($_SESSION['pgID']);
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit;
		
		$amb= addslashes($_POST['amb']);
		
			if(in_array(strtolower($string),array('exit','+exit')))
			{ 
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','<p class=\"directiveRemove\">".addslashes($user->pgUser)."</p>',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				exit;
			} 
		
		// if($string[0] == '+')
		// {
			// $userN = addslashes($user->pgUser);
			// $tag = ($_POST['chatTag'] == '') ? '' : '['.(strtoupper(addslashes($_POST['chatTag']))).']';
			// $string[0] = '';
			
			// $stringe=strtolower($string);
			
			// $string = '<p class="chatAction">'.date('H:i')." <span class=\"actionUser\">$userN</span> ".$tag.' '.str_replace(array('&lt;','&gt;'),array('<span class="chatQuotationAction">&laquo;','&raquo;</span>'),$string).'</p>';			
			// mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'ACTION')");

		// }
		
		
		else if($string[0] == '-' && (PG::mapPermissions('O',PG::getOMA($_SESSION['pgID']))))
		{
			$vali=new validator();
			$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
			
			$string[0] = '';
			$sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));
			 
			if($userSpecific != 0){
			$masSpec = 'MASTERSPEC';
			$received = addslashes(PG::getSomething($userSpecific,'username'));
			$string = '<div style="position:relative;" class="specificMasterAction">
			<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni di un singolo personaggio, la visione di questo responso è riservata al master ed al giocatore del personaggio selezionato. Indica elementi di cui solo il personaggio sembra accorgersi come consolle personali, impressioni sensoriali, stati fisici e mentali"/> Responso Master: '.$received.'</div>'.ucfirst(ltrim($string)).'</div>';
			} 
			else {
			$masSpec= 'MASTER';
			if(PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])) // Se sei un master
			$string = '<div style="position:relative;" class="masterAction">
			<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni del/i personaggio/i o utilizzata per descrizioni ambientali di singole location di gioco."/> Responso Master</div>'.ucfirst(ltrim($string)).'</div>';
			else{  // Olomaster
				$string = '<div style="position:relative;" class="oloMasterAction">
			<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nAzione descrittiva di un ambiente olografico responso di un Entertainer (un player abilitato a fornire esiti solo in simulazione e NON facente parte dello staff) collegata strettamente ad una sala ologrammi o sala parzialmente attrezzata per gli ologrammi. L\\\'entertainer non può masterare PnG o situazioni reali, ma solo ambienti e situazioni simulate sul ponte ologrammi."/> Entertainer</div>'.ucfirst(ltrim($string)).'</div>';
				}
			} 
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'$masSpec',$userSpecific,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
		}
		
		else if($string[0] == '*' && (PG::mapPermissions('O',PG::getOMA($_SESSION['pgID']))))
		{ 
			$tag = ($_POST['chatTag'] == '') ? '' : '['.(strtoupper(addslashes($_POST['chatTag']))).']';
			$string = str_replace('*','',$string);
			$string = '<p class="imageAction"><a href="'.$string.'" target="_blank"><img src="'.$string.'" alt="'.$string.'"/></a></p>';
 
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'IMAGE',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		}
		
		else if($string[0] == '%' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{ 
			$string = str_replace('%','',$string);
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'AUDIOE',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
		}
		
		else if($string[0] == '=' && PG::mapPermissions('SM',PG::getOMA($_SESSION['pgID'])))
		{	$vali=new validator();
			$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
			$string[0] = '';
			if($userSpecific != 0)
			$string = '<div style="position:relative;" class="offAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: Moderazione\nAzione di moderazione che un Moderatore indirizza all\\\'attenzione del giocatore. Va interpretata come un avviso formale da parte della moderazione a correggere atteggiamenti che si allontanano dal regolamento di gioco o dai principi di netiquette. La moderazione pu&ograve; utilizzare questo strumento anche per suggerimenti pi&ugrave; bonari :-)"/> Moderazione (Privata)</div>'.ucfirst(ltrim($string)).'</div>'; 
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'SPECIFIC')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$userSpecific.",'$amb','$string',".time().",'SPECIFIC')");
		}
		
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
			$string[0] = '';
			
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
		
		else if ($string[0] == '$' && PG::mapPermissions('SM',PG::getOMA($_SESSION['pgID'])))
		{
			$stringc = "<p class=\"directiveRemove\">".str_replace('$','',$string)."</p>";
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$stringc',".time().",'NORMAL')");
			exit;
		}
		
		
		else
		{
		$userN = addslashes($user->pgUser);
		$grado = addslashes($user->pgGrado);
		$pgID=$_SESSION['pgID'];
		if($user->pgMostrinaOlo != '') 
		{
			$mostrina = $user->pgMostrinaOlo;
			$olomostrina = true;
			$tAlign = (substr($mostrina,0,1) == 'r') ? 'text-align:right;' : 'text-align:left;';
		}
		else $mostrina = $user->pgMostrina;
		
		$sezione = $user->pgSezione;
		$specie = $user->pgSpecie;
		
		$ima1 = ($olomostrina) ? addslashes("<div style=\"width:70px; margin-left:5px; $tAlign margin-right:4.5px; margin-top:2.5px; background-repeat:no-repeat; height:15px; background-image:url('TEMPLATES/img/ranks/$mostrina.png'); float:left;\" title=\"$grado - Sezione $sezione\"><img src=\"TEMPLATES/img/interface/3little_holoicon.png\" title=\"Mostrina Olografica\"></img></div>") : addslashes("<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/ranks/$mostrina.png\" title=\"$grado - Sezione $sezione\"></img>");
		$ima2 = ($user->pgSesso == 'M') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_m.png\" alt='' title=\"$specie - Maschio\"/>" : (($user->pgSesso == 'F') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_f.png\" title=\"$specie - Femmina\" alt=''/>" : "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_t.png\" alt='' title=\"$specie - Sconosciuto\"></img>");
		$tag = ($_POST['chatTag'] == '') ? '' : '<span class="chatTag">['.strtoupper(addslashes($_POST['chatTag'])).']</span>';
		
		$stringe = strtolower($string);
		$realLen = strlen($string);
		$string = ($olomostrina) ? '<div style="margin-top:4px"><div class="chatDirect" style="float:left;">'.date('H:i')."</div> $ima1 ".addslashes($ima2)." <span onclick=\'javascript:schedaPOpen($pgID);\' class=\'chatUser chatDirect\' onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\">$userN</span> <span class=\"chatDirect\">$tag ".str_replace(array('&lt;','&gt;'),array(' <span class="chatQuotation">&laquo;','&raquo;</span> '),$string)."</span></div>" : '<p class="chatDirect">'.date('H:i')." $ima1 ".addslashes($ima2)." <span onclick=\'javascript:schedaPOpen($pgID);\' class=\'chatUser chatDirect\' onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\">$userN</span> $tag ".str_replace(array('&lt;','&gt;'),array(' <span class="chatQuotation">&laquo;','&raquo;</span> '),$string)."</p>";
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,realLen,privateAction) VALUES($pgID,'$amb','$string',".time().",'DIRECT',$realLen,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		
			
		if($stringe == 'computer, che ore sono?')
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Voce del Computer: &lt;Sono le ore '.date('H').', '.date('i').' minuti e '.date('s').' secondi&gt;</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			}
			
			elseif($stringe == 'computer, spegni le luci')
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si spengono</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 1 WHERE locID='$amb'");
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
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 2 WHERE locID='$amb'");
			}
			

		PG::updatePresence($_SESSION['pgID']);
		}
		/* Laura backup System. Adds spaces after and before actions sent with < and >, to prevent people not using chat properly. (FIX-CODE: LAURA#21.2)*/
		/* Laura backup System. Forces TAG to  (FIX-CODE: LAURA#21.2)*/
?>						