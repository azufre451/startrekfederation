<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$term = addslashes($_GET['term']);

$res = mysql_query("SELECT name,NCC,class,fleet FROM fed_ships WHERE status IN ('ACTIVE','INACTIVE','REFIT') AND name LIKE 'U.S.S. $term%' or name LIKE '$term%' OR class LIKE '$term%' OR NCC LIKE '%$term%'");
$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = array('value' => $row['name'].', '.$row['NCC'],'label' => $row['name'].', '.$row['NCC'], 'desc' => 'Classe '.$row['class'].', '.$row['fleet'].'a Flotta','icon' => 'http://miki.startrekfederation.it/SigmaSys/personal/ostevik/Vars/test_intrepid.png');
}
echo json_encode($aar);
//echo var_dump($aar);
?>