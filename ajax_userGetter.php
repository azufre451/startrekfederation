<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

$term = addslashes($_GET['term']);

if (isSet($_GET['extGropus'])) $q="(SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%') UNION (SELECT groupLabel as pgUser FROM pg_groups WHERE groupLabel LIKE '%$term%')";

else $q = "SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%'";

 $res = mysql_query($q);

$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pgUser'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>