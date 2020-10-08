<?php
session_start();
include('includes/app_include.php');
include('includes/validate_class.php');

 


if(isSet($_GET['registerUser']))
{	
	$pgTarget= explode('_',$_POST['select_grado']);

	$vali = new validator();
	
	
	$pgName= ucfirst(addslashes(($_POST['select_cognome'])));
	$pgNameFirst= ucfirst(addslashes(($_POST['select_nome'])));

	$emai= (htmlentities(addslashes(($_POST['select_email'])),ENT_COMPAT, 'UTF-8'));
	
	$pgSpecie= (htmlentities(addslashes(($_POST['select_razza'])),ENT_COMPAT, 'UTF-8'));

	$abil = array(
		'HT'=>$vali->numberOnly($_POST['select_ht']),
		'PE'=>$vali->numberOnly($_POST['select_pe']),
		'WP'=>$vali->numberOnly($_POST['select_wp']),
		'IQ'=>$vali->numberOnly($_POST['select_iq']),
		'DX'=>$vali->numberOnly($_POST['select_dx'])
		);
	
	$abM8=0;
	foreach($abil as $ra => $sa){if ($sa > 8) $abM8 = 1;}

	$pgTarget= explode('_',$_POST['select_grado']);
	$pgRealTarget= $vali->numberOnly($pgTarget[0]);
	
	$pgSesso= strtoupper(htmlentities(addslashes(($_POST['select_sesso'])),ENT_COMPAT, 'UTF-8'));
	$pgAuth= (htmlentities(addslashes(($_POST['pgAuth'])),ENT_COMPAT, 'UTF-8'));

    //832 : tenente JG COM-Strat

	$allRanks=array(837,827,836,831,826,813,809,834,829,824,811,833,828,823,810,806,835,830,825,812,808,101,119,137,155,173,125,143,161,179,103,121,139,157,175,135,153,171,191,127,145,1);
	$militaryRanks=array(837,827,836,831,826,813,809,834,829,824,811,833,828,823,810,806,835,830,825,812,808,1);

	if ($pgName =='' || !filter_var($emai, FILTER_VALIDATE_EMAIL) || $pgSpecie == '' || $pgSesso == '' || array_sum($abil) != 23 || $abM8 || !in_array($pgSpecie,array('Andoriana','Terosiana','Trill','Umana','Vulcaniana','Bajoriana')) || !in_array($pgRealTarget,$militaryRanks)) 
	{	
		echo json_encode(array('err'=>'IE'));
		exit;
	}
	

	$passer = createRandomPassword();
	$matri = createRandomMatricola(); 
	$pwd = md5($passer);
	$assignTOSHIP = 'SBSM';

	$re1=mysql_query("SELECT 1 FROM pg_users WHERE email = '$emai'");
	if (mysql_affected_rows() && $emai != 'png@stfederation.it'){echo json_encode(array('err'=>'ME')); exit;}
	
	$re1=mysql_query("SELECT 1 FROM pg_users WHERE pgUser = '$pgName'");
	if (mysql_affected_rows()){echo json_encode(array('err'=>'UE')); exit;}
	
	$curTimeLL = $curTime - 1801;	  
	 
	mysql_query("INSERT INTO pg_users(pgUser,pgNomeC, pgPass, pgGrado, pgSezione, pgAssign, pgSeclar, pgAuth, pgLocation, pgRoom, pgAuthOMA, pgSpecie, pgSesso, pgMostrina, rankCode, email,pgLock,pgFirst,iscriDate,pgPoints,audioEnable,pgMatricola,pgIncarico,pgLastAct,pgUpgradePoints,paddMail) VALUES ('$pgName','$pgNameFirst','$pwd','Civile','Nessuna','$assignTOSHIP',1,'$pgAuth','$assignTOSHIP','$assignTOSHIP','N','$pgSpecie','$pgSesso','CIV',1,'$emai',1,1,$curTime,10,1,'$matri','In attesa di assegnazione',$curTimeLL,700,0)");
	

	$pipo = mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '".$pgName."'"));
	$pgNew = new PG($pipo['pgID'],2);
	$pgNewID = $pgNew->ID;


	mysql_query("UPDATE pg_users SET mainPG = $pgNewID, pgType = 'MAIN' WHERE pgID = $pgNewID");
	mysql_query("INSERT INTO pg_users_bios (pgID) VALUES ($pgNewID)");
	
	mysql_query("INSERT INTO pg_imbarchi (pgID,dateInsert,toAppear) VALUES ($pgNewID,".time().",1)");
	
	/*Aggiungo achi iniziale */
	mysql_query("INSERT INTO pg_achievement_assign (owner,achi,timer) VALUES ($pgNewID,33,".time().")");
	
	mysql_query("INSERT INTO pg_users_pointStory (owner,points,cause,causeM,causeE,timer,assigner) VALUES ($pgNewID,10,'ISCR','Bonus iscrizione a STF','Bonus iscrizione a STF',".time().",1)");
	
	
	$pgNew->sendWelcomePadd();
 
	
	
	mysql_query("INSERT INTO connlog (user,time,ip) VALUES ($pgNewID,$curTime,'".$_SERVER['REMOTE_ADDR']."')");
	$pgName=stripslashes($pgName);
	mail($emai,"Star Trek Federation - Benvenuto!","Star Trek Federation - Benvenuto:\n\nCiao, $pgName,\n\nTu, o qualcuno per te, ha provveduto ad eseguire la registrazione del tuo indirizzo email a Star Trek Federation. L'operazione ha avuto esito positivo.\n\nUSERNAME: $pgName\nPASSWORD: $passer\n\nPotrai cambiare la password loggandoti in Star Trek Federation al link http://www.stfederation.it\n\nCi auguriamo di vederti presto tra noi!\nIl team di Star Trek: Federation.","From:staff@stfederation.it");
		
	PG::setMostrina($pgNewID,$pgRealTarget);

	foreach($abil as $keller => $valler)
		mysql_query("INSERT INTO pg_abilita_levels (pgID,abID,value) VALUES ((SELECT pgID FROM pg_users WHERE pgUser = '".$pgName."'),'$keller','$valler')");
	
	$abio = new abilDescriptor($pgNew->ID);
	$abio->superImposeRace($pgSpecie);

 		$me = $pgNew->ID;
		//PADD
		$trics=array(100,115,72);
		$trics_med=array(133,134,135);
		$trics_ing=array(136,137,138);
		mysql_query("DELETE FROM pg_current_dotazione WHERE owner = $me AND type='OBJECT' AND ref IN (SELECT oID FROM fed_objects WHERE oType ='SERVICE')");
		mysql_query("DELETE FROM fed_objects_ownership WHERE owner = $me AND oID IN (SELECT oID FROM fed_objects WHERE oType ='SERVICE')");
		

		if($pgNew->pgSezione == 'Scientifica e Medica')
		{
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (122,$me),(128,$me)");
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (".$trics_med[array_rand($trics_med)].",$me)");

		}
		elseif($pgNew->pgSezione == 'Difesa e Sicurezza')
		{	
			$ro=mysql_fetch_assoc(mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $me"));
			if((int)($ro['rankCode']) >= 823){
				mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (2,$me),(116,$me)");

			}
			else
				mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (97,$me)");

			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (98,$me)");

		}
		elseif($pgNew->pgSezione == 'Ingegneria e Operazioni')
		{
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (120,$me),(129,$me)");
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (".$trics_ing[array_rand($trics_ing)].",$me)");
		}

		elseif($pgNew->pgSezione == 'Comando e Strategia')
		{
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (98,$me)");
		}
		else{
			mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (".$trics[array_rand($trics)].",$me)");
		}


		mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (1,$me),(124,$me),(118,$me),(119,$me)");

	
  
	echo json_encode('OK');
	exec('/home/ND-47/tools/miniconda3/bin/python /home/ND-47/public_html/utils/slack_notifier.py newuser '.$me.' 1');
	exit;
	
}

if(isSet($_GET['createPNG']))
{
//	return;
	$pgName= ucfirst(addslashes(($_POST['pgName'])));
	$emai= 'png@stfederation.it';
	$pgSpecie= (htmlentities(addslashes(($_POST['specie'])),ENT_COMPAT, 'UTF-8'));
	$pgSesso= (htmlentities(addslashes(($_POST['pgSesso'])),ENT_COMPAT, 'UTF-8'));
	$passer = addslashes($_POST['password']);
	$pgPassword1 = md5($passer);
	
	$a1 = array('ALFA','BETA','GAMMA','DELTA','ETA','EPSILON','ZETA','ETA','THETA','IOTA','KAPPA','LAMBDA','MI','NI','XI','OMICRON','PI','RHO','SIGMA','TAU','YPSILON','PHI','CHI','PSI','OMEGA');
	$pgAuth= $a1[rand(0,24)].' '.$a1[rand(0,24)].' '.rand(0,10).' '.rand(0,10);
	
	if ($pgName =='' || $emai == '' || $pgSpecie == '' || $pgSesso == '') 
	{	
		header('Location:index.php?error=insertion_error');
		exit;
	}
	
	$assignTOSHIP = 'SOL';
	
	$currentUser = new PG($_SESSION['pgID']);
	if(PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
	
	$re1=mysql_query("SELECT 1 FROM pg_users WHERE pgUser = '$pgName'");
	if (mysql_affected_rows()){header("Location:index.php?error=96"); exit;}
	
	mysql_query("INSERT INTO pg_users(pgUser, pgPass, pgGrado, pgSezione, pgAssign, pgSeclar, pgAuth, pgLocation, pgRoom, pgAuthOMA, pgSpecie, pgSesso, pgMostrina, rankCode, email,pgLock,pgFirst,png, pgNote, pgMatricola) VALUES ('$pgName','$pgPassword1','Civile','Nessuna','$assignTOSHIP',1,'$pgAuth','$assignTOSHIP','$assignTOSHIP','N','$pgSpecie','$pgSesso','CIV',1,'$emai',0,0,1,'Password: $passer','".createRandomMatricola()."')");
	
	mysql_query("INSERT INTO pg_users_bios (pgID) VALUES ((SELECT pgID FROM pg_users WHERE pgUser = '$pgName'))");
	
	
	mysql_query("INSERT INTO connlog (user,time,ip) VALUES ((SELECT pgID FROM pg_users WHERE pgUser = '$pgName'),$curTime,'".$_SERVER['REMOTE_ADDR']."')");
	
	$pgName=stripslashes($pgName);
	
	// mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddTest,paddTime,paddRead) VALUES (1,,'Benvenuto!','Ciao<br />Benvenuto in Star Trek Federation. Ti invitiamo a consultare la guida al gioco ed il regolamento e a porre qualunque domanda ai master o agli admin loggati. Buon gioco,<br />Il team di Star Trek Federation',".time().",0)");
	
	if(!mysql_error()){
	header('Location:crew.php?equi=SOL');
	exit;
	}
	else
	{
		if (mysql_affected_rows()){header("Location:index.php?error=99"); exit;}
		exit;
	}
	
	}
}


if(isSet($_GET['addCallComment']))
{
$vali = new validator();
$callID = $vali->numberOnly($_POST['callID']);
$type = (addslashes($_POST['testoComm']));
$type = substr($type,0,255);
if(trim($type) != '') mysql_query("INSERT INTO cdb_calls_comments(owner,callID,text,timer) VALUES(".$_SESSION['pgID'].",$callID,'$type',".time().")");
header("Location:cdb.php?callView=$callID");
exit;
}

if (!isSet($_SESSION['pgID'])){echo "Errore di Login. Ritorna alla homepage ed effettua il login correttamente!"; exit;}



$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);
 
