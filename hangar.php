<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");
    include('includes/app_include.php');

include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");

$template = new PHPTAL('TEMPLATES/hangar.htm');
$currentUser = new PG($_SESSION['pgID']);
$toLocation = $currentUser->pgLocation;
//urrentUser->setPresenceInto($toLocation);
// location Per sbarco imbarco
$currentLocationQ = mysql_query("SELECT placeID,placeAlert, placeName, sector, placeType, pointer, sector FROM pg_places WHERE placeID = '$toLocation'");
if(mysql_affected_rows()) $currentLocation = mysql_fetch_array($currentLocationQ);
else header('Location:main.php');
// illumin. dbase
mysql_query("(SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper IN ('FL','CIV','HE') AND topicLastTime > ".$currentUser->pgLastVisit.") UNION (SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper = '".$currentUser->pgAssign."' AND topicLastTime > ".$currentUser->pgLastVisit.");");
$template->setDBOn = (mysql_affected_rows()) ? true : false;
// illumin, padd
mysql_query("SELECT 1 FROM fed_pad WHERE paddDeletedTo = 0 AND paddTo = ".($currentUser->ID)." AND paddRead = 0 AND paddTitle NOT LIKE '::special::%'");
$template->incomingPadd = (mysql_affected_rows()) ? true : false;

mysql_query("SELECT 1 FROM fed_sussurri WHERE susTo = ".($currentUser->ID)." AND reade = 0");
$template->incomingSuss = (mysql_affected_rows() > 0) ? true : false;
//limitrofe:

$limi = mysql_query("SELECT placeName,placeID,place_littleLogo1, placeMap1 FROM pg_places WHERE placeType = 'Navetta' AND attracco = '".$currentLocation['placeID']."' ORDER BY placeName");
$limitrofi = array();
while ($la = mysql_fetch_array($limi))$limitrofi[] = $la;
$template->places = $limitrofi;
$template->placeName = strtoupper($currentLocation['placeName']);
$template->alertCSS = str_replace('greenAlert',"",$currentLocation['placeAlert']);
$template->user = $currentUser;
$template->currentStarDate = $currentStarDate;
$template->gameName = $gameName;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
$template->gameServiceInfo = $gameServiceInfo;
if (PG::mapPermissions('SL',$currentUser->pgAuthOMA)) $template->isStaff = true;
	try
	{
		echo $template->execute();
	}	catch (Exception $e){	echo $e;
	}include('includes/app_declude.php');
	?>