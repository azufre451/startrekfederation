<?php 
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);

$vali = new validator();

if(isSet($_GET['setLockOFF']))
{
	$id = $vali->numberOnly($_GET['setLockOFF']);
	if(PG::mapPermissions('G',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgLockOff = !pgLockOff WHERE pgID = $id");

	header('Location:people.php');
} 

if(isSet($_GET['setLock']))
{
	$id = $vali->numberOnly($_GET['setLock']);
	if(PG::mapPermissions('G',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgLock = !pgLock WHERE pgID = $id");

	header('Location:people.php');
} 

		$template = new PHPTAL('TEMPLATES/cdb_people.htm');
		$crew = mysql_query("SELECT pg_users.pgID,(SELECT 1 FROM pg_alloggi WHERE pg_alloggi.pgID = pg_users.pgID LIMIT 1) as pgAlloggio, pgSpecie,rankCode,pgAssign,pgLockOff,placeName, pgSesso, pgLockOff, pgUser, pgGrado,iscriDate,pgLastAct, pgSezione,pgLock,  ordinaryUniform as pgMostrina FROM pg_users,pg_ranks,pg_places WHERE pgAssign <> 'BAVO' AND pgAssign = placeID AND prio = rankCode AND pgLastAct <> 0 AND png = 0 ORDER BY prio DESC LIMIT 35");
		
		while ($crewA = mysql_fetch_array($crew))
		{
		$personale[$crewA['placeName']][] = $crewA;
		}
		
		$template->personale = $personale;


$template->user = $currentUser;
if(PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->SM = 'ys';
$template->OM = (PG::mapPermissions('O',$currentUser->pgAuthOMA)) ? 'yes' : 'no'; 
 $template->currentDate = $currentDate;
 $template->currentStarDate = $currentStarDate;
// $template->gameName = $gameName;
// $template->gameVersion = $gameVersion;
// $template->debug = $debug;
// $template->gameServiceInfo = $gameServiceInfo;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>