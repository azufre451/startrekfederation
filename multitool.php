<?php
session_start();

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);



ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);

	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)){header('Location:scheda.php'); exit;} 

$mode = (isSet($_GET['s'])) ? $_GET['s'] : '';

if($mode == 'closeSession'){

	$vali = new validator();
	$rec = $vali->numberOnly($_GET['ida']); 

	$ree  = mysql_fetch_assoc(mysql_query("SELECT sessionOwner,sessionLabel,sessionStart FROM federation_sessions WHERE sessionID = '$rec'"));
	$pig = new PG($ree['sessionOwner']);
	$seTitle = addslashes($ree['sessionLabel']);
	$seNow = addslashes( ((time()-(int)($ree['sessionStart']))/3600) );
	$pig->sendPadd('OFF: Chiusura Sessione',"Un admin ha chiuso la tua sessione: $seTitle dopo <b>$seNow ore</b> di inattività. Non sono stati assegnati punti per questa sessione.");
	
	mysql_query("UPDATE federation_sessions SET sessionEnd = $curTime, sessionStatus = 'CLOSED' WHERE sessionID = '$rec'");
	if(mysql_affected_rows())
		echo json_encode("OK");
	exit;
}
if($mode == 'deletePoints')
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;
	$vali = new validator();
	$rec = $vali->numberOnly($_POST['idR']); 
	
	$recIL = mysql_query("SELECT pgPoints, pgID, points FROM pg_users,pg_users_pointStory WHERE pgID = owner AND recID = $rec");
	if (mysql_affected_rows())
	{
		$reaL = mysql_fetch_assoc($recIL);
		$pgPoints = $reaL['pgPoints'];
		$points = $reaL['points'];
		$pgID = $reaL['pgID'];
		
		$braker= false;
		for($i = $pgPoints-$points; $i < $pgPoints; $i++)
		{
			if($i % 12 == 0) $braker = true;
		}
		
		if(!$braker)
		{
			mysql_query("DELETE FROM pg_users_pointStory WHERE recID = $rec");
			$ok = mysql_affected_rows();
			mysql_query("UPDATE pg_users SET pgPoints = (SELECT SUM(points) FROM pg_users_pointStory WHERE owner = $pgID) WHERE pgID = $pgID ORDER BY recID ASC");
			
			if($ok) echo json_encode(array('OK' => 'Ok'));	 
		}
		else echo json_encode(array('OK' => 'No'));	
	}
	exit;
	
}


if($mode == "achiAssignBackground")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$vat =  $vali->numberOnly($_GET['what']);
	$pgID = $vali->numberOnly($_GET['pgID']);

	$tp = new PG($pgID);
	
	mysql_query("INSERT INTO pg_achievement_assign (owner,achi,timer) VALUES ($pgID,'$vat',".time().")");
	
	$res = mysql_query("SELECT aText,aImage FROM pg_achievements WHERE aID = '$vat'");
	if(mysql_error()) {echo mysql_error();exit;}
	$resA = mysql_fetch_array($res);
	$Descri =$resA['aText'];
	$ima =$resA['aImage'];
	
	$cString = addslashes("Congratulazioni!!<br />Hai sbloccato un nuovo achievement!<br /><br /><p style='text-align:center'><img src='TEMPLATES/img/interface/personnelInterface/$ima' /><br /><span style='font-weight:bold'>$Descri</span></p><br />Il Team di Star Trek: Federation");
	$eString = addslashes("Hai un nuovo achievement!::$Descri");
	
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (".$_SESSION['pgID'].",$pgID,'OFF: Nuovo Achievement!','$cString',".time().",0)");
	if(mysql_error()) {echo mysql_error();exit;}
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",$pgID,'::special::achiev','$eString',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')");
	if(mysql_error()) {echo mysql_error();exit;}

	header("Location:multitool.php?viewApprovals=true");

}



