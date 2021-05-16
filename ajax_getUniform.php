<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$emo = stf_real_escape($_POST['term']);
$id = $_SESSION['pgID'];
$user = new PG($_SESSION['pgID']);

$sex=str_replace("t","m",strtolower(substr($user->pgSesso,0,1)));
$sez=$user->pgSezione;
$aar = array();

	$my = mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $id");
	$myA = mysql_fetch_array($my);
	$parameter = $myA['rankCode'];

	if ($emo == "SER") $query ="SELECT ordinaryUniform AS 'unif' FROM pg_ranks,pg_users WHERE prio=rankCode AND pgID=$id";
	if ($emo == "HIG") $query ="SELECT dressUniform AS 'unif' FROM pg_ranks,pg_users WHERE prio=rankCode AND pgID=$id";
	if ($emo == "TAT") $query ="SELECT tacticalUniform AS 'unif' FROM pg_ranks,pg_users WHERE prio=rankCode AND pgID=$id";
	if ($emo == "DES") $query ="SELECT desertUniform AS 'unif' FROM pg_ranks,pg_users WHERE prio=rankCode AND pgID=$id";
	if ($emo == "POL") $query ="SELECT 'POL' AS 'unif'";
	if ($emo == "FAT") $query ="SELECT faticaUniform AS 'unif' FROM pg_ranks,pg_users WHERE prio=rankCode AND pgID=$id";
	if ($emo == "EVA") $query ="SELECT 'EVA' AS 'unif'";
	if ($emo == "NBC") $query ="SELECT 'NBC' AS 'unif'";
	if ($emo == "VOL") $query ="SELECT 'VOL' AS 'unif'";
	if ($emo == "CAM") $query ="SELECT 'CAM' AS 'unif'";
	if ($emo == "CIV") $query ="SELECT 'CIV' AS 'unif'";
	
	$ra = mysql_fetch_array(mysql_query($query));
	$re = mysql_fetch_array(mysql_query("SELECT uniform FROM pg_uniforms WHERE mostrina = '".$ra['unif']."'"));
	
	$arr['unif']='TEMPLATES/img/ranks/'.$ra['unif'].'.png';
	$arr['divis']='TEMPLATES/img/uniformi/'.$re['uniform'].$sex.'.png';

echo json_encode($arr);
//echo var_dump($aar);
?>