if (isSet($_GET['setAlert']))
{
	if(!PG::mapPermissions('M',$currentUser->pgAuthOMA))
		exit;

	$g = $_GET['setAlert'];
	$place = addslashes($_GET['place']);
	
	if($g == 'red' || $g=='intruder' || $g == 'yellow' || $g == 'blue' || $g == 'green' || $g == 'grey' || $g == 'quarantine')
	{
		if($g=='red') mysql_query("UPDATE fed_ambient SET ambientLightColor='#d10000' WHERE ambientLocation='$place'");
		else if($g=='yellow') mysql_query("UPDATE fed_ambient SET ambientLightColor='#e8cd05' WHERE ambientLocation='$place'");
		else if($g=='green') mysql_query("UPDATE fed_ambient SET ambientLightColor='#ffeecb' WHERE ambientLocation='$place'");
		else if($g=='grey' || $g=='quarantine'){ mysql_query("UPDATE fed_ambient SET ambientLight=2 WHERE ambientLocation='$place'");}
		else if($g=='blue') mysql_query("UPDATE fed_ambient SET ambientLightColor='#3f79ba' WHERE ambientLocation='$place'");
		
		$toset = $g.'Alert';
		mysql_query("UPDATE pg_places SET placeAlert = '$toset' WHERE placeID = '$place'");
		
		$string = '<p class="globalAction">Su tutta la nave si attiva '.str_replace(array('redAlert','yellowAlert','blueAlert','greenAlert','greyAlert','quarantineAlert','intruderAlert'),array('l\\\'allarme Rosso','l\\\'allarme Giallo','la condizione Blu','la condizione Verde','La condizione Grigia: tutti i sistemi non vitali vengono disattivati e l\\\'illuminazione viene ridotta al minimo.','la condizione di Quarantena. Voce del Computer: < Attenzione Procedura di Quarantena attivata. Da questo momento risulta in vigore la procedura di quarantena di Livello Tre. Tutte le autorizzazioni di sbarco e imbarco sono revocate con effetto immediato. Il personale non in servizio deve fare ritorno ai propri alloggi. Questa non è un\\\'esercitazione.>','L\\\'allarme Intruso'),$toset).'</p>';
		$locations = mysql_query("SELECT locID FROM fed_ambient WHERE ambientLocation = '$place'"); 
		 
		 
		while ($resLoc = mysql_fetch_array($locations))
		{
				$ambientTo = $resLoc['locID'];
				 
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'GLOBAL')");
				
				if($g == 'red') mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','redalert',".time().",'AUDIO')");
				if($g == 'intruder' || $g=='quarantine') mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','intruderAlert',".time().",'AUDIO')");
		} 
		header('Location:padd.php?s=sh');
		exit;
	}
}


