<?php session_start();
if (!isSet($_SESSION['pgID'])){echo "AUTORIZZAZIONI NON SUFFICIENTI! TRASSONE!";  exit;}
include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
if (!PG::mapPermissions('A',PG::getOMA($_SESSION['pgID']))) {echo "AUTORIZZAZIONI NON SUFFICIENTI2! TRASSONE!"; exit;}

$vali = new validator();  
$template = new PHPTAL('TEMPLATES/mod_kgb.htm');
PG::updatePresence($_SESSION['pgID']);
 

$focus = new PG($_GET['target']);
$focusID = $focus->ID;
$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE susTo NOT IN (0,7) AND (susFrom = $focusID OR susTo = $focusID) ORDER BY time");
while($chatLi = mysql_fetch_array($chatLines))
	$htmlLiner[] = $chatLi;


$paddQ = mysql_query("SELECT padID, paddTitle, paddText, paddFrom, paddTo, paddTime, fromPGT.pgAvatarSquare , toPGT.pgUser as ToPG, fromPGT.pgUser as FromPG,fromPGT.pgSpecie as pgSpecie,fromPGT.pgSesso as pgSesso, toPGT.pgID as ToPGID, fromPGT.pgID as FromPGID, ordinaryUniform FROM fed_pad, pg_users  AS fromPGT, pg_users AS toPGT, pg_ranks WHERE prio = fromPGT.rankCode AND (paddDeletedFrom <> 1 OR paddDeletedTo <> 1) AND toPGT.pgID = paddTo AND fromPGT.pgID = paddFrom AND (paddTo = $focusID OR paddFrom = $focusID) ORDER BY paddTime DESC");
	
	$padds=array();
	if(mysql_affected_rows())
	{
		while($padd = mysql_fetch_array($paddQ))
		{
			$padd['pcontent'] = CDB::bbcode($padd['paddText']);
			$padd['phour'] = date('H',$padd['paddTime']);
			$padd['pmin'] = date('i',$padd['paddTime']);
			$padd['pday'] = timeHandler::extrapolateDay($padd['paddTime']); 
			$padds[] = $padd;
		}
	}

$conns = array();
$res=mysql_query("SELECT * FROM connlog WHERE user = $focusID ORDER BY time DESC LIMIT 500");
while( $rap = mysql_fetch_assoc($res))
	$conns[] = $rap;

$template->conns = $conns;
$template->padds = $padds;
$template->focus = $focus;
$template->sussurri = $htmlLiner;
$template->gameOptions = $gameOptions;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
