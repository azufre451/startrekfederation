<?php session_start();
if (!isSet($_SESSION['pgID'])){echo "AUTORIZZAZIONI NON SUFFICIENTI! TRASSONE!";  exit;}
include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
if (!PG::mapPermissions('A',PG::getOMA($_SESSION['pgID']))) {echo "AUTORIZZAZIONI NON SUFFICIENTI2! TRASSONE!"; exit;}

$vali = new validator();  
$template = new PHPTAL('TEMPLATES/evil_whisper.htm');
PG::updatePresence($_SESSION['pgID']);


	$resPgPresenti = mysql_query('SELECT pgID, pgAvatar, pgUser FROM pg_users WHERE pgID <> '.$_SESSION['pgID'].' AND pgLastAct >= '.($curTime-1800).' ORDER BY pgUser ASC');
	$pgArray=array();
	while($resPG = mysql_fetch_array($resPgPresenti))
	$pgArray[$resPG['pgID']] = $resPG['pgUser'];
	$template->people = $pgArray;
	$template->coPeople = count($pgArray);

//chat

$chatNumber = mysql_query('SELECT COUNT(IDE) as CT FROM fed_sussurri');
$chatNumberL = mysql_fetch_array($chatNumber);
$chatNumberCounter = ($chatNumberL['CT'] > 35) ? ($chatNumberL['CT']-35) : 0;

$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri ORDER BY time");
$htmlLiner=''; $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines))
{
	$htmlLiner.=$chatLi['chat'];
	if($chatLi['IDE'] > $MAX) $MAX = $chatLi['IDE'];
}

$template->htmlLiner = $htmlLiner;
$template->maxVIS = $MAX;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