else if (isSet($_GET['bavosize']))
{
	$to = $vali->numberOnly($_GET['bavosize']);
	$dest = ($_GET['place']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
 
	mysql_query("UPDATE pg_users SET pgLocation = 'BAVO', pgRoom ='BAVO', pgAssign='BAVO' WHERE pgID = $to");
	mysql_query("DELETE FROM pg_alloggi WHERE pgID = $to");
	//mysql_query("DELETE FROM fed_ambient WHERE locID = (SELECT pgAlloggio FROM pg_users WHERE pgID = $to)");
	mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (".$_SESSION['pgID'].", $to, 'Importante', 'Ciao. La presente per comunicarti che sei stato spostato in una locazione di attesa a causa di ridotta attivita\' in gioco per molto tempo. Contatta la crew al piu\' presto!',".time().",0)");
	
	$pg = new PG($to);
	$pgName = $pg->pgUser;
	//mail(PG::getSomething($to,'email'),"Star Trek Federation - Ti abbiamo perso di vista!","Ciao $pgName,\n\nSono passati due mesi dal tuo ultimo login in Star Trek: Federation. Per garantire uno sviluppo funzionale degli organigrammi di bordo, il tuo personaggio verrà spostato in altra locazione a partire da oggi. Ci auguriamo di rivederti presto fra noi, e ti assicuriamo che, in caso volessi tornare, il tuo PG sarà mantenuto attivo per altri 30 giorni. Al termine dei 30 giorni, il PG sarà eliminato dai nostri server.\n\n A presto\n\nIl team di Star Trek: Federation\n\nhttp://www.stfederation.it","From:staff@stfederation.it");
	}
	header("Location:crew.php?equi=$dest");
	exit;
}

