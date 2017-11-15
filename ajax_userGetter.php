<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("../includes/PHPTAL/PHPTAL.php"); 

$term = addslashes($_GET['term']);

$res = mysql_query("SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%'");

$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pgUser'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>