if($mode == "remindBackground")
{

	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$pgID = $vali->numberOnly($_GET['pgID']);


	$tp = new PG($pgID);
	$usera = $tp->pgUser;
	$tp->sendPadd('Attesa per approvazione BG',"Ciao $usera<br /><br />Ti comunichiamo che abbiamo visionato i dati relativi alla registrazione del PG ed il Background.<br /><br />Non abbiamo potuto procedere all'approvazione del BG, che è ancora privo di alcuni elementi molto importanti. Per questo ti chiederei di provvedere a completare le parti mancanti del Background non appena avrai un momento di tempo.<br /><br />
Di seguito trovi alcune risorse utili per scrivere il BG. Lo staff è a tua disposizione qualora avessi delle domande sulla compilazione!<br /><br />
&raquo; <a href=\"javascript:dbOpenToTopic(242)\" class=\"interfaceLink\"> Lauree, Medaglie e Stato di Servizio </a>
&raquo; <a href=\"javascript:dbOpenToTopic(241)\" class=\"interfaceLink\"> Il Background del PG </a>

	 Intanto buon gioco, <br /><br /><br />Lo staff",$_SESSION['pgID']);

	mysql_query("UPDATE pg_users_bios SET lastReminder = ".time().", supervision = ".$_SESSION['pgID']." WHERE pgID = $pgID");

	header("Location:multitool.php?viewApprovals=true");

}
if($mode == "approveBackground")
{
	
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$pgID = $vali->numberOnly($_GET['pgID']);


	$lar = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as TLP FROM pg_users_bios WHERE pgID = $pgID AND valid = 1"));

	mysql_query("UPDATE pg_users_bios SET pgID = 6, valid = 0 WHERE pgID = $pgID AND valid = 1");
	mysql_query("UPDATE pg_users_bios SET valid = 1 WHERE pgID = $pgID");

	$tp = new PG($pgID);
	$usera = $tp->pgUser;
	
	

	if((int)$lar['TLP'] == 0) $tp->sendPadd('Approvazione BG',"Ciao $usera<br />Ti comunichiamo che abbiamo visionato i dati relativi alla registrazione del PG ed il Background. Tutto risulta in ordine ed il BG e' ora approvato! Ricorda che le eventuali aggiunte e modifiche (comunque sempre incoraggiate) dovranno essere approvate: se modificherai la scheda rimarrà sempre visibile (agli altri) il BG approvato, fino ad approvazione di quello nuovo.<br /><br /> Ricorda anche che da ora non è più possibile chiedere il reset delle abilità / caratteristiche: qualora volessi modificarle per l'ultima volta, ti invitiamo a chiedere immediatamente allo Staff!<br /><br />Ti auguriamo buon gioco in land,<br />Lo staff",'518');

	$tp->addNote('Approvazione BG e scheda',$currentUser->ID);

	header("Location:multitool.php?viewApprovals=true");

}

if($mode == "blockBulk")
{
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)");
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
	echo json_encode(array('stat'=>true)); exit;
}

if($mode == "reassignBulk")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$placeLocReassign = $_POST['placeAssign'];
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgLocation='$placeLocReassign',pgRoom = '$placeLocReassign' WHERE pgUser IN ($lisOuser)");
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
	echo json_encode(array('stat'=>true)); exit;
}



if($mode == "getProfilingPadds")
{

	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)) exit;
	$ider = addslashes($_POST['ider']);

	$padder=array();

	

	$res = mysql_query("SELECT padID, paddTitle, paddText, paddFrom, paddTo, paddTime, fromPGT.pgAvatarSquare , toPGT.pgUser as ToPG, fromPGT.pgUser as FromPG,fromPGT.pgSpecie as pgSpecie,fromPGT.pgSesso as pgSesso, toPGT.pgID as ToPGID, fromPGT.pgID as FromPGID, ordinaryUniform FROM fed_pad, pg_users  AS fromPGT, pg_users AS toPGT, pg_ranks WHERE prio = fromPGT.rankCode AND toPGT.pgID = paddTo AND fromPGT.pgID = paddFrom AND (paddTo = $ider OR paddFrom = $ider) AND bgRevision = 1 ORDER BY paddTime DESC");
	while($atpl = mysql_fetch_assoc($res))
	{

		$atpl['pcontent'] = nl2br($atpl['paddText']);
		$atpl['phour'] = date('H',$atpl['paddTime']);
		$atpl['pmin'] = date('i',$atpl['paddTime']);
		$atpl['pday'] = timeHandler::extrapolateDay($atpl['paddTime']); 

		$padder[] = $atpl;

	}
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
	echo json_encode(array('padder'=>$padder)); exit;
}

