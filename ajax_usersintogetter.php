<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$term = addslashes($_POST['amb']);

$resPgPresenti = mysql_query("SELECT pgID, pgUser, pgAvatar FROM pg_users WHERE pgRoom = '".$term."' AND pgLastAct >= ".($curTime-1800)." ORDER BY pgUser");
	
$people = array();
while($resa = mysql_fetch_array($resPgPresenti))
$people[] = $resa;

echo json_encode($people);
//echo var_dump($aar);
?>