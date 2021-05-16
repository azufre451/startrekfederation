<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

$term = stf_real_escape($_GET['term']);
 

if (isSet($_GET['extGropus'])) $q="(SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%') UNION (SELECT groupLabel as pgUser FROM pg_groups WHERE groupLabel LIKE '%$term%')";
elseif(isSet($_GET['wpng']))
	$q="(SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%') UNION (SELECT CONCAT(UCASE(LEFT(pngSurname, 1)),LCASE(SUBSTRING(pngSurname, 2))) FROM png_incarichi WHERE pngSurname LIKE '%$term%')";
elseif(isSet($_GET['epng_only']))
	$q="(SELECT pgUser FROM pg_users WHERE png=1 AND pgUser LIKE '$term%') UNION (SELECT CONCAT(UCASE(LEFT(pngSurname, 1)),LCASE(SUBSTRING(pngSurname, 2))) FROM png_incarichi WHERE pngSurname LIKE '%$term%')";
else $q = "SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%'";

 $res = mysql_query($q);

$aar = array();


if (strpos('ufficiali superiori',strtolower($term)) !== false )
	$aar[] = '[Ufficiali Superiori]';
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pgUser'];
}
echo json_encode($aar);
//echo var_dump($aar);
?>