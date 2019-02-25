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

$curPGID=$_SESSION['pgID'];
 
$resPgPresenti = mysql_query("SELECT pgID, pgUser, pgMostrina,pgAuthOMA,pgLock FROM pg_users,pg_ranks WHERE pgLastAct >= ".($curTime-1800)." AND rankCode = prio AND pgAuthOMA <> 'BAN' AND pgID <> '$curPGID' ORDER BY pgUser");
  
while($pgPres = mysql_fetch_assoc($resPgPresenti)){	$aar[] = array('pgID'=> $pgPres['pgID'],'label'=> $pgPres['pgUser'],'role'=> 
	($pgPres['pgLock']) ? 'L' : ( (PG::mapPermissions('A',$pgPres['pgAuthOMA'])) ? 'A' : ( (PG::mapPermissions('M',$pgPres['pgAuthOMA'])) ? 'M' : ( ( PG::mapPermissions('G',$pgPres['pgAuthOMA']) ) ? 'G' : '')   ) ) 
	,'pgMostrina'=> $pgPres['pgMostrina']);}




$aar['PGP'] = $aar;

if ((int)$vinculum == 0 or (int)$vinculum == 7)
{
	$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE IDE > $last AND ((susFrom = ".$_SESSION['pgID']." AND susTo NOT IN (0,7)) OR susTo = $vinculum OR susTo = '$curPGID') ORDER BY time ASC");
	if($focused) mysql_query('UPDATE fed_sussurri SET reade = 1 WHERE reade=0 AND susTo = '.$curPGID);
	$htmlLiner='';
	$MAX = 0;
	while($chatLi = mysql_fetch_assoc($chatLines)){	$htmlLiner.=$chatLi['chat'];
		if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
	}
	$aar['CH'] = $htmlLiner;
	$aar['LCH'] = $MAX;

}
echo json_encode($aar);
include('includes/app_declude.php');
?>