if($mode == "setSeclar")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$seclar = $vali->numberOnly($_POST['seclar']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgSeclar=$seclar WHERE pgUser IN ($lisOuser)");

	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "insmaster")
{
/*	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$eventTitle = addslashes($_POST['eventTitle']);
	$eventText = addslashes($_POST['eventText']);
	$place = $currentUser->pgAssign;
	$time = time();
	
	mysql_query("INSERT INTO fed_master_news (title,content,time,place) VALUES ('$eventTitle','$eventText',$time,'$place')");
	header('Location:multitool.php');
	
	$curID = $_SESSION['pgID'];
	$oneMonth = $curTime - 2505600; 
	$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND pgAssign = '$place' AND pgLastAct >= $oneMonth");
	while($res = mysql_fetch_assoc($idR))
	{ 
			$idA = $res['pgID']; 
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (518, $idA, 'NUOVO EVENTO MASTER', 'È stato inserito un nuovo evento master ($eventTitle) nel Computer di Bordo. Controlla la sezione Eventi Master del computer per visualizzarlo!',$curTime,0)");
	}	*/
}

if($mode == 'delete' || $mode == 'bavosize')
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	

	foreach ($sel as $auth)
		{
			$auth = addslashes(trim($auth));
			if($auth!='')
			{
				$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
				$pgID=$myco['pgID'];	
				$pigo = new PG($pgID);
				
				if($mode == 'bavosize'){
					$pigo->bavosize();
					$pigo->addNote("Bavosizzato",$currentUser->ID);
				}
				elseif($mode == 'delete') $pigo->delete();
			}
		}
	 
	echo json_encode(array('stat'=>true)); exit;


}

if($mode == "setIncarico")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$assegnazione = addslashes($_POST['assegnazione']);
	$incarico = addslashes($_POST['incarico']);
	$dipartimento = addslashes($_POST['dipartimento']);
	$divisione = addslashes($_POST['divisione']);


	$lisOuser="";
	foreach ($sel as $auth)
		{
			$auth = addslashes(trim($auth));
			if($auth!='')
			{	
				$auth=addslashes($auth);
				$adNote = 'Piallatura Incarico e Assegnazione: '.$incarico;
				$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
				$pgID=$myco['pgID'];
				$pigo = new PG($pgID);

				mysql_query("DELETE FROM pg_incarichi WHERE pgID = $pgID");				
				mysql_query("INSERT INTO pg_incarichi (pgID,incIncarico,incSezione,incDivisione,incDipartimento,pgPlace,incMain) VALUES((SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1),'$incarico',$pgID,'$divisione','$dipartimento','$assegnazione','1')");
				$pigo->addNote($adNote,$currentUser->ID);
			}
		}
	 
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}


if($mode == "addNote")
{
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']); 
	
	$lisOuser="";

	foreach ($sel as $auth)
	{
		$auth = addslashes(trim($auth));
		if($auth!=''){
			$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
			$pgID=$myco['pgID'];
			$pigo = new PG($pgID);
			$pigo->addNote($_POST['note'],$currentUser->ID);
		} 
	}
}

if($mode== "stor")
{
	$template = new PHPTAL('TEMPLATES/scheda_storicoPunti.htm');
	
	$arra=array();
	$res = mysql_query("SELECT recID,p1.pgUser as PB, p2.pgUser as PA,points,cause,causeE,causeM,timer FROM pg_users_pointStory,pg_users as p1, pg_users as p2 WHERE p1.pgID = assigner AND p2.pgID = owner ORDER BY timer DESC LIMIT 500");
	
	$causes = array();
	
	while($rea=mysql_fetch_array($res))
	{
		$arra[]=$rea;
		if (!in_array($rea['causeE'],$causes))
			$causes[$rea['causeE']] = '#' . strtoupper(dechex(rand(0,16777215)));	
	}
	
	$template->raa=$arra;
	$template->causes=$causes;
	
	if(PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->isAdmin=true;
	
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
	exit;
}

if($mode == "setMostrina")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$mostrina = $vali->numberOnly($_POST['mostrina']);
	foreach ($sel as $auth)
		if($auth!=''){	
						$tUser = trim(addslashes($auth));
						PG::setMostrinaL($tUser,$mostrina);
						Mailer::notificationMail("Il PG $sel e' stato promosso o degradato a $mostrina",$currentUser);
		}
	echo json_encode(array('stat'=>true)); exit;	
}

