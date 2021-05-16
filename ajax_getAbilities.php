<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
$term = stf_real_escape($_POST['term']);

 $res= mysql_query("SELECT image, descript, SUBSTR(image,-6) as orda FROM pg_brevetti_assign,pg_brevetti WHERE brev=brevID AND owner = $term ORDER BY orda DESC");
 echo mysql_error();

$aar = array();
while ($row = mysql_fetch_array($res)) $aar[] = $row;

echo json_encode($aar);
include('includes/app_declude.php');
?>