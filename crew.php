<?php


function getmicrotime(){
list( $usec, $sec) = explode( " ", microtime());
return ( ( float)$usec + ( float)$sec);
} 


$start_time = getmicrotime();
 

session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
PG::updatePresence($_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);
$vali = new validator();

if(isSet($_GET['equi']))
{
		$template = new PHPTAL('TEMPLATES/cdb_organigramma.htm');
		$equi = $vali->killchars($_GET['equi']);
		
		if(isSet($_GET['section']))
		{
		$sez = $vali->killchars($_GET['section']);
		$sez = str_replace(array('CN','TS','IN','ME','SC'),array('Comando e Navigazione','Tattica e Sicurezza','Ingegneria','Medica','Scientifica'),$sez);
		$ral = "AND rankCode IN (SELECT prio FROM pg_ranks WHERE Rsezione = '$sez')";
		}else $ral = "";
		
		if(isSet($_GET['ranks']))
		{
		$rank = $vali->killchars($_GET['ranks']);
	//	$rank = str_replace(array('OF','TS','IN','ME','SC'),array('Comando e Navigazione','Tattica e Sicurezza','Ingegneria','Medica','Scientifica'),$sez);
		if($rank == 'OFplus') $mral = "AND (SELECT COUNT(*) FROM pgDotazioni WHERE pgID = pg_users.pgID AND doatazioneType='MEDAL' AND dotazioneIcon = 18)";
		if($rank == 'OF') $mral = "AND rankCode >= 411";
		if($rank == 'sOF') $mral = "AND rankCode < 411";
		if($rank == 'ENT') $mral = "AND pgAuthOMA = 'O'";
		if($rank == 'JM') $mral = "AND isMasCapable=1 AND pgAuthOMA = 'JM'";
		if($rank == 'M') $mral = "AND pgAuthOMA = 'M'";
		if($rank == 'MOD') $mral = "AND pgAuthOMA = 'MM'";
		if($rank == 'GM') $mral = "AND pgAuthOMA = 'SM'";
		if($rank == 'ADM') $mral = "AND pgAuthOMA = 'A'";
		}else $mral = "";
		
		$personale = array('CIV' => array(),'MIL' =>array());
		$crew = mysql_query("SELECT pgID, pgSpecie,pgLastAct,rankCode, pgSesso, pgUser, pgGrado, pgIncarico, pgSezione, pgIncarico,pgLock,  ordinaryUniform, png FROM pg_users,pg_ranks WHERE prio = rankCode AND pgLock=0 AND pgAssign = '$equi' $ral $mral ORDER BY rankerprio DESC, pgUser ASC");
		$oneMonth = $curTime - 2505600;
		
		while ($crewA = mysql_fetch_array($crew))
		{
		$elendar = ($crewA['pgLastAct'] <= $oneMonth && !$crewA['png']) ? 'CIV' : 'MIL';
		$personale[$elendar][] = array(
		'pgID' => $crewA['pgID'],
		'pgUser' => $crewA['pgUser'],
		'pgLastAct' => $crewA['pgLastAct'],
		'pgSpecie' => $crewA['pgSpecie'],
		'pgSesso' => $crewA['pgSesso'],
		'pgGrado' => $crewA['pgGrado'],
		'pgIncarico' => $crewA['pgIncarico'],
		'pgSezione' => $crewA['pgSezione'],
		'pgMostrina' => $crewA['ordinaryUniform'],
		'pgLock' => $crewA['pgLock'],	
		'png' => $crewA['png']	
		);
		}
		$template->personale = $personale;
		$template->equi = $equi;
		
		$crew = mysql_query("SELECT placeID, placeMotto, placeName, placeLogo, place_littleLogo1, placeClass, catGDB, catDISP, catRAP, pgGrado,pgUser,pgNomeC,ordinaryUniform FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON rankCode = prio) ON pgID = placeCommander WHERE placeID = '$equi'");

		while($reissA = mysql_fetch_array($crew))
		//$place = array('placeName' => $reissA['placeName'], 'placeMotto' => $reissA['placeMotto'], 'placeClass' => $reissA['placeClass'], 'placeLittle' => $reissA['place_littleLogo1'],'placeLogo' => $reissA['placeLogo'], 'placeID' => $reissA['placeID']);
		$place = array('placeName' => $reissA['placeName'], 'placeMotto' => $reissA['placeMotto'], 'placeClass' => $reissA['placeClass'], 'placeLittle' => $reissA['place_littleLogo1'],'placeLogo' => $reissA['placeLogo'], 'placeID' => $reissA['placeID'], 'catRAP' => $reissA['catRAP'], 'catGDB' => $reissA['catGDB'], 'catDISP' => $reissA['catDISP'],'commander' => ($reissA['pgGrado'] != NULL) ? $reissA['pgGrado'].' '.$reissA['pgNomeC'].' '.$reissA['pgUser'] : '','uniform' => ($reissA['pgGrado'] != NULL) ? $reissA['ordinaryUniform'] : '');
		$template->place=$place;
		
}

else if(isSet($_GET['prest']))
{
		$template = new PHPTAL('TEMPLATES/cdb_prestavolto.htm');
		$reiss = mysql_query("SELECT pgID,pgOffAvatarC,pgOffAvatarN,pgUser FROM pg_users WHERE pgOffAvatarC <> '' AND pgOffAvatarN <> '' ORDER BY pgOffAvatarC");
		$pg = array();
		
		while($reissA = mysql_fetch_array($reiss))
		$pg[] = $reissA;
		
		$template->pg = $pg;
}
else 
{
		$template = new PHPTAL('TEMPLATES/cdb_assign.htm');
		$reiss = mysql_query("SELECT placeID, placeMotto, placeName, placeLogo, place_littleLogo1, placeClass, catGDB, catDISP, catRAP,pgGrado,pgUser,pgNomeC,ordinaryUniform FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON rankCode = prio) ON pgID = placeCommander WHERE hasCrew = 1 ORDER BY placeType, ordering");
		$places = array();
		
		while($reissA = mysql_fetch_array($reiss))
		$places[] = array('placeName' => $reissA['placeName'], 'placeMotto' => $reissA['placeMotto'], 'placeClass' => $reissA['placeClass'], 'placeLittle' => $reissA['place_littleLogo1'],'placeLogo' => $reissA['placeLogo'], 'placeID' => $reissA['placeID'], 'catRAP' => $reissA['catRAP'], 'catGDB' => $reissA['catGDB'], 'catDISP' => $reissA['catDISP'],'commander' => ($reissA['pgGrado'] != NULL) ? $reissA['pgGrado'].' '.str_replace("'","\'",$reissA['pgNomeC']).' '.str_replace("'","\'",$reissA['pgUser']) : '','uniform' => ($reissA['pgGrado'] != NULL) ? $reissA['ordinaryUniform'] : '');
		
		$template->places = $places;
		
}




$template->user = $currentUser;
$template->userSM = (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) ? 'yes' : 'no' ;
$template->userSL = (PG::mapPermissions('SL',$currentUser->pgAuthOMA)) ? true : false;
 $template->currentDate = $currentDate;
 $template->currentStarDate = $currentStarDate;
// $template->gameName = $gameName;
// $template->gameVersion = $gameVersion;
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