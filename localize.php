<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");
    
include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
PG::updatePresence($_SESSION['pgID']);


$currentUser = new PG($_SESSION['pgID']);
if ($currentUser->pgAuthOMA == 'BAN'){header("Location:http://images1.wikia.nocookie.net/__cb20111112213451/naruto/images/f/f0/Sasuke.jpeg"); exit;}
//$currentUser->setPresenceInto("000");

//$template->alertCSS = str_replace('greenAlert',"",PG::getLocationAlert($currentUser->pgLocation));

// location
$vali = new validator();
if(isSet($_GET['place']))
{
	$template = new PHPTAL('TEMPLATES/localize.htm');
	$lieu = $vali->killchars(htmlentities(addslashes($_GET['place'])));
	$currentLocation = PG::getLocation($lieu);
	$lieuName = $currentLocation['placeName'];
	$lieuID = $currentLocation['placeID'];
	$orderbyClause = (isSet($_GET['ambientPriority'])) ? "locName ASC, rankerprio DESC" : "rankerprio DESC, pgUser ASC";
	
	$resPgPresenti = mysql_query("SELECT pgID, pgAvatar, pgUser,pgSpecie,pgLock,pgSesso,pgLastAct, pgMostrina, pgGrado, pgSezione, locName, locID,ambientType FROM pg_users,pg_ranks,fed_ambient WHERE pgLocation = '$lieu' AND pgLastAct >= ".($curTime-1800)." AND rankCode = prio AND pgRoom = locID AND pgAuthOMA <> 'BAN' ORDER BY $orderbyClause");

	//echo "SELECT pgUser FROM pg_users WHERE pgLocation = $lieu AND pgLastAct >= ".(time()-2000);
	$people = array();
	$template->refreshRate = (mysql_affected_rows() > 20) ? '50' : '30';

	while($resa = mysql_fetch_array($resPgPresenti))
	{
	mysql_query("SELECT 1 FROM federation_chat WHERE sender = ".$resa['pgID']." AND ambient = '".$resa['locID']."' AND type <> ('SPECIFIC') AND time >= ".($curTime-900));
	if(mysql_affected_rows()) $pgIC = true;
	else $pgIC = false;
	
	$people[] = array(
	'ID' => $resa['pgID'],
	'pgUser' => $resa['pgUser'],
	'pgMostrina' => $resa['pgMostrina'],
	'pgAvatar' => $resa['pgAvatar'],
	'pgSpecie' => $resa['pgSpecie'],
	'pgSesso' => $resa['pgSesso'],
	'pgGrado' => $resa['pgGrado'],
	'pgSezione' => $resa['pgSezione'],
	'pgPlace' => $resa['locName'],
	'pgPlaceI' => $resa['locID'],
	'ambientType' => $resa['ambientType'],
	'pgLock' => $resa['pgLock'],
	'pgInChat' => $pgIC
	);
	}
	$template->people = $people;
	$template->placeName = $lieuName;
	$template->ungoable = (0) ? true : false;
//	$template->ungoable = ($currentUser->pgLocation != $lieuID && !PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? true : false;
	
	
}
else {
$template = new PHPTAL('TEMPLATES/localize_gen.htm');

$places = mysql_query("SELECT placeID, placeName,place_littleLogo1, (SELECT COUNT(*) FROM pg_users WHERE pgLocation = placeID AND pgLastAct >= ".(time()-1800).") AS contoTot FROM pg_places WHERE (SELECT COUNT(*) FROM pg_users WHERE pgLocation = placeID AND pgLastAct >= ".(time()-1800).") > 0");
$placeArray = array();

while($res = mysql_fetch_array($places)) $placeArray[]=$res;
$template->places = $placeArray;


}



// illumin. dbase

$template->online = timeHandler::getOnline(NULL);
 $template->user = $currentUser;
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