else if (isSet($_GET['mostrina']))
{
	$to = $vali->numberOnly($_GET['mostrina']);
	$dest = ($_GET['place']);
	
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA))
	{
		$selectable = new PG($to);
		if ($selectable->pgSpecie == 'Romulana')
		{
			$imaMostrina = 301;
			$textRecluta = "Eredh in Addestramento";
		}
		
		else 
		{	
			$imaMostrina = 302;
			$textRecluta = "Recluta in Addestramento";
		}
		
		PG::setMostrina($to,$imaMostrina);
		PG::setIncarico($to,$textRecluta);
	}
	
	header("Location:crew.php?equi=$dest");
	exit;
}

else if (isSet($_GET['delete']))
{
	$to = $vali->numberOnly($_GET['delete']);
	$pgID = $to;
	$dest = ($_GET['place']);
	$timeString =substr(time(),4,4).'_';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
	//mysql_query("UPDATE cdb_posts SET owner = 6 WHERE owner = $to");

mysql_query("UPDATE calendar_events SET 'sender' = 6 WHERE sender = '$pgID';");
mysql_query("UPDATE cdb_calls_comments SET 'owner' = 6 WHERE owner = '$pgID';");
mysql_query("UPDATE cdb_calls_results SET 'pgUser' = 6 WHERE pgUser = '$pgID';");
mysql_query("UPDATE cdb_posts SET 'owner' = 6 WHERE owner = '$pgID';");
mysql_query("UPDATE cdb_posts SET 'coOwner' = 6 WHERE coOwner = '$pgID';");
mysql_query("UPDATE cdb_topics SET 'topicLastUser' = 6 WHERE topicLastUser = '$pgID';");
mysql_query("UPDATE db_elements SET 'lvisit' = 6 WHERE lvisit = '$pgID';");
mysql_query("UPDATE federation_chat SET 'sender' = 6 WHERE sender = '$pgID';");
mysql_query("UPDATE federation_sessions SET 'sessionOwner' = 6 WHERE sessionOwner = '$pgID';");
mysql_query("UPDATE fed_pad SET 'paddFrom' = 6 WHERE paddFrom = '$pgID';");
mysql_query("UPDATE fed_pad SET 'paddTo' = 6 WHERE paddTo = '$pgID';");
mysql_query("UPDATE fed_sussurri SET 'susFrom' = 6 WHERE susFrom = '$pgID';");
mysql_query("UPDATE fed_sussurri SET 'susTo' = 6 WHERE susTo = '$pgID';");
mysql_query("UPDATE pg_notes SET 'owner' = 6 WHERE owner = '$pgID';");
mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE pgID = '$pgID';");
#mysql_query("DELETE FROM connlog WHERE user = '$pgID';");
mysql_query("DELETE FROM fed_ambient_auth WHERE pgID = '$pgID';");
mysql_query("DELETE FROM fed_food_replications WHERE user = '$pgID';");
mysql_query("DELETE FROM pgDotazioni WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pgMedica WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_abilita_levels WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_achievement_assign WHERE owner = '$pgID';");
mysql_query("DELETE FROM pg_alloggi WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_brevetti_assign WHERE owner = '$pgID';");
mysql_query("DELETE FROM pg_groups_ppl WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_objects WHERE owner = '$pgID';");
mysql_query("DELETE FROM pg_service_stories WHERE owner = '$pgID';");
mysql_query("DELETE FROM pg_users_bios WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_users_pointStory WHERE owner = '$pgID';");
mysql_query("DELETE FROM pg_users_tutorial WHERE pgID = '$pgID';");
mysql_query("DELETE FROM pg_user_stories WHERE pgID = '$pgID';");
mysql_query("UPDATE pg_users SET pgUser = CONCAT('$timeString',pgUser), pgLocation = 'BAVO',pgAssign='BAVO', email = CONCAT('$timeString',email), pgRoom ='BAVO',pgIncarico = '-', pgAuthOMA='BAN', pgOffAvatarC = '', pgOffAvatarN='' WHERE pgID = $to");

	}
	header("Location:crew.php?equi=$dest");
	exit;
}

