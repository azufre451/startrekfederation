<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');
$ambient = $_POST['ambient'];
$last = $_POST['lastID'];
$getAudio = isSet($_POST['getAudio']) ? $_POST['getAudio'] : 1;
$aar = array();

$res = mysql_query('SELECT 1 FROM fed_pad WHERE paddDeletedTo = 0 AND paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle NOT LIKE "::special::%" LIMIT 1');

if(mysql_affected_rows()) $aar['NP'] = 1;

$resNotify = mysql_query('SELECT paddText,extraField FROM fed_pad WHERE paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle LIKE "::special::%" LIMIT 1');
if(mysql_affected_rows()){$aal = mysql_fetch_array($resNotify); $etm = explode('::',$aal['paddText']); $aar['NOTIFY']['TEXT'] = ($etm[1]); $aar['NOTIFY']['TITLE'] = ($etm[0]);  $aar['NOTIFY']['IMG'] = $aal['extraField'];}

$res = mysql_query('SELECT 1 FROM fed_sussurri WHERE susTo = '.$_SESSION['pgID'].' AND reade = 0 LIMIT 1');
$aar['SU'] = (mysql_affected_rows()) ? true : false;


$res = mysql_query('SELECT placeAlert,pgAuthOMA,isMasCapable FROM pg_users,pg_places WHERE pgLocation = placeID AND pgID = '.($_SESSION['pgID']));
if(mysql_affected_rows()){
	$aarA = mysql_fetch_array($res);
	$aar['AL'] = $aarA['placeAlert'];
	$pgAuthOMA = $aarA['pgAuthOMA'];
	$isMasCapable = $aarA['isMasCapable'];
}

$maxTime = time()-3600;
// $masterCondition = (PG::mapPermissions("M",$pgAuthOMA) || $isMasCapable) ? '' : "AND (type <> 'APM' OR sender = ".$_SESSION['pgID'].')';
$adminCondition = (PG::mapPermissions("SM",$pgAuthOMA)) ? '' : "AND (type <> 'MASTERSPEC' OR sender = ".$_SESSION['pgID']." OR specReceiver = ".$_SESSION['pgID'].")";

//$masterCondition = '';
$chatLines = mysql_query("SELECT IDE,chat,time,type FROM federation_chat WHERE ambient = '$ambient' AND IDE > $last AND time > $maxTime AND (type <> 'SPECIFIC' OR sender = ".$_SESSION['pgID'].") $adminCondition ORDER BY time ASC");
$htmlLiner='';
 $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines)){
	if($chatLi['type'] != 'AUDIO' && $chatLi['type'] != 'AUDIOE') $htmlLiner .= $chatLi['chat'];
	else if($getAudio && ($chatLi['type'] == 'AUDIO' || $chatLi['type'] == 'AUDIOE')) $htmlLiner .= "<script>playSound('".$chatLi['chat']."','".(($chatLi['type'] == 'AUDIOE') ? 'extern' : '')."');</script>";
	if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
}
$aar['CH'] = $htmlLiner;
$aar['LCH'] = $MAX;
echo json_encode($aar);
//echo var_dump($aar);
?>