<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
$disc=(isSet($_GET['filter'])) ? $_GET['filter'] : '';
$term = addslashes(@$_GET['term']);

if($disc=="PNG")
$res = mysql_query("SELECT pgUser FROM pg_users WHERE png=1 AND pgUser LIKE '$term%'");
else if($disc == "PREST") $res= mysql_query("SELECT pgUser FROM pg_users WHERE pgID <> '".($_SESSION['pgID'])."' AND pgOffAvatarC = '".addslashes(strtolower($_POST['term2']))."' AND pgOffAvatarN = '".addslashes(strtolower($_POST['term1']))."'");
else $res = mysql_query("SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%'");

$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pgUser'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>