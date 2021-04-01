<?php 

session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		$string= str_replace("\xE2\x80\x8B", "", trim(preg_replace('/[\n\r]/','',htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'))));
		
		$amb= addslashes($_POST['amb']);

		$user = new PG($_SESSION['pgID']);
		
		if($user->pgLock || $user->pgAuthOMA == 'BAN') exit; 
		if ($string == '' || $string == '+' || $string == '-' || $string == '@' || $string == '#') exit;
		

		else if($string == '*mst::sounder**')
		{
			
			$string = str_replace('-','',$string); 
			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Il sensore della porta suona, emettendo il suo inconfondibile squillo</div>'; 
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','voy_door_chime',".time().",'AUDIO',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		} 
		else if($string[0] == '-')
		{ 
			$ambient = Ambient::getAmbient($amb);

			
			$string = substr ($string,1);

			if (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID']))
			{
				$vali=new validator();
				$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
				
				$stag= strtoupper(addslashes($_POST['chatTag']));
				$tag = ($_POST['chatTag'] == '') ? '' : '<span class="chatTag">['.strtoupper(addslashes($_POST['chatTag'])).']</span>';
			
				//$string[0] = '';
				$sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));

				$quotes_from_array = array('&lt;','&gt;','[',']');
				$quotes_to_array = array(' <span class="chatQuotation">&laquo;','&raquo;</span> ',' <span class="chatQuotation">&laquo;','&raquo;</span> ');

				$MString = ucfirst(ltrim(str_replace($quotes_from_array,$quotes_to_array,$string)));
				 
				if($userSpecific != 0){
				$masSpec = 'MASTERSPEC';
				$received = addslashes(PG::getSomething($userSpecific,'username')); 
				$string = '<div style="position:relative;" class="specificMasterAction">
				<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni di un singolo personaggio, la visione di questo responso &egrave; riservata al master ed al giocatore del personaggio selezionato. Indica elementi di cui solo il personaggio sembra accorgersi come consolle personali, impressioni sensoriali, stati fisici e mentali"/> Responso Master: '.$received.'</div>'.$MString.'</div>';
				}

				else {  
				$masSpec= 'MASTER';
				$string = '<div style="position:relative;" data-timecode="'.$curTime.'" data-loctag="'.$stag.'" class="masterAction">
				<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nResponso di un Master o Junior Master alle azioni del/i personaggio/i o utilizzata per descrizioni ambientali di singole location di gioco."/> Responso Master</div>'.$MString.'</div>';
				}
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'$masSpec',$userSpecific,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
			}
			elseif($ambient['ambientType'] == 'SALA_OLO' && PG::mapPermissions('O',PG::getOMA($_SESSION['pgID'])) )
			{

				$sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));


				$string = '<div style="position:relative;" class="oloMasterAction">
				<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: '.$sended.' ('.date('H:i').')\nAzione descrittiva di un ambiente olografico responso di un Entertainer (un player abilitato a fornire esiti solo in simulazione e NON facente parte dello staff) collegata strettamente ad una sala ologrammi o sala parzialmente attrezzata per gli ologrammi. L\\\'entertainer non può masterare PnG o situazioni reali, ma solo ambienti e situazioni simulate sul ponte ologrammi."/> Entertainer</div>'.ucfirst(ltrim($string)).'</div>';

				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'MASTER',0,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 

			}
		}
		else if($string[0] == '=' && PG::mapPermissions('SM',PG::getOMA($_SESSION['pgID'])))
		{	
			$vali=new validator();
			$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
			
			$string = substr ($string,1);
			if($userSpecific != 0)
			
			$string = '<div style="position:relative;" class="offAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Inviata da: Moderazione\nAzione di moderazione che un Moderatore indirizza all\\\'attenzione del giocatore. Va interpretata come un avviso formale da parte della moderazione a correggere atteggiamenti che si allontanano dal regolamento di gioco o dai principi di netiquette. La moderazione pu&ograve; utilizzare questo strumento anche per suggerimenti pi&ugrave; bonari :-)"/> Moderazione (Privata)</div>'.ucfirst(ltrim($string)).'</div>'; 
			
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'SPECIFIC')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$userSpecific.",'$amb','$string',".time().",'SPECIFIC')");
		}
			
		else if($string[0] == '*')
		{ 


			$ambient = Ambient::getAmbient($amb);
			$image = str_replace('*','',$string); 

			if (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID']))
			{
				$vali=new validator();
				$userSpecific= (isSet($_POST['userSpecific'])) ? $vali->numberOnly($_POST['userSpecific']): 0;
				
				$stag= strtoupper(addslashes($_POST['chatTag']));
				$tag = ($_POST['chatTag'] == '') ? '' : '<span class="chatTag">['.strtoupper(addslashes($_POST['chatTag'])).']</span>';
			
				$sended = addslashes(PG::getSomething($_SESSION['pgID'],'username'));
				 
				if($userSpecific != 0){
				$masSpec = 'MASTERSPEC';
				$received = addslashes(PG::getSomething($userSpecific,'username')); 
				$string = '<p class="imageAction"><img  style="border:1px solid #FFCC00" src="'.$image.'" alt="'.$image.'"/></p>';
			
				}

				else {  
				$masSpec= 'IMAGE';
				$string = '<p class="imageAction"><img src="'.$image.'" alt="'.$image.'"/></p>';

			
				}
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'$masSpec',$userSpecific,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");

			}
			elseif($ambient['ambientType'] == 'SALA_OLO' && PG::mapPermissions('O',PG::getOMA($_SESSION['pgID'])) )
			{

				$string = '<p class="imageAction"><img src="'.$image.'" alt="'.str_replace('*','',$image).'"/></p>';
			

				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'IMAGE',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			}
		}

		else if($string[0] == '%' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{ 


			if($string == "%%"){
				mysql_query("DELETE FROM federation_chat WHERE ambient ='$amb' AND type = 'MPI'");
				$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Messaggio di Servizio"/> Service </div>Multimedia ripuliti</div>'; 
			
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'SPECIFIC')");
				exit;
			}

			else{
				$string = substr($string,1);
				if(strpos($string,'youtube.com') !== false)
				{
					$actionType='MPI';
					$epl=explode('v=',$string);
					$stringToSend='YT|'.$epl[1];	
				}

				else if(strpos($string,'https://youtu.be/') !== false)
				{
					$actionType='MPI';
					$epl=explode('youtu.be/',$string);
					$stringToSend='YT|'.$epl[1];	
				}

				else if(strpos($string,'vimeo.com') !== false)
				{
					$actionType='MPI';
					$epl=explode('vimeo.com/',$string);
					$stringToSend='VM|'.$epl[1];	
				}
 				else if($string[0] == '*')
				{
					$actionType='MPI';
					$stringToSend='CS|'.substr($string,1);
				}
				else
				{
					$actionType='AUDIOE';
					$stringToSend=$string;					
				}

				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringToSend',".time().",'$actionType',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))"); 
			}
		}
		
		else if($string[0] == '@' && (PG::mapPermissions('M',PG::getOMA($_SESSION['pgID'])) || PG::isMasCapable($_SESSION['pgID'])))
		{
			
			$string = substr ($string,1);
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
	
		
		else if ($string[0] == '$' && PG::mapPermissions('G',PG::getOMA($_SESSION['pgID'])))
		{
			$stringc = "<p data-timecode=\"$curTime\" class=\"directiveRemove\">".str_replace('$','',$string)."</p>";
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringc',".time().",'OFF',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
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
		$mostrina = $user->pgMostrina; 
		$olomostrina = false;
		if($user->pgMostrinaOlo != '') 
		{
			$ambient = Ambient::getAmbient($amb);
			if($ambient['ambientType'] == 'SALA_OLO')
			{
				$mostrina = $user->pgMostrinaOlo;
				$olomostrina = true; 
			}
		} 

		$ima1 = ($olomostrina) ? addslashes("<div style=\"width:70px; margin-left:5px; text-align:left; margin-right:4.5px; margin-top:2.5px; background-repeat:no-repeat; height:15px; background-image:url('TEMPLATES/img/ranks/$mostrina.png'); float:left;\"  title=\"$grado - Sezione $sezione\"><img onmouseover=\"javascript:getDotazione(this,$pgID);\" src=\"TEMPLATES/img/interface/3little_holoicon.png\" title=\"Mostrina Olografica\"></img></div>") : addslashes("<img style=\"vertical-align:middle;\" onmouseover=\"javascript:getDotazione(this,$pgID);\" src=\"TEMPLATES/img/ranks/$mostrina.png\" title=\"$grado - Sezione $sezione\"></img>");

 
		$ima2 = ($user->pgSesso == 'M') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_m.png\" alt='' title=\"$specie - Maschio\"></img>" : (($user->pgSesso == 'F') ? "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_f.png\" title=\"$specie - Femmina\" alt=''></img>" : "<img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/specie/".$specie."_t.png\" alt='' title=\"$specie - Sconosciuto\"></img>");
		$stag=strtoupper(addslashes($_POST['chatTag']));
		$tag = ($_POST['chatTag'] == '') ? '' : '<span class="chatTag">['.$stag.']</span>';
		
		$stringe = strtolower($string); 
		$realLen = strlen($string); 
 		

		$quotes_from_array = array('&lt;','&gt;','[',']');
		$quotes_to_array = array(' <span class="chatQuotation">&laquo;','&raquo;</span> ',' <span class="chatQuotation">&laquo;','&raquo;</span> ');
		
 		$string = ($olomostrina) ? '<div style="margin-top:4px"><div class="chatDirect" style="float:left;">'.date('H:i')."</div> $ima1 ".addslashes($ima2)." <span onclick=\'javascript:schedaPOpen($pgID);\' class=\'chatUser chatDirect\' onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\">$userN</span> <span class=\"chatDirect\">$tag ".str_replace($quotes_from_array,$quotes_to_array,$string)."</span></div>" : '<p class="chatDirect">'.date('H:i')." $ima1 ".addslashes($ima2)." <span onclick=\'javascript:schedaPOpen($pgID);\' class=\'chatUser chatDirect\' onmouseover=\"javascript:selectOccur(\'$userN\');\" onmouseout=\"deselectOccur();\" data-timecode=\"$curTime\">$userN</span> $tag ".str_replace($quotes_from_array,$quotes_to_array,$string)."</p>";


		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,realLen,privateAction) VALUES($pgID,'$amb','$string',".time().",'DIRECT',$realLen,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
		
		
		if(strpos($stringe,'capo master con incarichi speciali kavanagh') !== false)
			{
			mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_SESSION['pgID'].",'::special::achiev','L\'ho sentita.::Dieci punti a Serpeverde!',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')");
			}
	
		elseif((strpos($stringe,'computer, che ore sono') !== false) or (strpos($stringe,'computer che ore sono') !== false))
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Voce del Computer: &lt;Sono le ore '.date('H').', '.date('i').' minuti e '.date('s').' secondi&gt;</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
			}
			elseif((strpos($stringe,'computer, spegni le luci') !== false) or (strpos($stringe,'computer spegni le luci') !== false))
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si spengono</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 1 WHERE locID='$amb'");
			}
			
			elseif((strpos($stringe,'computer, accendi le luci') !== false) or (strpos($stringe,'computer accendi le luci') !== false))
			{ 
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si accendono</div>';  
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 5 WHERE locID='$amb'");
			}
	
			elseif((strpos($stringe,'computer, abbassa le luci') !== false) or (strpos($stringe,'computer abbassa le luci') !== false))
			{
				$stringe = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si abbassano di intensit&agrave;</div>'; 
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$stringe',".(time()+1).",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
				mysql_query("UPDATE fed_ambient SET ambientLight = 2 WHERE locID='$amb'");
			}
			

		PG::updatePresence($_SESSION['pgID']);
		}

?>						