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

	if (isSet($_GET['coords']) && (str_contains($_GET['coords'],':')) &&  (str_contains($_GET['coords'],';')))
	{
		$coords = explode(':',addslashes($_GET['coords']));
		// take 0th element of coords (just need first char ;))
		$subChart = $coords[0][0];
	}
	else{
		$ra = mysql_fetch_array(mysql_query("SELECT placeName,pointerL,placeType, place_littleLogo1, placeClass FROM pg_places WHERE placeID = (SELECT pgLocation FROM pg_users WHERE pgID = $pgID)"));
	
		if($ra['pointerL'] != '')
			$coords = explode(':',$ra['pointerL']);

		$subChart = (isSet( $_GET['ct'])) ? ((  $_GET['ct'] == 'A' ) ?  $_GET['ct'] : 'D') : 'D';
	}

	$ipo = (isSet($coords)) ? explode(';',$coords[1]) : explode(';',"-1;-1");
	 
	$template->yPos = $ipo;
	
	$ra = mysql_query("SELECT pointerL,placeType,placeName FROM pg_places WHERE pointerL NOT IN ('A:','D:') AND attracco ='' AND pointerL LIKE '$subChart:%' ORDER BY placeName");
	$markers = array();
	while ($res = mysql_fetch_array($ra))
	{	

		if (! array_key_exists($res['pointerL'], $markers))
			$markers[$res['pointerL']] = array();
		$markers[$res['pointerL']][] = $res['placeName'];
	}
	
	$markersOrder = array();
	foreach($markers as $mk => $mklist)
	{
		$type = str_replace("'","&apos;",(implode('<br />',$mklist)));

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
