<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");
    include('includes/app_include.php');
	include('includes/validate_class.php');
	include("includes/PHPTAL/PHPTAL.php"); //NEW 
	
if ($_GET['get'] == 'quarters') $template = new PHPTAL('TEMPLATES/quarter.htm');
else if ($_GET['get'] == 'holodeck') $template = new PHPTAL('TEMPLATES/holodeck.htm');
else header('Location:main.php');

$currentUser = new PG($_SESSION['pgID']);
$toLocation = $currentUser->pgLocation;
//urrentUser->setPresenceInto($toLocation);
// location Per sbarco imbarco
$currentLocationQ = mysql_query("SELECT placeID,placeAlert, placeName,placeMap1, placeType FROM pg_places WHERE placeID = '$toLocation'");
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

if ($_GET['get'] == 'quarters') 
{
	$query = "SELECT DISTINCT locID,locName,ambientNumber,ambientLevel_deck, (SELECT COUNT(*) FROM pg_users WHERE pgRoom = locID AND pgLastAct >= ".($curTime-1800).") as counterPG FROM fed_ambient,pg_alloggi WHERE  alloggio = locID AND ambientType = 'ALLOGGIO' AND ambientLocation = '".$currentLocation['placeID']."' ORDER BY ambientLevel_deck, ambientNumber ASC";
	$limi = mysql_query($query);
	$limitrofi = array();
	
	$max=array();
	while ($la = mysql_fetch_array($limi)) 
	{
		$allo = $la['locID'];
		$thisAllo = array("AL" => $la, "US" => array());
		
		$alloQ = mysql_query("SELECT pgUser,pgMostrina,rankCode,ordinaryUniform, pgGrado, pgSezione FROM pg_ranks,pg_users,pg_alloggi WHERE prio=rankCode AND pg_alloggi.pgID = pg_users.pgID AND alloggio = '$allo'"); 
		
		
		
		while($people= mysql_fetch_array($alloQ))
		$thisAllo["US"][] = $people;
	
		
		if(isSet($max[$la['ambientLevel_deck']])){
			if (count($thisAllo["US"]) > $max[$la['ambientLevel_deck']]) $max[$la['ambientLevel_deck']] = count($thisAllo['US']);}
		else $max[$la['ambientLevel_deck']] = count($thisAllo['US']);
		
		
		
		$limitrofi[$la['ambientLevel_deck']][] = $thisAllo;
		
	}
	
	function p20($i){return $i*20;}
	
	$template->max = array_map("p20",$max);
	
	
	
}

//if ($_GET['get'] == 'quarters') $query = "SELECT locID,locName,ambientLevel_deck,pgMostrina,rankCode,ordinaryUniform, pgGrado, pgSezione, (SELECT COUNT(*) FROM pg_users WHERE pgRoom = locID AND pgLastAct >= ".($curTime-1800).") as counterPG FROM fed_ambient,pg_users,pg_ranks WHERE  pgAlloggio = locID AND ambientType = 'ALLOGGIO' AND prio = rankCode AND ambientLocation = '".$currentLocation['placeID']."' ORDER BY ambientLevel_deck, ambientNumber ASC";

else if ($_GET['get'] == 'holodeck')
{
	$query = "SELECT locID,locName,ambientLevel_deck FROM fed_ambient WHERE ambientType = 'SALA_OLO' AND ambientLocation = '".$currentLocation['placeID']."' ORDER BY ambientLevel_deck, locName";
	$limi = mysql_query($query);
	$limitrofi = array();
	while ($la = mysql_fetch_array($limi))$limitrofi[$la['ambientLevel_deck']][] = $la;
}


$template->places = $limitrofi;


$template->placeName = strtoupper($currentLocation['placeName']);
$template->placeMap1 = ($currentLocation['placeMap1']);
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