<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("../includes/PHPTAL/PHPTAL.php"); 

$term = addslashes($_GET['term']);

$res = mysql_query("SELECT pgUser,pgAvatar,pgIncarico,placeName FROM pg_users,pg_places WHERE pgAssign = placeID AND pgUser LIKE '$term%'");

$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[$row['pgUser']]['value'] = $row['pgUser'];
$aar[$row['pgUser']]['desc'] = $row['pgIncarico']." - ".$row['placeName'];
$aar[$row['pgUser']]['icon'] = $row['pgAvatar'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>