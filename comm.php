<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 

$vali = new validator();  
$template = new PHPTAL('TEMPLATES/comunicatore.htm');
$currentUser = new PG($_SESSION['pgID']);
PG::updatePresence($_SESSION['pgID']);


	$usQ = mysql_query("SELECT pgLocation,pointerL FROM pg_users,pg_places WHERE pgLocation = placeID AND placeID='".$currentUser->pgLocation."'");
	$usLL = mysql_fetch_array($usQ);
	$locat = $usLL['pgLocation'];
	$pointerL = $usLL['pointerL'];
	$pgLocation = $usLL['pgLocation'];

	$resPgPresenti = mysql_query('SELECT pgID, pgUser FROM pg_users,pg_places,fed_ambient WHERE pgLocation = placeID AND pgRoom = locID AND ((placeID = \''.$pgLocation.'\') OR (pointerL <> \'\' AND pointerL = \''.$pointerL.'\')) AND ambientType <> \'DEFAULT\' AND pgID <> '.$_SESSION['pgID'].' AND pgLastAct >= '.($curTime-1800).' ORDER BY pgUser ASC');
	if(mysql_affected_rows()) $pgArray=array(0=>'A Tutto il Personale');

	else $pgArray=array();
	while($resPG = mysql_fetch_array($resPgPresenti))
	$pgArray[$resPG['pgID']] = $resPG['pgUser'];
	
	if (!mysql_affected_rows()) $template->peoplePresent = false;
	else { $template->people = $pgArray; $template->peoplePresent = true;}
	
	$resPontiPresenti = mysql_query('SELECT DISTINCT ambientLevel_deck FROM fed_ambient WHERE ambientLevel_deck <> \'0\' AND ambientLocation = \''.$locat.'\' ORDER BY ambientLevel_deck + 0 ASC');
	$pontiArray=array();
	while($resPo = mysql_fetch_array($resPontiPresenti))
	$pontiArray[] = $resPo['ambientLevel_deck'];
	
	if (!mysql_affected_rows()) $template->deckPresent = false;
	else { $template->pontiArray = $pontiArray; $template->deckPresent = true;}
	
	$resAmbient = mysql_query('SELECT locID,locName FROM fed_ambient WHERE ambientType <> \'DEFAULT\' AND ambientLocation = \''.$locat.'\' ORDER BY locName');
	$ambientiArray=array();
	while($resAmb = mysql_fetch_array($resAmbient))
	$ambientiArray[] = $resAmb;
	$template->ambientiArray = $ambientiArray;

//chat

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