else if (isSet($_GET['updateStatus']))
{
	$statu = "";
	if(isSet($_GET['place']) && isSet($_POST['note']))
	{
	$place = addslashes($_GET['place']);
	$doki = addslashes($_POST['note']);
	str_replace(array('<iframe>','<frame>','<object>','<embed>','<img>','<script>'),array('iframe','frame','object','embed','img','script'),$doki);
	
	for($i = 0; $i<12;$i++)
		$statu.=$vali->numberOnly($_POST[$i]).',';
	
	$statu = trim($statu,',');
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_places SET status = '$statu', note = '$doki' WHERE placeID = '$place'");
	}
	header('Location:padd.php?s=sh');
	exit;
}

else if(isSet($_GET['comm']))
{
	$type = $_GET['comm'];
	if($currentUser->pgLock){header('Location:comm.php'); exit;}
	if ($type=='sendCommUser')
	{
		$to = (htmlentities(addslashes(($_POST['personTo'])),ENT_COMPAT, 'UTF-8'));
		$row = (htmlentities(addslashes(($_POST['rowSend'])),ENT_COMPAT, 'UTF-8'));
		
		
		
		if($to != 0)
		{
			$toQ = mysql_query('SELECT pgUser, pgRoom FROM pg_users WHERE pgID = '.$to);
			$toQE = mysql_fetch_array($toQ);
			$toRoom = $toQE['pgRoom'];
			$toPg = addslashes($toQE['pgUser']);
			
			
			$string = '<p class="commMessage">'.date('H:i').' <span class="commPreamble">'.addslashes($currentUser->pgUser)." a $toPg:</span> ".$row.'</p>';
			if($toRoom != $currentUser->pgRoom) mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'".$currentUser->pgRoom."','$string',".time().",'ACTION')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','$string',".time().",'ACTION')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','commbadge',".time().",'AUDIO')");
		}
		
		else if ($to == 0)
		{
			$allAmb = mysql_query('SELECT locID FROM fed_ambient WHERE ambientLocation = \''.$currentUser->pgLocation.'\' AND locID <> \''.$currentUser->pgLocation.'\'');
			
			$string = '<p class="commMessage">'.date('H:i').' <span class="commPreamble">'.addslashes($currentUser->pgUser)." a tutto il personale:</span> ".$row.'</p>';
			
			while($rea = mysql_fetch_array($allAmb))
			{
			$toRoom = $rea['locID'];
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','$string',".time().",'ACTION')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','commbadge',".time().",'AUDIO')");
			// mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','<audio autoplay=\"autoplay\"><source src=\"https://oscar.stfederation.it/audioBase/commbadge.ogg\" type=\"audio/ogg\" /><source src=\"https://oscar.stfederation.it/audioBase/commbadge.mp3\" type=\"audio/mpeg\" /></audio>',".time().",'AUDIO')");
			}
		}
	}
	
	else if ($type=='sendCommDeck')
	{
		$to = addslashes($_POST['deckTo']);
		
		
		$row = (htmlentities(addslashes(($_POST['rowSend'])),ENT_COMPAT, 'UTF-8'));
		
		$allAmb = mysql_query('SELECT locID FROM fed_ambient WHERE ambientLocation = \''.$currentUser->pgLocation.'\' AND ambientLevel_deck = \''.$to.'\' AND locID <> \''.$currentUser->pgLocation.'\'');
			
			$string = '<p class="commMessage">'.date('H:i').' <span class="commPreamble">'.addslashes($currentUser->pgUser)." a PONTE $to:</span> ".$row.'</p>';
			//mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'".$currentUser->pgRoom."','$string',".time().",'ACTION')");
			
			while($rea = mysql_fetch_array($allAmb))
			{
			$toRoom = $rea['locID'];
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','$string',".time().",'ACTION')");
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$toRoom','commbadge',".time().",'AUDIO')");
			}
	}
	
	else if ($type=='sendCommAmbient')
	{
		$to = (htmlentities(addslashes(($_POST['ambTo'])),ENT_COMPAT, 'UTF-8'));
		$row = (htmlentities(addslashes(($_POST['rowSend'])),ENT_COMPAT, 'UTF-8'));
		
		if(!$ambientName = Ambient::getAmbientName($to)) exit;
		
		$string = '<p class="commMessage">'.date('H:i').' <span class="commPreamble">'.addslashes($currentUser->pgUser)." a $ambientName:</span> ".$row.'</p>';
			
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$to','$string',".time().",'ACTION')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$to','commbadge',".time().",'AUDIO')");
		
		
		if($to != $currentUser->pgRoom){ mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'".$currentUser->pgRoom."','$string',".time().",'ACTION')");}
		
	}
	
	echo "<html><script type='text/javascript'>window.close();</script></html>";
}

