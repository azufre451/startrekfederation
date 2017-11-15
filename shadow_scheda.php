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

	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)){header('Location:scheda.php'); exit;} 

$mode = (isSet($_GET['s'])) ? $_GET['s'] : '';

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
			if($i % 100 == 0) $braker = true;
		}
		
		if(!$braker)
		{
			mysql_query("DELETE FROM pg_users_pointStory WHERE recID = $rec");
			$ok = mysql_affected_rows();
			mysql_query("UPDATE pg_users SET pgPoints = (SELECT SUM(points) FROM pg_users_pointStory WHERE owner = $pgID) WHERE pgID = $pgID");
			
			if($ok) echo json_encode(array('OK' => 'Ok'));	 
		}
		else echo json_encode(array('OK' => 'No'));	
	}
	exit;
	
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
	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$eventTitle = addslashes($_POST['eventTitle']);
	$eventText = addslashes($_POST['eventText']);
	$place = $currentUser->pgAssign;
	$time = time();
	
	mysql_query("INSERT INTO fed_master_news (title,content,time,place) VALUES ('$eventTitle','$eventText',$time,'$place')");
	header('Location:shadow_scheda.php');
	
	$curID = $_SESSION['pgID'];
	$oneMonth = $curTime - 2505600; 
	$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND pgAssign = '$place' AND pgLastAct >= $oneMonth");
	while($res = mysql_fetch_assoc($idR))
	{ 
			$idA = $res['pgID']; 
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (518, $idA, 'NUOVO EVENTO MASTER', 'È stato inserito un nuovo evento master ($eventTitle) nel Computer di Bordo. Controlla la sezione Eventi Master del computer per visualizzarlo!',$curTime,0)");
	}
	
}

if($mode == "setIncarico")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$assegnazione = addslashes($_POST['assegnazione']);
	$incarico = addslashes($_POST['incarico']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	mysql_query("UPDATE pg_users SET pgAssign = '$assegnazione', pgIncarico = '$incarico' WHERE pgUser IN ($lisOuser)");

	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "setDipartimento")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$dipartimento = addslashes($_POST['dipartimento']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	mysql_query("UPDATE pg_users SET pgDipartimento = '$dipartimento' WHERE pgUser IN ($lisOuser)");

	echo json_encode(array('stat'=>true)); exit;
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "addNote")
{
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$addNote = '\n'.addslashes($_POST['note']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	mysql_query("UPDATE pg_users SET pgNote=CONCAT(pgNote,'$addNote') WHERE pgUser IN ($lisOuser)");

	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
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
							$rea = mysql_fetch_array(mysql_query("SELECT pgID,pgPoints FROM pg_users WHERE pgUser = '$tUser'"));
							$pointsPre = $rea['pgPoints'];
							$pgID = $rea['pgID'];
		
							mysql_query("INSERT INTO pg_users_pointStory(owner,points,cause,causeM,timer,assigner,causeE) VALUES ((SELECT pgID FROM pg_users WHERE pgUser = '$tUser'),$p,'$little','$mex',$curTime,".$_SESSION['pgID'].",'$detail')");
							mysql_query("UPDATE pg_users SET pgPoints = pgPoints+$p WHERE pgUser='$tUser' AND pgID <> ".$_SESSION['pgID']);
					
							for($i = 0; $i < $p; $i++)
							{
						
							if(($pointsPre+$i) % 200 == 0)
							{
								mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+2,pgSpecialistPoints=pgSpecialistPoints+1, pgSocialPoints = pgSocialPoints+1 WHERE pgID = $pgID");
				
								$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 4 Upgrade Points!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarli per aumentare le tue caratteristiche nella Scheda PG!</span></p><br />Il Team di Star Trek: Federation");
								$eString = addslashes("Upgrade Points!::Hai ottenuto quattro UP!");
	
								mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (518,$pgID,'OFF: Upgrade Points!','$cString',$curTime,0,''),(518,$pgID,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png')");
							}
							elseif(($pointsPre+$i) % 100 == 0)
							{
								mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+2, pgSocialPoints = pgSocialPoints+1 WHERE pgID = $pgID");
				
								$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 3 Upgrade Points!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarli per aumentare le tue caratteristiche nella Scheda PG!</span></p><br />Il Team di Star Trek: Federation");
								$eString = addslashes("Upgrade Points!::Hai ottenuto tre UP!");
	
								mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (518,$pgID,'OFF: Upgrade Points!','$cString',$curTime,0,''),(518,$pgID,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png')");
							}
							}
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

else 
{
	
	$template = new PHPTAL('TEMPLATES/shadow_scheda_master.htm');
	
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

	$template->ranks = $ranks;
	$template->locations = $locArray;
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->bonusSM = 'show';
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->bonusM = 'show';
	if (PG::mapPermissions('MM',$currentUser->pgAuthOMA)) $template->bonusMM = 'show';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->bonusA = 'show';
}




	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
include('includes/app_declude.php');	

?>
