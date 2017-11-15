<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php");
 exit;
}include('includes/app_include.php');
if(isSet($_GET['mode']) && $_GET['mode'] == "1")
{
$term = addslashes($_GET['term']);
$res = mysql_query("SELECT placeID, placeName,sector FROM pg_places WHERE sector <> 0 AND pointer <> '' AND (placeName LIKE '$term%' OR sector LIKE '$term%')");
$aar = array();
while ($row = mysql_fetch_array($res)) {$aar[] = $row['placeName'];}
}

elseif(isSet($_GET['mode']) && $_GET['mode'] == "3") // PLANET COORDINATES
{
$term = addslashes($_POST['term']);
$res = mysql_query("SELECT pointerL FROM pg_places WHERE placeName = '$term'");
$aar = array();
while ($row = mysql_fetch_array($res)) {$aar = explode(';',$row['pointerL']);}
}

else
{
$term = addslashes($_POST['term']);
$res = mysql_query("SELECT placeType as N0, placeName as N1, placeLogo as N2,place_littleLogo1 as N3, placeAuxName as N4, placeAlignment as N5, placeLocation as N6, placePopulation as N8, placeAux1 as N7, ordinaryUniform as N10b, pgNomeC as N10c, pgUser as N10d, pgID as N10e, placeRotationTime as N9, placeMotto as N11, placeClass as N12, externalLink as N13 FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON prio=rankCode) ON pgID = placeCommander WHERE attracco ='' AND pointerL = '$term'");
$aar = array();
while ($row = mysql_fetch_assoc($res)) {$aar[] = $row;}
}
echo json_encode($aar);
//echo var_dump($aar);
?>