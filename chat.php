<?php
session_start();
if (!isSet($_SESSION['pgID'])) {header("location:index.php?login=do"); exit; }

include('includes/app_include.php');
include('includes/validate_class.php');
include_once('includes/abilDescriptor.php');
include('includes/notifyClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 
include('includes/markerClass.php');
include('includes/cdbClass.php');

$iu=0;



$vali = new validator();  
$template = new PHPTAL('TEMPLATES/mainChat.htm');
$currentUser = new PG($_SESSION['pgID'],1);

if ($currentUser->pgBavo)
{	$ambient = 'BAVO';}

else{

	if ($currentUser->pgAuthOMA == 'BAN'){header("Location:http://www.youtube.com/watch?v=wZZ7oFKsKzY"); exit;}
	$ambient = (isSet($_GET['amb'])) ? $vali->killchars(htmlentities(stf_real_escape($_GET['amb']))) : NULL;
}

$currentUser->setPresenceIntoChat($ambient);

$currentLocation = PG::getLocation($currentUser->pgLocation);
$template->alertCSS = $currentLocation['placeAlert'];

// location


$currentAmbient = Ambient::getAmbient($ambient);
$currentAmbient['descrizione'] = CDB::bbcode($currentAmbient['descrizione']);
$template->placeName = strtoupper($currentLocation['placeName']);
$template->ambient = $currentAmbient;

/* Check Permissions to enter */

$allowed=false;

if ((int)($currentAmbient['chatPwd']))
{
	$template->protect_on = true;
	
	if(PG::mapPermissions('SM',$currentUser->pgAuthOMA)){$allowed=true; $template->owner_of_private = true;} 
	
	if($currentAmbient['chatPwd'] == $_SESSION['pgID']){$allowed=true; $template->owner_of_private = true;}
	
	else{
		$res1= mysql_query("SELECT 1 FROM fed_ambient_auth WHERE pgID = ".$currentUser->ID." AND locID = '".$currentAmbient['locID']."'");
		if(mysql_affected_rows()) $allowed=true;
	} 
	 
	if(!$allowed){
		header('Location:main.php?message=no_auth');
		exit;
	}
}


if($currentAmbient['ambientType'] == 'DECKMAP')
{
	$resLocations = mysql_query('SELECT locID, locName FROM fed_ambient WHERE ambientLevel_deck = \''.$currentAmbient['ambientLevel_deck'].'\' AND ambientLocation = \''.$currentAmbient['ambientLocation'].'\' AND ambientType <> \'DECKMAP\' ORDER BY locName');

	
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['locID']] = $resLoc['locName'];
	$template->connectedPlaces = $locArray;
}
elseif($currentAmbient['ambientType'] == 'SALA_OLO'){

	$oloRankFilter = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? '' : 'WHERE masked = 0'; 

	$ranks=array();
	$ranks['Default']['norank'] = array('note' => 'Nessun Ologrado');
	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks $oloRankFilter ORDER BY rankerprio DESC");
	while($myA = mysql_fetch_array($my))
		$ranks[$myA['aggregation']][$myA['prio']] = array('uniform'=>$myA['ordinaryUniform'], 'note' => $myA['Note']);
	$template->ranks = $ranks;


	
	$resPgPresenti = mysql_query("SELECT pgID, pgUser FROM pg_users WHERE pgRoom = '".$currentAmbient['locID']."' AND pgLastAct >= ".($curTime-1800)." ORDER BY pgUser");
	
	$people = array();
	while($resa = mysql_fetch_array($resPgPresenti))
	$people[] = $resa;
	$template->people = $people;
	}