if($mode == "sendModeration")
{
	if (!PG::mapPermissions('MM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$moder = addslashes($_POST['moder']);
	foreach ($sel as $auth)
		if($auth!='')
		{	
						$tUser = trim(addslashes($auth));
						PG::sendModerationL($tUser,$moder);
		}
	echo json_encode(array('stat'=>true)); exit;	
}

if($mode == "setSalute")
{
	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$sal = htmlentities(addslashes(($_POST['salute'])));
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	//echo "UPDATE pg_users SET pgSalute = '$sal' WHERE pgUser IN ($lisOuser)";
	mysql_query("UPDATE pg_users SET pgSalute = '$sal' WHERE pgUser IN ($lisOuser)");
	
	if(!mysql_error()) 
	{
	foreach ($sel as $auth)
		if($auth!=''){	
						$tUser = trim(addslashes($auth));
						mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (".$_SESSION['pgID'].",(SELECT pgID FROM pg_users WHERE pgUser = '$tUser'), 'Modifica Salute', 'Ciao!\nIl tuo stato di salute e\' stato modificato in: $sal. Questo messaggio e\' stato generato automaticamente. Buon gioco!',".time().",0)");
		}
	}
	
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "addPoints")
{	
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$code = $vali->killChars($_POST['code']);
	$detail = addslashes($_POST['detail']);
	
	$p=0;$l="A";
if($code == "a1"){$p=1;$little="Q1";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a2"){$p=2;$little="Q2";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a3"){$p=3;$little="Q3";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "f2"){$p=2;$little="F2";$mex = "Punti Minishot";$l="SL";}
elseif($code == "a4"){$p=4;$little="Q4";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a5"){$p=5;$little="Q5";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a6"){$p=6;$little="Q6";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a7"){$p=7;$little="Q7";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a8"){$p=8;$little="Q8";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a9"){$p=9;$little="Q9";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a10"){$p=10;$little="Q10";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "b5"){$p=5;$little="B1";$mex = "Mastering One Shot";$l="SL";}
elseif($code == "r2"){$p=2;$little="R";$mex = "Stesura Rapporto";$l="SL";}

elseif($code == "kz1"){$p=20;$little="DISP";$mex = "Piccola Integrazione";$l="SM";}
elseif($code == "kz2"){$p=50;$little="DISP+";$mex = "Dispensa Completa";$l="SM";}
elseif($code == "aa11"){$p=$vali->numberOnly($_POST['points']);$little="Q00";$mex = 'Punteggio Admin';$l="SM";}

	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	
	if (PG::mapPermissions($l,$currentUser->pgAuthOMA))
	{
		
		
		foreach ($sel as $auth)
			if(trim($auth)!=''){	
						$tUser = trim(addslashes($auth));
						if($tUser != $currentUser->pgUser || $currentUser->pgAuthOMA != 'A')
						{
							$rea = mysql_fetch_array(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$tUser'"));
							$pgID = $vali->numberOnly($rea['pgID']);
							$selectedDUser = new PG($pgID);
							$selectedDUser->addPoints($p,$little,$mex,$detail,$currentUser->ID);
						}
			}
		
	}
	
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "insertionForm")
{
	$template = new PHPTAL('TEMPLATES/shadow_scheda_master_insert.htm');
}

if($mode == "diffpg")
{
	include('includes/Finediff.php');

	$rea=mysql_fetch_assoc(mysql_query("SELECT * FROM pg_users_bios WHERE pgID = 1 AND valid = 1"));
	$lines1 = (mysql_affected_rows()) ? $rea['pgBackground'] : '';
	$rea=mysql_fetch_assoc(mysql_query("SELECT * FROM pg_users_bios WHERE pgID = 1 AND valid = 0 ORDER BY recID DESC LIMIT 1"));
	$lines2 = (mysql_affected_rows()) ? $rea['pgBackground'] : '';

	echo $lines1;
	echo $lines2;


	echo "<hr />";
	
	echo FineDiff::getDiffOpcodes($lines1, $lines2 /* , default granularity is set to character */);


	exit;
}

else 
{
	
	$template = new PHPTAL('TEMPLATES/multitool.html');
	

	$oneMonthAgo=time()-2592000;
	$twoMonthAgo=time()-2592000-2592000-2592000;
	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places");

	
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	
	$ranks=array();
	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks ORDER BY rankerprio DESC");
	while($myA = mysql_fetch_array($my))
	$ranks[$myA['aggregation']][$myA['prio']] = array('note' => $myA['Note'], 'ord' => $myA['ordinaryUniform']);
	//var_dump($ranks);exit;

	$images = scandir('TEMPLATES/img/ruolini/');
	$template->images=array_diff($images,array('.','..'));

	if (isSet($_GET['viewApprovals'])) $template->viewApprovals = 1;
  
	$template->ranks = $ranks;
	$template->locations = $locArray;
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->bonusSM = 'show';
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->bonusM = 'show';
	if (PG::mapPermissions('MM',$currentUser->pgAuthOMA)) $template->bonusMM = 'show';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->bonusA = 'show';



	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{

		$r=array();

		$rea = mysql_query("SELECT pg_users_bios.*,pg_users.pgUser,pg_users.pgBavo,pg_users.pgLock,ordinaryUniform,iscriDate FROM pg_users_bios,pg_users,pg_ranks WHERE pg_users.pgID = pg_users_bios.pgID AND prio=rankCode AND pgLastAct > $oneMonthAgo AND pgRoom <> 'BAVO' AND pgBavo = 0 AND pgLock = 0 AND png=0 AND valid = 0 ORDER BY pgLastACT DESC");

		echo mysql_error();

		while($re = mysql_fetch_assoc(($rea)))
		{
			
			$curBG = PG::getSomething($re['pgID'],'BG'); 

			if($curBG){
				$re['approdiff'] = array(
					'pgBiometrics' => htmlDiff($curBG['pgBiometrics'],$re['pgBiometrics']),
					'pgBackground' => htmlDiff($curBG['pgBackground'],$re['pgBackground']),
					'pgCarattere' => htmlDiff($curBG['pgCarattere'],$re['pgCarattere']),
					'pgFamily' => htmlDiff($curBG['pgFamily'],$re['pgFamily']),
					'pgVarie' => htmlDiff($curBG['pgVarie'],$re['pgVarie']),
					'pgIlSegreto' => htmlDiff($curBG['pgIlSegreto'],$re['pgIlSegreto']));

				$c1 = 'REVISIONE';
			}
			else{
				$re['approdiff'] = array(
					'pgBiometrics' => htmlDiff("",$re['pgBiometrics']),
					'pgBackground' => htmlDiff("",$re['pgBackground']),
					'pgCarattere' => htmlDiff("",$re['pgCarattere']),
					'pgFamily' => htmlDiff("",$re['pgFamily']),
					'pgVarie' => htmlDiff("",$re['pgVarie']),
					'pgIlSegreto' => htmlDiff("",$re['pgIlSegreto']));

				if ($re['pgLock'] || $re['pgBavo'])
					$c1 = 'BLOCCATI_BANNATI_VERMI_BAVOSI';
				 elseif ($re['iscriDate'] > (time()-(604800*2)))
					$c1 = 'QUESTE_2_SETTIMANE';
				elseif ($re['iscriDate'] > (time()-(604800*4)))
					$c1 = 'QUESTO_MESE';

				else $c1 = 'MATUSALEMME';
			}


			$r[$c1][] = $re;
		}
	 
		$template->pendingBGS = $r;
	}

	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$rea = mysql_query("SELECT federation_sessions.*,pg_users.pgUser,pg_users.pgID,locName FROM federation_sessions,pg_users,fed_ambient WHERE pgID = sessionOwner AND sessionPlace = locID ORDER BY sessionStatus, sessionID DESC LIMIT 250");
		$sessions=array();
		while($rel = mysql_fetch_assoc($rea)) $sessions[] = $rel;

		$template->sessions = $sessions;
	}

	$lastPGS =  mysql_query("SELECT pgID,pgUser,ordinaryUniform,pgLock,(SELECT 1 FROM pg_alloggi WHERE pg_alloggi.pgID = pg_users.pgID LIMIT 1) as pgAlloggio,(SELECT 1 FROM pg_incarichi WHERE pg_incarichi.pgID = pg_users.pgID LIMIT 1) as pgIncarico, (SELECT 1 FROM pg_users_bios WHERE pg_users_bios.pgID = pg_users.pgID AND valid = 1 LIMIT 1) as pgBackground, (SELECT COUNT(*) FROM fed_pad WHERE (paddTo = pgID OR paddFrom = pgID) AND bgRevision = 1) as commpadd FROM pg_users,pg_ranks WHERE rankCode = prio AND pgBavo =0 AND png=0 and pgAuthOMA <> 'BAN' AND iscriDate > $twoMonthAgo ORDER BY iscriDate DESC");
	while($resa = mysql_fetch_assoc($lastPGS))
	{
		$resLastPGS[] = $resa;
	}
	$template->resLastPGS = $resLastPGS;

}



	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
include('includes/app_declude.php');	



function diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }   
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}
function htmlDiff($old, $new){
    $ret = '';
    $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
    foreach($diff as $k){
        if(is_array($k))
            $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
        else $ret .= $k . ' ';
    }
    return $ret;
}
?>
