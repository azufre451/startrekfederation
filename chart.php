<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 
include('includes/markerClass.php');

if(isSet($_SESSION['pgID'])) PG::updatePresence($_SESSION['pgID']);

$vali = new validator();
 	
	

	$template = new PHPTAL('TEMPLATES/chart2_sector.htm');
	$subChart = (isSet( $_GET['ct'])) ? ((  $_GET['ct'] == 'A' ) ?  $_GET['ct'] : 'D') : 'D';

	$pgID = $_SESSION['pgID'];
	$ra = mysql_fetch_array(mysql_query("SELECT placeName,pointerL,placeType, place_littleLogo1, placeClass FROM pg_places WHERE placeID = (SELECT pgLocation FROM pg_users WHERE pgID = $pgID)"));
	
	if($ra['pointerL'] != '')
	{
		$coords = explode(':',$ra['pointerL']);
		$ipo = explode(';',$coords[1]);
	}	
	else $ipo =  explode(';',"-1;-1");
	 
	$template->yPos = $ipo;
	
	$ra = mysql_query("SELECT pointerL,placeType FROM pg_places WHERE pointerL NOT IN ('A:','D:') AND attracco ='' AND pointerL LIKE '$subChart:%' ");
	$markers = array();
	while ($res = mysql_fetch_array($ra))
	{	

		if (! array_key_exists($res['pointerL'], $markers))
			$markers[$res['pointerL']] = array();
		$markers[$res['pointerL']][] = $res['placeType'];
	}
	
	$markersOrder = array();
	foreach($markers as $mk => $mklist)
	{
		$uniQ = array_unique($mklist);
		if (count($uniQ) == 1)
			$type = $uniQ[0];
		else
			$type = 'MIX';

		$coords = explode(':',$mk);
		$ele = explode(';',$coords[1]);
		$markersOrder[] = array($ele[0],$ele[1],$type);
	}

	
	$template->markers = json_encode($markersOrder);
	$template->gameOptions = $gameOptions;
	$template->subChart=$subChart;
	try 
	{
		echo $template->execute();
	}
	catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>
