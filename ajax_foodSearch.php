<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php");
 exit;
}include('includes/app_include.php');
$term = $_GET['term'];
$res = mysql_query("SELECT foodName FROM foods");
$aar = array();
while ($row = mysql_fetch_array($res)) {$aar[] = $row['foodName'];
}echo json_encode($aar);
//echo var_dump($aar);
?>