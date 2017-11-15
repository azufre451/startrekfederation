<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
$vali = new validator();  
$template = new PHPTAL('TEMPLATES/mod_chatLog.htm');
$currentUser = new PG($_SESSION['pgID']);
if (!PG::mapPermissions("M",$currentUser->pgAuthOMA)) header("Location:index.php");

$Sambient = (isSet($_POST['place'])) ? $vali->killchars(htmlentities(addslashes($_POST['place']))) : $currentUser->pgLocation;
//$currentUser->setPresenceIntoChat($ambient);

//$currentLocation = PG::getLocation($currentUser->pgLocation);
//emplate->alertCSS = str_replace('greenAlert',"",$currentLocation['placeAlert']);

// location

$template->ambientKey=$Sambient;

//$currentAmbient = Ambient::getAmbient($ambient);

$fromDay = (isSet($_POST['calFrom'])) ? htmlentities($_POST['calFrom']) : date('d/m/Y',time()-(24*60*60));
//$fromHour = (isSet($_POST['calFromH'])) ? $vali->htmlentities($_POST['calFromH']) : date('H:i',time()-(24*60*60));
$fromHour = (isSet($_POST['calFromH'])) ? $vali->numberOnly($_POST['calFromH']) : date('H',time()-(24*60*60));
$fromMin = (isSet($_POST['calFromM'])) ? $vali->numberOnly($_POST['calFromM']) : date('i',time()-(24*60*60));

$d1=explode('/',$fromDay);
$from = mktime($fromHour,$fromMin,0,$d1[1],$d1[0],$d1[2]);



$toDay = (isSet($_POST['calTo'])) ? htmlentities($_POST['calTo']) : date('d/m/Y');
$toHour = (isSet($_POST['calToH'])) ? $vali->numberOnly($_POST['calToH']) : date('H');
$toMin = (isSet($_POST['calToM'])) ? $vali->numberOnly($_POST['calToM']) : date('i');

$e1=explode('/',$toDay);
$toe = mktime($toHour,$toMin,0,$e1[1],$e1[0],$e1[2]);


$template->fromDate = date('d/m/Y H:i:s',$from);
$template->toDate = date('d/m/Y H:i:s',$toe);

$template->placeName = "";
//$template->ambient = $currentAmbient;

 

//places
$re = mysql_query("SELECT placeID,placeName FROM pg_places ORDER BY placeName");
$places=array();
while($rea = mysql_fetch_array($re)) $places[] = $rea;
$template->places = $places;

$masterCondition = (PG::mapPermissions("M",$currentUser->pgAuthOMA)) ? '' : "AND (type <> 'APM' OR sender = ".$_SESSION['pgID'].')';

$chatLines = mysql_query("SELECT chat,time,ambient,locName FROM federation_chat,fed_ambient WHERE type <> 'AUDIO' AND ambient=locID AND ambientLocation = '$Sambient' AND time BETWEEN $from AND $toe ORDER BY time");

$chatControl = mysql_query("SELECT ambient FROM `federation_chat` WHERE type IN ('DIRECT','ACTION') AND chat NOT LIKE '%a tutto il personale%' AND time BETWEEN $from AND $toe GROUP BY ambient HAVING count( * ) >5");
$chatArrayControl=array();
while($rea=mysql_fetch_array($chatControl)) $chatArrayControl[]= str_replace(',','_',$rea['ambient']);

$htmlLiner=array();
while($chatLi = mysql_fetch_array($chatLines))
{
$chatAmbient = str_replace(',','_',$chatLi['ambient']);
if(in_array($chatAmbient,$chatArrayControl))
{
	if(isSet($htmlLiner[$chatAmbient])) $htmlLiner[$chatAmbient].=$chatLi['chat'];
	else $htmlLiner[$chatAmbient]=$chatLi['chat'];
}
}

ksort($htmlLiner);
$template->htmlLiner = $htmlLiner;
$template->user = $currentUser;
$template->currentDate = date("D");


	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
