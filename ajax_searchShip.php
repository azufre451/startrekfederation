<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$term = stf_real_escape($_GET['term']);

$res = mysql_query("(SELECT name,NCC,class,fleet FROM fed_ships WHERE status IN ('ACTIVE','INACTIVE','REFIT') AND name LIKE 'U.S.S. $term%' or name LIKE '$term%' OR class LIKE '$term%' OR NCC LIKE '%$term%') UNION (SELECT placeName,'','','' FROM pg_places WHERE placeName LIKE '%$term%')");
$aar = array();
while ($row = mysql_fetch_array($res)) {
	$nam= ($row['NCC'] != '') ? $row['name'].', '.$row['NCC'] :  $row['name'];
$aar[] = array('value' => $nam,'label' => $nam, 'desc' => 'Classe '.$row['class'].', '.$row['fleet'].'a Flotta','icon' => 'http://oscar.stfederation.it/SigmaSys/personal/ostevik/Vars/test_intrepid.png');
}
echo json_encode($aar);
//echo var_dump($aar);
?>