<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$term = stf_real_escape($_GET['term']);

$res = mysql_query("SELECT placeID, placeName,sector FROM pg_places WHERE placeName LIKE '$term%' OR sector LIKE '$term%'");
$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['placeID'].' - '.$row['placeName'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>