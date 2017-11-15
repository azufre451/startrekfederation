<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');
$last = $_POST['lastID'];


$aar = array();


$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE IDE > $last ORDER BY time ASC");
$htmlLiner='';
$MAX = 0;
while($chatLi = mysql_fetch_array($chatLines)){	$htmlLiner.=$chatLi['chat'];
	if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
}
$aar['CH'] = $htmlLiner;
$aar['LCH'] = $MAX;
echo json_encode($aar);
?>