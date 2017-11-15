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
	
	$pgID = $_SESSION['pgID'];
	$ra = mysql_fetch_array(mysql_query("SELECT placeName,pointerL,placeType, place_littleLogo1, placeClass FROM pg_places WHERE placeID = (SELECT pgLocation FROM pg_users WHERE pgID = $pgID)"));
	
	if($ra['pointerL'] != '')
		$ipo = explode(';',$ra['pointerL']);
		
	else $ipo =  explode(';',"-1;-1");
	 
	$template->yPos = $ipo;
	
	$ra = mysql_query("SELECT DISTINCT pointerL FROM pg_places WHERE pointerL <> ''");
	$markers = array();
	while ($res = mysql_fetch_array($ra))
	{	$ele = explode(';',$res['pointerL']); 
		$markers[] = array($ele[0],$ele[1]);
	}
	
	
	// if($id!=0){
	// $markers= new MarkerCollection();
	// $ra = mysql_query("SELECT placeName,pointer,placeType, place_littleLogo1, placeClass FROM pg_places WHERE sector = $id");
	
	// while ($re = mysql_fetch_array($ra))
	// {
		// $po = explode(';',$re['pointer']);
		// $markers->addMarker($po[0],$po[1],$re['placeName']);
	// }
	// $template->markers = $markers->getmark();
	// } 
	
	$template->markers = json_encode($markers);


	try 
	{
		echo $template->execute();
	}
	catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>