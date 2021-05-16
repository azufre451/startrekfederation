<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 
$vali = new validator();  
$template = new PHPTAL('TEMPLATES/chatLog.htm');
$currentUser = new PG($_SESSION['pgID']);
$ambient = (isSet($_GET['amb'])) ? $vali->killchars(htmlentities(stf_real_escape($_GET['amb']))) : NULL;
//$currentUser->setPresenceIntoChat($ambient);

//$currentLocation = PG::getLocation($currentUser->pgLocation);
//emplate->alertCSS = str_replace('greenAlert',"",$currentLocation['placeAlert']);

// location



$currentAmbient = Ambient::getAmbient($ambient);

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
$template->ambient = $currentAmbient;


//chat

$masterCondition = (PG::mapPermissions("M",$currentUser->pgAuthOMA)) ? '' : "AND (type <> 'APM' OR sender = ".$_SESSION['pgID'].')';

$chatLines = mysql_query("SELECT IDE,chat,time FROM federation_chat WHERE ambient = '$ambient' AND time BETWEEN $from AND $toe AND type NOT IN('AUDIO','SPECIFIC','SERVICE') AND privateAction = 0 $masterCondition ORDER BY time");
$htmlLiner='';
$ide=0;
while($chatLi = mysql_fetch_array($chatLines))
{
	if(!$ide) $ide=$chatLi['IDE'];
	$htmlLiner.=$chatLi['chat'];
}

$template->htmlLiner = $htmlLiner;
$template->ide = $ide;

$template->user = $currentUser;
if(PG::mapPermissions("M",$currentUser->pgAuthOMA)) $template->userIsM = true;
$template->currentStarDate = $currentStarDate;


	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
