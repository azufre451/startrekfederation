<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');

//if($_SESSION['pgID'] == '1005') session_destroy();

$last = addslashes($_POST['lastID']);
$vinculum = addslashes($_POST['vinculum']);
$focused = isSet($_POST['focused']) ? addslashes($_POST['focused']) : 0;

$aar = array();


$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE IDE > $last AND ((susFrom = ".$_SESSION['pgID']." AND susTo NOT IN (0,7)) OR susTo = $vinculum OR susTo = ".$_SESSION['pgID'].") ORDER BY time ASC");
if($focused) mysql_query('UPDATE fed_sussurri SET reade = 1 WHERE reade=0 AND susTo = '.$_SESSION['pgID']);
$htmlLiner='';
$MAX = 0;
while($chatLi = mysql_fetch_array($chatLines)){	$htmlLiner.=$chatLi['chat'];
	if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
}
$aar['CH'] = $htmlLiner;
$aar['LCH'] = $MAX;
echo json_encode($aar);
?>