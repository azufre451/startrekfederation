<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');
include('includes/markerClass.php');

$x = $_POST['x'];
$y = $_POST['y'];
$ambID = addslashes($_POST['ambID']);
$pos = $x.';'.$y;

$re = mysql_query("UPDATE pg_users SET pgCoord = '$pos' WHERE pgID = ".$_SESSION['pgID']);


	$markers= new MarkerCollection();
	$ra = mysql_query("SELECT pgUser,pgCoord FROM pg_users WHERE pgLastAct >= ".($curTime-1800)." AND pgRoom = '$ambID'");
	
	while ($re = mysql_fetch_array($ra))
	{
		$po = explode(';',$re['pgCoord']);
		$markers->addMarker($po[0],$po[1],$re['pgUser']);
	}
	
echo json_encode($markers->getmark());
?>