else if(isSet($_GET['warpSpeed']))
{
		if(isSet($_POST['factor'])){
		$place = addslashes($_GET['warpSpeed']);
		$fact = addslashes($_POST['factor']);
		$fact = str_replace(',','.',$fact);
		
		if(PG::mapPermissions('M',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_places SET warp = $fact, pointerL='', attracco='' WHERE placeID = '$place' AND (placeType = 'Nave' OR placeType = 'Navetta')");
		mysql_query("UPDATE pg_places SET warp = $fact, pointerL='' WHERE attracco = '$place' AND placeType = 'Navetta'");
			
			$locations = mysql_query("SELECT locID,placeName FROM fed_ambient,pg_places WHERE ambientLocation = placeId AND ambientLocation = '$place'");
			while ($resLoc = mysql_fetch_array($locations))
			{
				$ambientTo = $resLoc['locID'];
				$placeName = $resLoc['placeName'];
				$string = "<p class=\"globalAction\" title=\"Warp\">La $placeName entra in curvatura, fattore $fact</p>";
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'GLOBAL')");
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','warp_in',".time().",'AUDIO')");
			}
		}
		header('Location:padd.php?s=shM');
		exit;
}

else if(isSet($_GET['exitHangar']))
{ 
		$place = addslashes($_GET['exitHangar']);
		
		$genitrice = mysql_query("SELECT pointerL FROM pg_places WHERE placeID = (SELECT attracco FROM pg_places WHERE placeID = '$place')");
		//echo "SELECT sector, pointer FROM pg_places WHERE placeID = (SELECT attracco FROM pg_places WHERE placeID = '$place')"; exit;
		if(mysql_affected_rows() && PG::mapPermissions('M',$currentUser->pgAuthOMA))
		{
			$ge = mysql_fetch_array($genitrice);
			$pointerL = $ge['pointerL'];
			mysql_query("UPDATE pg_places SET warp = '0', pointerL = '$pointerL', attracco = '' WHERE placeID = '$place'");
	
		}
		header('Location:padd.php?s=sh');
		exit;
}

else if(isSet($_GET['hangarize']))
{ 
	if(isSet($_POST['attracTo']) && isSet($_GET['hangarize'])){
	
		$place = addslashes($_GET['hangarize']);
		$toplace = addslashes($_POST['attracTo']);
		
		$p1 = mysql_query("SELECT pointerL FROM pg_places WHERE placeID = '$place'");
		$pa = mysql_fetch_array($p1);
		$p1A = $pa['pointerL'];
		$p1 = mysql_query("SELECT pointerL FROM pg_places WHERE placeID = '$toplace'");
		$pa = mysql_fetch_array($p1);
		$p1B = $pa['pointerL'];
		
		if($p1A == $p1B && PG::mapPermissions('M',$currentUser->pgAuthOMA))
			mysql_query("UPDATE pg_places SET warp = '0',pointerL='', attracco = '$toplace' WHERE placeID = '$place'");
		
	}
		header('Location:padd.php?s=sh');
		exit;
}

else if(isSet($_GET['punctualArriveTo']))
{ 
	if(isSet($_POST['systemSearcher']) && PG::mapPermissions('M',$currentUser->pgAuthOMA)){
	
		$place = addslashes($_GET['punctualArriveTo']);
		$toplaceR = addslashes($_POST['systemSearcher']);
		$toplaceS = explode(' - ',$toplaceR);
		$toplace = $toplaceS[0];
		$toplaceI = $toplaceS[1];
		
		$coordinate = mysql_query("SELECT pointerL FROM pg_places WHERE pointerL <> '' AND placeID = '$toplace'");
		
		if(mysql_affected_rows())
		{	
			$coordinate = mysql_fetch_array($coordinate); 
			$topointer = $coordinate['pointerL'];
			mysql_query("UPDATE pg_places SET warp='0',attracco='',pointerL='$topointer' WHERE placeID = '$place'");
			mysql_query("UPDATE pg_places SET warp='0',pointerL='$topointer' WHERE attracco = '$place'");
			
				$locations = mysql_query("SELECT locID,placeName FROM fed_ambient,pg_places WHERE ambientLocation = placeId AND ambientLocation = '$place'");
				while ($resLoc = mysql_fetch_array($locations))
				{
				$ambientTo = $resLoc['locID'];
				$placeName = $resLoc['placeName'];
				$string = "<p class=\"globalAction\" title=\"Warp\">La $placeName esce dalla curvatura, arrivando a $toplaceI</p>";
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'GLOBAL')");
				mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','warp_out',".time().",'AUDIO')");
				}
		}
	}
		header('Location:padd.php?s=sh');
		exit;
}