elseif($currentAmbient['ambientType'] == 'SALA_TEL' || $currentAmbient['ambientType']=='PLANCIA' || $currentAmbient['ambientType'] == 'INFERMERIA')
{

	

	$resPgPresenti = mysql_query("SELECT pgID, pgUser, pgAvatarSquare FROM pg_users WHERE pgRoom = '".$currentAmbient['locID']."' AND pgLastAct >= ".($curTime-1800)." ORDER BY pgUser");
	
	$people = array();
	while($resa = mysql_fetch_array($resPgPresenti))
	$people[] = $resa;

	$resLocations = mysql_query("SELECT locID,locName,placeID,placeName FROM fed_ambient,pg_places,pg_users WHERE pgRoom = locID AND pgLastAct >= ".($curTime-1800). " AND ambientLocation = placeID AND ambientType <> 'DEFAULT' AND pointerL = (SELECT pointerL FROM pg_places,fed_ambient WHERE ambientLocation = placeID AND locID = '".$currentAmbient['locID']."')");
	
	$resLocationsAll = mysql_query("SELECT locID,locName,placeID,placeName FROM fed_ambient,pg_places WHERE ambientLocation = placeID AND ambientType <> 'DEFAULT' AND pointerL = (SELECT pointerL FROM pg_places,fed_ambient WHERE ambientLocation = placeID AND locID = '".$currentAmbient['locID']."') ORDER BY locName");
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeName']][] = array('a' => $resLoc['locID'], 'b' => $resLoc['locName']);
	$template->locations = $locArray;

	$locArray2=array();
	while($resLoc = mysql_fetch_array($resLocationsAll))
	$locArray2[$resLoc['placeName']][] = array('a' => $resLoc['locID'], 'b' => $resLoc['locName']);
	$template->locationsAll = $locArray2;
	

	$template->people = $people;
	
	if($currentAmbient['ambientType']=='PLANCIA')
	{

	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places WHERE placePlancia <> '' AND attracco='' ORDER BY placeName");
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	$template->locationsA = $locArray;
	}

}
$template->getterURL = ($currentAmbient['ambientType'] == 'ALLOGGIO') ? 'ajax_chatGetterN.php' : 'ajax_chatGetter.php';

// illumin. dbase
mysql_query("(SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper IN ('FL','CIV','HE') AND topicLastTime > ".$currentUser->pgLastVisit.") UNION (SELECT 1 FROM cdb_cats,cdb_topics WHERE catCode = topicCat AND catSuper = '".$currentUser->pgAssign."' AND topicLastTime > ".$currentUser->pgLastVisit.");");
 

mysql_query("SELECT 1 FROM fed_pad WHERE paddDeletedTo = 0 AND paddTo = ".($currentUser->ID)." AND paddRead = 0 AND paddTitle NOT LIKE '::special::%'");
$template->incomingPadd = (mysql_affected_rows()) ? true : false;

mysql_query("SELECT 1 FROM fed_sussurri WHERE susTo = ".($currentUser->ID)." AND reade = 0");
$template->incomingSuss = (mysql_affected_rows() > 0) ? true : false;

$template->incomingNoti = (NotificationEngine::getMyNotifications($currentUser->ID)> 0) ? true : false;
$template->setDBOn = (NotificationEngine::getCDBUpdates($currentUser->ID,$currentUser->pgLocation)> 0) ? true : false;

// position and users

$userPresent= array();
if($currentAmbient['locationable'])
{
	$markers= new MarkerCollection();
	$ra = mysql_query("SELECT pgID,pgUser,pgCoord FROM pg_users WHERE pgLastAct >= ".($curTime-1800)." AND pgRoom = '".$currentAmbient['locID']."'");
	
	while ($re = mysql_fetch_array($ra))
	{ 
		$po = explode(';',$re['pgCoord']);
		$userPresent[] = array('pgID' => $re['pgID'],'pgUser' => $re['pgUser']);
		$markers->addMarker($po[0],$po[1],$re['pgUser']);
	}
	$template->markers = $markers->getmark();
}
else
{
	
	$ra = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLastAct >= ".($curTime-1800)." AND pgRoom = '".$currentAmbient['locID']."'");
	
	while ($re = mysql_fetch_array($ra)) $userPresent[] = $re;
}

$template->userPresent = $userPresent;

$maxTime = time()-3600;
//$masterCondition = (PG::mapPermissions("M",$currentUser->pgAuthOMA)) ? '' : "AND (type <> 'APM' OR sender = ".$_SESSION['pgID'].')';
$adminCondition = (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) ? '' : "AND (type <> 'MASTERSPEC' OR sender = ".$_SESSION['pgID']." OR specReceiver = ".$_SESSION['pgID'].") AND (type <> 'DICERSPEC' OR sender = ".$_SESSION['pgID']." OR specReceiver = ".$_SESSION['pgID'].")";


