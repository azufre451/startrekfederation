<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');


$ambient = $_POST['ambient'];
$last = $_POST['lastID'];
$getAudio = isSet($_POST['getAudio']) ? $_POST['getAudio'] : 1;
$aar = array();

$res =mysql_fetch_assoc ( mysql_query('SELECT padID,paddTitle,pgAvatarSquare FROM fed_pad,pg_users WHERE paddFrom = pg_users.pgID AND  paddDeletedTo = 0 AND paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle NOT LIKE "::special::%" ORDER BY paddTime DESC LIMIT 1 ') ) ;

if(mysql_affected_rows()){ $aar['NP'] = 1; $aar['NPtitle'] = $res['paddTitle']; $aar['NPavatar'] = $res['pgAvatarSquare'];}

 

$resNotify = mysql_query('SELECT paddText,extraField FROM fed_pad WHERE paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle LIKE "::special::%" LIMIT 1');
if(mysql_affected_rows()){$aal = mysql_fetch_array($resNotify); $etm = explode('::',$aal['paddText']); $aar['NOTIFY']['TEXT'] = ($etm[1]); $aar['NOTIFY']['TITLE'] = ($etm[0]);  $aar['NOTIFY']['IMG'] = $aal['extraField'];}

$res = mysql_query('SELECT 1 FROM fed_sussurri WHERE susTo = '.$_SESSION['pgID'].' AND reade = 0 LIMIT 1');
$aar['SU'] = (mysql_affected_rows()) ? true : false;


$res = mysql_query('SELECT placeAlert,pgAuthOMA FROM pg_users,pg_places WHERE pgLocation = placeID AND pgID = '.($_SESSION['pgID']));
if(mysql_affected_rows()){
	$aarA = mysql_fetch_array($res);
	$aar['AL'] = $aarA['placeAlert'];
	
	$pgAuthOMA = $aarA['pgAuthOMA'];
}

$resSession = mysql_query("SELECT sessionMaxChars FROM federation_sessions WHERE sessionStatus = 'ONGOING' AND sessionMaxChars <> 0 AND sessionPlace = '$ambient'");
if (mysql_affected_rows()){
	$rea = mysql_fetch_assoc($resSession);
	$aar['MC'] = $rea['sessionMaxChars'];
}

$res = mysql_query("SELECT ambientTemperature,ambientLight,ambientLightColor FROM fed_ambient WHERE locID = '$ambient'");
if(mysql_affected_rows()){
	$aarA = mysql_fetch_array($res);
	$aar['TEMP'] = $aarA['ambientTemperature'];
	$aar['LIGHT'] = $aarA['ambientLight'];
	$aar['COLOR'] = $aarA['ambientLightColor'];
}



$maxTime = time()-3600;
// $masterCondition = (PG::mapPermissions("M",$pgAuthOMA) || $isMasCapable) ? '' : "AND (type <> 'APM' OR sender = ".$_SESSION['pgID'].')';
$adminCondition = (PG::mapPermissions("SM",$pgAuthOMA)) ? '' : "AND (type <> 'MASTERSPEC' OR sender = ".$_SESSION['pgID']." OR specReceiver = ".$_SESSION['pgID'].") AND (type <> 'DICERSPEC' OR sender = ".$_SESSION['pgID']." OR specReceiver = ".$_SESSION['pgID'].")";


$diceOutcomes= array();
//$masterCondition = '';
$chatLines = mysql_query("SELECT IDE,chat,sender,time,type,dicerOutcome,dicerAbil,dicerThr FROM federation_chat WHERE ambient = '$ambient' AND IDE > $last AND time > $maxTime AND (type <> 'SPECIFIC' OR sender = ".$_SESSION['pgID'].") $adminCondition ORDER BY time ASC");
$htmlLiner='';
 $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines)){
	if($chatLi['type'] != 'AUDIO' && $chatLi['type'] != 'AUDIOE') $htmlLiner .= $chatLi['chat'];
	else if($getAudio && ($chatLi['type'] == 'AUDIO' || $chatLi['type'] == 'AUDIOE')) $htmlLiner .= "<script>playSound('".$chatLi['chat']."','".(($chatLi['type'] == 'AUDIOE') ? 'extern' : '')."');</script>";
	if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
	if ($chatLi['type'] == 'DICERSPEC' && $chatLi['dicerAbil'] != ''){
		

		$a = new abilDescriptor($chatLi['sender']);
		$abi = $a->abilDict[$chatLi['dicerAbil']];
		$stat = $a->explaindice($chatLi['dicerAbil']);
		$ara = $stat['ara'];
		$locale = array('F' => 'Fallimento','FC' => 'Fallimento Critico', 'S' => 'Successo', 'SC' => 'Successo Critico');

		$diceOutcomes[] = array('recID'=>$chatLi['IDE'],'pgID'=>$chatLi['sender'],'pgUser' => PG::getSomething($chatLi['sender'],'username'),'outcome' => $chatLi['dicerOutcome'], 'abID' => $chatLi['dicerAbil'], 'abName' => $abi['abName'], 'abImage' => $abi['abImage'],'threshold' => $stat['vs'],'outcomeW' => $locale[$ara[$chatLi['dicerOutcome']]]);
	} 
}

$aar['DICER'] = $diceOutcomes; 
$aar['CH'] = $htmlLiner;
$aar['LCH'] = $MAX;
echo json_encode($aar);
include('includes/app_declude.php');
//echo var_dump($aar);
?>