else if(isSet($_GET['coorArriveTo']))
{ 
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA)){
	
		$place = addslashes($_GET['coorArriveTo']);
		
		$x = ($vali->numberOnly($_POST['coX']) != '') ? $vali->numberOnly($_POST['coX']) : 0;
		$y = ($vali->numberOnly($_POST['coY']) != '') ? $vali->numberOnly($_POST['coY']) : 0;
	
		
		$topointer = "D:$x;$y";
			
		mysql_query("UPDATE pg_places SET warp='0',attracco='',pointerL='$topointer' WHERE placeID = '$place'");
		mysql_query("UPDATE pg_places SET warp='0',pointerL='$topointer' WHERE attracco = '$place'");
		
		
	}
		header('Location:padd.php?s=sh');
		exit;
}

else if (isSet($_GET['addMapLocation']))
{
	$to = addslashes($_GET['addMapLocation']);
	$name = addslashes(str_replace('\'','&apos;',$_POST['name'])); 
	$locID = strtoupper(preg_replace('/[^\x20-\x7E]/','',str_replace(array(' ','\'','&apos;'),array('_','',''),$to.'_'.$name)));
	$desc = addslashes($_POST['descript']);
	$icon = addslashes($_POST['icon']);
	$ima = addslashes($_POST['internalImage']);
	
	if($icon == '') $icon = 'https://oscar.stfederation.it/imaLocation/i_generic.png';
	if($ima == '') $ima = 'https://oscar.stfederation.it/imaLocation/c_generic.png';
	$map = ($vali->numberOnly(addslashes($_POST['mappNo'])));
	
	if(PG::mapPermissions('JM',$currentUser->pgAuthOMA))
		mysql_query("INSERT INTO fed_ambient (locID,locName,ambientLocation,descrizione,planetSub,icon,image,ambientType) VALUES ('$locID','$name','$to','$desc','$map','$icon','$ima','NORMAL')");
	
	header('Location:main.php');	
}