$chatLines = mysql_query("SELECT IDE,chat,time,type,privateAction,dicerAbil,dicerOutcome,sender FROM federation_chat WHERE ambient = '$ambient' AND time > $maxTime AND (type <> 'SPECIFIC' OR sender = ".$_SESSION['pgID'].") $adminCondition ORDER BY time ASC");

$diceOutcomes = array();
$htmlLiner=''; $MAX = 0; $lastTime = 0;
$mpi=array(); 
while($chatLi = mysql_fetch_array($chatLines))
{
	if($chatLi['type'] != 'AUDIO' && $chatLi['type'] != 'AUDIOE' && $chatLi['type'] != 'SERVICE' && $chatLi['type'] != 'MPI' && !$chatLi['privateAction']) $htmlLiner .= $chatLi['chat'];
	elseif($chatLi['type'] == 'MPI'){ $rtp=explode('|',$chatLi['chat']); $mpi[] = array('type'=>$rtp[0],'ref'=> $rtp[1]); }

	if(!isSet($minID)) $minID = $chatLi['IDE'];
	if($chatLi['IDE'] > $MAX) $MAX = $chatLi['IDE'];
	if($chatLi['time'] > $lastTime && $chatLi['sender'] != $_SESSION['pgID']) $lastTime = $chatLi['time'];


	if ($chatLi['type'] == 'DICERSPEC' && $chatLi['dicerAbil'] != ''){
		

		$a = new abilDescriptor($chatLi['sender']);
		$abi = $a->abilDict[$chatLi['dicerAbil']];
		$stat = $a->explaindice($chatLi['dicerAbil']);
		$ara = $stat['ara'];
		$locale = array('F' => 'Fallimento','FC' => 'Fallimento Critico', 'S' => 'Successo', 'SC' => 'Successo Critico', 'DF' => 'Fortuna Critica');

		$diceOutcomes[] = array('recID'=>$chatLi['IDE'],'pgID'=>$chatLi['sender'],'pgUser' => PG::getSomething($chatLi['sender'],'username'),'outcome' => (($chatLi['dicerOutcome'] == 99) ? "*" : $chatLi['dicerOutcome']), 'abID' => $chatLi['dicerAbil'], 'abName' => $abi['abName'], 'abImage' => $abi['abImage'],'threshold' => $stat['vs'],'outcomeW' => (($chatLi['dicerOutcome'] == 99) ? "Fortuna" : $locale[$ara[$chatLi['dicerOutcome']]] ));
	} 

}

$template->gameOptions = $gameOptions;
$template->diceEvents = $diceOutcomes; 
$template->htmlLiner = $htmlLiner;
$template->mpi = $mpi;
//echo $htmlLiner;exit;
$template->maxVIS = $MAX;
$template->lastTime = $lastTime;

$template->minID = isSet($minID) ? $minID : 0;
$template->getAudio = $currentUser->audioEnable;
$template->residualLuckyPoints=PG::getSomething($currentUser->ID,'upgradePoints')['pgSpecialistPoints'];
$template->user = $currentUser;
$template->currentStarDate = $currentStarDate;
$template->leastOlo = (PG::mapPermissions("O",$currentUser->pgAuthOMA)) ? true : false;

if (PG::mapPermissions('G',$currentUser->pgAuthOMA) && !$currentUser->png){ 
	$template->isStaff = true;

	mysql_query("SELECT 1 FROM pg_users_presence WHERE pgID = ".$currentUser->ID." AND value <> 0");
	if (mysql_affected_rows() <= 5) $template->presenceForce = true;
}


if (PG::mapPermissions('JM',$currentUser->pgAuthOMA) && (PG::mapPermissions('SL',$currentUser->pgAuthOMA) || PG::isMasCapable($currentUser->ID))) $template->isMasteringJuniorMasterOrMod = true;
if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->isMaster = true;
if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->isSuperMaster = true;

//if (PG::mapPermissions('SM',$currentUser->pgAuthOMA) || $currentAmbient['locID'] == PG::getSomething($_SESSION['pgID'],'pgAlloggioRealID')) $template->protectable = true;

if(substr($_SERVER['REQUEST_URI'],-4,4) == '%261') $template->showM = '1';

$template->gameName = $gameName;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
//$template->tips = $tips;
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
