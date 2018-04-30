<?php
session_start();
$a = microtime();
if (!isSet($_SESSION['pgID'])) { header("Location:http://www.startrekfederation.it"); exit;}
    
include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW

$template = new PHPTAL('TEMPLATES/main.htm'); 
$currentUser = new PG($_SESSION['pgID']);
if ($currentUser->pgAuthOMA == 'BAN'){header("Location:http://www.youtube.com/watch?v=wZZ7oFKsKzY"); exit;}
$toLocation= (isSet($_GET['l'])) ? addslashes($_GET['l']) : $currentUser->pgLocation;
if($toLocation == '') $toLocation = 'BAVO';
$currentUser->setPresenceInto($toLocation);

if(isSet($_GET['message']) && $_GET['message'] == 'no_auth') $template->noauth=true;

// location Per sbarco imbarco

$currentLocationQ = mysql_query("SELECT placeID,placeAlert,weather,overridePlanetMap, placeRotationTime,placeRotationOffset, placeName,placeLogo, placeMap1, placeMap2, placeMap3, placeMapSupport1,placeMapSupport2,placeMapSupport3, catGDB, catDISP, catRAP, placeType, warp,note, attracco, pointerL  FROM pg_places WHERE placeID = '$toLocation'");
if(mysql_affected_rows()) $currentLocation = mysql_fetch_array($currentLocationQ);

$pointerL = ($currentLocation['pointerL']);

$template->showSbarcoImbarco = false;
if($currentLocation['attracco'] != '') {$template->toHangar = PG::getHangar($currentLocation['attracco']);}
else if($currentLocation['attracco'] == '' && $pointerL != '')
	if(count($currentUser->getLimitrofi())>1) $template->showSbarcoImbarco = true;


if($currentLocation['placeType'] == 'Pianeta')
{
	$loca = mysql_query("SELECT locID, locName, planetSub, icon, (SELECT COUNT(*) FROM pg_users WHERE pgRoom = locID AND pgLastAct >= ".($curTime-1800).") as counterPG FROM fed_ambient WHERE ambientType <> 'DEFAULT' AND ambientLocation = '".$currentLocation['placeID']."' ORDER BY locName");
	
	$locationsPlanet= array();
	$locationsPlanet[1]= array();
	$locationsPlanet[2]= array();
	$locationsPlanet[3]= array();
	while ($locar = mysql_fetch_array($loca))
	{
		$locationsPlanet[$locar['planetSub']][] = array ('locID' => $locar['locID'], 'locName' => addslashes($locar['locName']), 'icon' => $locar['icon'],'counterPG' => $locar['counterPG']);
	}
	
	$a = $currentLocation['placeRotationTime'];
	$b = $currentLocation['placeRotationOffset'];
	$i = time();
	$trascorse = (($i/3600)+2+$b) % $a;
	$pM = date("i",$i);
	$template->clock =  $trascorse.':'.$pM;
	
	$template->weather =  $currentLocation['weather'];
	$template->locationsPlanet = $locationsPlanet;
}
	
// NAVE ATTRACCATA 

//$sba = mysql_query("SELECT count(*) FROM pg_places WHERE sector = ");

// illumin. dbase
mysql_query("(SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper IN ('FL','CIV','HE') AND topicLastTime > ".$currentUser->pgLastVisit.") UNION (SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper = '".$currentUser->pgAssign."' AND topicLastTime > ".$currentUser->pgLastVisit.");");

$template->setDBOn = (mysql_affected_rows()) ? true : false;

mysql_query("SELECT 1 FROM fed_pad WHERE paddDeletedTo = 0 AND paddTo = ".($currentUser->ID)." AND paddRead = 0 AND paddTitle NOT LIKE '::special::%'");
$template->incomingPadd = (mysql_affected_rows()) ? true : false;

mysql_query("SELECT 1 FROM fed_sussurri WHERE susTo = ".($currentUser->ID)." AND reade = 0");
$template->incomingSuss = (mysql_affected_rows() > 0) ? true : false;

$acurTime = $curTime-3600;
$nexTime = $curTime+43200;
$res = mysql_query("SELECT evID,event,date,sender,place,pgUser,placeName FROM calendar_events,pg_places,pg_users WHERE pgID = sender AND placeID = place AND date BETWEEN $acurTime AND $nexTime AND place = '$toLocation' ORDER BY date");
$events = array();
while($ra = mysql_fetch_assoc($res)) $events[] = $ra;


$template->events = $events;
$template->curTime = $curTime;
$template->placeID = $currentLocation['placeID'];
$template->placeName = strtoupper($currentLocation['placeName']);
$template->placeType = $currentLocation['placeType'];
$template->placeMap1 = ($currentLocation['placeMap1']);
$template->overridePlanetMap = ($currentLocation['overridePlanetMap']) ? true : false;
$template->Map1 = ($currentLocation['placeMapSupport1']);

$template->Map2 = ($currentLocation['placeMapSupport2']);
$template->Map3 = ($currentLocation['placeMapSupport3']);
$template->placeMap2 = ($currentLocation['placeMap2']);
$template->placeMap3 = ($currentLocation['placeMap3']);
$template->alertCSS = str_replace('greenAlert',"",$currentLocation['placeAlert']);
$template->note = nl2br($currentLocation['note']);


$template->user = $currentUser;
if (PG::mapPermissions('G',$currentUser->pgAuthOMA)) $template->isStaff = true;
	 
if (PG::mapPermissions('JM',$currentUser->pgAuthOMA)) $template->mapAdd2 = true;
 
if(strpos($_SERVER['HTTP_USER_AGENT'],'iPad') != 0) $template->isIpad = true;

$template->gameOptions = $gameOptions;
$template->currentStarDate = $currentStarDate;
$template->gameName = $gameName;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
$template->gameServiceInfo = $gameServiceInfo;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