else if (isSet($_GET['editMapLocation']))
{
	$locID = addslashes($_GET['editMapLocation']);
	$name = addslashes($_POST['name']);
	$desc = addslashes($_POST['descript']);
	$icon = addslashes($_POST['icon']);
	$ima = addslashes($_POST['internalImage']);
	$typer = addslashes($_POST['typer']);
	
	if($icon == '') $icon = 'https://oscar.stfederation.it/imaLocation/i_generic.png';
	if($ima == '') $ima = 'https://oscar.stfederation.it/imaLocation/c_generic.png';
	$map = ($vali->numberOnly(addslashes($_POST['mappNo'])));
	$deck = addslashes($_POST['pontNo']);
	
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA))
		mysql_query("UPDATE fed_ambient SET locName='$name', ambientType='$typer', planetSub='$map', ambientLevel_deck='$deck', descrizione='$desc', icon='$icon',image='$ima' WHERE locID='$locID'");
	
	header("Location:chat.php?amb=$locID");	
}

else if (isSet($_GET['purifyAlloggi']))
{
	$to = addslashes($_GET['purifyAlloggi']);
	
	mysql_query("DELETE FROM pg_alloggi WHERE alloggio IN (SELECT locID FROM fed_ambient WHERE locID LIKE 'ALL%' AND ambientType = 'ALLOGGIO' AND ambientLocation = '$to')");
	mysql_query("DELETE FROM fed_ambient WHERE locID LIKE 'ALL%' AND ambientType = 'ALLOGGIO' AND ambientLocation = '$to'");
	
	header('Location:main.php');	
}

include('includes/app_declude.php');	

function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}

function createRandomMatricola() {
	$chars = "abcdefghijkmnopqrstuvwxyz";
    $nums = "023456789";
    srand((double)microtime()*1000000);
   
    $i = 0;
    $let = '' ;
    $num1 = '' ;
    $num2 = '' ;

    while ($i < 2) {
        $num = rand() % 25;
        $tmp = substr($chars, $num, 1);
        $let = $let . $tmp;
        $i++;
    }
	 $i = 0;
	 while ($i < 3) {
        $num = rand() % 9;
        $tmp = substr($nums, $num, 1);
        $num1 = $num1 . $tmp;
        $i++;
    }
	$i = 0;
	 while ($i < 3) {
        $num = rand() % 9;
        $tmp = substr($nums, $num, 1);
        $num2 = $num2 . $tmp;
        $i++;
    }
	return strtoupper($let.'-'.$num1.'-'.$num2);
}

?>
