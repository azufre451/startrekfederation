<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$res = mysql_query("SELECT pgID, pgUser FROM pg_users WHERE pgLastAct >= ".($curTime-1800)." ORDER BY pgUser");

$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row;
}
echo json_encode($aar);

include('includes/app_declude.php');
?>