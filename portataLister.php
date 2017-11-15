<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");
    include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
$template = new PHPTAL('TEMPLATES/portataNavi.htm');
$currentUser = new PG($_SESSION['pgID']); 
//urrentUser->setPresenceInto($toLocation);
// location Per sbarco imbarco  

if(!$currentLocation = $currentUser->getLocationOfUser()) header('Location:main.php');

// illumin. dbase
mysql_query("SELECT * FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper <> 'MA' AND topicLastTime > ".$currentUser->pgLastVisit);
$template->setDBOn = (mysql_affected_rows()) ? true : false;
// illumin, padd
mysql_query("SELECT 1 FROM fed_pad WHERE paddDeletedTo = 0 AND paddTo = ".($currentUser->ID)." AND paddRead = 0 AND paddTitle NOT LIKE '::special::%'");
$template->incomingPadd = (mysql_affected_rows()) ? true : false;

mysql_query("SELECT 1 FROM fed_sussurri WHERE susTo = ".($currentUser->ID)." AND reade = 0");
$template->incomingSuss = (mysql_affected_rows() > 0) ? true : false;
//limitrofe:  
 
$template->places = $currentUser->getLimitrofi();
$template->placeName = strtoupper($currentLocation['placeName']);
$template->alertCSS = str_replace('greenAlert',"",$currentLocation['placeAlert']);
$template->user = $currentUser;
$template->currentStarDate = $currentStarDate;
$template->gameName = $gameName;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
$template->tips = $tips;
$template->gameServiceInfo = $gameServiceInfo;
if (PG::mapPermissions('SL',$currentUser->pgAuthOMA)) $template->isStaff = true;
	try
	{
		echo $template->execute();
	}	catch (Exception $e){	echo $e;
	}include('includes/app_declude.php');
	?>